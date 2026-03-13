<script type="text/javascript">
	$(document).ready(function () {
		$("#addgroup").validate();
	});
</script>
<?php echo $this->Html->script('ajax-pagging.js'); ?>

<?php
use Cake\ORM\TableRegistry;

$this->Users = TableRegistry::get('Users');
$this->Crstudentevents = TableRegistry::get('Crstudentevents');

$condTS = array();
$condTS[] = "(Crstudentevents.conventionregistration_id = '".$conventionRegD->id."')";
$condTS[] = "(Crstudentevents.convention_id = '".$conventionRegD->convention_id."')";
$condTS[] = "(Crstudentevents.season_id = '".$conventionRegD->season_id."')";
$condTS[] = "(Crstudentevents.season_year = '".$conventionRegD->season_year."')";
$condTS[] = "(Crstudentevents.event_id = '".$eventD->id."')";

$totalStudentsEvent = $this->Crstudentevents->find()->where($condTS)->count();
?>

<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>
			<div class="teachers-top-heading">
				<span>Event Groups ::
					<?php echo $eventD->event_name; ?> 
					(Min: <?php echo $eventD->min_no; ?>
					&nbsp;&nbsp;&nbsp; 
					Max: <?php echo $eventD->max_no; ?>)
					&nbsp;&nbsp;&nbsp;
					(Total Students in this event: <?php echo $totalStudentsEvent; ?>)
				</span>
				<?php //echo $this->Html->link('Add Events Heart', ['controller' => 'heartevents', 'action' => 'addnew'], ['escape' => false, 'class' => 'btn btn-primary']); ?>
			</div>
			<!-- dashboard-section-2 start-->

			<div class="dashboard-form">
				<h2 class="form-title">Create Group (Event ID Number: <?php echo $eventD->event_id_number; ?>)</h2>
				<?php echo $this->Form->create(NULL, ['id' => 'addgroup', 'type' => 'file', 'class' => '', 'autocomplete' => 'off']); ?>

				<div class="form-group">
					<label for="name">Choose Student</label>
					<div class="input-multiple">
						<?php echo $this->Form->select('Groups.student_id', $studentDD, ['id' => 'student_id', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'multiple' => 'multiple']); ?>
						<script>
							$(document).ready(function () {
								$('#student_id').select2();
							});
						</script>
					</div>
				</div>

				<div class="form-group">
					<label for="name">Group</label>

					<?php echo $this->Form->input('Groups.group_name', ['label' => false, 'type' => 'number', 'div' => false, 'class' => 'form-control required', 'placeholder' => 'Group']); ?>
				</div>

				<div class="form-group form-btns" style="padding-top:10px;">
					<label></label>
					<button type="submit" class="btn btn-secondary">Create Group</button>
					<!--<button type="button" class="btn btn-secondary">Cancel</button>-->
					<?php echo $this->Html->link('<< Back', ['controller' => 'groups', 'action' => 'viewlist'], ['class' => 'btn btn-secondary']); ?>
				</div>
				<?php echo $this->Form->end(); ?>
			</div>



			<div class="container">
				<div class="row" style="display:none;">
					<div class="col-sm-4">col-sm-4</div>
					<div class="col-sm-4">col-sm-4</div>
					<div class="col-sm-4">col-sm-4</div>
					<div class="col-sm-4">col-sm-4</div>
				</div>

				<?php
				if (count($stGArr) > 0) {
					?>
					<div class="row">
						<?php
						foreach ($stGArr as $stgname => $studentids) {
							?>
							<div class="col-lg-4">
								<div class="card">
									<div class="card-body">
										<h4 class="card-title">
											<b>Group
												<?php echo $stgname; ?>
											</b>
											<?php
											//echo count($studentids);
											if (count($studentids) >= $eventD->min_no && count($studentids) <= $eventD->max_no) {
												echo '<i class="fa fa-check-circle pull-right" style="color:green;"></i>';
											} else {
												echo '<i class="fa fa-times-circle pull-right" style="color:red;" title="Group does not fulfil min/max criteria."></i>';
											}
											?>
										</h4>

										<div class="table-responsive table-bordered tablescroll">
											<table class="table table-striped-columns">
												<tbody>
													<?php
													if (count($studentids))
														$implodeStIDS = implode(",", $studentids);
													else
														$implodeStIDS = 0;

													$condStudentE = array();
													$condStudentE[] = "(Users.id IN ($implodeStIDS) )";

													$studentsDList = $this->Users->find()->where($condStudentE)->order(["Users.first_name" => "ASC", "Users.middle_name" => "ASC"])->all();

													foreach ($studentsDList as $studentrecord) {
														?>
														<tr>
															<td class="">
																<?php echo $studentrecord->first_name; ?>
																<?php echo $studentrecord->middle_name; ?>
																<?php echo $studentrecord->last_name; ?> 
																(<?php echo date("Y") - $studentrecord->birth_year; ?> Yrs)
															</td>
															<td width="30%" style="text-align: center">
																<?php
																echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'groups', 'action' => 'removestudentfromgroup', $event_slug, $studentrecord->id], ['escape' => false, 'title' => 'Remove student from group ' . $stgname, 'class' => '', 'confirm' => 'Are you sure you want to remove this student from group ' . $stgname . '?']);
																?>
															</td>
														</tr>
														<?php
													}
													?>

												</tbody>
												<!-- end tbody -->
											</table>
											<!-- end table -->
										</div>
									</div>
								</div>
							</div>
							<?php
						}
						?>
						<!-- end col -->
					</div>
					<?php
				}
				?>





			</div>

		</main>
	</div>
</div>