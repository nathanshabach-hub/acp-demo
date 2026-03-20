<script type="text/javascript">
    $(document).ready(function() {
        $("#adminlogin").validate();
    });
</script>
<div class="login-logo">
    Administration - <?php echo strtolower(SITE_TITLE); ?>
    <?php
     //echo $this->Html->image('front/admin_logo.png');
     
    ?>
</div>
<div class="login-box-body">
    <p class="login-box-msg">Sign in to start your session</p>
    <?= $this->Flash->render() ?>
    <?php echo $this->Form->create($admin, ['id'=>'adminlogin']); ?>
        <div class="form-group has-feedback">
            <?php echo $this->Form->control('Admins.username', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Username']); ?>
            <span class="glyphicon glyphicon-user form-control-feedback"></span>
        </div>
        <div class="form-group has-feedback">
            <?php echo $this->Form->control('Admins.password', ['label'=>false, 'type'=>'password',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Password']); ?>
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
        </div>
        <div class="row">
            <div class="col-xs-8">
                <div class="checkbox icheck">
                    <label><?php echo $this->Form->control('Admins.remember', ['type' => 'checkbox', 'label'=>false, 'div'=>false]);?> Remember Me
                    </label>
                </div>
            </div>
            <div class="col-xs-4">
                <?php echo $this->Form->button('Sign In', ['class'=>'btn btn-primary btn-block btn-flat']); ?>
            </div>
        </div>
    <?php  echo $this->Form->end(); ?>
    <?php echo $this->Html->link('I forgot my password', ['action' => 'forgotPassword']); ?>
</div>
