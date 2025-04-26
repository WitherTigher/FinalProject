<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['userId'])) {
    header('Location: login.html');
    exit;
}
$user =$_SESSION['userId'];
define("ADAY", (60*60*24));
if ((!isset($_POST['month'])) || (!isset($_POST['year']))) {
	$nowArray = getdate();
	$month = $nowArray['mon'];
	$year = $nowArray['year'];
} else {
	$month = $_POST['month'];
	$year = $_POST['year'];
}

$start = mktime (12, 0, 0, $month, 1, $year);
$firstDayArray = getdate($start);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo "Calendar: ".$firstDayArray['month']." ".$firstDayArray['year']; ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- CSS Files -->
    <link rel="stylesheet" href="showcalendar_withevent.css" type="text/css">
    <link rel="stylesheet" href="sidebar.css" type="text/css">
    <link rel="stylesheet" href="navbar.css" type="text/css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" href="img/jasnlogo.png" alt="logo" />
    <style>
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px;
        border-radius: 4px;
        color: white;
        display: none;
        z-index: 1000;
    }

    .success {
        background-color: #4CAF50;
    }

    .error {
        background-color: #f44336;
    }
    body, table, th, td, form, select, button, .sidenav, .sidenav a, .sidenav h1, .user-info, .logout-link, .main-content, h1 {
        font-family: 'Heebo', sans-serif;
    }
    </style>
</head>
<body>
     <!-- Dark Mode Toggle Button -->
    <button id="toggleDark" class="dark-toggle">
    <i class="fas fa-moon"></i> Toggle Dark Mode
    </button>
    <!-- Navbar -->
    <div id="navbar"></div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            fetch('navbar.html')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('navbar').innerHTML = data;
                });
        });
    </script>

    <div id="sidebar-container"></div>
    <script>
    fetch('sidebar.html')
        .then(response => response.text())
        .then(data => {
        document.getElementById('sidebar-container').innerHTML = data;
        const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const sidebarToggle = document.getElementById('sidebarToggle');
    let isSidebarHidden = false;

    // Check if sidebar state is stored in localStorage
    const storedState = localStorage.getItem('sidebarHidden');
    if (storedState === 'true') {
        sidebar.classList.add('hidden');
        mainContent.classList.add('expanded');
        isSidebarHidden = true;
        updateToggleIcon();
    }

    sidebarToggle.addEventListener('click', function() {
        isSidebarHidden = !isSidebarHidden;
        sidebar.classList.toggle('hidden');
        mainContent.classList.toggle('expanded');
        
        // Store the state in localStorage
        localStorage.setItem('sidebarHidden', isSidebarHidden);
        
        updateToggleIcon();
    });

    function updateToggleIcon() {
        const icon = sidebarToggle.querySelector('i');
        icon.className = isSidebarHidden ? 'fas fa-bars' : 'fas fa-times';
    }
        })
        .catch(error => console.error('Error loading sidebar:', error));
    </script>
    <button class="sidebar-toggle" id="sidebarToggle">
    <i class="fas fa-bars"></i>
