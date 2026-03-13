<?php
use Cake\ORM\TableRegistry;
$this->Evaluationtags = TableRegistry::get('Evaluationtags');
$this->Events = TableRegistry::get('Events');
$this->Evaluationareas = TableRegistry::get('Evaluationareas');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$evaluationforms->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create('Evaluationforms', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">Evaluation Forms List</div>
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php 
                        $this->Paginator->options(array('update' => '#listID', 'url' => ['controller'=>'evaluationforms', 'action'=>'index', $separator]));
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
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('id', '#DB ID'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('name', 'Form Name'); ?></th>
                            <th class="sorting_paging">Tags</th>
                            <th class="sorting_paging">Events</th>
                            <th class="sorting_paging">Area(s)</th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('reference_pdf_file_name', 'Reference PDF File Name'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('notes', 'Notes'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('created', 'Created'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('modified', 'Last Modified'); ?></th>
                            <th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($evaluationforms as $datarecord) { ?>
                            <?php //pr($datarecord); exit;?> 
                            <tr>
                                <td data-title="DB ID"><?php echo $datarecord->id;?></td>
                                <td data-title="Form Name"><?php echo $datarecord->name;?></td>
                                <td data-title="Tags">
								<?php
								$arrTagNames = array();
								if(!empty($datarecord->tag_ids))
								{
									$tag_ids = $datarecord->tag_ids;
								}
								else
								{
									$tag_ids = 0;
								}
								
								$condTag = array();
								$condTag[] = "(Evaluationtags.id IN ($tag_ids) )";
								$formTagsList = $this->Evaluationtags->find()->where($condTag)->order(["Evaluationtags.name" => "ASC"])->all();
								foreach($formTagsList as $formtname)
								{
									$arrTagNames[] = $formtname->name;
								}
								echo implode(", ",$arrTagNames);
								?>
								</td>
                                <td data-title="Events">
								<?php
								$arrEventNames = array();
								$event_id_numbers = $datarecord->event_id_numbers;
								$condEvent = array();
								$condEvent[] = "(Events.event_id_number IN ($event_id_numbers) )";
								$formEventsList = $this->Events->find()->where($condEvent)->order(["Events.event_name" => "ASC"])->all();
								foreach($formEventsList as $formename)
								{
									$arrEventNames[] = $formename->event_name.' ('.$formename->event_id_number.')';
								}
								echo implode(", ",$arrEventNames);
								?>
								</td>
								<td data-title="Area(s)">
								<?php
								$total_areas = $this->Evaluationareas->find()->where(["Evaluationareas.evaluationform_id" => $datarecord->id])->count();
								echo $total_areas;
								?>
								</td>
                                <td data-title="Reference PDF File Name">
								<a target="_blank" href="<?php echo DISPLAY_JUDGING_REFERENCE_PDF_PATH.$datarecord->reference_pdf_file_name ?>">
								<?php echo $datarecord->reference_pdf_file_name;?>
								</a>
								</td>
                                <td data-title="Notes"><?php echo $datarecord->notes ? $datarecord->notes : 'N/A'; ?></td>
                                <td data-title="Created"><?php echo date('M d, Y', strtotime($datarecord->created)); ?></td>
                                <td data-title="Last Modified">
								<?php 
								if($datarecord->modified == NULL)
								{
									echo 'N/A';
								}
								else
								{
									echo date('M d, Y', strtotime($datarecord->modified));
								}
								
								?>
								</td>
                                <td data-title="Action">
                                    
                                    <div id="loderstatus<?php echo $datarecord->id; ?>" class="right_action_lo"><?php echo $this->Html->image("loading.gif"); ?></div>
                                    <span class="right_acdc" id="status<?php echo $datarecord->id; ?>">
                                        <?php
                                        if ($datarecord->status == '1') {
                                            echo $this->Html->link('<button class="btn btn-success btn-xs"><i class="fa fa-check"></i></button>', ['controller' => 'evaluationforms', 'action' => 'deactivateform',$datarecord->slug], [ 'escape' => false, 'title' => 'Deactivate']);
                                        } else {
                                            echo $this->Html->link('<button class="btn btn-danger btn-xs"><i class="fa fa-ban"></i></button>', ['controller' => 'evaluationforms', 'action' => 'activateform', $datarecord->slug], [ 'escape' => false, 'title' => 'Activate']);
                                        }
                                        ?>
                                    </span>
									
									<?php echo $this->Html->link('<i class="fa fa-list"></i>', ['controller' => 'evaluationareas', 'action' => 'index',$datarecord->slug], [ 'escape' => false, 'title' => 'Evaluation Areas', 'class'=>'btn btn-info btn-xs action-list delete-list']); ?>
									
									<?php echo $this->Html->link('<i class="fa fa-pencil"></i>', ['controller' => 'evaluationforms', 'action' => 'edit',$datarecord->slug], [ 'escape' => false, 'title' => 'Edit', 'class'=>'btn btn-primary btn-xs']); ?>
                                    <?php echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'evaluationforms', 'action' => 'deleteform',$datarecord->slug], [ 'escape' => false, 'title' => 'Delete', 'class'=>'btn btn-danger btn-xs action-list delete-list', 'confirm' => 'Are you sure you want to Delete ?']); ?>
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
            echo $this->Form->input('Evaluationforms.keyword', ['label'=>false, 'type'=>'hidden', 'value'=>$keyword]);
        }?>
        <?php echo $this->Form->end(); ?>
    
    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No record found.</div>
<?php }
?>