# Scheduling Program 17 Migration Plan (ACP)

## Goal
Adopt the useful scheduling behavior from Program 17 into ACP in controlled, reversible steps.

## Operating Principle
- Implement in small phases.
- Validate with live convention data after each phase.
- Keep each phase reversible (no destructive rewrites).

## Baseline First (Current State Snapshot)
Run baseline checks before each phase and save results:
- Overflow count on Friday/Saturday/Sunday
- Unplaced events count
- Same-room overlap conflicts
- Same-participant overlap conflicts
- Cross-category participant conflicts
- Number of rows scheduled Monday-Thursday

## Phase 1: Room Utilization Parity (High Impact)
Status: In progress

Completed
- Overflow auto-assignment uses available convention rooms and conflict checks.
- Monday-Thursday reassignment behavior kept as default policy.
- Overflow status panel added (weekend overflow, unplaced count, last auto-assign moved count).

Next
- Add one-click action to retry all current overflow against selected Mon-Thu rooms and report:
  - moved
  - still overflow
  - blocked by room conflicts
  - blocked by participant conflicts

Rollback
- Remove/disable the one-click action only (no schedule data rollback needed).

## Phase 2: Event Grouping Parity (High Impact)
Status: In progress

Completed
- Overwrite Timings event list switched to dynamic event sourcing.
- Grouping switched from schedule category to event-type buckets.
- Event code input supported (e.g., 001, 707).
- Rule fixes for known mis-buckets (Academics, Platform, Music Vocal, Music Instrumental, Music Combined, Scripture, Sports).

Next
- Move regex classification rules into a single config map for easier edits without controller code changes.
- Add an admin-only diagnostics view showing event -> bucket mapping for current season.

Rollback
- Keep previous static grouping as fallback mode.

## Phase 3: Scheduling Quality Metrics (Medium Impact)
Status: Planned

Deliverables
- "Schedule Health" panel on scheduling pages:
  - conflicts by type
  - Mon-Thu utilization per room
  - overflow trend after each auto-assign run
- Save each auto-assign run summary in DB for traceability.

Rollback
- Hide health panel, keep saved data unchanged.

## Phase 4: Program 17 Report Parity (Medium Impact)
Status: Planned

Deliverables
- Align report outputs with Program 17 style where useful:
  - by event
  - by location
  - by student
  - by match
- Keep ACP templates as source of truth.

Rollback
- Keep old report templates selectable.

## Phase 5: Convention-Specific Policy Profiles (Medium Impact)
Status: Planned

Deliverables
- Add per-convention policy profile settings:
  - allowed scheduling days
  - overflow strategy
  - preferred room pool behavior
  - strict/no-weekend mode

Rollback
- Default all conventions to current global behavior.

## UAT Checklist (Per Convention)
- Import/prepare schedule data
- Generate initial schedule
- Run overflow auto-assign
- Verify no critical conflicts
- Confirm high-priority events land in expected room/day windows
- Validate key reports
- Sign-off from scheduler owner

## Immediate Sprint Proposal
1. Externalize event bucket mapping rules into config.
2. Add mapping diagnostics page for current season.
3. Add auto-assign run summary persistence and display.

## Success Criteria
- Fewer overflow events after each run.
- No increase in participant/room conflicts.
- Faster schedule correction cycle for local conventions.
- Admin can tune grouping rules without code redeploy.
