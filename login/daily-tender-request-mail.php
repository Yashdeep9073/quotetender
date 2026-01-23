<?php
require_once "../vendor/autoload.php";
require_once "../env.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use Dompdf\Dompdf;

include("db/config.php");

// Fetch data
$query = "SELECT m.name, m.firm_name, m.mobile, m.email_id, department.department_name, ur.tenderID, ur.status,
ur.due_date, ur.created_at, sm.name, ur.allotted_at FROM user_tender_requests ur 
inner join members m on ur.member_id= m.member_id 
left join members sm on ur.selected_user_id= sm.member_id
inner join department on ur.department_id = department.department_id
where (ur.status='Requested' or ur.status='Sent' or ur.status='Allotted' )";

$requestedTenders = $sentTenders = $allottedTenders = [];
$result = mysqli_query($db, $query);

while ($row = mysqli_fetch_row($result)) {
    if ($row[6] == 'Requested') {
        $requestedTenders[] = $row;
    }
    if ($row[6] == 'Sent') {
        $sentTenders[] = $row;
    }
    if ($row[6] == 'Allotted') {
        $allottedTenders[] = $row;
    }
}

// === Generate Excel File ===
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Tender Requests");

// === Style for Column Headers ===
$headerStyleArray = [
    'font' => [
        'bold' => true,
        'color' => ['argb' => 'FFFFFFFF'], // White text
    ],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FF4CAF50'], // Green background
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
    ],
];

// === Style for Section Headers (Requested, Sent, Allotted) ===
$sectionStyleArray = [
    'font' => [
        'bold' => true,
        'color' => ['argb' => 'FF000000'], // Black text
    ],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFFFC107'], // Yellow background
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
    ],
];

// Set Column Headers
$sheet->setCellValue('A1', 'User Name')
    ->setCellValue('B1', 'Firm Name')
    ->setCellValue('C1', 'Mobile')
    ->setCellValue('D1', 'Email')
    ->setCellValue('E1', 'Department')
    ->setCellValue('F1', 'Tender ID')
    ->setCellValue('G1', 'Status')
    ->setCellValue('H1', 'Due Date')
    ->setCellValue('I1', 'Requested/Updated Date')
    ->setCellValue('J1', 'Alotted User');
// Apply Header Style
$sheet->getStyle('A1:J1')->applyFromArray($headerStyleArray);

// Start row index after headers
$rowIndex = 2;

// === Add Requested Tenders ===
$sheet->setCellValue('A' . $rowIndex, '--- Requested Tenders ---');
$sheet->mergeCells('A' . $rowIndex . ':J' . $rowIndex); // Merge cells for the section header
$sheet->getStyle('A' . $rowIndex . ':J' . $rowIndex)->applyFromArray($sectionStyleArray);
$rowIndex++;

foreach ($requestedTenders as $item) {
    $sheet->setCellValue('A' . $rowIndex, $item[0])
        ->setCellValue('B' . $rowIndex, $item[1])
        ->setCellValue('C' . $rowIndex, $item[2])
        ->setCellValue('D' . $rowIndex, $item[3])
        ->setCellValue('E' . $rowIndex, $item[4])
        ->setCellValue('F' . $rowIndex, $item[5])
        ->setCellValue('G' . $rowIndex, $item[6])
        ->setCellValue('H' . $rowIndex, date_format(date_create($item[7]), 'd-m-Y'))
        ->setCellValue('I' . $rowIndex, date_format(date_create($item[8]), 'd-m-Y'))
        ->setCellValue('J' . $rowIndex, '');
    $rowIndex++;
}

// === Add Sent Tenders ===
$sheet->setCellValue('A' . $rowIndex, '--- Sent Tenders ---');
$sheet->mergeCells('A' . $rowIndex . ':J' . $rowIndex);
$sheet->getStyle('A' . $rowIndex . ':J' . $rowIndex)->applyFromArray($sectionStyleArray);
$rowIndex++;

foreach ($sentTenders as $item) {
    $sheet->setCellValue('A' . $rowIndex, $item[0])
        ->setCellValue('B' . $rowIndex, $item[1])
        ->setCellValue('C' . $rowIndex, $item[2])
        ->setCellValue('D' . $rowIndex, $item[3])
        ->setCellValue('E' . $rowIndex, $item[4])
        ->setCellValue('F' . $rowIndex, $item[5])
        ->setCellValue('G' . $rowIndex, $item[6])
        ->setCellValue('H' . $rowIndex, date_format(date_create($item[7]), 'd-m-Y'))
        ->setCellValue('I' . $rowIndex, date_format(date_create($item[8]), 'd-m-Y'))
        ->setCellValue('J' . $rowIndex, '');
    $rowIndex++;
}

// === Add Allotted Tenders ===
$sheet->setCellValue('A' . $rowIndex, '--- Allotted Tenders ---');
$sheet->mergeCells('A' . $rowIndex . ':J' . $rowIndex);
$sheet->getStyle('A' . $rowIndex . ':J' . $rowIndex)->applyFromArray($sectionStyleArray);
$rowIndex++;

