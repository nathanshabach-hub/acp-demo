<?php if (!$conventionSeasonEvents->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create('Conventionseasons', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">Choose Events <br />
				<?php 
				if(count($conventionSeasonEvents))
					echo count($conventionSeasonEvents).' event(s) selected';
				else
					'No season selected';
				?>
				</div>
                
            </div>
			
			<div class="search_frm">
				<?php echo $this->Html->link('Reset Event List', ['controller'=>'conventions', 'action' => 'reseteventlist',$slug_convention_season,$slug_convention], ['class'=>'btn btn-success', 'confirm' => 'Are you sure you want to reset event list for this convention? This will delete all events for this convention & selected season ?', 'style' => "margin-bottom:20px;"]); ?>
			</div>
			
            <div class="tbl-resp-listing">
                <table class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">Event ID Number</th>
							<th class="sorting_paging">Event Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
						foreach ($conventionSeasonEvents as $datarecord)
						{
						?>
                            <tr>
                                <td data-title="Event Name"><?php echo $datarecord->Events['event_id_number'];?></td>
                                <td data-title="Event Number"><?php echo $datarecord->Events['event_name'];?></td>
                                
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
        
        <?php 
        if (isset($keyword) && $keyword != '') {
            echo $this->Form->input('Conventions.keyword', ['label'=>false, 'type'=>'hidden', 'value'=>$keyword]);
        }?>
        <?php echo $this->Form->end(); ?>
    
    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No record found.</div>
<?php }
?>