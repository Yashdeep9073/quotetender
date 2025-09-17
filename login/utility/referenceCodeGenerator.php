<?php

function referenceCode($db, $prefix)
{
    // Get current timestamp in YYYYMMDDHHMMSS format
    $timestamp = date('Ymd'); // e.g., '20250725152030' for July 25, 2025, 15:20:30

    // Use a transaction to ensure atomicity
    try {
        $db->begin_transaction();

        // Lock the sequence row
        $stmt = $db->prepare("SELECT last_sequence FROM reference_sequence WHERE id = 1 FOR UPDATE");
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // No sequence row, create one
            $stmt = $db->prepare("INSERT INTO reference_sequence (id, last_sequence) VALUES (1, 0)");
            $stmt->execute();
            $lastSequence = 0;
        } else {
            $row = $result->fetch_assoc();
            $lastSequence = $row['last_sequence'];
        }

        // Increment sequence
        $newSequence = $lastSequence + 1;

        // Update sequence
        $stmt = $db->prepare("UPDATE reference_sequence SET last_sequence = ? WHERE id = 1");
        $stmt->bind_param("i", $newSequence);
        $stmt->execute();

        $db->commit();

        // Format invoice number with prefix, timestamp, and sequence
        $refNumber = sprintf("%s-%s-%05d", $prefix, $timestamp, $newSequence); // e.g., VIS-20250725152030-00001
        return [
            "status" => 201,
            "data" => $refNumber
        ];
    } catch (Exception $e) {
        $db->rollback();
        return [
            "status" => 500,
            "error" => $e->getMessage()
        ];
    }
}

?>