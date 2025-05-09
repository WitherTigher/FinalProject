<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['userId'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
        exit;
    }
    header('Location: login.html');
    exit;
}

$mysqli = new mysqli("localhost", "root", "", "calendar");
if (!$mysqli) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Database connection failed']);
        exit;
    }
    die("Connection failed: " . mysqli_connect_error());
}

$user = $_SESSION['userId'];

// Handle all AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (!isset($_POST['action'])) {
        echo json_encode(['success' => false, 'error' => 'No action specified']);
        exit;
    }

    switch ($_POST['action']) {
        case 'delete':
            $event_id = mysqli_real_escape_string($mysqli, $_POST['event_id']);
            $delete_sql = "DELETE FROM calendar_events WHERE id = '$event_id' AND userId = '$user'";
            $delete_res = mysqli_query($mysqli, $delete_sql);
            echo json_encode(['success' => $delete_res ? true : false]);
            break;

        case 'update':
            try {
                $event_id = mysqli_real_escape_string($mysqli, $_POST['event_id']);
                $event_title = mysqli_real_escape_string($mysqli, $_POST['event_title']);
                $event_shortdesc = mysqli_real_escape_string($mysqli, $_POST['event_shortdesc']);
                
                // Parse the time inputs
                $start_time = strtotime($_POST['start_time']);
                $end_time = strtotime($_POST['end_time']);
                
                if ($start_time === false || $end_time === false) {
                    throw new Exception("Invalid time format");
                }
                
                $event_date = date('Y-m-d', strtotime("{$_POST['y']}-{$_POST['m']}-{$_POST['d']}")) . ' ' . date('H:i:s', $start_time);
                $event_end = date('Y-m-d', strtotime("{$_POST['y']}-{$_POST['m']}-{$_POST['d']}")) . ' ' . date('H:i:s', $end_time);
                
                $update_sql = "UPDATE calendar_events 
                              SET event_title = ?, 
                                  event_shortdesc = ?, 
                                  event_start = ?, 
                                  event_end = ? 
                              WHERE id = ? AND userId = ?";
                
                $stmt = mysqli_prepare($mysqli, $update_sql);
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . mysqli_error($mysqli));
                }
                
                mysqli_stmt_bind_param($stmt, "ssssii", 
                    $event_title,
                    $event_shortdesc,
                    $event_date,
                    $event_end,
                    $event_id,
                    $user
                );
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
                }
                
                echo json_encode(['success' => true]);
                
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            break;

        case 'add':
            try {
                // Validate required fields
                $required_fields = ['m', 'd', 'y', 'event_title', 'event_time_hh', 'event_time_mm', 'event_end_hh', 'event_end_mm'];
                $missing_fields = [];
                foreach ($required_fields as $field) {
                    if (!isset($_POST[$field]) || $_POST[$field] === '') {
                        $missing_fields[] = $field;
                    }
                }
                
                if (!empty($missing_fields)) {
                    throw new Exception('Missing required fields: ' . implode(', ', $missing_fields));
                }
                
                $safe_m = mysqli_real_escape_string($mysqli, $_POST['m']);
                $safe_d = mysqli_real_escape_string($mysqli, $_POST['d']);
                $safe_y = mysqli_real_escape_string($mysqli, $_POST['y']);
                $safe_event_title = mysqli_real_escape_string($mysqli, $_POST['event_title']);
                $safe_event_shortdesc = mysqli_real_escape_string($mysqli, $_POST['event_shortdesc'] ?? '');
                $safe_event_time_hh = mysqli_real_escape_string($mysqli, $_POST['event_time_hh']);
                $safe_event_time_mm = mysqli_real_escape_string($mysqli, $_POST['event_time_mm']);
                $safe_event_end_hh = mysqli_real_escape_string($mysqli, $_POST['event_end_hh']);
                $safe_event_end_mm = mysqli_real_escape_string($mysqli, $_POST['event_end_mm']);

                $event_date = sprintf("%04d-%02d-%02d %02d:%02d:00", $safe_y, $safe_m, $safe_d, $safe_event_time_hh, $safe_event_time_mm);
                $event_end = sprintf("%04d-%02d-%02d %02d:%02d:00", $safe_y, $safe_m, $safe_d, $safe_event_end_hh, $safe_event_end_mm);

                mysqli_begin_transaction($mysqli);

                $insEvent_sql = "INSERT INTO calendar_events (userId, event_title, event_shortdesc, event_start, event_end) 
                                VALUES (?, ?, ?, ?, ?)";
                
                $stmt = mysqli_prepare($mysqli, $insEvent_sql);
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . mysqli_error($mysqli));
                }

                mysqli_stmt_bind_param($stmt, "issss", 
                    $user,
                    $safe_event_title,
                    $safe_event_shortdesc,
                    $event_date,
                    $event_end
                );

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
                }

                $event_id = mysqli_insert_id($mysqli);

                // Handle reminders
                if (isset($_POST['reminders']) && is_array($_POST['reminders'])) {
                    foreach ($_POST['reminders'] as $reminder) {
                        $safe_reminder = mysqli_real_escape_string($mysqli, $reminder);
                        $insReminder_sql = "INSERT INTO event_reminders (event_id, reminder_time) VALUES (?, ?)";
                        $stmt = mysqli_prepare($mysqli, $insReminder_sql);
                        if (!$stmt) {
                            throw new Exception("Failed to prepare reminder statement");
                        }
                        mysqli_stmt_bind_param($stmt, "is", $event_id, $safe_reminder);
                        if (!mysqli_stmt_execute($stmt)) {
                            throw new Exception("Failed to insert reminder");
                        }
                    }
                }

                mysqli_commit($mysqli);
                echo json_encode(['success' => true]);

            } catch (Exception $e) {
                mysqli_rollback($mysqli);
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    exit;
}

