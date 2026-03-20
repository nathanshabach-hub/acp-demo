<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });
</script>
<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Transaction Details
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-dollar"></i> Transactions ', ['controller'=>'transactions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li class="active">Transaction Details </li>
      </ol>
    </section>
	
	<?php
	if($transactionD->status == 2 || $transactionD->status == 3)
	{
	?>
	<section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create($transactions, ['id'=>'adminForm', 'type' => 'file']); ?>
                <div class="form-horizontal">
                    <div class="box-body">
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Confirm this payment <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->select('Transactions.status', $changePaymentS, ['id' => 'status', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose']); ?>
						  <em>Note: If you receive payment via online or invoice, then only you can update payment status as confirmed.</em>
							 
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Transaction ID <span class="require"></span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Transactions.transaction_id_received', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control', 'placeholder'=>'Transaction ID', 'autocomplete'=>'off']); ?>
						  <em>Note: Please enter transaction ID from your bank statements on behalf of the payment received.</em>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Notes <span class="require"></span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Transactions.transaction_data', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control', 'placeholder'=>'Notes', 'autocomplete'=>'off']); ?>
						  <em>Note: Please enter any other information regarding this invoice or online payment.</em>
                      </div>
                    </div>
                    
                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->control('Transactions.id', ['label'=>false, 'type'=>'hidden']); ?>
                        <?php echo $this->Form->button('Save', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
                        <?php echo $this->Html->link('Cancel', ['controller'=>'transactions', 'action' => 'index'], ['class'=>'btn btn-default canlcel_le']); ?>
                    </div>
					
					<div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <em>Note: Once you update payment status, it cannot be change.</em>
                    </div>
					
					
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
	<?php
	}
	?>

    <section class="content">
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
                            <th class="sorting_paging">School</th>
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
                                <td data-title="School"><?php echo $transactionD->Users['first_name'];?></td>
								<td data-title="Season Year"><?php echo $transactionD->season_year; ?></td>
								<td data-title="Price Structure"><?php echo $priceStructureCR[$transactionD->price_structure]; ?></td>
								<td data-title="Discount"><?php echo CURR.' '.number_format($transactionD->total_discount_applied,2); ?></td>
								<td data-title="Amount"><?php echo CURR.' '.number_format($transactionD->total_amount,2); ?></td>
								<td data-title="Status"><?php echo $paymentStatus[$transactionD->status]; ?></td>
                                <td data-title="Transaction Date"><?php echo date('M d, Y H:i A', strtotime($transactionD->created)); ?></td>
								<td data-title="Transaction ID"><?php echo $transactionD->transaction_id_received ? $transactionD->transaction_id_received : 'N/A'; ?></td>
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