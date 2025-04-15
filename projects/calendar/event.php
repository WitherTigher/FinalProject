<!DOCTYPE html>
<html>
<head>
  <title>Show/Add Events</title>
  <link rel="stylesheet" href="sidebar.css" type="text/css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <script>
    async function fetchEvents() {
      const response = await fetch('/api/calendar/events');
      return await response.json();
    }

    async function addEvent(eventData) {
      const response = await fetch('/api/calendar/events', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(eventData)
      });
      return await response.json();
    }
  </script>
</head>
<body>
    <div id="navbar-container"></div>
    <script>
    fetch('../../navbar.html')
        .then(response => response.text())
        .then(data => {
        document.getElementById('navbar-container').innerHTML = data;
        })
        .catch(error => console.error('Error loading navbar:', error));
    </script>

    <div id="sidebar-container"></div>
    <script>
    fetch('sidebar.html')
        .then(response => response.text())
        .then(data => {
        document.getElementById('sidebar-container').innerHTML = data;
        })
        .catch(error => console.error('Error loading sidebar:', error));
    </script>

    <div class="main-content" id="mainContent">
        <h1>Show/Add Events</h1>
        <?php
        $m = $_GET['m'] ?? $_POST['m'];
        $d = $_GET['d'] ?? $_POST['d'];
        $y = $_GET['y'] ?? $_POST['y'];

        // Fetch events from Edge Config API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, '/api/calendar/events');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $events = [];
        if ($response) {
            $events = json_decode($response, true);
        }

        // Filter events for the current date
        $currentDate = date('Y-m-d', mktime(0, 0, 0, $m, $d, $y));
        $dayEvents = array_filter($events, function($event) use ($currentDate) {
            return date('Y-m-d', strtotime($event['start'])) === $currentDate;
        });

        if (!empty($dayEvents)) {
            echo "<p><strong>Today's Events:</strong></p><ul>";
            foreach ($dayEvents as $event) {
                $time = date('g:i A', strtotime($event['start']));
                echo "<li><strong>{$time}</strong>: " . htmlspecialchars($event['title']) . "<br>" . 
                    htmlspecialchars($event['description']) . "</li>";
            }
            echo "</ul><hr>";
        }

        // Show form for adding an event
        echo <<<END_OF_TEXT
        <form method="post" action="$_SERVER[PHP_SELF]" onsubmit="return handleSubmit(event)">
        <p><strong>Would you like to add an event?</strong><br>
        Complete the form below and press the submit button to add the event and refresh this window.</p>

        <p><label for="event_title">Event Title:</label><br>
        <input type="text" id="event_title" name="event_title" size="25" maxlength="25" required></p>

        <p><label for="event_shortdesc">Event Description:</label><br>
        <input type="text" id="event_shortdesc" name="event_shortdesc" size="25" maxlength="255" required></p>

        <fieldset>
        <legend>Event Time (hh:mm):</legend>
        <select name="event_time_hh" id="event_time_hh">
        END_OF_TEXT;

        for ($x=1; $x <= 24; $x++) {
            echo "<option value=\"$x\">$x</option>";
        }

        echo <<<END_OF_TEXT
        </select> :
        <select name="event_time_mm" id="event_time_mm">
        <option value="00">00</option>
        <option value="15">15</option>
        <option value="30">30</option>
        <option value="45">45</option>
        </select>
        </fieldset>
        <input type="hidden" name="m" value="$m">
        <input type="hidden" name="d" value="$d">
        <input type="hidden" name="y" value="$y">
        
        <button type="submit" name="submit" value="submit">Add Event</button>
        </form>

        <script>
        async function handleSubmit(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const eventData = {
                title: formData.get('event_title'),
                description: formData.get('event_shortdesc'),
                start: new Date(
                    formData.get('y'),
                    formData.get('m') - 1,
                    formData.get('d'),
                    formData.get('event_time_hh'),
                    formData.get('event_time_mm')
                ).toISOString()
            };

            try {
                await addEvent(eventData);
                window.location.reload();
            } catch (error) {
                console.error('Error adding event:', error);
                alert('Failed to add event. Please try again.');
            }
        }
        </script>
        END_OF_TEXT;
        ?>
    </div>
</body>
</html>