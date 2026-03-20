#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
WEB_CONTAINER="${WEB_CONTAINER:-acp-web}"
DB_CONTAINER="${DB_CONTAINER:-mysql-db}"
DB_NAME="${DB_NAME:-convention_acpdemo}"
DB_USER="${DB_USER:-root}"
DB_PASSWORD="${DB_PASSWORD:-rootpass}"
SEASON_ID="${SEASON_ID:-}"
BASE_URL="${BASE_URL:-http://127.0.0.1}"

usage() {
  cat <<EOF
Usage: bash acp_demo/bin/smoke_gate.sh --season-id <id> [--base-url <url>]

Checks performed:
1) Fails if error/debug logs are non-empty after smoke requests.
2) Fails if SQL exceptions are found in logs.
3) Fails if unresolved scheduling placements exist for the season.

Environment overrides:
  WEB_CONTAINER, DB_CONTAINER, DB_NAME, DB_USER, DB_PASSWORD
EOF
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --season-id)
      SEASON_ID="$2"
      shift 2
      ;;
    --base-url)
      BASE_URL="$2"
      shift 2
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    *)
      echo "Unknown option: $1" >&2
      usage
      exit 1
      ;;
  esac
done

if [[ -z "$SEASON_ID" ]]; then
  echo "Missing --season-id" >&2
  usage
  exit 1
fi

echo "[1/5] Clearing app logs"
docker exec "$WEB_CONTAINER" sh -lc 'truncate -s 0 /var/www/html/acp_demo/logs/error.log 2>/dev/null || true; truncate -s 0 /var/www/html/acp_demo/logs/debug.log 2>/dev/null || true'

echo "[2/5] Running smoke HTTP requests"
docker exec -i "$WEB_CONTAINER" php <<'PHP'
<?php
$base = getenv('BASE_URL') ?: 'http://127.0.0.1';
$urls = [
    '/users/login',
    '/admin/admins/login',
    '/',
];
foreach ($urls as $path) {
    $u = rtrim($base, '/') . $path;
    $ctx = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 15]]);
    @file_get_contents($u, false, $ctx);
    echo "checked: $path\n";
}
PHP

echo "[3/5] Validating scheduling placement status"
UNSCHEDULED=$(docker exec "$DB_CONTAINER" sh -lc "MYSQL_PWD='$DB_PASSWORD' mysql -N -B -u'$DB_USER' -D '$DB_NAME' -e \"SELECT COALESCE(SUM(CASE WHEN (day IS NULL OR start_time IS NULL OR finish_time IS NULL) AND IFNULL(is_bye,0)=0 THEN 1 ELSE 0 END),0) FROM schedulingtimings WHERE conventionseasons_id=$SEASON_ID;\"" | tr -d '\r')

if [[ "$UNSCHEDULED" =~ ^[0-9]+$ ]] && [[ "$UNSCHEDULED" -gt 0 ]]; then
  echo "FAIL: unresolved scheduling placements found for conventionseason_id=$SEASON_ID (unscheduled=$UNSCHEDULED)" >&2
  exit 2
fi

echo "[4/5] Validating log files are empty"
LOG_BYTES=$(docker exec "$WEB_CONTAINER" sh -lc 'wc -c /var/www/html/acp_demo/logs/error.log /var/www/html/acp_demo/logs/debug.log 2>/dev/null | tail -n 1 | awk "{print \$1}"')
if [[ "$LOG_BYTES" =~ ^[0-9]+$ ]] && [[ "$LOG_BYTES" -gt 0 ]]; then
  echo "FAIL: logs are not empty after smoke run (bytes=$LOG_BYTES)" >&2
  docker exec "$WEB_CONTAINER" sh -lc 'echo "--- error.log ---"; cat /var/www/html/acp_demo/logs/error.log 2>/dev/null || true; echo "--- debug.log ---"; cat /var/www/html/acp_demo/logs/debug.log 2>/dev/null || true'
  exit 3
fi

echo "[5/5] Checking for SQL exceptions in logs"
SQL_ERRS=$(docker exec "$WEB_CONTAINER" sh -lc 'cat /var/www/html/acp_demo/logs/error.log /var/www/html/acp_demo/logs/debug.log 2>/dev/null | grep -Ei "SQLSTATE|PDOException|Database Error|QueryException" || true')
if [[ -n "$SQL_ERRS" ]]; then
  echo "FAIL: SQL exceptions detected under strict mode checks" >&2
  echo "$SQL_ERRS" >&2
  exit 4
fi

echo "PASS: smoke gate checks passed"
