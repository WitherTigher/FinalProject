<?php
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
<div id="navbar"></div>
    <script>document.addEventListener('DOMContentLoaded', () => {
     fetch('navbar.html')
         .then(response => response.text())
         .then(data => {
             document.getElementById('navbar').innerHTML = data;
             
         });
 });</script>
<title><?php echo "Calendar: ".$firstDayArray['month']." ".$firstDayArray['year']; ?></title>
<link rel="stylesheet" href="sidebar.css" type="text/css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">    
    <link rel="icon" href="img/jasnlogo.png" alt="logo" />
    <link rel="stylesheet" href="showcalendar.css" type="text/css">
    <link rel="stylesheet" href="sidebar.css" type="text/css">

<style type="text/css">
    
    .sidenav {
  height: 100vh;
  width: 0;
  position: fixed;
  z-index: 100;
  top: 80;
  right: 0;
  background-color: white;
  overflow: auto;
  transition:width 0.5s ease;
  border: 2.5px solid black;
  padding:auto;
  overflow-x:hidden;
  
}

.sidenav a {
  padding: 8px 8px 8px 32px;
  text-decoration: none;
  font-size: 25px;
  color: white;
  display: block;
  transition: 0.3s;
}

.sidenav a:hover {
  color: #f1f1f1;
}

.sidenav .closebtn {
  position: absolute;
  top: 0px;
  right: 15px;
  font-size: 30px;
  margin: 0;
  color:white;
}

.sidebar-toggle {
    position: fixed;
    top: 65px; /* Position below the main navbar */
    left: 250px;
    background: none;
    border: none;
    color: #2c3e50;
    font-size: 24px;
    cursor: pointer;
    z-index: 101;
    transition: transform 0.3s ease-in-out;
    padding: 10px;
    background-color: white;
    border-radius: 50%;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.sidebar-toggle:hover {
    transform: scale(1.1);
}



</style>
</head>
<body>
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
      <div id="side"></div>
    </div>
  <h1>Select a Month/Year Combination</h1>
  <div class="main-content" id="mainContent">
  <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
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
         $mysqli = mysqli_connect("localhost", "root", "", "cal");
		 $chkEvent_sql = "SELECT event_title FROM calendar_events WHERE
						  month(event_start) = '".$month."' AND
						  dayofmonth(event_start) = '".$dayArray['mday']."'
						  AND year(event_start) = '".$year."' ORDER BY event_start";
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
      const keeper =document.getElementById("side");
      keeper.innerHTML = data;
      setTimeout(() =>{
      const form = keeper.querySelector("form");
      if (form){
        form.addEventListener("submit", function(keep) {
          keep.preventDefault();
          const submitdata = new FormData(form);
          fetch(form.action, {
        method: form.method,
        body: submitdata
    })
    .then(response => response.text())
    .then(data => { keeper.innerHTML =data; 
      sidebarOpen(url);
    });
        });
      }
    },50);
    })
    .catch(error => {
        console.error('Error:', error);
    });

  }
  
  </script>
<div>
</body>
</html>