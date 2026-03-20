<?php
use Cake\ORM\TableRegistry;
$this->Eventcategories = TableRegistry::getTableLocator()->get('Eventcategories');
$this->Divisions = TableRegistry::getTableLocator()->get('Divisions');
$this->Events = TableRegistry::getTableLocator()->get('Events');

//echo '<pre>';
//print_r($liveEvents);
//exit;
?>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>

			<h2 class="mt-3">Manage Student Event</h2>
			
			<?php echo $this->Html->link('<< Back', ['controller' => 'conventionregistrations', 'action' => 'studentevents'], ['class' => 'btn btn-secondary']); ?>

			<!-- dashboard-section-3 start-->
			<div class="dashboard-form">
				<?php echo $this->Form->create($conventionregistrationstudents, ['id' => 'addstudentevent', 'type' => 'file', 'class' => ' ']); ?>
				
				<table id="tbl_header-se" class="table table-striped table-bordered" style="width:100%">
					<thead class="cf ajshort">
						<tr>
							<th width="20%">First Name</th>
							<td width="20%"><?php echo $checkCRS->Students['first_name']; ?></td>
							<th width="15%">Middle Name</th>
							<td width="15%"><?php echo $checkCRS->Students['middle_name']; ?></td>
							<th width="15%">Last Name</th>
							<td width="15%"><?php echo $checkCRS->Students['last_name']; ?></td>
						</tr>
						<tr>
							<th width="20%">School</th>
							<td colspan="3" width="50%"><?php echo $checkCRS->Users['first_name']; ?></td>
							<th width="15%">Year of Birth</th>
							<td width="15%"><?php echo $checkCRS->Students['birth_year']; ?></td>
						</tr>
					</thead>
				</table>
				

				<div class="filterwrap">
				<div class="tabscrollno">
				<b>Event Count</b>: <span id="live_event_counter"><?php echo $liveEventsCounter; ?></span>/<?php echo $minMaxEventsArr['max_events_student']; ?>
				</div>
				<div class="float-end">
					<input id="filter" type="text" data-type="search" class="form-control" placeholder="Type to search..." style="margin-bottom:20px;">
				</div>
				</div>
				

				
				<div class="tabscroll">
				
				
				
				
				<?php
				if(count($arrConvSeasonEventsCats)>0)
				{
					echo '<div class="linkbtn">';
					$arrConvSeasonEventsCatsImplode = implode(",",$arrConvSeasonEventsCats);
					$condEvCats = array();
					$condEvCats[] = "(Eventcategories.id IN ($arrConvSeasonEventsCatsImplode) )";
					$eventCatList = $this->Eventcategories->find()->where($condEvCats)->order(['Eventcategories.name' => 'ASC'])->all();
					foreach($eventCatList as $eventcat)
					{
						echo '<a href="#category_'.$eventcat->id.'">'.$eventcat->name.'</a>';
						echo '';
					}
					echo '</div>';
				}
				?>
				</div>
				
				<table id="cr_student_events_list" class="table table-striped table-bordered" style="width:100%">
					<?php
					// first show categories
					if(count($arrConvSeasonEventsCats)>0)
					{
						$arrConvSeasonEventsCatsImplode = implode(",",$arrConvSeasonEventsCats);
						
						$condEvCats = array();
						$condEvCats[] = "(Eventcategories.id IN ($arrConvSeasonEventsCatsImplode) )";
						$eventCatList = $this->Eventcategories->find()->where($condEvCats)->order(['Eventcategories.name' => 'ASC'])->all();
						foreach($eventCatList as $eventcat)
						{
					?>
					<thead>
					<tr>
						<th colspan="3" class="text-center">
						<h3 id="category_<?php echo $eventcat->id; ?>"><?php echo $eventcat->name; ?> - <?php echo $eventcat->max_events; ?> max </h3>
						</th>
					</tr>
					</thead>
					<tbody>
						<?php
						// fetch all divisions for each category
						$arrConvSeasonEventsDivsImplode = implode(",",$arrConvSeasonEventsDivs);
						
						$condEvDivs = array();
						$condEvDivs[] = "(Divisions.eventcategory_id = '".$eventcat->id."')";
						$condEvDivs[] = "(Divisions.id IN ($arrConvSeasonEventsDivsImplode) )";
						$eventDivList = $this->Divisions->find()->where($condEvDivs)->order(['Divisions.sort_order' => 'ASC'])->all();
						foreach($eventDivList as $eventdiv)
						{
						?>
						
						<?php
						// start color to make different
						if($eventdiv->id == 12 )
						{
							// fetch parent div details
							$parentDivD = $this->Divisions->find()->where(["Divisions.id" => $eventdiv->parent_division_id])->first();
						?>
						<tr>
							<th colspan="3" class="text-center" style="background-color:#78A4C8;">
							<h3 id="" ><?php echo $parentDivD->name; ?> - <?php echo $parentDivD->max_events; ?> max </h3>
							</th>
						</tr>
						<?php
						}
						?>
						
						<?php
						// end color to make different
						if($eventdiv->id == 6 )
						{
						?>
						<tr>
							<th colspan="3" class="text-center" style="background-color:#78A4C8;">
							<h3 id="">&nbsp;</h3>
							</th>
						</tr>
						<?php
						}
						?>
						
						<tr>
							<td colspan="3" class="text-center">
								<span style="font-size:18px;">
									<?php echo $eventdiv->name; ?> - <?php echo $eventdiv->max_events; ?>
								max 
								</span>
							</td>
						</tr>
						
							<?php
							// now fetch all events for this Division
							$arrConvSeasonEventsListImplode = implode(",",$arrConvSeasonEventsList);
							
							$condEvents = array();
							$condEvents[] = "(Events.division_id = '".$eventdiv->id."')";
							$condEvents[] = "(Events.id IN ($arrConvSeasonEventsListImplode) )";
							$eventList = $this->Events->find()->where($condEvents)->order(['Events.event_name' => 'ASC'])->all();
							foreach($eventList as $event)
							{
							?>
							<tr>
								<td width="40%" class="text-center"><?php echo $event->event_name; ?></td>
								<td width="40%" class="text-center"><?php echo $event->event_id_number; ?></td>
								<td width="20%" class="text-center">
									<input type="checkbox" name="eventIDS[]" value="<?php echo $event->id; ?>" id="event_id_<?php echo $event->id; ?>"
									<?php echo in_array($event->id,(array)$selectedEvents) ? 'checked' : ''; ?> <?php if($regAccepted==0) echo 'disabled'; ?>
									/>
								</td>
							</tr>
						
						<?php
							} // end foreach($eventList as $event)
						?>
						<tr><td colspan="3" class="text-center">&nbsp;</td></tr>
						<?php
						} // end foreach($eventDivList as $eventdiv)
						?>
						<tr><td colspan="3" class="text-center">&nbsp;</td></tr>
					<?php
						} // end foreach($eventCatList as $eventcat)
					}// end if
					?>
					</tbody>
				</table>
				

				<div class="form-group form-btns">
					<label></label>
					<button type="submit" class="btn btn-secondary" <?php if($regAccepted==0) echo 'disabled'; ?>>Save</button>
					<!--<button type="button" class="btn btn-secondary">Cancel</button>-->
					<?php echo $this->Html->link('Cancel', ['controller' => 'conventionregistrations', 'action' => 'studentevents'], ['class' => 'btn btn-secondary']); ?>
				</div>
				
				<?php echo $this->Form->end(); ?>
			</div>
			<!-- dashboard-section-3 end-->

		</main>
	</div>
