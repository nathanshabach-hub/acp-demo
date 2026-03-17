<script type="text/javascript">
    $(document).ready(function() {
        $("#smallProgramCustomizeForm").validate();

        function nl2brSafe(text) {
            return $('<div/>').text(text).html().replace(/\n/g, '<br>');
        }

        function syncColorInputs(textSelector, colorSelector, fallback) {
            var textValue = $(textSelector).val();
            if (/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/.test(textValue)) {
                $(colorSelector).val(textValue);
            } else if (!textValue) {
                $(colorSelector).val(fallback);
            }

            $(colorSelector).on('input change', function() {
                $(textSelector).val($(this).val());
                renderPreview();
            });
        }

        function renderPreview() {
            var reportTitle = $('#report-title').val() || 'Small Program';
            var reportSubtitle = $('#report-subtitle').val() || '<?php echo h($conventionSD->Conventions['name'] . ' - ' . $conventionSD->season_year); ?>';
            var introNote = $('#intro-note').val();
            var footerNote = $('#footer-note').val();
            var morningLabel = $('#morning-label').val() || 'Convention Events';
            var afternoonLabel = $('#afternoon-label').val() || 'Convention Events';
            var lunchLabel = $('#lunch-label').val() || 'LUNCH';
            var primaryColor = $('#primary-color').val() || '#1a3a5c';
            var secondaryColor = $('#secondary-color').val() || '#2e6da4';
            var tableHeaderColor = $('#table-header-color').val() || '#ddeeff';
            var customCss = $('#custom-css').val() || '';
            var logoAltText = $('#logo-alt-text').val() || reportTitle;

            $('#preview-report-title').text(reportTitle);
            $('#preview-report-subtitle').text(reportSubtitle);
            $('#preview-morning-label').text(morningLabel);
            $('#preview-afternoon-label').text(afternoonLabel);
            $('#preview-lunch-label').text(lunchLabel);

            $('#preview-intro-note').html(nl2brSafe(introNote)).toggle(!!introNote);
            $('#preview-footer-note').html(nl2brSafe(footerNote)).toggle(!!footerNote);

            $('.sp2-preview .sp2-day-header').css('background-color', primaryColor);
            $('.sp2-preview .sp2-session-header').css('background-color', secondaryColor);
            $('.sp2-preview .sp2-wrap-custom-note').css('border-left-color', secondaryColor);
            $('.sp2-preview .sp2-table th').css('background-color', tableHeaderColor);
            $('#preview-logo').attr('alt', logoAltText);

            $('#sp2-live-custom-css').text(customCss);
        }

        syncColorInputs('#primary-color', '#primary-color-picker', '#1a3a5c');
        syncColorInputs('#secondary-color', '#secondary-color-picker', '#2e6da4');
        syncColorInputs('#table-header-color', '#table-header-color-picker', '#ddeeff');

        $('#logo-file').on('change', function(event) {
            var file = event.target.files && event.target.files[0] ? event.target.files[0] : null;
            if (!file) {
                return;
            }

            var reader = new FileReader();
            reader.onload = function(e) {
                $('#preview-logo').attr('src', e.target.result).show();
                $('#preview-logo-wrap').show();
                $('#remove-logo').prop('checked', false);
            };
            reader.readAsDataURL(file);
        });

        $('#remove-logo').on('change', function() {
            if ($(this).is(':checked')) {
                $('#preview-logo-wrap').hide();
            } else if ($('#preview-logo').attr('src')) {
                $('#preview-logo-wrap').show();
            }
        });

        $('#smallProgramCustomizeForm').find('input[type="text"], textarea').on('input keyup change', renderPreview);
        renderPreview();
    });
</script>

