<?php

ini_set('display_errors', 1);
header('Content-Type: application/json');

session_start();


if (!isset($_SESSION["login_user"])) {
    header("location: index.php");
}
$name = $_SESSION['login_user'];

include("db/config.php");


try {

    //fetch division
    if (isset($_POST['divisionIdSentTender']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $divisionId = intval($_POST['divisionIdSentTender']); // Sanitize the input

        if ($divisionId == 0) {
            // Prepare the SQL statement    
            // $stmtFetchDivision = $db->prepare("SELECT * FROM division
            //     WHERE status = 1 ");
            $divisionId = [0];
            $divisionName = ["All"];

            

            echo json_encode([
                'success' => true,
                'divisionId' => $divisionId,
                'divisionName' => $divisionName,
            ]);
        } else {
            // Prepare the SQL statement
            $stmtFetchDivision = $db->prepare("SELECT * FROM division
                WHERE division_id = ? AND status = 1 ");
            $stmtFetchDivision->bind_param("i", $divisionId);

            // Execute the statement
            if (!$stmtFetchDivision->execute()) {
                throw new Exception("Query execution failed: " . $stmtFetchDivision->error);
            }

            // Get the result
            $resultDivision = $stmtFetchDivision->get_result();

            // Check if any product is found
            if ($resultDivision->num_rows > 0) {
                $divisionId = [];
                $divisionName = [];

                // Fetch product details
                while ($rowDivision = $resultDivision->fetch_assoc()) {
                    $divisionId[] = $rowDivision['division_id'];
                    $divisionName[] = $rowDivision['division_name'];
                }

                // Return success response
                echo json_encode([
                    'success' => true,
                    'divisionId' => $divisionId,
                    'divisionName' => $divisionName,
                ]);
            } else {
                // Return error response if no product is found
                echo json_encode([
                    'success' => false,
                    'error' => 'No division found'
                ]);
            }
            // Close the statement
            $stmtFetchDivision->close();
        }




    } else {
        // Return error response for invalid request
        echo json_encode([
            'success' => false,
            'error' => 'Invalid request'
        ]);
    }

} catch (Exception $e) {
    // Return error response for any exception
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred: ' . $e->getMessage()
    ]);
}


?>