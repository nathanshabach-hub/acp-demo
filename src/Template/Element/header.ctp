<?php
use Cake\ORM\TableRegistry;
$this->Users = TableRegistry::get('Users');

$user_id = $this->request->session()->read("user_id");
if($user_id>0)
{
	$loggedinUserD = $this->Users->find()->where(["Users.id" => $user_id])->first();
}
?>
<section>
	<nav class="navbar navbar-expand-lg ">
		<div class="container">
			<div class="main-logo">
				<a target="_blank" href="https://convention.accelerateministries.com.au/"><?php echo $this->Html->image('front/main-logo-120px.png'); ?></a>
			</div>
			<button class="navbar-toggler bg-white" type="button" data-bs-toggle="collapse"
				data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false"
				aria-label="Toggle navigation">
				<span class="navbar-toggler-icon "></span>
			</button>

			<?php
			$displayedTopDD = 0;
			// school admin + Supervisor header dropdown menu
			if ($this->request->session()->read("user_id") > 0 && ($this->request->session()->read("user_type") == "School" || ($this->request->session()->read("user_type") == "Teacher_Parent" && $this->request->session()->read("current_session_profile_type") == "Supervisor")))
			{	
				$displayedTopDD = 1;

				$sess_selected_convention_id = "";
				if ($this->request->session()->read("sess_selected_convention_registration_id") > 0) {
					$sess_selected_convention_id = $this->request->session()->read("sess_selected_convention_id");
				}
				?>

				<span class="header_conv_reg_box">
					<form method="post" action="<?php echo HTTP_PATH; ?>/homes/headerconvddfrmsubmit"
						name="header_conventions_dd_frm" id="header_conventions_dd_frm">

						<select name="Conventions[convention_id]" id="convention_id" class="form-control form-select"
							autocomplete="off" 4="4">
							<option value="">Choose Convention</option>
							<?php
							foreach ($userConvHeaderDD as $uhconvid => $uhconvname) {
								if ($sess_selected_convention_id == $uhconvid)
									$selectedCH = 'selected';
								else
									$selectedCH = '';
								?>
								<option value="<?php echo $uhconvid; ?>" <?php echo $selectedCH; ?>><?php echo $uhconvname; ?>
								</option>
								<?php
							}
							?>
						</select>

						<?php //echo $this->Form->select('Conventions.convention_id', $userConvHeaderDD, ['id' => 'convention_id', 'label' => false, 'div' => false, 'class' => 'form-control form-select', 'autocomplete' => 'off', 'empty' => 'Choose Convention',$sess_selected_convention_id]); ?>
						<script>
							$(document).ready(function () {
								$('#convention_id').select2();
							});
						</script>
						<input id="header_conv_dd_frm" type="submit" name="submit" value="Submit"
							style="visibility:hidden;">
					</form>
				</span>

				<script>
					$(document).ready(function () {
						$('#convention_id').change(function () {
							$('#header_conv_dd_frm').click();
						});
					});
				</script>
			<?php
			}
			?>
			
			<?php
			if($displayedTopDD == 0)
			{
				// school judge + Supervisor as a judge header dropdown menu
				if ($this->request->session()->read("user_id") > 0 && ($this->request->session()->read("user_type") == "Judge" || ($this->request->session()->read("user_type") == "Teacher_Parent" || $this->request->session()->read("current_session_profile_type") == "Judge")))
				/* changes - 06june2024
				if ($this->request->session()->read("user_id") > 0 && ($this->request->session()->read("user_type") == "Judge" || ($this->request->session()->read("user_type") == "Teacher_Parent" && $this->request->session()->read("current_session_profile_type") == "Judge")))
				*/
				{
					
					$sess_selected_convention_id = "";
					if ($this->request->session()->read("sess_selected_convention_registration_id") > 0) {
						$sess_selected_convention_id = $this->request->session()->read("sess_selected_convention_id");
					}
					?>

					<span class="header_conv_reg_box">
						<form method="post" action="<?php echo HTTP_PATH; ?>/homes/headerconvddfrmsubmit"
							name="header_conventions_dd_frm" id="header_conventions_dd_frm">

							<select name="Conventions[convention_id]" id="convention_id" class="form-control form-select"
								autocomplete="off" 4="4">
								<option value="">Choose Convention</option>
								<?php
								foreach ($userConvHeaderDD as $uhconvid => $uhconvname) {
									if ($sess_selected_convention_id == $uhconvid)
										$selectedCH = 'selected';
									else
										$selectedCH = '';
									?>
									<option value="<?php echo $uhconvid; ?>" <?php echo $selectedCH; ?>><?php echo $uhconvname; ?>
									</option>
									<?php
								}
								?>
							</select>

							<?php //echo $this->Form->select('Conventions.convention_id', $userConvHeaderDD, ['id' => 'convention_id', 'label' => false, 'div' => false, 'class' => 'form-control form-select', 'autocomplete' => 'off', 'empty' => 'Choose Convention',$sess_selected_convention_id]); ?>
							<script>
								$(document).ready(function () {
									$('#convention_id').select2();
								});
							</script>
							<input id="header_conv_dd_frm" type="submit" name="submit" value="Submit"
								style="visibility:hidden;">
						</form>
					</span>

					<script>
						$(document).ready(function () {
							$('#convention_id').change(function () {
								$('#header_conv_dd_frm').click();
							});
						});
					</script>
				<?php
				}
			}
			?>
			
			<div class="collapse navbar-collapse " id="navbarNavDropdown">
				<ul class="navbar-nav">
					
					<?php
					if(!($this->request->session()->read("user_id") > 0))
					{
					?>
					<li class="nav-item ">
						<a class="nav-link px-3" aria-current="page" href="<?php echo HTTP_PATH; ?>">Home</a>
					</li>
					<?php
					}
					else
					{
					?>
					<li class="nav-item ">
						<a class="nav-link px-3" aria-current="page" href="<?php echo HTTP_PATH; ?>/users/dashboard">Home</a>
					</li>
					<?php
					}
					?>
					
					<li class="nav-item">
						<a target="_blank" class="nav-link px-3" href="https://convention.accelerateministries.com.au/what-is-student-convention/">About</a>
					</li>
					
					<li class="nav-item conventions-dropdown">
						<a class="nav-link px-3 " href="#">Conventions</a>
						<ul class="conventions-dropdown-menu dropdown-menu">
							
							<li class="conventions-sub-dropdown">
								<a class="conventions-dropdown-item" href="#">Online</a>
								<ul class="conventions-sub-dropdown-menu dropdown-menu">
									<li><a class="conventions-dropdown-item" target="_blank" href="https://convention.accelerateministries.com.au/convention-online-2022/">2023 Online Convention</a></li>
								</ul>
							</li>
							
							<li>
								<a class="conventions-dropdown-item" target="_blank" href="https://convention.accelerateministries.com.au/regional/">Regional</a>
							</li>
							<li>
								<a class="conventions-dropdown-item" target="_blank" href="https://convention.accelerateministries.com.au/south-pacific/">South Pacific</a>
							</li>
							<li>
								<a class="conventions-dropdown-item" target="_blank" href="https://convention.accelerateministries.com.au/international/">International</a>
							</li>
						</ul>
					</li>
					
					<?php
					if(!($this->request->session()->read("user_id") > 0))
					{
					?>
					<li class="nav-item ">
						<a class="nav-link px-3 <?php echo $header_menu_register_active; ?>"
							href="<?php echo HTTP_PATH; ?>">Register</a>
					</li>
					<li class="nav-item ">
						<a class="nav-link px-3 <?php echo $header_menu_login_active; ?>" href="<?php echo HTTP_PATH; ?>/users/login">Login</a>
					</li>
					<?php
					}
					?>
					
					<li class="nav-item guide-dropdown">
						<a class="nav-link px-3 " href="#">Guidelines</a>
						<ul class="dropdown-menu">
							<li><a class="dropdown-item" target="_blank" href="https://convention.accelerateministries.com.au/guidelines/">Guidelines (In Person)</a></li>
							<li><a class="dropdown-item" target="_blank" href="https://convention.accelerateministries.com.au/guidelines-online/">Guidelines (Online)</a></li>
						</ul>
					</li>
					<li class="nav-item ">
						<a target="_blank" class="nav-link px-3" href="https://convention.accelerateministries.com.au/updates/">Update</a>
					</li>
					
					<?php
					if(!($this->request->session()->read("user_id") > 0))
					{
					?>
					<li class="nav-item ">
						<a class="nav-link px-3 <?php echo $header_menu_judgesreg_active; ?>" href="<?php echo HTTP_PATH; ?>/users/judgesregistration">Judges Registration</a>
					</li>
					<?php
					}
					?>
					
					
					
					<?php
					// switch between profiles
					if($user_id>0 && $loggedinUserD->user_type == 'Teacher_Parent' && $loggedinUserD->is_judge == '1')
					{
					?>
					
					<li class="nav-item guide-dropdown">
						<a class="nav-link px-3 " href="#">Switch</a>
					 
						<?php
						if($this->request->session()->read("current_session_profile_type") == 'Supervisor')
						{
						?>
						<ul class="dropdown-menu">
							<li><a class="dropdown-item" href="<?php echo HTTP_PATH; ?>/users/switchprofile/switchtojudge">Switch to Judge</a></li>
						</ul>
						<?php
						}
						?>
						
						<?php
						if($this->request->session()->read("current_session_profile_type") == 'Judge')
						{
						?>
						<ul class="dropdown-menu">
							<li><a class="dropdown-item" href="<?php echo HTTP_PATH; ?>/users/switchprofile/switchtosupervisor">Switch to Supervisor</a></li>
						</ul>
						<?php
						}
						?>
					</li>
					<?php
					}
					?>
					
					
					
					
					<?php
					if($this->request->session()->read("user_id") > 0)
					{
					?>
					<li class="nav-item ">
						<a class="nav-link px-3" href="<?php echo HTTP_PATH; ?>/users/logout">Logout</a>
					</li>
					
					<?php
					}
					?>
					
				</ul>
			</div>
		</div>
	</nav>
</section>