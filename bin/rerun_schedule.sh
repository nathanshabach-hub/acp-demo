#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"

DEFAULT_DB="convention_acpdemo"
DEFAULT_WEB_CONTAINER="acp-web"
DEFAULT_DB_CONTAINER="mysql-db"
DEFAULT_ADMIN_ID="1"
DEFAULT_ADMIN_USERNAME="Events"
DEFAULT_MAX_RESOLVE_PASSES="20"

usage() {
  cat <<'EOF'
Usage: bash acp_demo/bin/rerun_schedule.sh <convention-season-slug> [options]

Options:
  --season-id <id>           Use a known convention season ID instead of looking it up by slug.
  --db-name <name>           Database name. Default: convention_acpdemo
  --web-container <name>     PHP app container. Default: acp-web
  --db-container <name>      MySQL container. Default: mysql-db
  --admin-id <id>            Admin session ID. Default: 1
  --admin-username <name>    Admin session username. Default: Events
  --max-resolve-passes <n>   Max resolveconflicts passes. Default: 20
  -h, --help                 Show this help.

The script runs the scheduler in the same order as the app flow:
  startschedulec1 -> startschedulec2 -> startschedulec3 -> startschedulec4
  -> fillgroupuserids -> listconflicts -> resolveconflicts until clear
EOF
}

require_command() {
  local command_name="$1"
  if ! command -v "$command_name" >/dev/null 2>&1; then
    echo "Missing required command: $command_name" >&2
    exit 1
  fi
}

sql_escape() {
  printf "%s" "$1" | sed "s/'/''/g"
}

mysql_query() {
  local query="$1"
  docker exec -e MYSQL_PWD="$DB_PASSWORD" "$DB_CONTAINER" \
    mysql -N -u"$DB_USER" "$DB_NAME" -e "$query"
}

run_controller_action() {
  local controller="$1"
  local action="$2"

  docker exec \
    -e ACP_CONTROLLER="$controller" \
    -e ACP_ACTION="$action" \
    -e ACP_SLUG="$SLUG" \
    -e ACP_ADMIN_ID="$ADMIN_ID" \
    -e ACP_ADMIN_USERNAME="$ADMIN_USERNAME" \
    -i -w /var/www/html/acp_demo "$WEB_CONTAINER" php <<'PHP' >/dev/null
<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
set_time_limit(0);

require 'config/bootstrap.php';

use Cake\Network\Request;
use Cake\Network\Response;

$controllerName = getenv('ACP_CONTROLLER');
$action = getenv('ACP_ACTION');
$slug = getenv('ACP_SLUG');
$adminId = getenv('ACP_ADMIN_ID');
$adminUsername = getenv('ACP_ADMIN_USERNAME');

$request = new Request('/admin/' . strtolower($controllerName) . '/' . $action . '/' . $slug);
$request->params['prefix'] = 'admin';
$request->params['controller'] = $controllerName;
$request->params['action'] = $action;
$request->params['pass'] = [$slug];

if ($controllerName === 'Schedulings' && $action === 'resolveconflicts') {
    $request->query = ['ref' => 'schedulecategory'];
}

$request->session()->write('admin_id', (int)$adminId);
$request->session()->write('admin_username', $adminUsername);

$className = '\\App\\Controller\\Admin\\' . $controllerName . 'Controller';
$controller = new $className($request, new Response());

ob_start();
$controller->$action($slug);
ob_end_clean();
PHP
}

print_category_counts() {
  mysql_query "SELECT schedule_category, COUNT(*) FROM schedulingtimings WHERE conventionseasons_id = $SEASON_ID GROUP BY schedule_category ORDER BY schedule_category;"
}

