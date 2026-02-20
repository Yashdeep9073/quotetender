<?php
require_once "../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;

include("db/config.php");

/* ==============================
   FETCH DATA
=================================*/

$query = "
SELECT
    m.name,
    m.firm_name,
    m.mobile,
    m.email_id,
    department.department_name,
    ur.tenderID,
    ur.tender_no,
    ur.status,
    ur.due_date,
    ur.created_at,
    sm.name AS allotted_user,
    ur.allotted_at
FROM user_tender_requests ur
INNER JOIN members m ON ur.member_id = m.member_id
LEFT JOIN members sm ON ur.selected_user_id = sm.member_id
INNER JOIN department ON ur.department_id = department.department_id
WHERE ur.status IN ('Requested','Sent','Allotted')
";

$result = mysqli_query($db, $query);

$requestedTenders = [];
$sentTenders = [];
$allottedTenders = [];

while ($row = mysqli_fetch_row($result)) {
    if ($row[7] === 'Requested') {
        $requestedTenders[] = $row;
    } elseif ($row[7] === 'Sent') {
        $sentTenders[] = $row;
    } elseif ($row[7] === 'Allotted') {
        $allottedTenders[] = $row;
    }
}

/* ==============================
   HELPER FUNCTION
=================================*/

function formatDateSafe($date)
{
    return !empty($date) ? date('d-m-Y', strtotime($date)) : '';
}

/* ==============================
   GENERATE EXCEL
=================================*/

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Tender Requests");

$headerStyle = [
    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FF4CAF50']
    ],
];

$sectionStyle = [
    'font' => ['bold' => true],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFFFC107']
    ],
];

// Headers
$sheet->fromArray([
    'User Name',
    'Firm Name',
    'Mobile',
    'Email',
    'Department',
    'Tender ID',
    'CA No',
    'Status',
    'Due Date',
    'Requested Date',
    'Allotted User'
], NULL, 'A1');

$sheet->getStyle('A1:K1')->applyFromArray($headerStyle);

$rowIndex = 2;

/* ==============================
   FUNCTION TO WRITE SECTION
=================================*/

function writeSection($sheet, $title, $data, &$rowIndex, $sectionStyle)
{
    $sheet->setCellValue("A{$rowIndex}", "--- {$title} ---");
    $sheet->mergeCells("A{$rowIndex}:K{$rowIndex}");
    $sheet->getStyle("A{$rowIndex}:K{$rowIndex}")->applyFromArray($sectionStyle);
    $rowIndex++;

    foreach ($data as $item) {
        $sheet->fromArray([
            $item[0],
            $item[1],
            $item[2],
            $item[3],
            $item[4],
            $item[5],
            $item[6],
            $item[7],
            formatDateSafe($item[8]),
            formatDateSafe($item[9]),
            $item[10] ?? ''
        ], NULL, "A{$rowIndex}");

        $rowIndex++;
    }
}

writeSection($sheet, "Requested Tenders", $requestedTenders, $rowIndex, $sectionStyle);
writeSection($sheet, "Sent Tenders", $sentTenders, $rowIndex, $sectionStyle);
writeSection($sheet, "Allotted Tenders", $allottedTenders, $rowIndex, $sectionStyle);

$excelFilePath = "tender_requests.xlsx";
$writer = new Xlsx($spreadsheet);
$writer->save($excelFilePath);

/* ==============================
   GENERATE PDF
=================================*/

function buildTableHtml($title, $data, $includeAllotted = false)
{
    $html = "<h2>{$title}</h2>
    <table border='1' cellpadding='5' cellspacing='0' width='100%'>
    <tr>
        <th>User Name</th>
        <th>Firm Name</th>
        <th>Mobile</th>
        <th>Email</th>
        <th>Department</th>
        <th>Tender ID</th>
        <th>CA No</th>
        <th>Status</th>
        <th>Due Date</th>
        <th>Requested Date</th>";

    if ($includeAllotted) {
        $html .= "<th>Allotted User</th><th>Allotted Date</th>";
    }

    $html .= "</tr>";

    foreach ($data as $item) {
        $html .= "<tr>
            <td>{$item[0]}</td>
            <td>{$item[1]}</td>
            <td>{$item[2]}</td>
            <td>{$item[3]}</td>
            <td>{$item[4]}</td>
            <td>{$item[5]}</td>
            <td>{$item[6]}</td>
            <td>{$item[7]}</td>
            <td>" . formatDateSafe($item[8]) . "</td>
            <td>" . formatDateSafe($item[9]) . "</td>";

        if ($includeAllotted) {
            $html .= "
            <td>{$item[10]}</td>
            <td>" . formatDateSafe($item[11]) . "</td>";
        }

        $html .= "</tr>";
    }

    $html .= "</table><br>";
    return $html;
}

$html = buildTableHtml("Requested Tenders", $requestedTenders);
$html .= buildTableHtml("Sent Tenders", $sentTenders);
$html .= buildTableHtml("Allotted Tenders", $allottedTenders, true);

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$pdfFilePath = "tender_requests.pdf";
file_put_contents($pdfFilePath, $dompdf->output());

/* ==============================
   SEND EMAIL
=================================*/

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = getenv('SMTP_HOST');
    $mail->SMTPAuth = true;
    $mail->Username = getenv('SMTP_USER_NAME');
    $mail->Password = getenv('SMTP_PASSCODE');
    $mail->SMTPSecure = "ssl";
    $mail->Port = getenv('SMTP_PORT');

    $mail->setFrom(getenv('SMTP_USER_NAME'), "DVEPL");
    // $mail->addAddress("enquiry@dvepl.com");
    $mail->addAddress("yashdeep@vibrantick.in");

    $mail->isHTML(true);
    $mail->Subject = "List of All Tender Requests";
    $mail->Body = "Please find attached the PDF and Excel reports.";

    $mail->addAttachment($pdfFilePath);
    $mail->addAttachment($excelFilePath);

    $mail->send();
    echo "Email sent successfully.";

} catch (Exception $e) {
    echo "Mailer Error: " . $mail->ErrorInfo;
}

// Cleanup
unlink($pdfFilePath);
unlink($excelFilePath);