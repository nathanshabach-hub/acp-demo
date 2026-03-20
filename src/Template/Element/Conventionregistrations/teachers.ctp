<?php
use Cake\ORM\TableRegistry;
$this->Transactionteachers = TableRegistry::getTableLocator()->get('Transactionteachers');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$conventionregistrationteachers->isEmpty()) { ?> 
    <div class="panel-body">
        
        <?php echo $this->Form->create('Conventionregistrationteachers', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <!--<div class="topn_left">Ads List</div>-->
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php 
                        $this->Paginator->options(array('update' => '#listID', 'url' => ['controller'=>'conventionregistrations', 'action'=>'teachers', $separator]));
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
                            <th class="sorting_paging">Convention</th>
                            <th class="sorting_paging">Season Year</th>
                            <th class="sorting_paging">Supervisor</th>
							<th class="sorting_paging">Payment Status</th>
                            <th class="sorting_paging">Registration Date</th>
							<th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
						foreach ($conventionregistrationteachers as $datarecord)
						{
						
							// to check if payment has been done for this student or not
							$condTeacherPayment = array();
							$condTeacherPayment[] = "(Transactionteachers.conventionregistration_id = '".$datarecord->conventionregistration_id."')";
							$condTeacherPayment[] = "(Transactionteachers.conventionregistrationteacher_id = '".$datarecord->id."')";
							$condTeacherPayment[] = "(Transactionteachers.teacher_id = '".$datarecord->teacher_id."')";
							//echo '<pre>';print_r($condStudentPayment);echo '</pre>';
							
							$checkTeacherPaymentStatus = $this->Transactionteachers->find()->where($condTeacherPayment)->first();						
						
						?>
                            
							
                            <tr>
                                <td data-title="Convention"><?php echo $datarecord->Conventions['name'];?></td>
                                <td data-title="Season Year"><?php echo $datarecord->season_year;?></td>
                                <td data-title="Supervisor"><?php echo $datarecord->Teachers['first_name'].' '.$datarecord->Teachers['middle_name'].' '.$datarecord->Teachers['last_name'];?></td>
								<td data-title="Payment Status">
								<?php
								if($checkTeacherPaymentStatus)
								{
									if($checkTeacherPaymentStatus->status == 0)
										echo 'Failed';
									else
									if($checkTeacherPaymentStatus->status == 1)
										echo 'Confirmed';
									else
									if($checkTeacherPaymentStatus->status == 2)
										echo 'Pending';
									else
									if($checkTeacherPaymentStatus->status == 3)
										echo 'Invoiced';
								}
								else
								{
									echo 'Not yet paid';
								}
								
								?>
                                <td data-title="Created"><?php echo date('M d, Y', strtotime($datarecord->created)); ?></td>
                                <td data-title="Action">
                                    <?php
									echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'conventionregistrations', 'action' => 'removeteacher',$datarecord->slug], [ 'escape' => false, 'title' => 'Remove', 'class'=>'', 'confirm' => 'Are you sure you want to remove this supervisor from this convention registration ?']);
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
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No teacher found.</div>
<?php }
?>
