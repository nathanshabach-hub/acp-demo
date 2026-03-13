<?php
include '../../config/my_const.php';
ini_set('display_errors','ON');

//print_r($_GET[];);exit;

$convention_season_slug = $_GET['convention_season_slug'];

$mysqli = new mysqli("localhost", "root", "root", "accelerate"); // Update credentials
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Get details of convention season slug
$queryCS 	= "SELECT * FROM conventionseasons WHERE slug = '".$convention_season_slug."'LIMIT 1";
$resultCS 	= $mysqli->query($queryCS);
$rowCS 		= $resultCS->fetch_assoc();

// Get all records sorted by user_id, day, start_time
$query = "SELECT id, user_id, day, start_time, finish_time FROM schedulingtimings
				WHERE 
				conventionseasons_id = '".$rowCS['id']."' AND 
				convention_id = '".$rowCS['convention_id']."' AND 
				season_id = '".$rowCS['season_id']."' AND
				season_year = '".$rowCS['season_year']."' AND 
				user_id>0 AND 
				user_type = 'Student' AND 
				is_bye!= 1
				ORDER BY user_id, day, start_time";
$result = $mysqli->query($query);

$schedules = [];
while ($row = $result->fetch_assoc()) {
    $schedules[] = $row;
}

// Group schedules by student and day
$grouped = [];
foreach ($schedules as $row) {
    $key = $row['user_id'] . '_' . $row['day'];
    $grouped[$key][] = $row;
}

// Function to check if a time overlaps with any existing slots
function isConflict($start, $end, $existingSlots) {
    foreach ($existingSlots as $slot) {
        if (
            ($start >= $slot['start'] && $start < $slot['end']) ||
            ($end > $slot['start'] && $end <= $slot['end']) ||
            ($start <= $slot['start'] && $end >= $slot['end'])
        ) {
            return true;
        }
    }
    return false;
}

// Reschedule conflicts
foreach ($grouped as $key => $entries) {
    $slots = [];
    $seen = [];

    foreach ($entries as $entry) {
        $id = $entry['id'];
        $user_id = $entry['user_id'];
        $day = $entry['day'];

        $start = strtotime($entry['start_time']);
        $end = strtotime($entry['finish_time']);
        $duration = $end - $start;

        if (!isset($seen[$start])) {
            $slots[] = ['start' => $start, 'end' => $end];
            $seen[$start] = true;
        } else {
            // Conflict detected
            $searchTime = min(array_column($slots, 'start'));

            // Start searching from minimum time of the day (e.g., 8:00 AM)
            $candidate = strtotime("08:00:00");

            while ($candidate < strtotime("18:00:00")) {
                $candidateEnd = $candidate + $duration;
                if (!isConflict($candidate, $candidateEnd, $slots)) {
                    // Update DB with new schedule
                    $newStart = date("H:i:s", $candidate);
                    $newEnd = date("H:i:s", $candidateEnd);

                    $update = $mysqli->prepare("UPDATE schedulingtimings1 SET start_time = ?, finish_time = ? WHERE id = ?");
                    $update->bind_param("ssi", $newStart, $newEnd, $id);
                    $update->execute();

                    //echo "Rescheduled ID $id for user $user_id on $day to $newStart - $newEnd<hr>";

                    $slots[] = ['start' => $candidate, 'end' => $candidateEnd];
                    break;
                }
                $candidate += 60; // move by 1 minute
            }
        }
    }
}

$mysqli->close();

//exit;

header("Location: ".HTTP_PATH.'/admin/schedulingtimings/conflictdone/'.$convention_season_slug);
//exit;
//echo "Rescheduling complete.\n";
?>
