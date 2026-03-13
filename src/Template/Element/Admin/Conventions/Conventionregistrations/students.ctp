<?php echo $this->Html->script('facebox.js'); ?>
<?php echo $this->Html->css('facebox.css'); ?>
<script type="text/javascript">
    $(document).ready(function ($) {
        $('.close_image').hide();
        $('a[rel*=facebox]').facebox({
            loadingImage: '<?php echo HTTP_IMAGE ?>/loading.gif',
            closeImage: '<?php echo HTTP_IMAGE ?>/close.png'
        })
    })            
</script>
<?php
use Cake\ORM\TableRegistry;
$this->Events = TableRegistry::get('Events');
?>

<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$conventionregistrationstudents->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create('Conventionregistrationstudents', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">Students List</div>
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php 
                        $this->Paginator->options(array('update' => '#listID', 'url' => ['controller'=>'Conventionregistrationstudents', 'action'=>'teachers', $slug, $separator]));
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
                            <th class="sorting_paging">#</th>
                            <th class="sorting_paging">First Name</th>
                            <th class="sorting_paging">Middle Name</th>
                            <th class="sorting_paging">Last Name</th>
                            <th class="sorting_paging">Birth Year</th>
                            <th class="sorting_paging">Gender</th>
                            <th class="sorting_paging">Supervisor</th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('created', 'Registration Date'); ?></th>
                            <th class="sorting_paging">Events</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
						$cntrS = 0;
						foreach ($conventionregistrationstudents as $datarecord)
						{
							$cntrS++;
						?>
                            <tr>
                                <td data-title="#"><?php echo $cntrS;?></td>
                                <td data-title="First Name"><?php echo $datarecord->Students['first_name'];?></td>
                                <td data-title="Middle Name"><?php echo $datarecord->Students['middle_name'];?></td>
								<td data-title="Last Name"><?php echo $datarecord->Students['last_name'];?></td>
								<td data-title="Birth Year"><?php echo $datarecord->Students['birth_year'];?></td>
								<td data-title="Gender"><?php echo $datarecord->Students['gender'];?></td>
								<td data-title="Supervisor"><?php echo $datarecord->Teachers['first_name'].' '.$datarecord->Teachers['last_name'];?></td>
                                <td data-title="Registration Date"><?php echo date('M d, Y', strtotime($datarecord->created)); ?></td>
                                <td data-title="Events">
									<?php
									if($datarecord->event_ids != '' && $datarecord->event_ids != NULL)
									{
									?>
									<a href="#info<?php echo $datarecord->id; ?>" rel="facebox" title="View" class="btn btn-info btn-xs eyee"><i class="fa fa-eye "></i></a>
									<?php
									}
									?>
								</td>
								
                                
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="search_frm" style="display:none;">
            <button type="button" name="chkRecordId" onclick="checkAll(true);"  class="btn btn-info">Select All</button>
            <button type="button" name="chkRecordId" onclick="checkAll(false);" class="btn btn-info">Unselect All</button>
            <?php
            $arr = array(
                "" => "Action for selected record",
                'Activate' => "Activate",
                'Deactivate' => "Deactivate",
                //'Delete' => "Delete",
            );
            ?>
            <div class="list_sel"><?php echo $this->Form->input('action', ['options' => $arr, 'type'=>'select', 'label'=>false, 'class'=>"small form-control",'id'=>'action']);?></div>
            <button type="submit" class="small btn btn-success btn-cons btn-info" onclick="return ajaxActionFunction();" id="submit_action">OK</button>
        </div>
        <?php 
        if (isset($keyword) && $keyword != '') {
            echo $this->Form->input('Conventionregistrations.keyword', ['label'=>false, 'type'=>'hidden', 'value'=>$keyword]);
        }?>
        <?php echo $this->Form->end(); ?>
    
    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No record found.</div>
<?php }
?>

<?php foreach ($conventionregistrationstudents as $datarecord) { ?>
    <div id="info<?php echo $datarecord->id; ?>" style="display: none;">
        <!-- Fieldset -->
        <div class="nzwh-wrapper">
            <fieldset class="nzwh">
                <legend class="head_pop">
				<?php echo $datarecord->Students['first_name'];?> <?php echo $datarecord->Students['middle_name'];?> <?php echo $datarecord->Students['last_name'];?> [Total Events: <?php echo count(explode(",",$datarecord->event_ids)); ?>]
                </legend>
                <div class="drt">
					
					<table class="table table-bordered table-striped table-condensed cf">
					
					<?php
					$condStudEvents = array();
					if($datarecord->event_ids != '' && $datarecord->event_ids != NULL)
					{
						$condStudEvents[] = "(Events.id  IN ($datarecord->event_ids) )";
					
					$eventsL = $this->Events->find()->where($condStudEvents)->all();
					?>
					<tr>
						<td>
							Event Number
						</td>
						<td>
							Event Name
						</td>
					</tr>
					<?php
					foreach($eventsL as $eventd)
					{
					?>
					
					<tr>
                        <td><?php echo $eventd->event_id_number; ?></td>
                        <td><?php echo $eventd->event_name; ?></td>
                    </tr>
					<?php
					}
					?>
					
					<?php
					}
					else
					{
					?>
                    <tr colspan="2">
                        <td>Sorry, no event found.</td>
                    </tr>
					<?php
					}
					?>
					</table>
					
					
					
                    
                </div>
            </fieldset>
        </div>
    </div>
<?php } ?>