// Regular page load - only output HTML if not an AJAX request
?>
<!DOCTYPE html>
<html>
<head>
  <title>Show/Add Events</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    .event-item { 
      margin: 10px 0;
      padding: 15px;
      padding-right: 160px; /* Add space for buttons */
      border: 1px solid #ddd;
      border-radius: 8px;
      position: relative;
      background-color: #fff;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .event-display {
      word-wrap: break-word;
      padding-right: 10px;
    }
    .event-actions {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      display: flex;
      gap: 8px;
    }
    .event-actions button {
      padding: 8px 15px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
      transition: background-color 0.3s;
      white-space: nowrap;
      min-width: 70px;
    }
    .event-actions button:first-child {
      background-color: #4CAF50;
      color: white;
    }
    .event-actions button:last-child {
      background-color: #f44336;
      color: white;
    }
    .edit-form {
      display: none;
      padding: 15px;
    }
    .edit-form input[type="text"],
    .edit-form textarea,
    .edit-form input[type="time"] {
      width: 100%;
      padding: 8px;
      margin: 8px 0;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 14px;
    }
    .edit-form textarea {
      min-height: 80px;
      resize: vertical;
    }
    .edit-form label {
      display: block;
      margin-top: 10px;
      font-weight: bold;
      color: #333;
    }
    .edit-form .form-buttons {
      margin-top: 15px;
      text-align: right;
    }
    .edit-form .form-buttons button {
      padding: 8px 20px;
      margin-left: 10px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
      transition: background-color 0.3s;
    }
    .edit-form .form-buttons button[onclick*="updateEvent"] {
      background-color: #4CAF50;
      color: white;
    }
    .edit-form .form-buttons button[onclick*="cancelEdit"] {
      background-color: #777;
      color: white;
    }
    #addEventForm {
      display: block;
      margin-top: 20px;
      padding: 20px;
      border: 1px solid #ddd;
      border-radius: 8px;
      background-color: #fff;
    }
    .editing #addEventForm {
      display: none !important;
    }
    h1 {
      color: #2c3e50;
      margin-bottom: 20px;
    }
    body {
      background-color: #f5f5f5;
      padding: 20px;
      font-family: Arial, sans-serif;
    }
  </style>
