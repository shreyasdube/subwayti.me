<?php
function formatDate($epoch) {
    return date('g:i:s a', $epoch);
}

function isAlewife($name) {
    return strcmp("Alewife", $name) == 0;
}

function getPredictionFor($predictions, $stop) {
    foreach ($predictions as $p) {
        if ($p["StopID"] == $stop) {
            return $p["Seconds"];
        }
    }
    return -1;
}

function printAsList($title, $timeArray, $baseTime) {
    echo "<li data-role='list-divider'>$title</li>";
    foreach ($timeArray as $seconds) {
        echo "<li>" . formatDate($baseTime + $seconds) . " (" . $seconds . " seconds)</li>";
    }
}

$feed = file_get_contents("http://developer.mbta.com/lib/rthr/red.json");
$json = json_decode($feed, true);

$porter = 70065;
$kendall = 70072;

$tripList = $json["TripList"];
$currentTime = $tripList["CurrentTime"];
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Red Line Tracker</title>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="http://code.jquery.com/mobile/1.1.0-rc.1/jquery.mobile-1.1.0-rc.1.min.css" />
        <script src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
        <script src="http://code.jquery.com/mobile/1.1.0-rc.1/jquery.mobile-1.1.0-rc.1.min.js"></script>
    </head>

    <body>
        <div data-role="page">
            <div data-role="header">
                <h1><?php echo formatDate($currentTime); ?></h1>
            </div>
            <div data-role="content">
                <ul data-role="listview">
                    <?php
                    $home = array();
                    $work = array();

                    foreach ($tripList["Trips"] as $trip) {
                        $destination = $trip["Destination"];
                        $predictions = $trip["Predictions"];

                        $stop = $porter;
                        $isAlewife = isAlewife($destination);

                        if ($isAlewife) {
                            $stop = $kendall;
                        }

                        $seconds = getPredictionFor($predictions, $stop);
                        if ($seconds != -1) {
                            if ($isAlewife) {
                                array_push($home, $seconds);
                            } else {
                                array_push($work, $seconds);
                            }
                        }
                    }
                    
                    sort($home);
                    printAsList("kendall -> home", $home, $currentTime);

                    sort($work);
                    printAsList("porter -> work", $work, $currentTime);
                    ?>
                </ul>
            </div>
        </div>
    </body>
</html>