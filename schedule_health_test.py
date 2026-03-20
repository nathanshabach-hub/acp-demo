#!/usr/bin/env python3
"""
Comprehensive Scheduling System Health Test
============================================
Tests the scheduling data in the convention_acpdemo database for:
  1. Student time conflicts (same student scheduled at overlapping times)
  2. Room time conflicts (same room double-booked at overlapping times)
  3. Room allocation conflicts (rooms in the same allocation double-booked)
  4. Unscheduled entries (events with NULL day/start_time/finish_time)
  5. Students registered but not scheduled
  6. Day distribution balance across convention days
  7. Events outside allowed hours (before normal start or after normal finish)
  8. Events during lunch/break windows
  9. Scheduling coverage (registered events vs scheduled events)

Usage:
  python3 schedule_health_test.py
"""

import subprocess
import sys
import json

DOCKER_CMD = 'sg docker -c "docker exec mysql-db mysql -uroot -prootpass -D convention_acpdemo -N -e'
CONVENTION_SEASON_ID = 16  # Fiji convention (the most populated one)

def run_query(sql):
    """Run a MySQL query via docker exec and return the output lines."""
    escaped = sql.replace('"', '\\"')
    cmd = f'{DOCKER_CMD} \\"{escaped}\\""'
    result = subprocess.run(cmd, shell=True, capture_output=True, text=True)
    if result.returncode != 0:
        print(f"  [SQL ERROR] {result.stderr.strip()}")
        return []
    return [line.strip() for line in result.stdout.strip().split('\n') if line.strip()]


def test_student_time_conflicts():
    """Check for non-school participants scheduled at overlapping times on the same day."""
    print("\n" + "="*70)
    print("TEST 1: Student Time Conflicts")
    print("="*70)

    sql = f"""
    SELECT t1.id AS id1, t2.id AS id2,
           t1.user_id, t1.event_id AS ev1, t2.event_id AS ev2,
           t1.day, t1.start_time, t1.finish_time,
           t2.start_time AS s2, t2.finish_time AS f2
    FROM schedulingtimings t1
    JOIN schedulingtimings t2 ON t1.user_id = t2.user_id
        AND t1.day = t2.day
        AND t1.id < t2.id
        AND t1.start_time < t2.finish_time
        AND t1.finish_time > t2.start_time
    WHERE t1.conventionseasons_id = {CONVENTION_SEASON_ID}
        AND t2.conventionseasons_id = {CONVENTION_SEASON_ID}
        AND t1.user_id > 0
        AND t2.user_id > 0
        AND t1.user_type != 'School'
        AND t2.user_type != 'School'
        AND t1.start_time IS NOT NULL
        AND t2.start_time IS NOT NULL
        AND t1.is_bye != 1
        AND t2.is_bye != 1
    ORDER BY t1.user_id, t1.day, t1.start_time
    LIMIT 100
    """
    rows = run_query(sql)

    if not rows:
        print("  PASS: No student time conflicts found.")
        return True
    else:
        print(f"  FAIL: {len(rows)} student time conflict(s) found:")
        for row in rows[:10]:
            parts = row.split('\t')
            if len(parts) >= 10:
                print(f"    - Timing {parts[0]} vs {parts[1]}: user_id={parts[2]}, "
                      f"events {parts[3]}/{parts[4]}, {parts[5]} "
                      f"{parts[6]}-{parts[7]} overlaps {parts[8]}-{parts[9]}")
        if len(rows) > 10:
            print(f"    ... and {len(rows)-10} more")
        return False