foreach ($allottedTenders as $item) {
    $sheet->setCellValue('A' . $rowIndex, $item[0])
        ->setCellValue('B' . $rowIndex, $item[1])
        ->setCellValue('C' . $rowIndex, $item[2])
        ->setCellValue('D' . $rowIndex, $item[3])
        ->setCellValue('E' . $rowIndex, $item[4])
        ->setCellValue('F' . $rowIndex, $item[5])
        ->setCellValue('G' . $rowIndex, $item[6])
        ->setCellValue('H' . $rowIndex, date_format(date_create($item[7]), 'd-m-Y'))
        ->setCellValue('I' . $rowIndex, date_format(date_create($item[8]), 'd-m-Y'))
        ->setCellValue('J' . $rowIndex, $item[9]);
    $rowIndex++;
}


// Save Excel File
$excelFilePath = "tender_requests.xlsx";
$writer = new Xlsx($spreadsheet);
$writer->save($excelFilePath);

// === Generate PDF File ===
$html = "<h1>Requested Tenders</h1><table border='1' cellpadding='5' cellspacing='0'>
<tr>
    <th>User Name</th><th>Firm Name</th><th>Mobile</th><th>Email</th>
    <th>Department</th><th>Tender ID</th><th>Status</th><th>Due Date</th><th>Requested Date</th>
</tr>";
foreach ($requestedTenders as $item) {
    $html .= "<tr>
        <td>{$item[0]}</td><td>{$item[1]}</td><td>{$item[2]}</td><td>{$item[3]}</td>
        <td>{$item[4]}</td><td>{$item[5]}</td><td>{$item[6]}</td>
        <td>" . date_format(date_create($item[7]), 'd-m-Y') . "</td>
        <td>" . date_format(date_create($item[8]), 'd-m-Y') . "</td>
    </tr>";
}
$html .= "</table>";

$html .= "<h1>Sent Tenders</h1><table border='1' cellpadding='5' cellspacing='0'>
<tr>
    <th>User Name</th><th>Firm Name</th><th>Mobile</th><th>Email</th>
    <th>Department</th><th>Tender ID</th><th>Status</th><th>Due Date</th>
    <th>Requested Date</th>
</tr>";
foreach ($sentTenders as $item) {
    $html .= "<tr>
        <td>{$item[0]}</td><td>{$item[1]}</td><td>{$item[2]}</td><td>{$item[3]}</td>
        <td>{$item[4]}</td><td>{$item[5]}</td><td>{$item[6]}</td>
        <td>" . date_format(date_create($item[7]), 'd-m-Y') . "</td>
        <td>" . date_format(date_create($item[8]), 'd-m-Y') . "</td>
        
        
    </tr>";
}
$html .= "</table>";


$html .= "<h1>Allotted Tenders</h1><table border='1' cellpadding='5' cellspacing='0'>
<tr>
    <th>User Name</th><th>Firm Name</th><th>Mobile</th><th>Email</th>
    <th>Department</th><th>Tender ID</th><th>Status</th><th>Due Date</th>
    <th>Requested Date</th><th>Allotted User</th><th>Allotted Date</th>
</tr>";
foreach ($allottedTenders as $item) {
    $html .= "<tr>
        <td>{$item[0]}</td><td>{$item[1]}</td><td>{$item[2]}</td><td>{$item[3]}</td>
        <td>{$item[4]}</td><td>{$item[5]}</td><td>{$item[6]}</td>
        <td>" . date_format(date_create($item[7]), 'd-m-Y') . "</td>
        <td>" . date_format(date_create($item[8]), 'd-m-Y') . "</td>
        <td>{$item[9]}</td>
        <td>" . date_format(date_create($item[10]), 'd-m-Y') . "</td>
    </tr>";
}
$html .= "</table>";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$pdfFilePath = "tender_requests.pdf";
file_put_contents($pdfFilePath, $dompdf->output());

// === Send Email with Attachments ===
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = getenv('SMTP_HOST');
$mail->SMTPAuth = true;
$mail->Username = getenv('SMTP_USER_NAME');
$mail->Password = getenv('SMTP_PASSCODE');
$mail->SMTPSecure = "ssl";
$mail->Port = getenv('SMTP_PORT');

// enquiry@dvepl.com
$mail->From = getenv('SMTP_USER_NAME');
$mail->FromName = "Quote Tender";
$mail->addAddress("enquiry@dvepl.com", "Recipient Name");

$mail->isHTML(true);
$mail->Subject = "List of All Tender Requests";
$mail->Body = "Please find the attached PDF and Excel files for the list of tender requests and allotted tenders.";

// Attach PDF and Excel files
$mail->addAttachment($pdfFilePath, "tender_requests.pdf");
$mail->addAttachment($excelFilePath, "tender_requests.xlsx");

try {
    $mail->send();
    echo "Email with PDF and Excel files sent successfully.";
} catch (Exception $e) {
    echo "Mailer Error: " . $mail->ErrorInfo;
}

// Clean up files
unlink($pdfFilePath);
unlink($excelFilePath);
?>