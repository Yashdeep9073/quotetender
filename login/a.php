<?php
require "./db/config.php";

$queryMain = "SELECT
    ur.id,
    m.name,
    m.member_id,
    m.firm_name,
    m.mobile,
    m.email_id,
    department.department_name,
    ur.due_date,
    ur.file_name,
    ur.tenderID,
    ur.created_at,
    ur.file_name2
FROM
    user_tender_requests ur
INNER JOIN
    members m ON ur.member_id = m.member_id
INNER JOIN
    department ON ur.department_id = department.department_id
INNER JOIN
    (
        SELECT MIN(id) AS min_id, tenderID
        FROM user_tender_requests
        WHERE status = 'Requested' AND delete_tender = '0'
        GROUP BY tenderID
    ) AS unique_tenders ON ur.id = unique_tenders.min_id
ORDER BY
    NOW() >= CAST(ur.created_at AS DATE),
    CAST(ur.created_at AS DATE) ASC,
    ABS(DATEDIFF(NOW(), CAST(ur.created_at AS DATE)));
";

$resultMain = mysqli_query($db, $queryMain);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <!-- jQuery UI JS -->
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <!-- Moment.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
</head>
<body>
    <div>
        <label for="min-date">Min Date:</label>
        <input type="text" id="min-date" name="min-date">
        <label for="max-date">Max Date:</label>
        <input type="text" id="max-date" name="max-date">
    </div>
    <table id="example" class="display">
        <thead>
            <tr>
                <th>ID</th>
                <!-- <th>Name</th> -->
                <!-- <th>Member ID</th> -->
                <!-- <th>Firm Name</th> -->
                <!-- <th>Mobile</th> -->
                <!-- <th>Email ID</th> -->
                <th>Department</th>
                <th>Due Date</th>
                <!-- <th>File Name</th> -->
                <th>Tender ID</th>
                <th>Created At</th>
                <!-- <th>File Name 2</th> -->
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($resultMain)) {?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']);?></td>
                    <!-- <td><?php //echo htmlspecialchars($row['name']);?></td> -->
                    <!-- <td><?php //echo htmlspecialchars($row['member_id']);?></td> -->
                    <!-- <td><?php //echo htmlspecialchars($row['firm_name']);?></td> -->
                    <!-- <td><?php //echo htmlspecialchars($row['mobile']);?></td> -->
                    <!-- <td><?php //echo htmlspecialchars($row['email_id']);?></td> -->
                    <td><?php echo htmlspecialchars($row['department_name']);?></td>
                    <td><?php
                        $date = DateTime::createFromFormat('Y-m-d', $row['due_date']);
                        if ($date) {
                            echo htmlspecialchars($date->format('dmy'));
                        } else {
                            echo htmlspecialchars($row['due_date']);
                        }
                    ?></td>
                    <!-- <td><?php // echo htmlspecialchars($row['file_name']);?></td> -->
                    <td><?php echo htmlspecialchars($row['tenderID']);?></td>
                    <td><?php echo htmlspecialchars($row['created_at']);?></td>
                    <!-- <td><?php //echo htmlspecialchars($row['file_name2']);?></td> -->
                </tr>
            <?php }?>
        </tbody>
    </table>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#example').DataTable();

            // Initialize Datepicker
            $("#min-date, #max-date").datepicker({
                dateFormat: "yy-mm-dd",
                changeMonth: true,
                changeYear: true,
                onSelect: function() {
                    table.draw();
                }
            });

            // Custom filtering function
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    var min = $('#min-date').datepicker("getDate");
                    var max = $('#max-date').datepicker("getDate");
                    var startDate = data[4] || 0; // Use data for the start date

                    if (
                        (min === null && max === null) ||
                        (min === null && startDate <= max) ||
                        (min <= startDate && max === null) ||
                        (min <= startDate && startDate <= max)
                    ) {
                        return true;
                    }
                    return false;
                }
            );
        });
    </script>
</body>
</html>