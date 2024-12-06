<?php

session_start();
include("db/config.php");





$query = "SELECT 
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
ur.id,
ur.file_name2 
FROM 
    user_tender_requests ur 
INNER JOIN 
    members m ON ur.member_id= m.member_id
INNER JOIN 
    department ON ur.department_id = department.department_id 
WHERE 
    ur.status= 'Requested' AND ur.delete_tender = '0' 
GROUP BY 
    ur.id
ORDER BY 
    NOW() >= CAST(ur.due_date AS DATE), 
    ABS(DATEDIFF(NOW(), CAST(ur.due_date AS DATE)))";

$result = mysqli_query($db, $query);

$tenders = [];
while($row = mysqli_fetch_assoc($result)){
    $tenders[$row['tenderID']][] = $row;
}
echo '<pre>';
print_r($tenders);

?>