-- Schedule Health Audit
-- Usage:
--   SET @slug='convention-season-11-2025-1746674220-38249';
--   SOURCE query_schedule_health.sql;

SET @csid = (SELECT id FROM conventionseasons WHERE slug=@slug LIMIT 1);

SELECT @csid AS conventionseason_id;

-- Assignment coverage by category
SELECT schedule_category,
       COUNT(*) AS total_rows,
       SUM(CASE WHEN day IS NULL OR start_time IS NULL OR finish_time IS NULL OR room_id IS NULL THEN 1 ELSE 0 END) AS unassigned_rows,
       SUM(CASE WHEN day IS NOT NULL AND start_time IS NOT NULL AND finish_time IS NOT NULL AND room_id IS NOT NULL THEN 1 ELSE 0 END) AS assigned_rows
FROM schedulingtimings
WHERE conventionseasons_id=@csid
GROUP BY schedule_category
ORDER BY schedule_category;

-- Weekend overflow rows
SELECT COUNT(*) AS weekend_overflow_rows
FROM schedulingtimings
WHERE conventionseasons_id=@csid
  AND day IN ('Friday','Saturday','Sunday');

-- True same-category room conflicts (different events)
SELECT COUNT(*) AS same_category_room_conflicts_diff_event
FROM schedulingtimings a
JOIN schedulingtimings b
  ON a.conventionseasons_id=b.conventionseasons_id
 AND a.id < b.id
 AND a.schedule_category=b.schedule_category
 AND a.day=b.day
 AND a.room_id=b.room_id
 AND a.start_time < b.finish_time
 AND a.finish_time > b.start_time
 AND IFNULL(a.event_id_number,'') <> IFNULL(b.event_id_number,'')
WHERE a.conventionseasons_id=@csid
  AND a.day IS NOT NULL AND b.day IS NOT NULL
  AND a.room_id IS NOT NULL AND b.room_id IS NOT NULL
  AND a.start_time IS NOT NULL AND a.finish_time IS NOT NULL
  AND b.start_time IS NOT NULL AND b.finish_time IS NOT NULL;

DROP TABLE IF EXISTS z_health_participants;
CREATE TABLE z_health_participants (
  timing_id BIGINT,
  schedule_category INT,
  day VARCHAR(255),
  start_time TIME,
  finish_time TIME,
  event_id_number VARCHAR(255),
  participant_id INT,
  KEY idx_participant (participant_id),
  KEY idx_time (day,start_time,finish_time),
  KEY idx_timing (timing_id),
  KEY idx_cat (schedule_category)
);

INSERT INTO z_health_participants (timing_id,schedule_category,day,start_time,finish_time,event_id_number,participant_id)
SELECT DISTINCT timing_id,schedule_category,day,start_time,finish_time,event_id_number,participant_id
FROM (
  SELECT id AS timing_id,schedule_category,day,start_time,finish_time,event_id_number,user_id AS participant_id
  FROM schedulingtimings
  WHERE conventionseasons_id=@csid
    AND user_id IS NOT NULL
  UNION ALL
  SELECT id AS timing_id,schedule_category,day,start_time,finish_time,event_id_number,user_id_opponent AS participant_id
  FROM schedulingtimings
  WHERE conventionseasons_id=@csid
    AND user_id_opponent IS NOT NULL
) x;

-- Same-category participant conflicts (different events)
SELECT COUNT(*) AS same_category_participant_conflicts_diff_event
FROM z_health_participants a
JOIN z_health_participants b
  ON a.timing_id < b.timing_id
 AND a.participant_id=b.participant_id
 AND a.schedule_category=b.schedule_category
 AND a.day=b.day
 AND a.start_time < b.finish_time
 AND a.finish_time > b.start_time
 AND IFNULL(a.event_id_number,'') <> IFNULL(b.event_id_number,'')
WHERE a.participant_id > 0 AND b.participant_id > 0;

-- Cross-category participant conflicts (different events)
SELECT COUNT(*) AS cross_category_participant_conflicts_diff_event
FROM z_health_participants a
JOIN z_health_participants b
  ON a.timing_id < b.timing_id
 AND a.participant_id=b.participant_id
 AND a.schedule_category<>b.schedule_category
 AND a.day=b.day
 AND a.start_time < b.finish_time
 AND a.finish_time > b.start_time
 AND IFNULL(a.event_id_number,'') <> IFNULL(b.event_id_number,'')
WHERE a.participant_id > 0 AND b.participant_id > 0;