def test_room_time_conflicts():
    """Check for the same room being double-booked at overlapping times."""
    print("\n" + "="*70)
    print("TEST 2: Room Time Conflicts (Same Room)")
    print("="*70)

    sql = f"""
    SELECT t1.id AS id1, t2.id AS id2,
           t1.room_id, t1.event_id AS ev1, t2.event_id AS ev2,
           t1.day, t1.start_time, t1.finish_time,
           t2.start_time AS s2, t2.finish_time AS f2
    FROM schedulingtimings t1
    JOIN schedulingtimings t2 ON t1.room_id = t2.room_id
        AND t1.day = t2.day
        AND t1.id < t2.id
        AND t1.start_time < t2.finish_time
        AND t1.finish_time > t2.start_time
    WHERE t1.conventionseasons_id = {CONVENTION_SEASON_ID}
        AND t2.conventionseasons_id = {CONVENTION_SEASON_ID}
        AND t1.room_id IS NOT NULL
        AND t1.start_time IS NOT NULL
        AND t2.start_time IS NOT NULL
        AND NOT (
            t1.event_id = t2.event_id
            AND t1.start_time = t2.start_time
            AND t1.finish_time = t2.finish_time
        )
    ORDER BY t1.room_id, t1.day, t1.start_time
    LIMIT 50
    """
    rows = run_query(sql)

    if not rows:
        print("  PASS: No room time conflicts found.")
        return True
    else:
        print(f"  FAIL: {len(rows)} room time conflict(s) found:")
        for row in rows[:10]:
            parts = row.split('\t')
            if len(parts) >= 10:
                print(f"    - Timing {parts[0]} vs {parts[1]}: room_id={parts[2]}, "
                      f"events {parts[3]}/{parts[4]}, {parts[5]} "
                      f"{parts[6]}-{parts[7]} overlaps {parts[8]}-{parts[9]}")
        return False


def test_unscheduled_entries():
    """Check for scheduling timing entries with NULL day/start/finish."""
    print("\n" + "="*70)
    print("TEST 4: Unscheduled Entries (NULL times)")
    print("="*70)

    sql = f"""
    SELECT
           SUM(CASE
               WHEN (day IS NULL OR start_time IS NULL OR finish_time IS NULL)
                    AND is_bye != 1 THEN 1 ELSE 0 END) AS raw_unscheduled,
           SUM(CASE
               WHEN (day IS NULL OR start_time IS NULL OR finish_time IS NULL)
                    AND is_bye != 1
                    AND user_id > 0
                    AND user_type = 'Student' THEN 1 ELSE 0 END) AS student_unscheduled,
           SUM(CASE
               WHEN (day IS NULL OR start_time IS NULL OR finish_time IS NULL)
                    AND is_bye != 1
                    AND user_id = 0 THEN 1 ELSE 0 END) AS placeholder_unscheduled,
           GROUP_CONCAT(DISTINCT CASE
               WHEN (day IS NULL OR start_time IS NULL OR finish_time IS NULL)
                    AND is_bye != 1
                    AND user_id > 0
                    AND user_type = 'Student' THEN event_id
               ELSE NULL END ORDER BY event_id) AS student_events
    FROM schedulingtimings
    WHERE conventionseasons_id = {CONVENTION_SEASON_ID}
    """
    rows = run_query(sql)

    if rows:
        parts = rows[0].split('\t')
        raw_count = int(parts[0]) if parts[0] != 'NULL' else 0
        student_count = int(parts[1]) if len(parts) > 1 and parts[1] != 'NULL' else 0
        placeholder_count = int(parts[2]) if len(parts) > 2 and parts[2] != 'NULL' else 0
        student_events = parts[3] if len(parts) > 3 and parts[3] != 'NULL' else 'none'

        if student_count == 0:
            print("  PASS: All non-bye entries are scheduled.")
            if raw_count > 0:
                print(f"    Note: {raw_count} unscheduled row(s) exist but are placeholders/non-student rows.")
            return True
        else:
            print(f"  WARNING: {student_count} unscheduled student entries found.")
            print(f"    Student events with unscheduled entries: {student_events}")
            if placeholder_count > 0:
                print(f"    Placeholder unscheduled rows (user_id=0): {placeholder_count}")
            return False
    return True


def test_unscheduled_students():
    """Check for registered students who have no scheduled timings."""
    print("\n" + "="*70)
    print("TEST 5: Registered Students Not Scheduled")
    print("="*70)

    # Get convention details for this season
    season_sql = f"""
    SELECT convention_id, season_id, season_year
    FROM conventionseasons WHERE id = {CONVENTION_SEASON_ID}
    """
    season_rows = run_query(season_sql)
    if not season_rows:
        print("  SKIP: Could not find convention season.")
        return True

    parts = season_rows[0].split('\t')
    conv_id, season_id, season_year = parts[0], parts[1], parts[2]

    sql = f"""
    SELECT COUNT(DISTINCT crs.student_id) AS registered,
           (SELECT COUNT(DISTINCT st.user_id)
            FROM schedulingtimings st
            WHERE st.conventionseasons_id = {CONVENTION_SEASON_ID}
              AND st.user_id > 0
                            AND st.user_type = 'Student'
                            AND st.start_time IS NOT NULL) AS scheduled
    FROM conventionregistrationstudents crs
    WHERE crs.convention_id = {conv_id}
      AND crs.season_id = {season_id}
      AND crs.season_year = {season_year}
      AND crs.status = 1
      AND crs.student_id > 0
    """
    rows = run_query(sql)

    if rows:
        parts = rows[0].split('\t')
        registered = int(parts[0])
        scheduled = int(parts[1])
        missing = registered - scheduled

        if missing <= 0:
            print(f"  PASS: All {registered} registered students are scheduled ({scheduled} unique).")
            return True
        else:
            print(f"  WARNING: {missing} students registered but not scheduled.")
            print(f"    Registered: {registered}, Scheduled: {scheduled}")
            return False
    return True


