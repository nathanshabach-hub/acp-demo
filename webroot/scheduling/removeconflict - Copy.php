<?php
include '../../config/my_const.php';
ini_set('display_errors','ON');

//print_r($_GET[];);exit;

$convention_season_slug = $_GET['convention_season_slug'];


$pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8", DB_USER, DB_PASSWORD);

// 1. Get details of convention season
$stmtCS = $pdo->prepare("SELECT * FROM conventionseasons 
                       WHERE slug = :slug
                       LIMIT 1");
$stmtCS->execute([
    ':slug' => $convention_season_slug
]);
$conventionSD = $stmtCS->fetch(PDO::FETCH_ASSOC); // fetch single row
//print_r($conventionSD['id']);exit;



// 2. Get all schedules
$stmt = $pdo->prepare("SELECT * FROM schedulingtimings 
                       WHERE 
						conventionseasons_id = :conventionseasons_id AND 
						convention_id = :convention_id AND
						season_id = :season_id AND
						season_year = :season_year AND  
						is_bye != 1
                       ORDER BY day, start_time ASC");

$stmt->execute(
[
	'conventionseasons_id' 	=> $conventionSD['id'],
	'convention_id' 		=> $conventionSD['convention_id'],
	'season_id' 			=> $conventionSD['season_id'],
	'season_year' 			=> $conventionSD['season_year'],
]);
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);


// 3. Find global min start and max end
$minStart = min(array_column($schedules, 'start_time'));
$maxEnd   = max(array_column($schedules, 'finish_time'));



// 4. Organize events per day
$eventsByDay = [];
foreach ($schedules as $row) {
    $eventsByDay[$row['day']][] = $row;
}


// 5. Try rescheduling
foreach ($eventsByDay as $day => &$events) {
    // Build per-student allocations
    $studentMap = [];
    foreach ($events as $ev) {
        $participants = getEventParticipants($ev);
        foreach ($participants as $p) {
            $studentMap[$p][] = $ev;
        }
    }

    foreach ($events as &$ev) {
        $duration = timeToSeconds($ev['finish_time']) - timeToSeconds($ev['start_time']);
        $participants = getEventParticipants($ev);

        // check conflicts for this event
        $conflict = false;
        foreach ($participants as $p) {
            foreach ($studentMap[$p] as $otherEv) {
                if ($otherEv['id'] == $ev['id']) continue;
                $s1 = timeToSeconds($ev['start_time']);
                $e1 = timeToSeconds($ev['finish_time']);
                $s2 = timeToSeconds($otherEv['start_time']);
                $e2 = timeToSeconds($otherEv['finish_time']);
                if ($s1 < $e2 && $e1 > $s2) {
                    $conflict = true;
                    break 2;
                }
            }
        }

        if ($conflict) {
            // find next free slot
            $newStart = timeToSeconds($minStart);
            while (true) {
                $newEnd = $newStart + $duration;
                $slotOk = true;
                foreach ($participants as $p) {
                    if (hasConflict($studentMap[$p], $newStart, $newEnd)) {
                        $slotOk = false;
                        break;
                    }
                }
                if ($slotOk) {
                    $ev['start_time']  = secondsToTime($newStart);
                    $ev['finish_time'] = secondsToTime($newEnd);
                    // update DB
                    $upd = $pdo->prepare("UPDATE schedulingtimings SET start_time=?, finish_time=? WHERE id=?");
                    $upd->execute([$ev['start_time'], $ev['finish_time'], $ev['id']]);
                    break;
                }
                $newStart += 60; // shift forward 1 minute
                if ($newStart > timeToSeconds($maxEnd)) break; // stop if exceeds day max
            }
        }
    }
}


header("Location: ".HTTP_PATH.'/admin/schedulingtimings/conflictdone/'.$convention_season_slug);
//exit;
//echo "Rescheduling complete.\n";






// Convert to seconds helper
function timeToSeconds($time) {
    [$h, $m, $s] = explode(':', $time);
    return $h*3600 + $m*60 + $s;
}
function secondsToTime($sec) {
    return sprintf("%02d:%02d:%02d", floor($sec/3600), floor(($sec%3600)/60), $sec%60);
}

// Build student → events mapping (considering groups too)
function getEventParticipants($row) {
    $ids = [];
    if ($row['user_id']) $ids[] = $row['user_id'];
    if ($row['user_id_opponent']) $ids[] = $row['user_id_opponent'];
    if (!empty($row['group_name_user_ids'])) {
        $ids = array_merge($ids, explode(",", $row['group_name_user_ids']));
    }
    if (!empty($row['group_name_opponent_user_ids'])) {
        $ids = array_merge($ids, explode(",", $row['group_name_opponent_user_ids']));
    }
    return array_unique(array_filter($ids));
}

// Conflict check
function hasConflict($studentEvents, $startSec, $endSec) {
    foreach ($studentEvents as $ev) {
        $evStart = timeToSeconds($ev['start_time']);
        $evEnd   = timeToSeconds($ev['finish_time']);
        if ($startSec < $evEnd && $endSec > $evStart) {
            return true;
        }
    }
    return false;
}