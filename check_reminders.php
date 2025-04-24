<?php
session_start();

if (!isset($_SESSION['userId'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$mysqli = mysqli_connect("localhost", "root", "", "calendar");
if (!$mysqli) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$user = $_SESSION['userId'];
$now = date('Y-m-d H:i:s');

// Get all upcoming events with their reminders
$query = "SELECT ce.id, ce.event_title, ce.event_start, er.reminder_time
          FROM calendar_events ce
          JOIN event_reminders er ON ce.id = er.event_id
          WHERE ce.userId = ? AND ce.event_start > NOW()
          ORDER BY ce.event_start";

$stmt = $mysqli->prepare($query);
$stmt->bind_param('i', $user);
$stmt->execute();
$result = $stmt->get_result();

$reminders = [];
while ($row = $result->fetch_assoc()) {
    $event_start = strtotime($row['event_start']);
    $current_time = time();
    
    // Calculate the reminder time based on the reminder_time value
    switch ($row['reminder_time']) {
        case '1_DAY':
            $reminder_time = $event_start - (24 * 60 * 60);
            $reminder_type = "in 1 day";
            break;
        case '2_DAY':
            $reminder_time = $event_start - (2 * 24 * 60 * 60);
            $reminder_type = "in 2 days";
            break;
        case '3_DAY':
            $reminder_time = $event_start - (3 * 24 * 60 * 60);
            $reminder_type = "in 3 days";
            break;
        case '1_MIN':
            $reminder_time = $event_start - 60;
            $reminder_type = "in 1 minute";
            break;
        default:
            continue;
    }
    
    // If we're within 30 seconds of the reminder time, add it to the reminders array
    if (abs($current_time - $reminder_time) <= 30) {
        $reminders[] = [
            'event_title' => $row['event_title'],
            'reminder_type' => $reminder_type
        ];
    }
}

echo json_encode(['reminders' => $reminders]);

$stmt->close();
$mysqli->close();
?> 