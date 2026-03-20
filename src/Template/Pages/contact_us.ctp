<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });
</script>
<script src='https://www.google.com/recaptcha/api.js'></script>

<section class="contact_section  main-wrapper pt-0">
	
	<div class="container">
	<div class="titlepage_contact">Contact Us</div>
		<div class="contact-bar aos-init aos-animate" data-aos="fade-up">
			<div class="ersu_message"><?php echo $this->Flash->render() ?> </div>
			<div class="row">
				<div class="col-12 col-sm-12 col-md-12">
					<div class="contact-details">
						<div class="address-details addre-info">
							<h3>Address</h3>
							<i class="fa fa-home" aria-hidden="true"></i>
							<span><?php echo $adminInfo->contact_address; ?></span>
						</div>
						<div class="address-details email-details">
							<h3>E-mail</h3>
							<i class="fa fa-envelope" aria-hidden="true"></i>
							<span><?php echo $adminInfo->contact_us_email; ?></span>
						</div>
						<div class="address-details phone-details">
							<h3>Phones</h3>
							<i class="fa fa-phone" aria-hidden="true"></i>
							<span><?php echo $adminInfo->contact_phone; ?></span>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-12 col-sm-6 col-md-12">
					<div class="book-posts aos-init aos-animate" data-aos="fade-up">
						<div class="my-contact">
							<!--<h2 class="header-title">Contact us</h2>-->
							<div class="well well-sm">
								<h3>You can contact us any way that is convenient for you. We are available 24/7 via fax or email.<br>
									You can also use a quick contact form below or visit our salon personally.
								</h3>
								<?php echo $this->Form->create('', ['id'=>'adminForm', 'type' => 'file']); ?>
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<div class="input text">
													<?php echo $this->Form->control('Contact.first_name', ['label'=>false, 'type'=>'text', 'autocomplete'=>'off', 'div'=>false, 'class'=>'form-control required', 'placeholder'=>'First Name']); ?>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<div class="input text">
													<?php echo $this->Form->control('Contact.last_name', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Last Name']); ?>
												</div>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<div class="form-group">
												<div class="input text">
													<?php echo $this->Form->control('Contact.email_address', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required email', 'placeholder'=>'Email']); ?>
												</div>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<div class="form-group">
												<div class="input textarea">
													<?php echo $this->Form->control('Contact.message', ['label'=>false, 'type'=>'textarea',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Message','style'=>'margin-left:0px;']); ?>
												</div>
											</div>
										</div>
									</div>
									<div class="row">
										
										<div class="col-md-12">
											<div class="g-recaptcha" data-sitekey="<?php echo SITEKEY; ?>"></div>
										</div>
										
										<div class="col-md-12" style="margin-top:10px;">
											<button type="submit" class="btn btn-primary theme_btn" style="margin-left:0px;">Send Inquiry</button>										
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
				
			</div>
		</div>
	</div>
</section>
