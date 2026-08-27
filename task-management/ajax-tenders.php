<?php
/**
 * AJAX endpoint: search existing tender/query/request records
 * (user_tender_requests) for the Tender/Query task selector.
 *
 * Returns JSON for Select2:
 *   { "results": [ { "id": <utr.id>, "text": "..." }, ... ] }
 */

require_once __DIR__ . '/inc/init.php';

if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
}

$q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
if ($q === '') {
    echo json_encode(['results' => []]);
    exit;
}

$limit = 20;
$like = '%' . $q . '%';

// Any authenticated user may search tender metadata (the selector is only
// rendered on create/edit which are already permission-gated server-side).
$stmt = $db->prepare(
    "SELECT utr.id,
            utr.tenderID,
            utr.reference_code,
            utr.status,
            utr.created_at,
            m.name      AS member_name,
            m.firm_name AS member_firm
       FROM user_tender_requests utr
       LEFT JOIN members m ON m.member_id = utr.member_id
      WHERE utr.delete_tender = '0'
        AND (utr.tenderID LIKE ? OR utr.reference_code LIKE ?
             OR m.name LIKE ? OR m.firm_name LIKE ?)
      ORDER BY utr.id DESC
      LIMIT " . (int) $limit
);
$stmt->bind_param('ssss', $like, $like, $like, $like);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$results = [];
foreach ($rows as $row) {
    $parts = [];
    $parts[] = $row['tenderID'] !== null && $row['tenderID'] !== '' ? $row['tenderID'] : 'No Tender ID';
    if ($row['reference_code'] !== null && $row['reference_code'] !== '') {
        $parts[] = $row['reference_code'];
    }
    if ($row['status'] !== null && $row['status'] !== '') {
        $parts[] = $row['status'];
    }
    $registeredBy = trim((string) $row['member_name']);
    if ($row['member_firm'] !== null && $row['member_firm'] !== '') {
        $registeredBy = $registeredBy !== '' ? $registeredBy . ' (' . $row['member_firm'] . ')' : $row['member_firm'];
    }
    if ($registeredBy !== '') {
        $parts[] = 'Registered by: ' . $registeredBy;
    }
    if ($row['created_at'] !== null) {
        $parts[] = date('d M Y', strtotime($row['created_at']));
    }
    $results[] = [
        'id'   => (int) $row['id'],
        'text' => implode(' | ', $parts),
    ];
}

echo json_encode(['results' => $results]);
