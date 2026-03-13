 <section class="position-relative body-bg">
	<div class=" left-element">
		<?php echo $this->Html->image('front/left-element.png'); ?>
	</div>
	<div class=" ryt-element">
		<?php echo $this->Html->image('front/ryt-element.png'); ?>
	</div>
	<div class="container">
		<div class="row center-section align-items-center">
			
			<?php echo $this->element('Homes/home_left_content'); ?>
			
			
			<div class="col-lg-6 ">
				<div class="ryt-box bg-white ">
					<div class="ryt-box-text">
						<div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
						<?php echo $this->Form->create(NULL, ['id'=>'home_conventions', 'type' => 'file']); ?>
						<h2 class="mb-4">Select Convention</h2>
						<!--
						<select class="form-select mb-4" aria-label="Default select example">
							<option selected>Open this select menu</option>
							<option value="1">One</option>
							<option value="2">Two</option>
							<option value="3">Three</option>
						</select>
						-->
						<input type="hidden" name="hidd_season_id" id="hidd_season_id" value="<?php echo $season_id; ?>" />
						<?php echo $this->Form->select('Events.convention_id', $conventionDD, ['id' => 'convention_id', 'label' => false, 'div' => false, 'class' => 'form-control form-select required', 'autocomplete' => 'off', 'empty' => 'Choose Convention']); ?>
						<script>
							$(document).ready(function() {
								$('#convention_id').select2();
							});
						</script>
						
						<script>
							$(document).ready(function(){
							   $('#convention_id').change(function(){
								   //document.getElementById('home_conventions').submit();

									$("#reg_login_buttons_box").css("display", "none");
									
									var hidd_season_id 	= $('#hidd_season_id').val();
									
									var convention_id 	= $('#convention_id').val();
									if(convention_id == "")
									{
										alert("Please choose convention.");
										return false;
									}
									else
									{
										$.ajax({
											type: 'POST',
											url: "<?php echo HTTP_PATH; ?>/homes/chooseconvention/"+convention_id+"/"+hidd_season_id,
											cache: false,
											beforeSend: function () {
												//$("#loaderdiv").show();
											},
											complete: function () {
												//$("#loaderdiv").hide();
											},
											success: function (result) {
												//$("#loaderdiv").hide();
												//$("#reg_login_buttons_box").css("display", "block");
												$("#reg_login_buttons_box").html(result).show();
											} 
										});
									
									}
									return false;
								   
								});
							});
						</script>
						
						<div class="mt-3" id="reg_login_buttons_box" style="display:none;">
							
						</div>
						
						
						<?php echo $this->Form->end(); ?>
						
						<!--<button type="button" class="btn btn-secondary px-3"><a href="">Next</a> </button>-->
					</div>
				</div>
			</div>
			
			
		</div>
	</div>
</section>