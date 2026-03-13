<aside class="main-sidebar">
    <section class="sidebar">
        <ul class="sidebar-menu">
            <li class="treeview <?php if(isset($dashboard)){ echo 'active';} ?>">
                <a href="<?php echo HTTP_PATH;?>/admin/admins/dashboard">
                    <i class="fa fa-dashboard"></i> <span>Dashboard</span> <i class="fa fa-angle-left pull-right"></i>
                </a>
            </li>
            <li class="treeview <?php if(isset($manageConfig)){ echo 'active';} ?>">
                <a href="javascript:void(0)">
                    <i class="fa fa-gears"></i> <span>Configuration</span> <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="<?php if(isset($changeEmail)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> Change Email', ['controller'=>'admins', 'action' => 'changeEmail'], ['escape'=>false]); ?></li>
                    <li class="<?php if(isset($changeUsername)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> Change Username', ['controller'=>'admins', 'action' => 'changeusername'], ['escape'=>false]); ?></li>
                    <li class="<?php if(isset($changePassword)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> Change Password', ['controller'=>'admins', 'action' => 'changePassword'], ['escape'=>false]); ?></li>
					<li class="<?php if(isset($settings)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> Settings', ['controller'=>'admins', 'action' => 'settings'], ['escape'=>false]); ?></li>
					<li class="<?php if(isset($postinfo)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> Post Information', ['controller'=>'admins', 'action' => 'postinfo'], ['escape'=>false]); ?></li>
                </ul>
            </li>
			
			<li class="treeview <?php if(isset($manageEvents)){ echo 'active';} ?>">
                <a href="javascript:void(0)">
                    <i class="fa fa-puzzle-piece"></i> <span>Manage Global Events</span> <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="<?php if(isset($manageEventCategories)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> Categories', ['controller'=>'eventcategories', 'action' => 'index'], ['escape'=>false]); ?></li>
					<li class="<?php if(isset($manageDivisions)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> Divisions', ['controller'=>'divisions', 'action' => 'index'], ['escape'=>false]); ?></li>
					<li class="<?php if(isset($eventList)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> List Events', ['controller'=>'events', 'action' => 'index'], ['escape'=>false]); ?></li>
                    <li class="<?php if(isset($eventAdd)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> Add Event', ['controller'=>'events', 'action' => 'add'], ['escape'=>false]); ?></li>
                </ul>
            </li>
			
			<li class="treeview <?php if(isset($manageSeasons)){ echo 'active';} ?>">
                <a href="javascript:void(0)">
                    <i class="fa fa-bullhorn"></i> <span>Manage Seasons</span> <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="<?php if(isset($seasonList)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> List Seasons', ['controller'=>'seasons', 'action' => 'index'], ['escape'=>false]); ?></li>
                    <li class="<?php if(isset($seasonAdd)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> Add Season', ['controller'=>'seasons', 'action' => 'add'], ['escape'=>false]); ?></li>
                </ul>
            </li>
			
			<li class="treeview <?php if(isset($manageConventions)){ echo 'active';} ?>">
                <a href="javascript:void(0)">
                    <i class="fa fa-bars"></i> <span>Manage Conventions</span> <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="<?php if(isset($conventionList)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> List Conventions', ['controller'=>'conventions', 'action' => 'index'], ['escape'=>false]); ?></li>
                    <li class="<?php if(isset($conventionAdd)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> Add Convention', ['controller'=>'conventions', 'action' => 'add'], ['escape'=>false]); ?></li>
                </ul>
            </li>
			
			<li class="treeview <?php if(isset($manageSchools)){ echo 'active';} ?>">
                <a href="javascript:void(0)">
                    <i class="fa fa-bank"></i> <span>Manage Schools</span> <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="<?php if(isset($schoolList)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> List Schools', ['controller'=>'users', 'action' => 'index'], ['escape'=>false]); ?></li>
                    <li class="<?php if(isset($schoolAdd)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> Add School', ['controller'=>'users', 'action' => 'add'], ['escape'=>false]); ?></li>
					<li class="<?php if(isset($schoolImport)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> Import School', ['controller'=>'users', 'action' => 'csvimport'], ['escape'=>false]); ?></li>
                </ul>
            </li>
			
			<li class="treeview <?php if(isset($manageTeachers)){ echo 'active';} ?>">
                <a href="javascript:void(0)">
                    <i class="fa fa-user-secret"></i> <span>Manage Supervisors</span> <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="<?php if(isset($teacherList)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> List Supervisors', ['controller'=>'users', 'action' => 'teachers'], ['escape'=>false]); ?></li>
                </ul>
            </li>
			
			<li class="treeview <?php if(isset($manageJudges)){ echo 'active';} ?>">
                <a href="javascript:void(0)">
                    <i class="fa fa-bookmark"></i> <span>Manage Judges</span> <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="<?php if(isset($activeJudges)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> Judges', ['controller'=>'users', 'action' => 'judges'], ['escape'=>false]); ?></li>
					<li class="<?php if(isset($pendingJudges)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> Pending Judges', ['controller'=>'users', 'action' => 'pendingjudges'], ['escape'=>false]); ?></li>
                </ul>
            </li>
			
			<li class="treeview <?php if(isset($manageStudents)){ echo 'active';} ?>">
                <a href="javascript:void(0)">
                    <i class="fa fa-group"></i> <span>Manage Students</span> <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="<?php if(isset($studentList)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> List Students', ['controller'=>'users', 'action' => 'students'], ['escape'=>false]); ?></li>
                </ul>
            </li>
			
			<li class="treeview <?php if(isset($manageRegistrations)){ echo 'active';} ?>">
                <a href="javascript:void(0)">
                    <i class="fa fa-newspaper-o"></i> <span>Convention Registrations</span> <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="<?php if(isset($registrationsList)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> List Registrations', ['controller'=>'conventionregistrations', 'action' => 'index'], ['escape'=>false]); ?></li>
                </ul>
            </li>
			
			<li class="treeview <?php if(isset($judgeEvaluations)){ echo 'active';} ?>">
                <a href="javascript:void(0)">
                    <i class="fa fa-gavel"></i> <span>Judge Evaluations</span> <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="<?php if(isset($judgeEvaluationsList)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> List Judge Evaluations', ['controller'=>'judgeevaluations', 'action' => 'index'], ['escape'=>false]); ?></li>
					<li class="<?php if(isset($guidelineBreachList)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> Guideline Breach', ['controller'=>'eventsubmissions', 'action' => 'guidelinebreach'], ['escape'=>false]); ?></li>
					<li class="<?php if(isset($commandPerformanceList)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> Command Performance', ['controller'=>'eventsubmissions', 'action' => 'commandperformance'], ['escape'=>false]); ?></li>
                </ul>
            </li>
			
			<li class="treeview <?php if(isset($manageTransactions)){ echo 'active';} ?>">
                <a href="javascript:void(0)">
                    <i class="fa fa-dollar"></i> <span>Manage Transactions</span> <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="<?php if(isset($transactionsList)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> List Transactions', ['controller'=>'transactions', 'action' => 'index'], ['escape'=>false]); ?></li>
                </ul>
            </li>
			
			<li class="treeview <?php if(isset($manageCombinedRequests)){ echo 'active';} ?>">
                <a href="javascript:void(0)">
                    <i class="fa fa-user-secret"></i> <span>Combined Team/Group Events </span> <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="<?php if(isset($combinedRequestsList)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> List Requests', ['controller'=>'combinerequests', 'action' => 'index'], ['escape'=>false]); ?></li>
                </ul>
            </li>
			
			
			
			<?php
			if($this->request->session()->read("sess_admin_header_season_id") >0)
			{
			?>
			
			<li class="treeview <?php if(isset($nameTags)){ echo 'active';} ?>">
                <a href="javascript:void(0)">
                    <i class="fa fa-tasks"></i> <span>Name Tags</span> <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="<?php if(isset($nameTagsStudents)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> List Students', ['controller'=>'nametags', 'action' => 'students'], ['escape'=>false]); ?></li>
					<li class="<?php if(isset($nameTagsSponsors)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> List Sponsors', ['controller'=>'nametags', 'action' => 'sponsors'], ['escape'=>false]); ?></li>
					<li class="<?php if(isset($nameTagsVisitors)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> List Visitors', ['controller'=>'nametags', 'action' => 'visitors'], ['escape'=>false]); ?></li>
					
                </ul>
            </li>
			<?php
			}
			?>
			
			
			<!--
			<li class="treeview <?php if(isset($manageEvaluations)){ echo 'active';} ?>">
                <a href="javascript:void(0)">
                    <i class="fa fa-bullseye"></i> <span>Evaluations Forms</span> <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="<?php if(isset($tagsList)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> List Tags', ['controller'=>'evaluationtags', 'action' => 'index'], ['escape'=>false]); ?></li>
					<li class="<?php if(isset($evalcategoriesList)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> List Categories', ['controller'=>'evaluationcategories', 'action' => 'index'], ['escape'=>false]); ?></li>
					<li class="<?php if(isset($questionsList)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> List Questions', ['controller'=>'evaluationquestions', 'action' => 'index'], ['escape'=>false]); ?></li>
					<li class="<?php if(isset($formsList)){ echo 'active';} ?>"><?php echo $this->Html->link('<i class="fa fa-circle-o"></i> List Forms', ['controller'=>'evaluationforms', 'action' => 'index'], ['escape'=>false]); ?></li>
                </ul>
            </li>
			-->

             
			
			
             
        </ul>
    </section>
</aside>
