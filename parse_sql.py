import re
import sys

sql_file = "convention_acpdemo.sql"
target_ids = {'7564', '7707', '7714', '8839', '8856', '8877'}
in_table = False

columns = [
    'id', 'schedule_category', 'conventionseasons_id', 'convention_id', 'season_id', 'season_year', 'conventionregistration_id', 'event_id', 'event_id_number', 'user_type', 'user_id', 'group_name', 'group_name_user_ids', 'room_id', 'sch_date_time', 'day', 'start_time', 'finish_time', 'user_id_opponent', 'schtimeautoid1', 'schtimeautoid2', 'round_number', 'match_number', 'is_bye', 'group_name_opponent', 'group_name_opponent_user_ids', 'created', 'modified'
]

print("Scanning for conflicts...")
with open(sql_file, "r", encoding="utf-8") as f:
    for line in f:
        if line.startswith("INSERT INTO `schedulingtimings`"):
            in_table = True
        elif line.startswith("INSERT INTO") and not line.startswith("INSERT INTO `schedulingtimings`"):
            in_table = False
            
        if in_table:
            # find all tuples (...)
            tuples = re.findall(r'\((.*?)\)', line)
            for t in tuples:
                parts = [p.strip().strip("'") for p in t.split(',')]
                if len(parts) >= 19:
                    user_id = parts[10]
                    user_id_opponent = parts[18]
                    group_users = parts[12]
                    group_opps = parts[25]
                    
                    found = False
                    matched_id = None
                    for tid in target_ids:
                        if user_id == tid or user_id_opponent == tid or tid in group_users or tid in group_opps:
                            found = True
                            matched_id = tid
                            break
                            
                    if found:
                        event_id_num = parts[8]
                        start_time = parts[16]
                        finish_time = parts[17]
                        day = parts[15]
                        print(f"Match for target {matched_id}! Sch.ID: {parts[0]} Event: {event_id_num} user_id: {user_id} opponent: {user_id_opponent} GroupUsers: {group_users} Day: {day} Time: {start_time}-{finish_time}")
