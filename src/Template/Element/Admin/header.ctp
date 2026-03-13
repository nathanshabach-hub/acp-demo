<?php
use Cake\ORM\TableRegistry;
$this->Seasons = TableRegistry::get('Seasons');
$this->Conventionseasons = TableRegistry::get('Conventionseasons');

$sess_admin_header_season_id = $this->request->session()->read("sess_admin_header_season_id");

// to get list of all active seasons
$seasonLAdH = $this->Seasons->find()->where(["status"=> 1])->order(["season_year"=> 'DESC'])->all();
?>
<script type="text/javascript">
$(document).ready(function(){
   $('#admin_header_season_id').change(function(){
       $('#adminHCSSelection').submit();
    });
});
</script>
<header class="main-header">
    <!-- Logo -->
    <a href="<?php echo HTTP_PATH; ?>/admin/admins/dashboard" class="logo">
        <span class="logo-mini"><b>A<?php
     //echo $this->Html->image('mini-logo.png');
    ?></b></span>
        <span class="logo-lg"><?php echo SITE_TITLE; ?><?php
     //echo $this->Html->image('logo.png');
    ?></span>
    </a>
    <nav class="navbar navbar-static-top">
        <a href="javascript:void(0);" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>
		
		
		<div class="header-filterwrap">
		<div class="header-dropdown">
			<?php echo $this->Form->create(false, array('url' => array('controller' => 'admins', 'action' => 'headerchooseconvseas'),'id' => 'adminHCSSelection')); ?>
				<select name="admin_header_season_id" id="admin_header_season_id">
					<option value="0">-- All Convention/Season --</option>
					<?php
					foreach($seasonLAdH as $seasadh)
					{
						// now check conventions of this season year
						$condadhCS = array();
						$condadhCS[] = "(Conventionseasons.season_id = '".$seasadh->id."' AND Conventionseasons.season_year = '".$seasadh->season_year."')";
						$listadhCS = $this->Conventionseasons->find()->where($condadhCS)->contain(["Conventions"])->all();
						if($listadhCS)
						{
							foreach($listadhCS as $adhcsrecord)
							{
								if(!empty($adhcsrecord->Conventions['name']))
								{
									if($sess_admin_header_season_id == $adhcsrecord->id)
										$selectADHCSR = 'selected';
									else
										$selectADHCSR = '';
					?>
								<option value="<?php echo $adhcsrecord->id; ?>" <?php echo $selectADHCSR; ?>><?php echo $adhcsrecord->Conventions['name']; ?> (<?php echo $seasadh->season_year; ?>)</option>
					<?php
								}
							}
						} // end if($listadhCS)
					}
					?>
				</select>
				<script>
					$(document).ready(function () {
						$('#admin_header_season_id').select2();
					});
				</script>
			</form>
			
			
		</div>
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <li class="dropdown user user-menu">
                    <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">
                        <?php //echo $this->Html->image('user2-160x160.jpg', ['alt' => SITE_TITLE, "class" => "user-image"]); ?>
                        <span class="hidden-xs"><?php echo $this->request->session()->read('admin_username') ?></span>
                    </a>
                </li>
                <li><?php echo $this->Html->link('<i class="fa fa-sign-out fa-lg"></i> Logout', ['controller' => 'admins', 'action' => 'logout'], ['escape' => false]); ?>  </li>

            </ul>
        </div>
		</div>
    </nav>
</header>