</div>
<script>
$(document).ready(function() {
	$("input:checkbox").change(function () {
	   
	   /* var countEvents = $('#cr_student_events_list').find('input[type=checkbox]:checked').length;
	   alert(countEvents);
	   return false; */
	   
	   //alert($(this).val());
	   var lastCheckedEVID = $(this).val();
	   
	   event.preventDefault();
	   var checkedEventIDS = $("#cr_student_events_list input:checkbox:checked").map(function(){
		  return $(this).val();
		}).get(); // <----
		//console.log(checkedEventIDS);
		
		// now call ajax to validate min/max/limitations in a Division
		$.ajax({
			type: 'POST',
			url: "<?php echo HTTP_PATH."/homes/checkstudentevent/".$checkCRS->slug; ?>/"+checkedEventIDS+"/"+lastCheckedEVID,
			//data: {checkedEventIDS : checkedEventIDS},
			cache: false,
			beforeSend: function () {
				//$("#loderstatus").show();
			},
			complete: function () {
				//$("#loderstatus").hide();
			},
			success: function (result) {
				//$("#loderstatus").hide();
				//$("#test_res").html(result);
				//return false;
				
				var objReturned = $.parseJSON(result);
				
				var totalEvChecked = objReturned.totalEvChecked;
				
				
				
				// to check if we receive error flag
				var errorFlag = objReturned.errorFlag;
				if(errorFlag)
				{
					var errorMsg = objReturned.errorMsg;
					var errorMsgShow = errorMsg.join('\n');
					alert(errorMsgShow);
				}
				
				// to check if need to discard last event checked
				if(objReturned.discardLastEventSelected)
				{
					//alert(objReturned.lastEventIDChecked);
					$('#event_id_'+objReturned.lastEventIDChecked).prop('checked', false);
					
					totalEvChecked = totalEvChecked-1;
				}
				
				$('#live_event_counter').html(totalEvChecked);
				
				return false;
				
				
				//alert(result);return false;
			}
		});
		return false;	   
	   
	   
	});
});
</script>
<script>
$(document).ready(function() {
	
	$("#filter").keyup(function() {

      // Retrieve the input field text and reset the count to zero
      var filter = $(this).val(),
        count = 0;

      // Loop through the comment list
      $('#cr_student_events_list tr').each(function() {

        // If the list item does not contain the text phrase fade it out
        if ($(this).text().search(new RegExp(filter, "i")) < 0) {
          $(this).hide();  // MY CHANGE

          // Show the list item if the phrase matches and increase the count by 1
        } else {
          $(this).show(); // MY CHANGE
          count++;
        }

      });

    });
	
});
</script>
<?php echo $this->element("jquery_datatable_code"); ?>