</button>
<div id="mySidenav" class="sidenav">
    <a href="javascript:void(0)" class="closebtn" onclick="sidebarC()">&times;</a>
    <div class="sidenav-content">
        <div id="side"></div>
    </div>
    <div class="user-info">
        <i class="fas fa-user"></i> Hello, <?php echo htmlspecialchars($_SESSION['userName']); ?>
        <a href="#" onclick="logout()" class="logout-link">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>
  <h1>Select a Month/Year Combination</h1>
  <div class="main-content" id="mainContent">
  <form id="reload" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <select name="month">
    <?php
    $months = Array("January", "February", "March", "April", "May",  "June", "July", "August", "September", "October", "November", "December");
    for ($x=1; $x <= count($months); $x++) {
    	echo"<option value=\"$x\"";
  	    if ($x == $month) {
   	      echo " selected";
  	    }
	    echo ">".$months[$x-1]."</option>";
    }
    ?>
    </select>
    <select name="year">
    <?php
    for ($x=1990; $x<=2025; $x++) {
    	echo "<option";
    	if ($x == $year) {
    		echo " selected";
    	}
    	echo ">$x</option>";
    }
    ?>
    </select>
    <button type="submit" name="submit" value="submit">Go!</button>
    </form>
    <br>
    <?php
    $days = Array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
    echo "<table><tr>\n";
    foreach ($days as $day) {
	  echo "<th>".$day."</th>\n";
    }
    for ($count=0; $count < (6*7); $count++) {
	  $dayArray = getdate($start);
	  if (($count % 7) == 0) {
        if ($dayArray['mon'] != $month) {
			break;
		} else {
			echo "</tr><tr>\n";
		}
      }
      if ($count < $firstDayArray['wday'] || $dayArray['mon'] != $month) {
	    echo "<td>&nbsp;</td>\n";
	  } else {
		 $event_title = "";
         $mysqli = mysqli_connect("localhost", "root", "", "calendar");
		 $chkEvent_sql = "SELECT event_title FROM calendar_events WHERE
						  month(event_start) = '".$month."' AND
						  dayofmonth(event_start) = '".$dayArray['mday']."'
						  AND year(event_start) = '".$year."' AND userId = '".$user."'ORDER BY event_start";
		 $chkEvent_res = mysqli_query($mysqli, $chkEvent_sql)
						 or die(mysqli_error($mysqli));

		 if (mysqli_num_rows($chkEvent_res) > 0) {
			  while ($ev = mysqli_fetch_array($chkEvent_res)) {
				   $event_title .= stripslashes($ev['event_title'])."<br>";
			  }
		 } else {
			  $event_title = "";
		 }

		 echo "<td><a href=\"javascript:sidebarOpen('event.php?m=".$month.
		 "&amp;d=".$dayArray['mday']."&amp;y=$year');\">".$dayArray['mday']."</a>
		 <br>".$event_title."</td>\n";

		 unset($event_title);

		 $start += ADAY;
	  }
    }
    echo "</tr></table>";

    //close connection to MySQL
    mysqli_close($mysqli);
    ?>

    
  <script type="text/javascript">
  function sidebar(){
    document.getElementById("mySidenav").style.width ="400px";
  }
  function sidebarC(){
    document.getElementById("mySidenav").style.width ="0px";
  }
  function sidebarOpen(url){
    sidebar();
    fetch(url)
    .then(response => response.text())
    .then(data => {
      const keeper = document.getElementById('side');
      keeper.innerHTML = data;
      
      // Add event handlers to any forms in the sidebar
      const forms = keeper.querySelectorAll("form");
      forms.forEach(form => {
        form.addEventListener("submit", function(e) {
          e.preventDefault();
          
          const formData = new FormData(this);
          formData.append('action', 'add');
          
          fetch('event.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showNotification('Event added successfully', 'success');
              window.location.reload();
            } else {
              showNotification('Error adding event: ' + (data.error || 'Unknown error'), 'error');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            showNotification('Error processing your request. Please try again.', 'error');
          });
        });
      });
    })
    .catch(error => {
      console.error('Error:', error);
      showNotification('Error loading content. Please try again.', 'error');
    });
  }
  
  </script>
<div>
<div class="user-info">
    <i class="fas fa-user"></i> Hello, <?php echo htmlspecialchars($_SESSION['userName']); ?>
    <a href="#" onclick="logout()" class="logout-link">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>
</div>
<div id="notification" class="notification"></div>
</body>
</html>
<script>
     // Dark Mode Toggle Script
    document.addEventListener('DOMContentLoaded', () => {
    if (localStorage.getItem('darkMode') === 'true') {
          document.body.classList.add('dark-mode');
        }

        document.getElementById('toggleDark').addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
        });
    });
function logout() {
    fetch('logout.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'login.html';
            }
        })
        .catch(error => console.error('Error:', error));
}

// Add notification functions
function showNotification(message, type) {
    const notification = document.getElementById('notification');
    notification.textContent = message;
    notification.className = 'notification ' + type;
    notification.style.display = 'block';
    
    setTimeout(() => {
        notification.style.display = 'none';
    }, 3000);
}

// Edit event handler
function editEvent(button) {
    const eventItem = button.closest('.event-item');
    eventItem.querySelector('.event-display').style.display = 'none';
    eventItem.querySelector('.edit-form').style.display = 'block';
}

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
            showNotification('Event deleted successfully', 'success');
            window.location.reload();
        } else {
            showNotification('Error deleting event: ' + (data.error || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error deleting event. Please try again.', 'error');
    });
}

// Update event handler
function updateEvent(eventId, form) {
    const formData = new FormData(form);
    formData.append('action', 'update');
    formData.append('event_id', eventId);
    
    // Convert time inputs to proper format
    const startTime = form.querySelector('[name="start_time"]').value;
    const endTime = form.querySelector('[name="end_time"]').value;
    
    if (!startTime || !endTime) {
      showNotification('Please fill in all required fields', 'error');
      return;
    }
    
    fetch('event.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showNotification('Event updated successfully', 'success');
        window.location.reload();
      } else {
        showNotification('Error updating event: ' + (data.error || 'Unknown error'), 'error');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showNotification('Error updating event. Please try again.', 'error');
    });
}

// Cancel edit handler
function cancelEdit(button) {
    const eventItem = button.closest('.event-item');
    eventItem.querySelector('.event-display').style.display = 'block';
    eventItem.querySelector('.edit-form').style.display = 'none';
}
</script>