</head>
<body>
  <h1>Show/Add Events</h1>
  <div id="events-container">
  <?php

  // Get events for this day
  $safe_m = mysqli_real_escape_string($mysqli, $_GET['m']);
  $safe_d = mysqli_real_escape_string($mysqli, $_GET['d']);
  $safe_y = mysqli_real_escape_string($mysqli, $_GET['y']);

  $getEvent_sql = "SELECT ce.*, GROUP_CONCAT(er.reminder_time) as reminders 
                   FROM calendar_events ce 
                   LEFT JOIN event_reminders er ON ce.id = er.event_id
                   WHERE month(event_start) = '$safe_m' 
                   AND dayofmonth(event_start) = '$safe_d' 
                   AND year(event_start) = '$safe_y' 
                   AND userId = '$user' 
                   GROUP BY ce.id
                   ORDER BY event_start";
  
  $getEvent_res = mysqli_query($mysqli, $getEvent_sql) or die(mysqli_error($mysqli));

  if (mysqli_num_rows($getEvent_res) > 0) {
    echo "<div id='events-list'>";
    while ($ev = mysqli_fetch_array($getEvent_res)) {
      $event_id = $ev['id'];
      $event_title = htmlspecialchars(stripslashes($ev['event_title']));
      $event_shortdesc = htmlspecialchars(stripslashes($ev['event_shortdesc']));
      $start_time = date('H:i', strtotime($ev['event_start']));
      $end_time = date('H:i', strtotime($ev['event_end']));
      $reminders = $ev['reminders'] ? explode(',', $ev['reminders']) : [];
      
      echo "<div class='event-item' data-id='$event_id'>
              <div class='event-display'>
                <strong>$start_time-$end_time</strong>: $event_title<br>$event_shortdesc
                <div class='event-actions'>
                  <button onclick='editEvent(this)'>Edit</button>
                  <button onclick='deleteEvent($event_id)'>Delete</button>
                </div>
              </div>
              <form class='edit-form'>
                <label for='event_title_".$event_id."'>Event Title:</label>
                <input type='text' id='event_title_".$event_id."' name='event_title' value='$event_title' required>
                
                <label for='event_desc_".$event_id."'>Event Description:</label>
                <textarea id='event_desc_".$event_id."' name='event_shortdesc'>$event_shortdesc</textarea>
                
                <input type='hidden' name='m' value='$safe_m'>
                <input type='hidden' name='d' value='$safe_d'>
                <input type='hidden' name='y' value='$safe_y'>
                <input type='hidden' name='event_time_hh' value='".date('G', strtotime($ev['event_start']))."'>
                <input type='hidden' name='event_time_mm' value='".date('i', strtotime($ev['event_start']))."'>
                <input type='hidden' name='event_end_hh' value='".date('G', strtotime($ev['event_end']))."'>
                <input type='hidden' name='event_end_mm' value='".date('i', strtotime($ev['event_end']))."'>
                
                <div>
                  <label for='start_time_".$event_id."'>Start Time:</label>
                  <input type='time' id='start_time_".$event_id."' name='start_time' value='$start_time' required>
                </div>
                
                <div>
                  <label for='end_time_".$event_id."'>End Time:</label>
                  <input type='time' id='end_time_".$event_id."' name='end_time' value='$end_time' required>
                </div>
                
                <div class='form-buttons'>
                  <button type='button' onclick='cancelEdit(this)'>Cancel</button>
                  <button type='button' onclick='updateEvent($event_id, this.form)'>Save Changes</button>
                </div>
              </form>
            </div>";
    }
    echo "</div>";
  }
  mysqli_close($mysqli);
  ?>

  <form id="addEventForm" method="POST">
    <p><strong>Add New Event</strong></p>
    <p><label for="event_title">Event Title:</label><br>
    <input type="text" id="event_title" name="event_title" size="25" maxlength="25"></p>

    <p><label for="event_shortdesc">Event Description:</label><br>
    <input type="text" id="event_shortdesc" name="event_shortdesc" size="25" maxlength="255"></p>

    <fieldset>
      <legend>Starting Time (hh:mm):</legend>
      <select name="event_time_hh">
        <?php for ($x=1; $x <= 24; $x++) echo "<option value='$x'>$x</option>"; ?>
      </select> :
      <select name="event_time_mm">
        <option value="00">00</option>
        <option value="15">15</option>
        <option value="30">30</option>
        <option value="45">45</option>
      </select>
    </fieldset>

    <fieldset>
      <legend>Ending Time (hh:mm):</legend>
      <select name="event_end_hh">
        <?php for ($x=1; $x <= 24; $x++) echo "<option value='$x'>$x</option>"; ?>
      </select> :
      <select name="event_end_mm">
        <option value="00">00</option>
        <option value="15">15</option>
        <option value="30">30</option>
        <option value="45">45</option>
      </select>
    </fieldset>

    <fieldset>
      <legend>Reminders:</legend>
      <input type="checkbox" name="reminders[]" value="1_DAY"> 1 Day Before<br>
      <input type="checkbox" name="reminders[]" value="2_DAY"> 2 Days Before<br>
      <input type="checkbox" name="reminders[]" value="3_DAY"> 3 Days Before<br>
      <input type="checkbox" name="reminders[]" value="1_MIN"> 1 Minute Before
    </fieldset>

    <input type="hidden" name="m" value="<?php echo $safe_m; ?>">
    <input type="hidden" name="d" value="<?php echo $safe_d; ?>">
    <input type="hidden" name="y" value="<?php echo $safe_y; ?>">
    
    <button type="submit">Add Event</button>
  </form>

  <script>
    // Add event handler
    document.getElementById('addEventForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Validate form
      const title = this.querySelector('[name="event_title"]').value;
      const desc = this.querySelector('[name="event_shortdesc"]').value;
      
      if (!title.trim()) {
        alert('Please enter an event title');
        return;
      }
      
      const formData = new FormData(this);
      formData.append('action', 'add');
      
      fetch('event.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Clear form
          this.reset();
          // Refresh the events list
          window.location.reload();
        } else {
          alert('Error adding event: ' + (data.error || 'Unknown error'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error adding event. Please try again.');
      });
    });

    // Delete event handler
    function deleteEvent(eventId) {
      if (!confirm('Are you sure you want to delete this event?')) {
        return;
      }
      
      const formData = new FormData();
      formData.append('action', 'delete');
      formData.append('event_id', eventId);
      
      fetch('event.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          window.location.reload();
        } else {
          alert('Error deleting event: ' + (data.error || 'Unknown error'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error deleting event. Please try again.');
      });
    }

    // Edit event handlers
    function editEvent(button) {
      const eventItem = button.closest('.event-item');
      eventItem.querySelector('.event-display').style.display = 'none';
      eventItem.querySelector('.edit-form').style.display = 'block';
      document.getElementById('events-container').classList.add('editing');
    }

    function cancelEdit(button) {
      const eventItem = button.closest('.event-item');
      eventItem.querySelector('.event-display').style.display = 'block';
      eventItem.querySelector('.edit-form').style.display = 'none';
      document.getElementById('events-container').classList.remove('editing');
    }

    function updateEvent(eventId, form) {
      const formData = new FormData(form);
      formData.append('action', 'update');
      formData.append('event_id', eventId);
      
      fetch('event.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          window.location.reload();
        } else {
          alert('Error updating event: ' + (data.error || 'Unknown error'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error updating event. Please try again.');
      });
    }

    // Check for reminders
    function checkReminders() {
      fetch('check_reminders.php')
        .then(response => response.json())
        .then(data => {
          if (data.reminders && data.reminders.length > 0) {
            data.reminders.forEach(reminder => {
              alert(`Reminder: ${reminder.event_title} starts ${reminder.reminder_type}`);
            });
          }
        })
        .catch(error => console.error('Error checking reminders:', error));
    }

    // Check reminders every minute
    setInterval(checkReminders, 60000);
    checkReminders(); // Check immediately when page loads
  </script>
</body>
</html>