#!/usr/bin/env bash
set -euo pipefail

# Usage:
#   ./acp_demo/bin/schedule_health_audit.sh [season_id]
# Example:
#   ./acp_demo/bin/schedule_health_audit.sh 16

SEASON_ID="${1:-16}"

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
ACP_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

if [[ ! -f "$ACP_ROOT/compose.yaml" ]]; then
  echo "ERROR: compose.yaml not found at $ACP_ROOT"
  exit 1
fi

echo "== ACP Schedule Health Audit =="
echo "Season ID: $SEASON_ID"
echo "Root: $ACP_ROOT"
echo

echo "-- Basic coverage metrics --"
cd "$ACP_ROOT"
docker compose exec -T mysql-db mysql -uroot -prootpass convention_acpdemo -e "
SELECT 'total_rows_non_bye' AS metric, COUNT(*) AS value
FROM schedulingtimings
WHERE conventionseasons_id = ${SEASON_ID} AND is_bye != 1
UNION ALL
SELECT 'school_rows_with_group_name', COUNT(*)
FROM schedulingtimings
WHERE conventionseasons_id = ${SEASON_ID}
  AND user_type = 'School'
  AND group_name IS NOT NULL AND group_name <> ''
  AND is_bye != 1
UNION ALL
SELECT 'school_rows_group_name_user_ids_filled', COUNT(*)
FROM schedulingtimings
WHERE conventionseasons_id = ${SEASON_ID}
  AND user_type = 'School'
  AND group_name IS NOT NULL AND group_name <> ''
  AND group_name_user_ids IS NOT NULL AND group_name_user_ids <> ''
  AND is_bye != 1
UNION ALL
SELECT 'school_rows_with_group_opponent', COUNT(*)
FROM schedulingtimings
WHERE conventionseasons_id = ${SEASON_ID}
  AND user_type = 'School'
  AND group_name_opponent IS NOT NULL AND group_name_opponent <> ''
  AND is_bye != 1
UNION ALL
SELECT 'school_rows_group_name_opponent_user_ids_filled', COUNT(*)
FROM schedulingtimings
WHERE conventionseasons_id = ${SEASON_ID}
  AND user_type = 'School'
  AND group_name_opponent IS NOT NULL AND group_name_opponent <> ''
  AND group_name_opponent_user_ids IS NOT NULL AND group_name_opponent_user_ids <> ''
  AND is_bye != 1;
"

echo
echo "-- Conflict coverage comparison --"
docker compose exec -T mysql-db mysql -uroot -prootpass convention_acpdemo -e "
SELECT 'old_student_only_pairs' AS metric, COUNT(*) AS value
FROM (
  SELECT st.user_id AS student_id, st.id AS timing_id, st.day, st.start_time, st.finish_time
  FROM schedulingtimings st
  WHERE st.conventionseasons_id = ${SEASON_ID}
    AND st.user_type = 'Student'
    AND st.user_id > 0
    AND st.is_bye != 1
    AND st.start_time IS NOT NULL
) p1
JOIN (
  SELECT st.user_id AS student_id, st.id AS timing_id, st.day, st.start_time, st.finish_time
  FROM schedulingtimings st
  WHERE st.conventionseasons_id = ${SEASON_ID}
    AND st.user_type = 'Student'
    AND st.user_id > 0
    AND st.is_bye != 1
    AND st.start_time IS NOT NULL
) p2
  ON p1.student_id = p2.student_id
 AND p1.timing_id < p2.timing_id
 AND p1.day = p2.day
WHERE p1.start_time < p2.finish_time
  AND p1.finish_time > p2.start_time
