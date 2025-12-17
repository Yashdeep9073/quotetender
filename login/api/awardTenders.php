<?php

session_start();
header("Content-Type: application/json"); // Add JSON header
require("../db/config.php");

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['token'])) {
    $token = $_GET['token'];
    $timestamp = $_GET['ts'];
    $signature = $_GET['sig'];

    $secretKey = "!@#$%^&*())_+";
    $expectedSignature = hash_hmac('sha256', $token . $timestamp, $secretKey);

    if (abs(time() - $timestamp) > 300) {
        echo json_encode([
            "status" => 400,
            "error" => "Expired request",
        ]);
        exit;
    }

    if (hash_equals($expectedSignature, $signature)) {
        // Prepare and execute the query
        $stmt = $db->prepare("SELECT DISTINCT
                sm.name, 
                m.email_id, 
                m.mobile, 
                m.firm_name, 
                ur.tender_no, 
                department.department_name,
                ur.name_of_work,
                ur.remarked_at, 
                ur.file_name, 
                ur.id as t_id,
                se.section_name,
                dv.division_name,
                sd.subdivision,
                ur.tenderID,
                ur.remark,
                ur.reference_code,
                MAX(st.state_name) AS state_name,  -- Get state_name from state table, not members
                MAX(ct.city_name) AS city_name    -- Use MAX() for consistency
                FROM 
                    user_tender_requests ur 
                LEFT JOIN
                    members m ON ur.member_id = m.member_id
                LEFT JOIN
                    department ON ur.department_id = department.department_id
                LEFT JOIN
                    section se ON ur.section_id = se.section_id
                LEFT JOIN
                    members sm ON ur.selected_user_id = sm.member_id
                LEFT JOIN
                        division dv ON ur.division_id = dv.division_id
                LEFT JOIN
                        sub_division sd ON ur.sub_division_id = sd.id
                LEFT JOIN
                        state st ON CONVERT(sm.state_code USING utf8mb4) = CONVERT(st.state_code USING utf8mb4)  -- Fix collation
                LEFT JOIN   
                        cities ct ON CAST(sm.city_state AS UNSIGNED) = ct.city_id  -- Convert string to number
                WHERE 
                    ur.remark = 'accepted' AND ur.delete_tender = '0'
                GROUP BY 
                ur.id, 
                sm.name, 
                m.email_id, 
                m.mobile, 
                m.firm_name, 
                ur.tender_no, 
                department.department_name,
                ur.name_of_work,
                ur.remarked_at, 
                ur.file_name, 
                se.section_name,
                dv.division_name,
                sd.subdivision,
                ur.tenderID
                ORDER BY 
                NOW() >= CAST(ur.due_date AS DATE), 
                CAST(ur.remarked_at AS DATE) ASC, 
                ABS(DATEDIFF(NOW(), CAST(ur.due_date AS DATE)));");

        $stmt->execute();

        // Fetch all rows as an associative array
        $result = $stmt->get_result();
        $awardTenders = $result->fetch_all(MYSQLI_ASSOC);

        // Send JSON response
        echo json_encode([
            "status" => 200,
            "data" => $awardTenders,
        ]);
    } else {
        // Send JSON response
        echo json_encode([
            "status" => 400,
            "error" => "Invalid signature",
        ]);
        exit;
    }
} else {
    // Send JSON response
    echo json_encode([
        "status" => 400,
        "error" => "Bad request",
    ]);
    exit;
}

?>