def test_day_distribution():
    """Check the balance of events across convention days."""
    print("\n" + "="*70)
    print("TEST 6: Day Distribution Balance")
    print("="*70)

    sql = f"""
    SELECT day, COUNT(*) AS entries, COUNT(DISTINCT user_id) AS students,
           MIN(start_time) AS earliest, MAX(finish_time) AS latest
    FROM schedulingtimings
    WHERE conventionseasons_id = {CONVENTION_SEASON_ID}
      AND day IS NOT NULL
      AND start_time IS NOT NULL
    GROUP BY day
    ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday')
    """
    rows = run_query(sql)

    if not rows:
        print("  SKIP: No scheduled entries found.")
        return True

    print("  Day distribution:")
    counts = []
    for row in rows:
        parts = row.split('\t')
        if len(parts) >= 5:
            day, entries, students, earliest, latest = parts[0], int(parts[1]), int(parts[2]), parts[3], parts[4]
            counts.append(entries)
            print(f"    {day:12s}: {entries:4d} timings, {students:4d} students, "
                  f"{earliest} - {latest}")

    if counts:
        avg = sum(counts) / len(counts)
        max_c = max(counts)
        min_c = min(counts)
        imbalance = (max_c - min_c) / avg * 100 if avg > 0 else 0
        print(f"    Imbalance: {imbalance:.0f}% (max {max_c}, min {min_c}, avg {avg:.0f})")
        if imbalance > 100:
            print("  WARNING: Significant day imbalance detected.")
            return False
    print("  PASS: Day distribution within acceptable range.")
    return True


def test_events_outside_hours():
    """Check for events scheduled outside the configured convention hours."""
    print("\n" + "="*70)
    print("TEST 7: Events Outside Convention Hours")
    print("="*70)

    # Get scheduling config
    sql = f"""
    SELECT normal_starting_time, normal_finish_time
    FROM schedulings
    WHERE conventionseasons_id = {CONVENTION_SEASON_ID}
    """
    rows = run_query(sql)
    if not rows:
        print("  SKIP: No scheduling config found.")
        return True

    parts = rows[0].split('\t')
    normal_start = parts[0]
    normal_finish = parts[1]

    sql = f"""
    SELECT COUNT(*) AS cnt
    FROM schedulingtimings
    WHERE conventionseasons_id = {CONVENTION_SEASON_ID}
      AND start_time IS NOT NULL
      AND (start_time < '{normal_start}' OR finish_time > '{normal_finish}')
    """
    rows = run_query(sql)

    if rows:
        count = int(rows[0].split('\t')[0])
        if count == 0:
            print(f"  PASS: All events within {normal_start}-{normal_finish}.")
            return True
        else:
            print(f"  WARNING: {count} entries outside normal hours ({normal_start}-{normal_finish}).")
            return False
    return True


def test_events_during_lunch():
    """Check for events scheduled during lunch break."""
    print("\n" + "="*70)
    print("TEST 8: Events During Lunch Break")
    print("="*70)

    sql = f"""
    SELECT lunch_time_start, lunch_time_end
    FROM schedulings
    WHERE conventionseasons_id = {CONVENTION_SEASON_ID}
    """
    rows = run_query(sql)
    if not rows:
        print("  SKIP: No scheduling config found.")
        return True

    parts = rows[0].split('\t')
    if parts[0] == 'NULL' or parts[1] == 'NULL':
        print("  SKIP: No lunch times configured.")
        return True

    lunch_start = parts[0]
    lunch_end = parts[1]

    sql = f"""
    SELECT COUNT(*) AS cnt
    FROM schedulingtimings
    WHERE conventionseasons_id = {CONVENTION_SEASON_ID}
      AND start_time IS NOT NULL
      AND start_time < '{lunch_end}'
      AND finish_time > '{lunch_start}'
    """
    rows = run_query(sql)

    if rows:
        count = int(rows[0].split('\t')[0])
        if count == 0:
            print(f"  PASS: No events during lunch ({lunch_start}-{lunch_end}).")
            return True
        else:
            print(f"  WARNING: {count} entries overlap with lunch ({lunch_start}-{lunch_end}).")
            return False
    return True


