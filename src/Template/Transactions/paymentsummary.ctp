<script type="text/javascript">
$(document).ready(function () {
	$("#addstudent").validate();
});
</script>
<?php
$localCurrText = '&nbsp;&nbsp;<small><b>(Amount shown in local currency to Convention region)</b></small>';
$onlinetPTxt = '&nbsp;&nbsp;<small><b>(Online payment only available for Australian Conventions, all other regions please request invoice)</b></small>';
?>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
		
		<div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
		
			<h2 class="mt-3">Payment Summary</h2>
			  
			<!-- dashboard-section-3 start-->
			<div class="dashboard-form">
				<!--<h2 class="form-title">Payment Summary</h2>-->
				<?php echo $this->Form->create($users, ['id'=>'addstudent', 'type' => 'file', 'class' =>' ']); ?>
					
					<div class="form-group">
						<label for="name">Price Structure</label>
						<?php echo $priceStructureCR[$CRDetails->price_structure]; ?>
					</div>
					
					<div class="form-group">
						<label for="name">Total Students Registered</label>
						<?php echo $totalStudentsReg; ?>
					</div>
					
					<div class="form-group">
						<label for="name">Already Paid Students</label>
						<?php echo $alreadyPaidStudents; ?>
					</div>
					
					<div class="form-group">
						<label for="name">Pending Payment Students (A)</label>
						<?php echo $pendingPaymentStudents; ?>
					</div>
					
					<div class="form-group">
						<label for="name">Price Per Student (B)</label>
						<?php echo number_format($pricePerStudent,2); ?> <?php echo $localCurrText; ?>
					</div>
					
					<div class="form-group">
						<label for="name">Sub-total (AxB)</label>
						<?php echo number_format($subTotalPaymentStudents,2); ?> <?php echo $localCurrText; ?>
					</div>
					
					<?php
					if($totalStudentsApplicableDiscount>0)
					{
					?>
					
					<div class="form-group">
						&nbsp;
					</div>
					
					
					<div class="form-group">
						<label for="name">Total Students Applicable For Discount</label>
						<?php echo $totalStudentsApplicableDiscount; ?>
					</div>
					
					<div class="form-group">
						<label for="name">Discount Per Student</label>
						<?php echo number_format($perStudentDiscountAmount,2); ?>% <?php echo $localCurrText; ?>
					</div>
					
					<div class="form-group">
						<label for="name">Total Discount Amount</label>
						<?php echo number_format($totalDiscountAmount,2); ?> <?php echo $localCurrText; ?>
					</div>
					<?php
					}
					?>
					
					<div class="form-group">
						<label for="name">Net Payable Amount Student (C)</label>
						<?php echo number_format($netPayableAmountStudent,2); ?> <?php echo $localCurrText; ?>
					</div>
					
					
					<div class="form-group">
						----------------------------------------------------------------
					</div>
					
					
					<div class="form-group">
						<label for="name">Total Teachers Registered</label>
						<?php echo $totalTeachersReg; ?>
					</div>
					
					<div class="form-group">
						<label for="name">Already Paid Teachers</label>
						<?php echo $alreadyPaidTeachers; ?>
					</div>
					
					<div class="form-group">
						<label for="name">Pending Payment Teachers (D)</label>
						<?php echo $pendingPaymentTeachers; ?>
					</div>
					
					<div class="form-group">
						<label for="name">Price Per Teacher (E)</label>
						<?php echo number_format($pricePerTeacher,2); ?> <?php echo $localCurrText; ?>
					</div>
					
					
					<div class="form-group">
						----------------------------------------------------------------
					</div>
					
					
					<div class="form-group">
						<label for="name">Payable Amount (C) + (DxE)</label>
						<?php echo number_format($payableAmount,2); ?> <?php echo $localCurrText; ?>
					</div>
					
					
					
					<?php
					if($pendingPaymentStudents>0)
					{
					?>
					<div class="form-group form-btns">
						<button id="btn_online_payment" type="submit" class="btn btn-success">Proceed to online payment</button>
						<button id="btn_invoice" type="submit" class="btn btn-info">Request invoice from SCEE</button>
						<?php echo $this->Html->link('Cancel', ['controller'=>'conventionregistrations', 'action' => 'students'], ['class'=>'btn btn-secondary']); ?>
						<input type="hidden" name="hidd_pay_type" id="hidd_pay_type" value="" />
					</div>
					<div class="form-group form-btns">
						<div class="clear" style="color:#000;"><?php echo $onlinetPTxt; ?></div>
					</div>
					<?php
					}
					else
					{
					?>
					<div class="form-group form-btns">
						<div class="clear" style="color:#006400;">No student found pending for payment.</div>
					</div>
					<div class="form-group form-btns">
						<?php echo $this->Html->link('Back to students list', ['controller'=>'conventionregistrations', 'action' => 'students'], ['class'=>'btn btn-secondary']); ?>
					</div>
					<?php
					}
					?>
				<?php echo $this->Form->end(); ?>
			</div>
			<!-- dashboard-section-3 end-->
			
		</main>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function () { //alert('ddddd');
		$('#btn_online_payment').click(function(){
			$('#hidd_pay_type').val('online');  
		});
		$('#btn_invoice').click(function(){
			$('#hidd_pay_type').val('invoice');  
		});
		
	});
</script>