print_final_summary() {
  local placement_summary
  local category_summary
  local conflict_summary

  placement_summary="$(mysql_query "SELECT COUNT(*) AS total_rows, SUM(CASE WHEN day IS NULL OR start_time IS NULL OR finish_time IS NULL THEN 1 ELSE 0 END) AS unscheduled_rows, SUM(CASE WHEN day IN ('Friday','Saturday','Sunday') THEN 1 ELSE 0 END) AS overflow_rows FROM schedulingtimings WHERE conventionseasons_id = $SEASON_ID;")"
  category_summary="$(print_category_counts)"
  conflict_summary="$(mysql_query "SELECT COALESCE(conflict_user_ids,'NULL'), COALESCE(conflict_user_ids_group,'NULL') FROM schedulings WHERE conventionseasons_id = $SEASON_ID;")"

  echo
  echo "Final placement summary:"
  echo "$placement_summary"
  echo
  echo "Category counts:"
  echo "$category_summary"
  echo
  echo "Stored conflict fields:"
  echo "$conflict_summary"
}

require_command docker
require_command sed

if [[ $# -eq 0 ]]; then
  usage
  exit 1
fi

SLUG=""
SEASON_ID=""
DB_NAME="$DEFAULT_DB"
WEB_CONTAINER="$DEFAULT_WEB_CONTAINER"
DB_CONTAINER="$DEFAULT_DB_CONTAINER"
ADMIN_ID="$DEFAULT_ADMIN_ID"
ADMIN_USERNAME="$DEFAULT_ADMIN_USERNAME"
MAX_RESOLVE_PASSES="$DEFAULT_MAX_RESOLVE_PASSES"
DB_USER="root"
DB_PASSWORD="rootpass"

while [[ $# -gt 0 ]]; do
  case "$1" in
    -h|--help)
      usage
      exit 0
      ;;
    --season-id)
      SEASON_ID="$2"
      shift 2
      ;;
    --db-name)
      DB_NAME="$2"
      shift 2
      ;;
    --web-container)
      WEB_CONTAINER="$2"
      shift 2
      ;;
    --db-container)
      DB_CONTAINER="$2"
      shift 2
      ;;
    --admin-id)
      ADMIN_ID="$2"
      shift 2
      ;;
    --admin-username)
      ADMIN_USERNAME="$2"
      shift 2
      ;;
    --max-resolve-passes)
      MAX_RESOLVE_PASSES="$2"
      shift 2
      ;;
    --*)
      echo "Unknown option: $1" >&2
      usage
      exit 1
      ;;
    *)
      if [[ -z "$SLUG" ]]; then
        SLUG="$1"
      else
        echo "Unexpected argument: $1" >&2
        usage
        exit 1
      fi
      shift
      ;;
  esac
done

if [[ -z "$SLUG" ]]; then
  echo "Missing convention-season slug." >&2
  usage
  exit 1
fi

if [[ -z "$SEASON_ID" ]]; then
  ESCAPED_SLUG="$(sql_escape "$SLUG")"
  SEASON_ID="$(mysql_query "SELECT id FROM conventionseasons WHERE slug = '$ESCAPED_SLUG' LIMIT 1;")"
fi

if [[ -z "$SEASON_ID" ]]; then
  echo "Could not find a convention season for slug: $SLUG" >&2
  exit 1
fi

echo "Re-running schedule for slug: $SLUG"
echo "Convention season ID: $SEASON_ID"
echo

for action in startschedulec1 startschedulec2 startschedulec3 startschedulec4 fillgroupuserids listconflicts; do
  echo "Running SchedulingtimingsController::$action"
  run_controller_action "Schedulingtimings" "$action"
done

echo
echo "Resolving conflicts"
for pass in $(seq 1 "$MAX_RESOLVE_PASSES"); do
  run_controller_action "Schedulings" "resolveconflicts"
  remaining="$(mysql_query "SELECT COALESCE(conflict_user_ids,'NULL') FROM schedulings WHERE conventionseasons_id = $SEASON_ID LIMIT 1;")"
  echo "  pass $pass: conflict_user_ids=$remaining"
  if [[ "$remaining" == "NULL" ]]; then
    break
  fi
done

print_final_summary