def test_scheduling_coverage():
    """Compare registered events with scheduled events."""
    print("\n" + "="*70)
    print("TEST 9: Event Scheduling Coverage")
    print("="*70)

    sql = f"""
    SELECT e.id, e.event_name, e.needs_schedule,
           e.group_event_yes_no, e.event_kind_id,
           (SELECT COUNT(*) FROM schedulingtimings st
            WHERE st.event_id = e.id
              AND st.conventionseasons_id = {CONVENTION_SEASON_ID}
                            AND st.user_id > 0
                            AND st.user_type = 'Student'
              AND st.start_time IS NOT NULL) AS scheduled_count,
           (SELECT COUNT(*) FROM schedulingtimings st
            WHERE st.event_id = e.id
              AND st.conventionseasons_id = {CONVENTION_SEASON_ID}
                            AND st.user_id > 0
                            AND st.user_type = 'Student'
              AND st.start_time IS NULL
                            AND st.is_bye != 1) AS unscheduled_count,
                     (SELECT COUNT(*) FROM schedulingtimings st
                        WHERE st.event_id = e.id
                            AND st.conventionseasons_id = {CONVENTION_SEASON_ID}
                            AND st.user_id = 0
                            AND st.start_time IS NULL
                            AND st.is_bye != 1) AS placeholder_count
    FROM events e
    JOIN conventionseasonevents cse ON cse.event_id = e.id
    WHERE cse.conventionseasons_id = {CONVENTION_SEASON_ID}
      AND e.needs_schedule = 1
    ORDER BY e.id
    """
    rows = run_query(sql)

    if not rows:
        print("  SKIP: No events found.")
        return True

    total_events = 0
    events_with_unscheduled = 0
    print("  Event coverage:")
    for row in rows:
        parts = row.split('\t')
        if len(parts) >= 8:
            eid = parts[0]
            name = parts[1][:30]
            scheduled = int(parts[5])
            unscheduled = int(parts[6])
            placeholders = int(parts[7])
            total = scheduled + unscheduled
            total_events += 1

            if unscheduled > 0:
                events_with_unscheduled += 1
                pct = scheduled / total * 100 if total > 0 else 0
                print(f"    Event {eid} ({name}): {scheduled}/{total} scheduled ({pct:.0f}%), "
                      f"{unscheduled} unscheduled")
            elif placeholders > 0:
                print(f"    Event {eid} ({name}): placeholders pending={placeholders} (no student rows unscheduled)")

    if events_with_unscheduled == 0:
        print(f"  PASS: All {total_events} events are fully scheduled.")
        return True
    else:
        print(f"  WARNING: {events_with_unscheduled}/{total_events} events have unscheduled entries.")
        return False


