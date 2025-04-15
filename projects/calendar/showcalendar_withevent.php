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

$start = mktime(12, 0, 0, $month, 1, $year);
$firstDayArray = getdate($start);

// Fetch events from Edge Config API
$events = [];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, '/api/calendar/events');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
  $events = json_decode($response, true);
}
?>
<!DOCTYPE html>
<html>
<head>
  	<title><?php echo "Calendar: ".$firstDayArray['month']." ".$firstDayArray['year']; ?></title>
    <link rel="stylesheet" href="showcalendar.css" type="text/css">
    <link rel="stylesheet" href="sidebar.css" type="text/css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">    
    <link rel="icon" href="img/jasnlogo.png" alt="logo" />
    <link rel="stylesheet" href="../../styles/footer.css">
    <link rel="stylesheet" href="../../styles/navbar.css">
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
        <h1>Select a Month/Year Combination</h1>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <select name="month">
            <?php
            $months = Array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
            for ($x = 1; $x <= count($months); $x++) {
                echo "<option value=\"$x\"";
                if ($x == $month) {
                echo " selected";
                }
                echo ">".$months[$x-1]."</option>";
            }
            ?>
            </select>
            <select name="year">
            <?php
            for ($x = 1990; $x <= 2025; $x++) {
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
        for ($count = 0; $count < (6*7); $count++) {
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
                $currentDate = date('Y-m-d', $start);
                
                foreach ($events as $event) {
                    $eventDate = date('Y-m-d', strtotime($event['start']));
                    if ($eventDate === $currentDate) {
                        $event_title .= htmlspecialchars($event['title'])."<br>";
                    }
                }

                echo "<td><a href=\"javascript:eventWindow('event.php?m=".$month.
                "&amp;d=".$dayArray['mday']."&amp;y=$year');\">".$dayArray['mday']."</a>
                <br>".$event_title."</td>\n";

                unset($event_title);
                $start += ADAY;
            }
        }
        echo "</tr></table>";
        ?>

        <script type="text/javascript">
            function eventWindow(url) {
                event_popupWin = window.open(url, 'event', 'resizable=yes, scrollbars=yes, toolbar=no,width=400,height=400');
                event_popupWin.opener = self;
            }
        </script>

        <div id="footer-container"></div>
        <script>
            fetch('../../footer.html')
            .then(response => response.text())
                .then(data => {
                document.getElementById('footer-container').innerHTML = data;
                })
            .catch(error => console.error('Error loading footer:', error));
        </script>
    </div>
</body>
</html>