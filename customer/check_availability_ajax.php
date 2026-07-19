<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db_connect.php';

$room_id = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
$check_in = sanitize($_POST['check_in'] ?? '');
$check_out = sanitize($_POST['check_out'] ?? '');

if (!$room_id || empty($check_in) || empty($check_out)) {
    echo json_encode(['available' => false, 'message' => 'Invalid parameters provided.']);
    exit;
}

// Double booking query: Check if there's an overlapping booking that is not cancelled
$query = "SELECT COUNT(*) FROM bookings 
          WHERE room_id = ? 
          AND status IN ('Confirmed', 'CheckedIn', 'Pending') 
          AND (
              (check_in <= ? AND check_out >= ?) OR
              (check_in <= ? AND check_out >= ?) OR
              (check_in >= ? AND check_out <= ?)
          )";

$stmt = $pdo->prepare($query);
$stmt->execute([
    $room_id,
    $check_in, $check_in,
    $check_out, $check_out,
    $check_in, $check_out
]);

$count = $stmt->fetchColumn();

if ($count > 0) {
    echo json_encode(['available' => false, 'message' => 'Room is already reserved for the selected dates. Please choose different dates.']);
} else {
    echo json_encode(['available' => true, 'message' => 'Room is available!']);
}
