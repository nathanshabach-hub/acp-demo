<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$heartevents->isEmpty()) { ?> 
    <div class="panel-body">
        
        <?php echo $this->Form->create('Heartevents', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <!--<div class="topn_left">Ads List</div>-->
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php 
                        $this->Paginator->options(array('update' => '#listID', 'url' => ['controller'=>'heartevents', 'action'=>'viewlist', $separator]));
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
                            <th class="sorting_paging">Student</th>
                            <th class="sorting_paging">Title</th>
                            <th class="sorting_paging">Document</th>
							<?php
							if($userDetails->user_type == "School")
							{
							?>
                            <th class="sorting_paging">Uploaded By</th>
							<?php
							}
							?>
							
                            <th class="sorting_paging">Uploaded Date</th>
							<th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($heartevents as $datarecord) { ?>
                            <?php //pr($datarecord); exit;?> 
                            <tr>
                                <td data-title="Convention"><?php echo $datarecord->Conventions['name'];?></td>
                                <td data-title="Season Year"><?php echo $datarecord->season_year;?></td>
                                <td data-title="Student"><?php echo $datarecord->Students['first_name'].' '.$datarecord->Students['middle_name'].' '.$datarecord->Students['last_name'];?></td>
								<td data-title="Title"><?php echo $datarecord->mediafile_title;?></td>
								<td data-title="Document"><?php echo $datarecord->mediafile_original_file_name;?></td>
								
								<?php
								if($userDetails->user_type == "School")
								{
								?>
								<td data-title="Uploaded By">
								<?php
								if($this->request->session()->read("user_id") == $datarecord->uploaded_by_user_id)
								{
									echo 'You';
								}
								else
								{
									echo $datarecord->Uploadeduser['first_name'].' '.$datarecord->Uploadeduser['last_name'];
								}
								?>
								</td>
								<?php
								}
								?>
								
                                <td data-title="Created"><?php echo date('M d, Y', strtotime($datarecord->created)); ?></td>
                                <td data-title="Action">
									
									<?php
									$imgToShow = $datarecord->mediafile_file_system_name;
									if(file_exists(UPLOAD_EVENTS_HEART_PATH.$imgToShow) && !empty($imgToShow))
									{
										echo '<a target="_blank" title="Click to view/download" href="'.DISPLAY_EVENTS_HEART_PATH.$imgToShow.'"><i class="fa fa-cloud-download"></i></a>';
									}
									?>
									
									<?php
									if($userDetails->user_type == "School")
									{
										echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'heartevents', 'action' => 'removedocument',$datarecord->slug], [ 'escape' => false, 'title' => 'Remove', 'class'=>'', 'confirm' => 'Are you sure you want to remove this document?']);
									}
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
    <div class="admin_no_record">No record found.</div>
<?php }
?>
