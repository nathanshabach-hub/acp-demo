<?php
// school admin left menu
if ($this->request->session()->read("user_id") > 0 && ($this->request->session()->read("user_type") == "School"))
{
?>
	<div class="sidebar-icon d-md-none"><i class="fa fa-bars"></i>
		</div>
	<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-blue sidebar collapse">
		
		<div class="position-sticky pt-3 pb-3 sidebar-sticky">
			<ul class="nav flex-column">
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Dashboard', ['controller' => 'users', 'action' => 'dashboard'], ['escape' => false, 'class' => 'nav-link ' . $active_dashboard]); ?>
				</li>
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Global Supervisors List', ['controller' => 'users', 'action' => 'teachers'], ['escape' => false, 'class' => 'nav-link ' . $active_teachers]); ?>
				</li>
				<li class="nav-item dash-item ">
					<?php echo $this->Html->link('Global Student List', ['controller' => 'users', 'action' => 'students'], ['escape' => false, 'class' => 'nav-link ' . $active_students]); ?>
				</li>
				<li class="nav-item dash-item ">
					<?php echo $this->Html->link('Convention Registrations', ['controller' => 'conventionregistrations', 'action' => 'myregistrations'], ['escape' => false, 'class' => 'nav-link ' . $active_convention_registrations]); ?>
				</li>
				
				
				<?php
				// to show links related to convention registrations
				if($this->request->session()->read("sess_selected_convention_registration_id")>0)
				{
				?>
				<hr>
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Price Structure', ['controller' => 'conventionregistrations', 'action' => 'pricestructure'], ['escape' => false, 'class' => 'nav-link ' . $active_cr_price_structure]); ?>
				</li>
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Supervisor Registration', ['controller' => 'conventionregistrations', 'action' => 'teachers'], ['escape' => false, 'class' => 'nav-link ' . $active_cr_teachers]); ?>
				</li>
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Student Registration', ['controller' => 'conventionregistrations', 'action' => 'students'], ['escape' => false, 'class' => 'nav-link ' . $active_cr_students]); ?>
				</li>
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Student Event Registration', ['controller' => 'conventionregistrations', 'action' => 'studentevents'], ['escape' => false, 'class' => 'nav-link ' . $active_cr_studentevents]); ?>
				</li>
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Student Grouping', ['controller' => 'groups', 'action' => 'viewlist'], ['escape' => false, 'class' => 'nav-link ' . $active_cr_studentgroups]); ?>
				</li>
				<!--
				<li class="nav-item dash-item">
					<a class="nav-link" href="#">
						Update Scripture Awards
					</a>
				</li>
				-->
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Events of the Heart', ['controller' => 'heartevents', 'action' => 'viewlist'], ['escape' => false, 'class' => 'nav-link ' . $active_cr_eventsheart]); ?>
				</li>
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('View/Edit Event Submissions', ['controller' => 'eventsubmissions', 'action' => 'viewlist'], ['escape' => false, 'class' => 'nav-link ' . $active_cr_eventsubmission]); ?>
				</li>
				
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Registration Checklist', ['controller' => 'conventionregistrations', 'action' => 'packageregistration'], ['escape' => false, 'class' => 'nav-link ' . $active_cr_packageregistration]); ?>
				</li>
				
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Result Package', ['controller' => 'conventionregistrations', 'action' => 'resultpackage'], ['escape' => false, 'class' => 'nav-link ' . $active_cr_resultpackage]); ?>
				</li>
				
				<hr>
				<?php
				}
				?>
				
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Payment Transactions', ['controller' => 'transactions', 'action' => 'mytransactions'], ['escape' => false, 'class' => 'nav-link ' . $active_transactions]); ?>
				</li>
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Edit Profile', ['controller' => 'users', 'action' => 'editprofile'], ['escape' => false, 'class' => 'nav-link ' . $active_editprofile]); ?>
				</li>
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Change Password', ['controller' => 'users', 'action' => 'changepassword'], ['escape' => false, 'class' => 'nav-link ' . $active_changepassword]); ?>
				</li>
				
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Logout', ['controller' => 'users', 'action' => 'logout'], ['escape' => false, 'class' => 'nav-link']); ?>
				</li>
			</ul>
		</div>
	</nav>
	<?php
	// school admin left menu
}
?>

