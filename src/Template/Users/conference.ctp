<div class="container-fluid p-0">
    <div class="row">
        <?php echo $this->element('user_left_menu'); ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

            <div class="ersu_message">
                <?php echo $this->Flash->render() ?>
            </div>

            <div class="teachers-top-heading">
                <span>Conference<?php echo isset($activeYear) && $activeYear ? ' ' . h($activeYear->year) : ''; ?></span>
            </div>

            <div class="m_content">
                <div class="table-responsive">
                    <div class="card p-3">
                        <?php if (!isset($allConferenceYears) || $allConferenceYears->isEmpty()): ?>
                            <div class="alert alert-warning">No active conference year has been created yet. Please contact the administrator.</div>
                        <?php else: ?>

                        <div class="mb-3">
                            <label for="conference_year_select"><strong>Select Conference Year:</strong></label>
                            <select id="conference_year_select" class="form-control" style="max-width:300px; display:inline-block; margin-left:10px;" onchange="window.location.href='<?php echo $this->Url->build(['controller' => 'users', 'action' => 'conference']); ?>?year_id=' + this.value;">
                                <?php foreach ($allConferenceYears as $cy): ?>
                                    <option value="<?php echo (int)$cy->id; ?>" <?php echo (isset($activeYear) && $activeYear && $activeYear->id == $cy->id) ? 'selected' : ''; ?>>
                                        Conference <?php echo h($cy->year); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="alert alert-info"><strong>Conference Year <?php echo h($activeYear->year); ?></strong> is now open for registration.</div>
                        <p><strong>Total supervisors available:</strong> <?php echo (int)$supervisorCount; ?></p>

                        <?php if ((int)$supervisorCount === 0) { ?>
                            <p>No supervisors found in Global Supervisors List. Please add supervisors first.</p>
                        <?php } else { ?>
                            <?php echo $this->Form->create(null, ['url' => ['controller' => 'users', 'action' => 'conference']]); ?>
                            <?php echo $this->Form->hidden('conference_year_id', ['value' => $activeYear->id]); ?>

                            <div class="mb-3">
                                <label><strong>Select supervisor name(s) to register</strong></label>
                                <div class="form-check mt-2 mb-2">
                                    <input type="checkbox" class="form-check-input" id="select_all_supervisors">
                                    <label class="form-check-label" for="select_all_supervisors">Select All</label>
                                </div>

                                <div style="max-height: 260px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                    <?php foreach ($supervisorDropDown as $supId => $supName) { ?>
                                        <?php $isChecked = in_array((string)$supId, array_map('strval', $selectedSupervisorIds), true); ?>
                                        <div class="form-check mb-1">
                                            <input
                                                type="checkbox"
                                                class="form-check-input supervisor-checkbox"
                                                id="supervisor_<?php echo (int)$supId; ?>"
                                                name="Conference[supervisor_ids][]"
                                                value="<?php echo (int)$supId; ?>"
                                                <?php echo $isChecked ? 'checked' : ''; ?>
                                            >
                                            <label class="form-check-label" for="supervisor_<?php echo (int)$supId; ?>"><?php echo h($supName); ?></label>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="mb-3">
                                <p><strong>Names selected for registration:</strong> <span id="selected_count"><?php echo (int)$selectedSupervisorCount; ?></span></p>
                            </div>

                            <hr>

                            <!-- Add New Supervisor -->
                            <div class="mb-3">
                                <label><strong>Add a new supervisor</strong></label>
                                <p class="text-muted" style="font-size:13px;">If a parent or supervisor is not in the list above, add them here.</p>
                                <div id="new-supervisors-container"></div>
                                <button type="button" class="btn btn-success btn-sm" id="add_supervisor_btn">
                                    <i class="fa fa-plus"></i> Add Supervisor
                                </button>
                            </div>

                            <hr>

                            <div>
                                <?php echo $this->Form->button('Register', ['class' => 'btn btn-primary']); ?>
                            </div>
                            <?php echo $this->Form->end(); ?>

                            <script>
                                (function() {
                                    var selectAll = document.getElementById('select_all_supervisors');
                                    var checkboxes = Array.prototype.slice.call(document.querySelectorAll('.supervisor-checkbox'));
                                    var selectedCount = document.getElementById('selected_count');

                                    function refreshCountAndSelectAll() {
                                        var checkedTotal = checkboxes.filter(function(cb) { return cb.checked; }).length;
                                        selectedCount.textContent = checkedTotal;
                                        selectAll.checked = checkboxes.length > 0 && checkedTotal === checkboxes.length;
                                    }

                                    selectAll.addEventListener('change', function() {
                                        checkboxes.forEach(function(cb) {
                                            cb.checked = selectAll.checked;
                                        });
                                        refreshCountAndSelectAll();
                                    });

                                    checkboxes.forEach(function(cb) {
                                        cb.addEventListener('change', refreshCountAndSelectAll);
                                    });

                                    refreshCountAndSelectAll();
                                })();

                                // Add Supervisor dynamic rows
                                (function() {
                                    var container = document.getElementById('new-supervisors-container');
                                    var btn = document.getElementById('add_supervisor_btn');
                                    var idx = 0;

                                    btn.addEventListener('click', function() {
                                        var row = document.createElement('div');
                                        row.className = 'new-supervisor-row mb-2 p-2';
                                        row.style.cssText = 'border:1px solid #ddd; border-radius:4px; background:#f9f9f9;';
                                        row.innerHTML =
                                            '<div class="row">' +
                                                '<div class="col-md-3 mb-1">' +
                                                    '<input type="text" name="NewSupervisors[' + idx + '][first_name]" class="form-control" placeholder="First Name" required>' +
                                                '</div>' +
                                                '<div class="col-md-3 mb-1">' +
                                                    '<input type="text" name="NewSupervisors[' + idx + '][last_name]" class="form-control" placeholder="Last Name" required>' +
                                                '</div>' +
                                                '<div class="col-md-4 mb-1">' +
                                                    '<input type="email" name="NewSupervisors[' + idx + '][email]" class="form-control" placeholder="Email Address" required>' +
                                                '</div>' +
                                                '<div class="col-md-2 mb-1">' +
                                                    '<button type="button" class="btn btn-danger btn-sm remove-sup-btn" style="margin-top:2px;">Remove</button>' +
                                                '</div>' +
                                            '</div>';
                                        container.appendChild(row);
                                        idx++;

                                        row.querySelector('.remove-sup-btn').addEventListener('click', function() {
                                            container.removeChild(row);
                                        });
                                    });
                                })();
                            </script>
                        <?php } ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>