<style>
.sp2-customize-grid { display: flex; gap: 24px; align-items: flex-start; }
.sp2-customize-form { flex: 1 1 56%; min-width: 0; }
.sp2-customize-preview { flex: 1 1 44%; min-width: 320px; position: sticky; top: 20px; }
.sp2-preview-frame { background: #f3f5f7; border: 1px solid #d9e1e8; border-radius: 6px; padding: 14px; }
.sp2-preview-toolbar { font-size: 12px; color: #666; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.08em; }
.sp2-preview-sheet { background: #fff; border-radius: 4px; box-shadow: 0 6px 24px rgba(0,0,0,0.08); overflow: hidden; }
.sp2-preview-header { padding: 16px 18px 10px; border-bottom: 1px solid #edf1f4; }
.sp2-preview-header h2 { margin: 0; font-size: 22px; color: #1b1f23; }
.sp2-preview-header p { margin: 4px 0 0; color: #666; }
.sp2-preview { padding: 16px 18px 20px; font-family: Arial, sans-serif; }
.sp2-preview .sp2-brand { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
.sp2-preview .sp2-brand-logo { max-height: 68px; max-width: 150px; width: auto; height: auto; display: block; }
.sp2-preview .sp2-wrap-custom-note { margin: 0 0 14px; padding: 10px 12px; background: #f8f8f8; border-left: 4px solid #2e6da4; color: #333; }
.sp2-preview .sp2-day-header { background: #1a3a5c; color: #fff; padding: 10px 14px; border-radius: 4px 4px 0 0; font-weight: bold; }
.sp2-preview .sp2-day-header .sp2-date { font-weight: normal; font-size: 12px; opacity: 0.9; margin-left: 8px; }
.sp2-preview .sp2-session-header { background: #2e6da4; color: #fff; padding: 6px 10px; font-size: 13px; font-weight: bold; }
.sp2-preview .sp2-lunch { background: #f5f0d8; border: 1px solid #e0d080; padding: 7px; text-align: center; color: #7a6000; font-weight: bold; }
.sp2-preview .sp2-table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 12px; }
.sp2-preview .sp2-table th { background: #ddeeff; border: 1px solid #aaccee; padding: 6px; text-align: center; }
.sp2-preview .sp2-table td { border: 1px solid #ccd9e8; padding: 8px 6px; vertical-align: top; }
.sp2-preview .sp2-event { margin-bottom: 4px; }
.sp2-color-field { display: flex; gap: 8px; align-items: center; }
.sp2-color-field .form-control { flex: 1 1 auto; }
.sp2-color-field input[type="color"] { width: 48px; height: 34px; padding: 2px; border: 1px solid #ccc; border-radius: 4px; background: #fff; }
.sp2-field-help { color: #777; font-size: 12px; margin-top: 4px; }
@media (max-width: 1199px) {
    .sp2-customize-grid { flex-direction: column; }
    .sp2-customize-preview { position: static; width: 100%; }
}
</style>

<style id="sp2-live-custom-css"></style>

<div class="content-wrapper">
    <section class="content-header">
      <h1>Customize Small Program v2</h1>
        <p style="margin:2px 0 0 0; font-size:13px; color:#777;"><small><?php echo h($conventionSD->Conventions['name']); ?> - <?php echo h($conventionSD->season_year); ?></small></p>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> Dashboard', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('Conventions', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('Seasons', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('Reports', ['controller'=>'schedulings', 'action'=>'reports',$convention_season_slug], ['escape'=>false]);?></li>
          <li class="active">Customize Small Program v2</li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="ersu_message"><?php echo $this->Flash->render(); ?></div>

            <div class="box-body" style="padding:20px;">
                <?php echo $this->Form->create($customization, ['id'=>'smallProgramCustomizeForm', 'autocomplete'=>'off', 'type'=>'file']); ?>

                <div class="sp2-customize-grid">
                    <div class="sp2-customize-form">
                        <div class="form-group">
                            <label>Report Title</label>
                            <?php echo $this->Form->input('report_title', ['label'=>false, 'type'=>'text', 'class'=>'form-control', 'id'=>'report-title', 'placeholder'=>'Small Program']); ?>
                        </div>

                        <div class="form-group">
                            <label>Report Subtitle</label>
                            <?php echo $this->Form->input('report_subtitle', ['label'=>false, 'type'=>'text', 'class'=>'form-control', 'id'=>'report-subtitle', 'placeholder'=>'Convention name and season year']); ?>
                        </div>

                        <div class="form-group">
                            <label>Intro Note</label>
                            <?php echo $this->Form->input('intro_note', ['label'=>false, 'type'=>'textarea', 'rows'=>4, 'class'=>'form-control', 'id'=>'intro-note', 'placeholder'=>'Shown above the program tables']); ?>
                        </div>

                        <div class="form-group">
                            <label>Footer Note</label>
                            <?php echo $this->Form->input('footer_note', ['label'=>false, 'type'=>'textarea', 'rows'=>4, 'class'=>'form-control', 'id'=>'footer-note', 'placeholder'=>'Shown below the program tables']); ?>
                        </div>

                        <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label>Morning Label</label>
                                    <?php echo $this->Form->input('morning_label', ['label'=>false, 'type'=>'text', 'class'=>'form-control', 'id'=>'morning-label', 'placeholder'=>'Convention Events']); ?>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label>Afternoon Label</label>
                                    <?php echo $this->Form->input('afternoon_label', ['label'=>false, 'type'=>'text', 'class'=>'form-control', 'id'=>'afternoon-label', 'placeholder'=>'Convention Events']); ?>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label>Lunch Label</label>
                                    <?php echo $this->Form->input('lunch_label', ['label'=>false, 'type'=>'text', 'class'=>'form-control', 'id'=>'lunch-label', 'placeholder'=>'LUNCH']); ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Primary Color</label>
                            <div class="sp2-color-field">
                                <?php echo $this->Form->input('primary_color', ['label'=>false, 'type'=>'text', 'class'=>'form-control', 'id'=>'primary-color', 'placeholder'=>'#1a3a5c']); ?>
                                <input type="color" id="primary-color-picker" value="#1a3a5c">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Secondary Color</label>
                            <div class="sp2-color-field">
                                <?php echo $this->Form->input('secondary_color', ['label'=>false, 'type'=>'text', 'class'=>'form-control', 'id'=>'secondary-color', 'placeholder'=>'#2e6da4']); ?>
                                <input type="color" id="secondary-color-picker" value="#2e6da4">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Table Header Color</label>
                            <div class="sp2-color-field">
                                <?php echo $this->Form->input('table_header_color', ['label'=>false, 'type'=>'text', 'class'=>'form-control', 'id'=>'table-header-color', 'placeholder'=>'#ddeeff']); ?>
                                <input type="color" id="table-header-color-picker" value="#ddeeff">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Logo Image</label>
                            <?php echo $this->Form->input('logo_file', ['label'=>false, 'type'=>'file', 'class'=>'form-control', 'id'=>'logo-file', 'accept'=>'image/png,image/jpeg,image/gif']); ?>
                            <p class="help-block">Upload a PNG, JPG, JPEG or GIF logo for this convention season.</p>
                            <?php if (!empty($customization->logo_path)): ?>
                            <div style="margin-top:10px;">
                                <img src="<?php echo h($customization->logo_path); ?>" alt="<?php echo h(!empty($customization->logo_alt_text) ? $customization->logo_alt_text : 'Current logo'); ?>" style="max-height:60px;max-width:160px;width:auto;height:auto;display:block;">
                                <label style="margin-top:8px;font-weight:normal;">
                                    <?php echo $this->Form->checkbox('remove_logo', ['hiddenField'=>false, 'id'=>'remove-logo']); ?> Remove current logo
                                </label>
                            </div>
                            <?php else: ?>
                            <?php echo $this->Form->hidden('remove_logo', ['value'=>0, 'id'=>'remove-logo']); ?>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label>Logo Alt Text</label>
                            <?php echo $this->Form->input('logo_alt_text', ['label'=>false, 'type'=>'text', 'class'=>'form-control', 'id'=>'logo-alt-text', 'placeholder'=>'Convention logo']); ?>
                        </div>

                        <div class="form-group">
                            <label>Custom CSS</label>
                            <?php echo $this->Form->input('custom_css', ['label'=>false, 'type'=>'textarea', 'rows'=>8, 'class'=>'form-control', 'id'=>'custom-css', 'placeholder'=>'.sp2-event { font-weight: bold; }']); ?>
                            <p class="help-block">This is injected into the Small Program v2 page and print view. Use trusted CSS only.</p>
                        </div>

                        <div class="sp2-field-help">The live preview updates as you type. Save when you are happy with the result.</div>

                        <div style="margin-top:20px;">
                            <?php echo $this->Form->button('<i class="fa fa-save"></i> Save Customization', ['class'=>'btn btn-primary', 'escape'=>false]); ?>
                            &nbsp;
                            <?php echo $this->Html->link('Preview Report', ['controller'=>'schedulingreports', 'action'=>'smallprogramv2',$convention_season_slug], ['class'=>'btn btn-info']); ?>
                            &nbsp;
                            <?php echo $this->Html->link('Cancel', ['controller'=>'schedulings', 'action'=>'reports',$convention_season_slug], ['class'=>'btn btn-default canlcel_le']); ?>
                        </div>
                    </div>

                    <div class="sp2-customize-preview">
                        <div class="sp2-preview-frame">
                            <div class="sp2-preview-toolbar">Live Preview</div>
                            <div class="sp2-preview-sheet">
                                <div class="sp2-preview-header">
                                    <h2 id="preview-report-title">Small Program</h2>
                                    <p id="preview-report-subtitle"><?php echo h($conventionSD->Conventions['name'] . ' - ' . $conventionSD->season_year); ?></p>
                                </div>

                                <div class="sp2-preview">
                                    <div id="preview-logo-wrap" class="sp2-brand"<?php echo empty($customization->logo_path) ? ' style="display:none;"' : ''; ?>>
                                        <img id="preview-logo" class="sp2-brand-logo" src="<?php echo !empty($customization->logo_path) ? h($customization->logo_path) : ''; ?>" alt="<?php echo h(!empty($customization->logo_alt_text) ? $customization->logo_alt_text : 'Convention logo'); ?>">
                                    </div>
                                    <div id="preview-intro-note" class="sp2-wrap-custom-note" style="display:none;"></div>

                                    <div class="sp2-day-header">
                                        TUESDAY <span class="sp2-date">17 June 2025</span>
                                    </div>

                                    <div class="sp2-session-header">
                                        8:30 am - 12:30 pm &mdash; <span id="preview-morning-label">Convention Events</span>
                                    </div>

                                    <table class="sp2-table">
                                        <thead>
                                            <tr>
                                                <th>Main Hall</th>
                                                <th>Poetry Room</th>
                                                <th>Junior Hall</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <div class="sp2-event">Instrumental Ensemble</div>
                                                    <div class="sp2-event">Piano Solo Sacred</div>
                                                </td>
                                                <td>
                                                    <div class="sp2-event">Poetry Recitation Female U16</div>
                                                </td>
                                                <td>
                                                    <div class="sp2-event">Bible Memory Primary</div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <div class="sp2-lunch"><span id="preview-lunch-label">LUNCH</span> 12:30 pm - 1:30 pm</div>

                                    <div class="sp2-session-header">
                                        1:30 pm - 5:00 pm &mdash; <span id="preview-afternoon-label">Convention Events</span>
                                    </div>

                                    <table class="sp2-table">
                                        <thead>
                                            <tr>
                                                <th>Main Hall</th>
                                                <th>Poetry Room</th>
                                                <th>Junior Hall</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <div class="sp2-event">Choir Mixed</div>
                                                </td>
                                                <td>
                                                    <div class="sp2-event">Expressive Reading Open</div>
                                                </td>
                                                <td>
                                                    <div class="sp2-event">Bible Storytelling</div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <div id="preview-footer-note" class="sp2-wrap-custom-note" style="display:none;margin-top:14px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php echo $this->Form->end(); ?>
            </div>
        </div>
    </section>
</div>