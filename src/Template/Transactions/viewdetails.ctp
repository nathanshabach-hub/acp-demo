<script type="text/javascript">
$(document).ready(function () {
	$("#editprofile").validate();
});
</script>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
			<h2 class="mt-3">Transaction Details</h2>
			  
			<!-- dashboard-section-3 start-->
			<div class="dashboard-form">
				<!--<h2 class="form-title">Edit Profile</h2>-->
				
				<section id="no-more-tables" class="lstng-section">
					 <div class="box box-info">
							<div class="box-header with-border">
								<h3 class="box-title">Transaction Details</h3>
							</div>
							<div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
							
							 
							<div class="tbl-resp-listing">
								<table class="table table-bordered table-striped table-condensed cf">
									<thead class="cf ajshort">
										<tr>
											<th class="sorting_paging">Convention</th>
											<th class="sorting_paging">Season Year</th>
											<th class="sorting_paging">Price Structure</th>
											<th class="sorting_paging">Discount</th>
											<th class="sorting_paging">Amount</th>
											<th class="sorting_paging">Status</th>
											<th class="sorting_paging">Transaction Date</th>
											<th class="sorting_paging">Transaction ID</th>
										</tr>
									</thead>
									<tbody>
										
											<?php //pr($datarecord); exit;?> 
											<tr>
												<td data-title="Convention"><?php echo $transactionD->Conventions['name'];?></td>
												<td data-title="Season Year"><?php echo $transactionD->season_year; ?></td>
												<td data-title="Price Structure"><?php echo $priceStructureCR[$transactionD->price_structure]; ?></td>
												<td data-title="Discount"><?php echo CURR.' '.number_format($transactionD->total_discount_applied,2); ?></td>
												<td data-title="Amount"><?php echo CURR.' '.number_format($transactionD->total_amount,2); ?></td>
												<td data-title="Status"><?php echo $paymentStatus[$transactionD->status]; ?></td>
												<td data-title="Transaction Date"><?php echo date('M d, Y H:i A', strtotime($transactionD->created)); ?></td>
												<td data-title="Transaction ID"><?php echo $transactionD->transaction_id_received ? $transactionD->transaction_id_received : 'N/A'; ?></td>
												
												</td>
											</tr>
									</tbody>
								</table>
							</div>
							
							
							<?php
							if($transactionStudents)
							{
							?>
							<div class="box-header with-border">
								<h3 class="box-title">Transaction Students</h3>
							</div>
							<div class="tbl-resp-listing">
								<table class="table table-bordered table-striped table-condensed cf">
									<thead class="cf ajshort">
										<tr>
											<th class="sorting_paging">#</th>
											<th class="sorting_paging">First Name</th>
											<th class="sorting_paging">Middle Name</th>
											<th class="sorting_paging">Last Name</th>
											<th class="sorting_paging">Birth Year</th>
											<th class="sorting_paging">Gender</th>
											<th class="sorting_paging">Discount</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$cntrS = 0;
										foreach ($transactionStudents as $datarecord)
										{
											$cntrS++;
										?>
											
											<tr>
												<td data-title="#"><?php echo $cntrS;?></td>
												<td data-title="First Name"><?php echo $datarecord->Users['first_name'];?></td>
												<td data-title="Middle Name"><?php echo $datarecord->Users['middle_name'];?></td>
												<td data-title="Last Name"><?php echo $datarecord->Users['last_name'];?></td>
												<td data-title="Birth Year"><?php echo $datarecord->Users['birth_year'];?></td>
												<td data-title="Gender"><?php echo $datarecord->Users['gender'];?></td>
												<td data-title="Discount"><?php if($datarecord->applicable_for_discount == 1) echo 'Yes'; else echo 'No';?></td>
											</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
							<?php
							}
							?>
							
							
							<?php
							if($transactionTeachers)
							{
							?>
							<div class="box-header with-border">
								<h3 class="box-title">Transaction Teachers</h3>
							</div>
							<div class="tbl-resp-listing">
								<table class="table table-bordered table-striped table-condensed cf">
									<thead class="cf ajshort">
										<tr>
											<th class="sorting_paging">#</th>
											<th class="sorting_paging">Title</th>
											<th class="sorting_paging">First Name</th>
											<th class="sorting_paging">Last Name</th>
											<th class="sorting_paging">Gender</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$cntrS = 0;
										foreach ($transactionTeachers as $datarecord)
										{
											$cntrS++;
										?>
											
											<tr>
												<td data-title="#"><?php echo $cntrS;?></td>
												<td data-title="Title"><?php echo $datarecord->Users['title'];?></td>
												<td data-title="First Name"><?php echo $datarecord->Users['first_name'];?></td>
												<td data-title="Last Name"><?php echo $datarecord->Users['last_name'];?></td>
												<td data-title="Gender"><?php echo $datarecord->Users['gender'];?></td>
											</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
							<?php
							}
							?>
							
							
							
							
							<div class="form-horizontal" style="padding-bottom:20px;">
									<?php echo $this->Html->link('Back to transactions', ['controller'=>'transactions', 'action' => 'index'], ['class'=>'btn btn-default canlcel_le']); ?>
							</div>
							
							
							
							
						  </div>
					</section>
				
				
			</div>
			<!-- dashboard-section-3 end-->
			
		</main>
	</div>
</div>