<?php
// teacher left menu
if ($this->request->session()->read("user_id") > 0 && ($this->request->session()->read("user_type") == "Teacher_Parent")) {
?>
	<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-blue sidebar collapse">
		<div class="position-sticky pt-3 pb-3 sidebar-sticky">
			<ul class="nav flex-column">
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Dashboard', ['controller' => 'users', 'action' => 'dashboard'], ['escape' => false, 'class' => 'nav-link ' . $active_dashboard]); ?>
				</li>
				
				<?php
				// to show links related to convention registrations
				if($this->request->session()->read("sess_selected_convention_registration_id")>0 && $this->request->session()->read("current_session_profile_type") == "Supervisor")
				{
				?>
				<hr>
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Student Event Registration', ['controller' => 'conventionregistrations', 'action' => 'studentevents'], ['escape' => false, 'class' => 'nav-link ' . $active_cr_studentevents]); ?>
				</li>
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Student Grouping', ['controller' => 'groups', 'action' => 'viewlist'], ['escape' => false, 'class' => 'nav-link ' . $active_cr_studentgroups]); ?>
				</li>
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Events of the Heart', ['controller' => 'heartevents', 'action' => 'viewlist'], ['escape' => false, 'class' => 'nav-link ' . $active_cr_eventsheart]); ?>
				</li>
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('View/Edit Event Submissions', ['controller' => 'eventsubmissions', 'action' => 'viewlist'], ['escape' => false, 'class' => 'nav-link ' . $active_cr_eventsubmission]); ?>
				</li>
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Registration Checklist', ['controller' => 'conventionregistrations', 'action' => 'packageregistration'], ['escape' => false, 'class' => 'nav-link ' . $active_cr_packageregistration]); ?>
				</li>
				<hr>
				<?php
				}
				?>
				
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Edit Profile', ['controller' => 'users', 'action' => 'editprofile'], ['escape' => false, 'class' => 'nav-link ' . $active_editprofile]); ?>
				</li>
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Change Password', ['controller' => 'users', 'action' => 'changepassword'], ['escape' => false, 'class' => 'nav-link ' . $active_changepassword]); ?>
				</li>
				
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Judge Profile', ['controller' => 'users', 'action' => 'applyforjudge'], ['escape' => false, 'class' => 'nav-link ' . $active_applyforjudge]); ?>
				</li>
				
			
				<?php
				if ($this->request->session()->read("current_session_profile_type")  == "Judge")
				{
				?>
				<hr>
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Judge Experience', ['controller' => 'users', 'action' => 'judgeexperience'], ['escape' => false, 'class' => 'nav-link ' . $active_judgeexp]); ?>
				</li>
				<li class="nav-item dash-item ">
					<?php echo $this->Html->link('Convention Registrations', ['controller' => 'conventionregistrations', 'action' => 'myregistrations'], ['escape' => false, 'class' => 'nav-link ' . $active_convention_registrations]); ?>
				</li>
				<hr>
				<?php
				}// end if($userDetails->is_judge == 0) 
				?>
				
				
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Logout', ['controller' => 'users', 'action' => 'logout'], ['escape' => false, 'class' => 'nav-link']); ?>
				</li>
			</ul>
		</div>
	</nav>
	<?php
	// teacher left menu
}
?>

<?php
// judges left menu
if ($this->request->session()->read("user_id") > 0 && ($this->request->session()->read("user_type") == "Judge"))
{
?>
	<div class="sidebar-icon d-md-none"><i class="fa fa-bars"></i></div>
	<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-blue sidebar collapse">
		
		<div class="position-sticky pt-3 pb-3 sidebar-sticky">
			<ul class="nav flex-column">
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Dashboard', ['controller' => 'users', 'action' => 'dashboard'], ['escape' => false, 'class' => 'nav-link ' . $active_dashboard]); ?>
				</li>
				
				<li class="nav-item dash-item ">
					<?php echo $this->Html->link('Convention Registrations', ['controller' => 'conventionregistrations', 'action' => 'myregistrations'], ['escape' => false, 'class' => 'nav-link ' . $active_convention_registrations]); ?>
				</li>
				
				<?php
				// to show links related to convention registrations
				if($this->request->session()->read("sess_selected_convention_registration_id")>0)
				{
				?>
				<hr>
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Events', ['controller' => 'conventionregistrations', 'action' => 'judgeevents'], ['escape' => false, 'class' => 'nav-link ' . $active_cr_judgeevents]); ?>
				</li>
				<hr>
				<?php
				}
				?>
				
				
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Edit Profile', ['controller' => 'users', 'action' => 'judgeeditprofile'], ['escape' => false, 'class' => 'nav-link ' . $active_editprofile]); ?>
				</li>
				
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Change Password', ['controller' => 'users', 'action' => 'changepassword'], ['escape' => false, 'class' => 'nav-link ' . $active_changepassword]); ?>
				</li>
				
				<li class="nav-item dash-item">
					<?php echo $this->Html->link('Logout', ['controller' => 'users', 'action' => 'logout'], ['escape' => false, 'class' => 'nav-link']); ?>
				</li>
			</ul>
		</div>
	</nav>
<?php
}
?>