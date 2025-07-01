<?php

ini_set('display_errors', 1);

session_start();


if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}
$name = $_SESSION['login_user'];

include("db/config.php");




// Initialize the row number variable
mysqli_query($db, "SET @row_number = 0;");

// $queryMain = "
//   SELECT 
//     ROW_NUMBER() OVER (ORDER BY ur.created_at) AS sno,
//     ur.id AS t_id, 
//     m.name, 
//     m.member_id, 
//     m.firm_name, 
//     m.mobile, 
//     m.email_id, 
//     department.department_name, 
//     ur.due_date, 
//     ur.file_name, 
//     ur.tenderID, 
//     ur.created_at, 
//     ur.file_name2,
//     ur.reference_code,
//     ur.tentative_cost,
//     ur.tender_no, 
//     ur.status, 
//     s.*, 
//     dv.*, 
//     sd.*
// FROM 
//     user_tender_requests ur
// INNER JOIN 
//     members m ON ur.member_id = m.member_id
// LEFT JOIN  
//     department ON ur.department_id = department.department_id
// LEFT JOIN 
//     section s ON ur.section_id = s.section_id
// LEFT JOIN 
//     division dv ON ur.division_id = dv.division_id
// LEFT JOIN
//     sub_division sd ON ur.sub_division_id = sd.id
// WHERE 
//     ur.status IN ('Sent', 'Allotted') AND ur.delete_tender = '0'
//     AND ur.tenderID IN (
//         SELECT tenderID
//         FROM user_tender_requests
//         WHERE status IN ('Sent', 'Allotted') AND delete_tender = '0'
//         GROUP BY tenderID
//         HAVING COUNT(*) > 1
//     )
// ORDER BY 
//     ur.created_at ASC;
//     ";


// // SQL Query
//     $queryMain = "
//         SELECT 
//          ROW_NUMBER() OVER (ORDER BY ur.created_at) AS sno,
//          ur.id as t_id, 
//          m.name, 
//          m.member_id, 
//          m.firm_name, 
//          m.mobile, 
//          m.email_id, 
//          department.department_name, 
//          ur.due_date, 
//          ur.file_name, 
//          ur.tenderID, 
//          ur.created_at, 
//          ur.file_name2,
//          ur.reference_code,
//          ur.tentative_cost,
//          ur.tender_no, 
//          s.*,
//          dv.*,
//          sd.* 
//      FROM 
//          user_tender_requests ur
//      INNER JOIN 
//          members m ON ur.member_id = m.member_id
//      LEFT JOIN  
//          department ON ur.department_id = department.department_id
//      LEFT JOIN 
//          section s ON ur.section_id = s.section_id
//      LEFT JOIN 
//          division dv ON ur.division_id = dv.division_id
//      LEFT JOIN
//          sub_division sd ON ur.sub_division_id = sd.id
//      INNER JOIN 
//          (
//              SELECT MIN(id) AS min_id, tenderID
//              FROM user_tender_requests
//              WHERE status = 'Sent' AND delete_tender = '0'
//              GROUP BY tenderID
//          ) AS unique_tenders ON ur.id = unique_tenders.min_id
//         $whereClause
//      ORDER BY 
//          ur.created_at ASC;
//          ";

$queryMain = "
    SELECT 
        ROW_NUMBER() OVER (ORDER BY ur.created_at) AS sno,
        ur.id as t_id, 
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
        ur.file_name2,
        ur.reference_code,
        ur.tentative_cost,
        ur.tender_no, 
        s.*, 
        dv.*, 
        sd.*
    FROM 
        user_tender_requests ur
    INNER JOIN 
        members m ON ur.member_id = m.member_id
    LEFT JOIN  
        department ON ur.department_id = department.department_id
    LEFT JOIN 
        section s ON ur.section_id = s.section_id
    LEFT JOIN 
        division dv ON ur.division_id = dv.division_id
    LEFT JOIN
        sub_division sd ON ur.sub_division_id = sd.id
    INNER JOIN 
        (
            SELECT MIN(id) AS min_id
            FROM user_tender_requests sent
            WHERE sent.status = 'Sent' AND sent.delete_tender = '0'
            AND NOT EXISTS (
                SELECT 1 FROM user_tender_requests a
                WHERE a.tenderID = sent.tenderID
                AND a.status = 'Allotted'
                AND a.delete_tender = '0'
            )
            GROUP BY sent.tenderID
        ) AS unique_sent_only ON ur.id = unique_sent_only.min_id
    $whereClause
    ORDER BY ur.created_at ASC;
";


$resultMain = mysqli_query($db, $queryMain);

echo "<pre>";
print_r($resultMain->fetch_all());
?>