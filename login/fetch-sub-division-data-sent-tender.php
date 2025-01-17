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

    //fetch sub-division
    if (isset($_POST['subDivisionIdSentTender']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $subDivisionId = intval($_POST['subDivisionIdSentTender']); // Sanitize the input

        if ($subDivisionId == 0) {
            // Prepare the SQL statement
            // $stmtFetchSubDivision = $db->prepare("SELECT * FROM sub_division
            // WHERE status = 1 ");

            $subDivisionId = [0];
            $subDivisionName = ["All"];

            echo json_encode([
                'success' => true,
                'subDivisionId' => $subDivisionId,
                'subDivisionName' => $subDivisionName,
            ]);
        } else {
            // Prepare the SQL statement
            $stmtFetchSubDivision = $db->prepare("SELECT * FROM sub_division
            WHERE id = ? AND status = 1 ");
            $stmtFetchSubDivision->bind_param("i", $subDivisionId);

            // Execute the statement
            if (!$stmtFetchSubDivision->execute()) {
                throw new Exception("Query execution failed: " . $stmtFetchSubDivision->error);
            }

            // Get the result
            $resultSubDivision = $stmtFetchSubDivision->get_result();

            // Check if any product is found
            if ($resultSubDivision->num_rows > 0) {
                $subDivisionId = [];
                $subDivisionName = [];

                // Fetch product details
                while ($rowSubDivision = $resultSubDivision->fetch_assoc()) {
                    $subDivisionId[] = $rowSubDivision['id'];
                    $subDivisionName[] = $rowSubDivision['subdivision'];
                }

                // Return success response
                echo json_encode([
                    'success' => true,
                    'subDivisionId' => $subDivisionId,
                    'subDivisionName' => $subDivisionName,
                ]);
            } else {
                // Return error response if no product is found
                echo json_encode([
                    'success' => false,
                    'error' => 'No division found'
                ]);
            }
            // Close the statement
            $stmtFetchSubDivision->close();
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