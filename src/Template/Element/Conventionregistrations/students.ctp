<?php
use Cake\ORM\TableRegistry;
$this->Transactionstudents = TableRegistry::get('Transactionstudents');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$conventionregistrationstudents->isEmpty()) { ?>



    <div class="panel-body">
        
        <?php echo $this->Form->create('Conventionregistrationstudents', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <!--<div class="topn_left">Ads List</div>-->
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php 
                        $this->Paginator->options(array('update' => '#listID', 'url' => ['controller'=>'conventionregistrations', 'action'=>'students', $separator]));
                        echo $this->Paginator->counter('{{page}} of {{pages}} &nbsp;');
                        echo $this->Paginator->prev('« Prev');
                        echo $this->Paginator->numbers();
                        echo $this->Paginator->next('Next »');
                        
                    ?>
                </div>
            </div>   

            <div class="tbl-resp-listing">
                <table class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">Season Year</th>
                            <th class="sorting_paging">Student</th>
                            <th class="sorting_paging">Payment Status</th>
                            <th class="sorting_paging">Registration Date</th>
							<th class="sorting_paging" style="width:24%">Supervisor</th>
							<th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
						foreach ($conventionregistrationstudents as $datarecord)
						{
							// to check if payment has been done for this student or not
							$condStudentPayment = array();
							$condStudentPayment[] = "(Transactionstudents.conventionregistration_id = '".$datarecord->conventionregistration_id."')";
							$condStudentPayment[] = "(Transactionstudents.conventionregistrationstudent_id = '".$datarecord->id."')";
							$condStudentPayment[] = "(Transactionstudents.student_id = '".$datarecord->student_id."')";
							//echo '<pre>';print_r($condStudentPayment);echo '</pre>';
							
							$checkStudentPaymentStatus = $this->Transactionstudents->find()->where($condStudentPayment)->first();
						?>
                            
                            <tr>
                                <td data-title="Season Year"><?php echo $datarecord->season_year;?></td>
                                <td data-title="Student"><?php echo $datarecord->Students['first_name'].' '.$datarecord->Students['middle_name'].' '.$datarecord->Students['last_name'];?></td>
                                <td data-title="Payment Status">
								<?php
								if($checkStudentPaymentStatus)
								{
									if($checkStudentPaymentStatus->status == 0)
										echo 'Failed';
									else
									if($checkStudentPaymentStatus->status == 1)
										echo 'Confirmed';
									else
									if($checkStudentPaymentStatus->status == 2)
										echo 'Pending';
									else
									if($checkStudentPaymentStatus->status == 3)
										echo 'Invoiced';
								}
								else
								{
									echo 'Not yet paid';
								}
								
								?>
								</td>
								<td data-title="Created"><?php echo date('M d, Y', strtotime($datarecord->created)); ?></td>
								<td data-title="Supervisor">
								<?php echo $this->Form->select('Conventionregistrationstudents.teacher_parent_id', $teacherDropDownData, ['id' => 'teacher_parent_id_'.$datarecord->id, 'label' => false, 'div' => false, 'class' => 'form-control', 'autocomplete' => 'off', 'empty' => 'Choose', 'value' => $datarecord->teacher_parent_id]); ?>
									<script>
									$(document).ready(function() {
										$('#teacher_parent_id_<?php echo $datarecord->id; ?>').select2();
									});
								</script>
								
								<script type="text/javascript">
									$(document).ready(function () {
										$('#teacher_parent_id_<?php echo $datarecord->id; ?>').change(function () {
											
											var teacher_parent_id = $("#teacher_parent_id_<?php echo $datarecord->id; ?>").val();
											//alert(teacher_parent_id);return false;
											
											
											if(teacher_parent_id == 0 || teacher_parent_id == "")
											{
												alert("Please choose teacher.");
												//$("#box_city").css("display","none");
												return false;
											}
											
											
											$.ajax({
												type: 'POST',
												url: "<?php echo HTTP_PATH."/homes/assignteachertostudent/".$datarecord->slug; ?>/"+teacher_parent_id,
												cache: false,
												beforeSend: function () {
													//$("#loderstatus").show();
												},
												complete: function () {
													//$("#loderstatus").hide();
												},
												success: function (result) {
													alert(result);
													//var statusList 		= JSON.parse(result);
													
													/* if(statusList != '')
													{
														$("#box_city").css("display","block");
														$('#city').empty();
														// Populate dropdown with list of provinces 
														$.each(statusList, function (key, entry) {
															$('#city').append($('<option></option>').attr('value', entry.id).text(entry.name));
														})
													}
													else
													{
														$("#box_city").css("display","none");
													} */
												}
											});
											return false;
										});
									});
								</script>
								
								
								</td>
								
								<td data-title="Action">
                                    <?php
									if(!$checkStudentPaymentStatus)
									{
										echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'conventionregistrations', 'action' => 'removestudent',$datarecord->slug], [ 'escape' => false, 'title' => 'Remove', 'class'=>'', 'confirm' => 'Are you sure you want to remove this student from this convention registration ?']);
									}
									else
									{
										echo $this->Html->link('<i class="fa fa-file-pdf-o"></i>', ['controller' => 'judgeevaluations', 'action' => 'indrespackprint',$datarecord->slug], [ 'escape' => false, 'title' => 'Download Individual Result Package', 'target'=>'_blank']);
									}
									
									
									//echo $datarecord->event_ids;
									/* $showCertLink = 0;
									if(isset($datarecord->event_ids) && !empty($datarecord->event_ids))
									{
										$studentEventExplode = explode(",",$datarecord->event_ids);
										foreach($studentEventExplode as $steventid)
										{
											if(in_array($steventid,$arrEventCP))
											{
												$showCertLink = 1;
												break;
											}
										}
									} */
									
									// to generare participation pdf certificate if payment is confirmed
									//if($checkStudentPaymentStatus->status == 1)
									/* if($showCertLink == 1)
									{
										
									} */
									?>
                                </td>
                                
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

         
        
        <?php echo $this->Form->end(); ?>
    
    </div>
	
	<?php 
	if($checkPriceStructure->price_per_student>0)
	{
		echo $this->Html->link('Proceed to Payment/Invoice', ['controller' => 'transactions', 'action' => 'paymentsummary'], ['escape' => false, 'class' => 'btn btn-success']);
	}
	else
	{
		echo $this->Html->link('Price Structure', ['controller' => 'conventionregistrations', 'action' => 'pricestructure'], ['escape' => false, 'class' => 'btn btn-info']);
	}
	?>
	
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No student found.</div>
<?php }
?>
