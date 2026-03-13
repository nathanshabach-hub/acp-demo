<?php echo $this->Html->script('ajax-pagging.js'); ?>

<?php
if($userDetails->user_type == 'School')
{
?>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>
			
			
			<div class="teachers-top-heading">
				<span>Convention Registrations</span>
				<?php //echo $this->Html->link('+ Add New', ['controller' => 'users', 'action' => 'addteacher'], ['escape' => false, 'class' => 'btn btn-primary']); ?>
			</div>

			<div class="m_content" id="listID">
				<?php echo $this->element("Conventionregistrations/myregistrations"); ?>
			</div>
			
			
			<?php if (!$remainingconventions->isEmpty()) { ?>
			
			<hr>
			
			<div class="teachers-top-heading">
				<span>New Conventions to Register</span>
				<?php //echo $this->Html->link('+ Add New', ['controller' => 'users', 'action' => 'addteacher'], ['escape' => false, 'class' => 'btn btn-primary']); ?>
			</div>
			
			<div class="m_content" id="listID">
				<?php echo $this->element("Conventionregistrations/remainingconventions"); ?>
			</div>
			
			<?php } ?>
			
			
			<!-- to show past registrations -->
			<?php if (!$pastRegistrations->isEmpty()) { ?>
			
			<hr>
			
			<div class="teachers-top-heading">
				<span>Past Registrations</span>
			</div>
			
			<div class="m_content" id="listID">
				<?php echo $this->element("Conventionregistrations/pastRegistrations"); ?>
			</div>
			
			<?php } ?>
			
		</main>
	</div>
</div>
<?php
}
?>


<?php
if($userDetails->user_type == 'Judge' || $this->request->session()->read("current_session_profile_type") == 'Judge')
{
?>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>
			
			
			<div class="teachers-top-heading">
				<span>Convention Registrations</span>
				<?php //echo $this->Html->link('+ Add New', ['controller' => 'users', 'action' => 'addteacher'], ['escape' => false, 'class' => 'btn btn-primary']); ?>
			</div>

			<div class="m_content" id="listID">
				<?php echo $this->element("Conventionregistrations/judgesregistrations"); ?>
			</div>
			
			
			<?php if (!$remainingconventions->isEmpty()) { ?> 
			
			<hr>
			
			<div class="teachers-top-heading">
				<span>New Conventions to Register</span>
				<?php //echo $this->Html->link('+ Add New', ['controller' => 'users', 'action' => 'addteacher'], ['escape' => false, 'class' => 'btn btn-primary']); ?>
			</div>
			
			<div class="m_content" id="listID">
				<?php echo $this->element("Conventionregistrations/judgesremainingconventions"); ?>
			</div>
			
			<?php } ?> 

		</main>
	</div>
</div>
<?php
}
?>