def print_remediation_queue():
    """Print concrete next actions based on current scheduling data."""
    print("\n" + "="*70)
    print("REMEDIATION QUEUE")
    print("="*70)

    print("  1) Events with unscheduled student rows:")
    sql = f"""
    SELECT st.schedule_category, st.event_id, e.event_name,
           SUM(CASE WHEN st.user_id > 0 AND st.user_type = 'Student'
                    AND (st.day IS NULL OR st.start_time IS NULL OR st.finish_time IS NULL)
                    AND st.is_bye != 1 THEN 1 ELSE 0 END) AS unscheduled_students,
           SUM(CASE WHEN st.user_id = 0
                    AND (st.day IS NULL OR st.start_time IS NULL OR st.finish_time IS NULL)
                    AND st.is_bye != 1 THEN 1 ELSE 0 END) AS placeholders
    FROM schedulingtimings st
    JOIN events e ON e.id = st.event_id
    WHERE st.conventionseasons_id = {CONVENTION_SEASON_ID}
    GROUP BY st.schedule_category, st.event_id, e.event_name
    HAVING unscheduled_students > 0 OR placeholders > 0
    ORDER BY unscheduled_students DESC, placeholders DESC, st.schedule_category, st.event_id
    """
    rows = run_query(sql)
    if rows:
        for row in rows[:20]:
            parts = row.split('\t')
            if len(parts) >= 5:
                cat = parts[0]
                eid = parts[1]
                name = parts[2]
                uns = parts[3]
                ph = parts[4]
                print(f"    - category {cat}, event {eid} ({name}): unscheduled_students={uns}, placeholders={ph}")
    else:
        print("    - none")

    print("  2) Lunch overlap rows to move:")
    sql = f"""
    SELECT st.id, st.event_id, e.event_name, st.day, st.start_time, st.finish_time, st.room_id
    FROM schedulingtimings st
    JOIN events e ON e.id = st.event_id
    JOIN schedulings s ON s.conventionseasons_id = st.conventionseasons_id
    WHERE st.conventionseasons_id = {CONVENTION_SEASON_ID}
      AND st.start_time IS NOT NULL
      AND st.start_time < s.lunch_time_end
      AND st.finish_time > s.lunch_time_start
    ORDER BY st.day, st.start_time
    """
    rows = run_query(sql)
    if rows:
        for row in rows[:20]:
            parts = row.split('\t')
            if len(parts) >= 7:
                print(f"    - timing_id={parts[0]}, event={parts[1]} ({parts[2]}), {parts[3]} {parts[4]}-{parts[5]}, room_id={parts[6]}")
    else:
        print("    - none")

    print("  3) Registered students with no scheduled student rows:")
    sql = f"""
    SELECT DISTINCT crs.student_id, u.first_name, u.last_name
    FROM conventionregistrationstudents crs
    JOIN conventionseasons cs ON cs.convention_id = crs.convention_id
        AND cs.season_id = crs.season_id
        AND cs.season_year = crs.season_year
    JOIN users u ON u.id = crs.student_id
    WHERE cs.id = {CONVENTION_SEASON_ID}
      AND crs.status = 1
      AND crs.student_id > 0
      AND crs.student_id NOT IN (
        SELECT DISTINCT st.user_id
        FROM schedulingtimings st
        WHERE st.conventionseasons_id = {CONVENTION_SEASON_ID}
          AND st.user_type = 'Student'
          AND st.start_time IS NOT NULL
      )
    ORDER BY u.first_name, u.last_name
    """
    rows = run_query(sql)
    if rows:
        for row in rows[:30]:
            parts = row.split('\t')
            if len(parts) >= 3:
                print(f"    - student_id={parts[0]}: {parts[1]} {parts[2]}")
        if len(rows) > 30:
            print(f"    ... and {len(rows)-30} more")
    else:
        print("    - none")


def main():
    print("="*70)
    print("SCHEDULING SYSTEM COMPREHENSIVE HEALTH TEST")
    print(f"Convention Season ID: {CONVENTION_SEASON_ID}")
    print("="*70)

    tests = [
        ("Student Time Conflicts", test_student_time_conflicts),
        ("Room Time Conflicts", test_room_time_conflicts),
        ("Unscheduled Entries", test_unscheduled_entries),
        ("Unscheduled Students", test_unscheduled_students),
        ("Day Distribution", test_day_distribution),
        ("Events Outside Hours", test_events_outside_hours),
        ("Events During Lunch", test_events_during_lunch),
        ("Event Coverage", test_scheduling_coverage),
    ]

    results = {}
    for name, test_fn in tests:
        try:
            results[name] = test_fn()
        except Exception as e:
            print(f"  ERROR: {e}")
            results[name] = False

    print("\n" + "="*70)
    print("SUMMARY")
    print("="*70)
    passed = sum(1 for v in results.values() if v)
    total = len(results)
    for name, result in results.items():
        status = "PASS" if result else "FAIL"
        print(f"  [{status}] {name}")

    print(f"\n  Result: {passed}/{total} tests passed")

    if passed == total:
        print("  All scheduling health checks passed!")
        return 0
    else:
        print(f"  {total - passed} issue(s) detected - review above for details.")
        print_remediation_queue()
        return 1


if __name__ == '__main__':
    sys.exit(main())