UNION ALL
SELECT 'canonical_all_rows_pairs', COUNT(*)
FROM (
  SELECT cse.student_id, st.id AS timing_id, st.day, st.start_time, st.finish_time
  FROM schedulingtimings st
  JOIN crstudentevents cse
    ON cse.conventionseason_id = st.conventionseasons_id
   AND cse.user_id = st.user_id
   AND cse.event_id = st.event_id
   AND cse.group_name = st.group_name
  WHERE st.conventionseasons_id = ${SEASON_ID}
    AND st.user_type = 'School'
    AND st.is_bye != 1
    AND st.start_time IS NOT NULL

  UNION ALL

  SELECT st.user_id AS student_id, st.id AS timing_id, st.day, st.start_time, st.finish_time
  FROM schedulingtimings st
  WHERE st.conventionseasons_id = ${SEASON_ID}
    AND st.user_type = 'Student'
    AND st.user_id > 0
    AND st.is_bye != 1
    AND st.start_time IS NOT NULL
) p1
JOIN (
  SELECT cse.student_id, st.id AS timing_id, st.day, st.start_time, st.finish_time
  FROM schedulingtimings st
  JOIN crstudentevents cse
    ON cse.conventionseason_id = st.conventionseasons_id
   AND cse.user_id = st.user_id
   AND cse.event_id = st.event_id
   AND cse.group_name = st.group_name
  WHERE st.conventionseasons_id = ${SEASON_ID}
    AND st.user_type = 'School'
    AND st.is_bye != 1
    AND st.start_time IS NOT NULL

  UNION ALL

  SELECT st.user_id AS student_id, st.id AS timing_id, st.day, st.start_time, st.finish_time
  FROM schedulingtimings st
  WHERE st.conventionseasons_id = ${SEASON_ID}
    AND st.user_type = 'Student'
    AND st.user_id > 0
    AND st.is_bye != 1
    AND st.start_time IS NOT NULL
) p2
  ON p1.student_id = p2.student_id
 AND p1.timing_id < p2.timing_id
 AND p1.day = p2.day
WHERE p1.start_time < p2.finish_time
  AND p1.finish_time > p2.start_time;
"

echo
echo "-- Sample canonical conflict rows (up to 20) --"
docker compose exec -T mysql-db mysql -uroot -prootpass convention_acpdemo -e "
SELECT p1.student_id, p1.timing_id AS slot_a, p1.day,
       p1.start_time AS a_start, p1.finish_time AS a_finish,
       p2.timing_id AS slot_b, p2.start_time AS b_start, p2.finish_time AS b_finish
FROM (
  SELECT cse.student_id, st.id AS timing_id, st.day, st.start_time, st.finish_time
  FROM schedulingtimings st
  JOIN crstudentevents cse
    ON cse.conventionseason_id = st.conventionseasons_id
   AND cse.user_id = st.user_id
   AND cse.event_id = st.event_id
   AND cse.group_name = st.group_name
  WHERE st.conventionseasons_id = ${SEASON_ID}
    AND st.user_type = 'School'
    AND st.is_bye != 1
    AND st.start_time IS NOT NULL

  UNION ALL

  SELECT st.user_id AS student_id, st.id AS timing_id, st.day, st.start_time, st.finish_time
  FROM schedulingtimings st
  WHERE st.conventionseasons_id = ${SEASON_ID}
    AND st.user_type = 'Student'
    AND st.user_id > 0
    AND st.is_bye != 1
    AND st.start_time IS NOT NULL
) p1
JOIN (
  SELECT cse.student_id, st.id AS timing_id, st.day, st.start_time, st.finish_time
  FROM schedulingtimings st
  JOIN crstudentevents cse
    ON cse.conventionseason_id = st.conventionseasons_id
   AND cse.user_id = st.user_id
   AND cse.event_id = st.event_id
   AND cse.group_name = st.group_name
  WHERE st.conventionseasons_id = ${SEASON_ID}
    AND st.user_type = 'School'
    AND st.is_bye != 1
    AND st.start_time IS NOT NULL

  UNION ALL

  SELECT st.user_id AS student_id, st.id AS timing_id, st.day, st.start_time, st.finish_time
  FROM schedulingtimings st
  WHERE st.conventionseasons_id = ${SEASON_ID}
    AND st.user_type = 'Student'
    AND st.user_id > 0
    AND st.is_bye != 1
    AND st.start_time IS NOT NULL
) p2
  ON p1.student_id = p2.student_id
 AND p1.timing_id < p2.timing_id
 AND p1.day = p2.day
WHERE p1.start_time < p2.finish_time
  AND p1.finish_time > p2.start_time
ORDER BY p1.student_id, p1.day, p1.timing_id, p2.timing_id
LIMIT 20;
"

echo
echo "Audit complete."
