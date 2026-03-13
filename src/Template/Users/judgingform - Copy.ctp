<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
			<div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
			<h2 class="mt-3">Judging Form</h2>
			<!-- dashboard-section-1 start-->
			<div class="dasboard-section">
				<div class="dashboard-text">
					<h2>Judging Form</h2>
					<div class="classform-container">
						<h2>JUDGES FORM - SCIENCE EXHIBIT</h2>
						<div class="checkboxrow fwbold">
							<div class="singlechecknox"><input type="checkbox" id="OPEN" name="OPEN" value="OPEN">
								<label for="OPEN"> OPEN</label><br>
							</div>
							<div class="singlechecknox"><input type="checkbox" id="Collection" name="Collection" value="Collection">
								<label for="Collection"> U/16 - Collection on only</label><br>
							</div>
							<div class="centertext">(Please <i class="fa fa-check"></i> the appropriate box)</div>
						</div>
						<div class="checkboxrow">
							<div class="singlechecknox"><input type="checkbox" id="Collectionn" name="Collectionn" value="Collectionn">
								<label for="vehicle1"> Collection</label><br>
							</div>
							<div class="singlechecknox"><input type="checkbox" id="Engineering" name="Engineering" value="Engineering">
								<label for="vehicle1"> Engineering</label><br>
							</div>
							<div class="singlechecknox"><input type="checkbox" id="Research" name="Research" value="Research">
								<label for="vehicle1"> Research</label><br>
							</div>
							<div class="singlechecknox"><input type="checkbox" id="Theoretical" name="Theoretical" value="Theoretical">
								<label for="vehicle1"> Theoretical</label><br>
							</div>
						</div>
						<div class="tableheader">
							<div class="singlerow">
								<div class="namecolom">Name : </div>
								<div class="inputfield"><input type="text" placeholder=""></div>
								<div class="dobcolom">DOB : </div>
								<div class="inputfield smallinpout"><input type="text" placeholder=""></div>
							</div>
							<div class="singlerow">
								<div class="namecolom">Name : </div>
								<div class="inputfield"><input type="text" placeholder=""></div>
								<div class="dobcolom">DOB : </div>
								<div class="inputfield smallinpout"><input type="text" placeholder=""></div>
							</div>
							<div class="singlerow">
								<div class="namecolom">School : </div>
								<div class="inputfield"><input type="text" placeholder=""></div>
								<div class="dobcolom">Cust Code : </div>
								<div class="inputfield smallinpout"><input type="text" placeholder=""></div>
							</div>
						</div>
						<p class="simpletext">(JUDGES! Please remember that items MUST be consistent with a Biblical Worldview)</p>
						<div class="EVALUATIONtable">
							<div class="headerparttable">
								<div class="seventycolom">AREAS OF EVALUATION</div>
								<div class="thirtyycolom">
									<div class="fullcom">POINTS</div>
									<div class="halfcom borderright">POSSIBLE</div>
									<div class="halfcom">AWARDED</div>
								</div>
							</div>
							<div class="subjectcolom">
								<div class="fulcolm">
									I. Choice of Subject
								</div>
								<div class="wraprow">
									<div class="seventycolom">A. There is a defi nite scientific purpose or theme</div>
									<div class="thirtyycolom">
										<div class="halfcom">10</div>
										<div class="halfcom"><input type="text" placeholder=""></div>
									</div>
								</div>
								<div class="wraprow lastrowborderhide">
									<div class="seventycolom">B. Shows creativity vity and originality</div>
									<div class="thirtyycolom">
										<div class="halfcom borderhide">10</div>
										<div class="halfcom borderhide"><input type="text" placeholder=""></div>
									</div>
								</div>
							</div>
							<div class="subjectcolom">
								<div class="fulcolm">
									II. scientific Thought
								</div>
								<div class="wraprow">
									<div class="seventycolom">A. scientific facts or principles are displayed accurately</div>
									<div class="thirtyycolom">
										<div class="halfcom">15</div>
										<div class="halfcom"><input type="text" placeholder=""></div>
									</div>
								</div>
								<div class="wraprow ">
									<div class="seventycolom">B. Exhibit clearly agrees with and illustrates what is discussed in the
										accompanying paper/science report
									</div>
									<div class="thirtyycolom minheightt">
										<div class="halfcom">10</div>
										<div class="halfcom"><input type="text" placeholder=""></div>
									</div>
								</div>
								<div class="wraprow lastrowborderhide">
									<div class="seventycolom">c. Degree of difficulty</div>
									<div class="thirtyycolom">
										<div class="halfcom borderhide">10</div>
										<div class="halfcom borderhide"><input type="text" placeholder=""></div>
									</div>
								</div>
							</div>
							<div class="subjectcolom">
								<div class="fulcolm">
									III. Workmanship
								</div>
								<div class="wraprow">
									<div class="seventycolom">A. Neatness, general appearance</div>
									<div class="thirtyycolom">
										<div class="halfcom">5</div>
										<div class="halfcom"><input type="text" placeholder=""></div>
									</div>
								</div>
								<div class="wraprow ">
									<div class="seventycolom">B. Shows evidence that materials have been used appropriately</div>
									<div class="thirtyycolom">
										<div class="halfcom">5</div>
										<div class="halfcom"><input type="text" placeholder=""></div>
									</div>
								</div>
								<div class="wraprow lastrowborderhide">
									<div class="seventycolom">c. Shows evidence that tools/construction on have been used
										appropriately
									</div>
									<div class="thirtyycolom minheightt">
										<div class="halfcom ">5</div>
										<div class="halfcom "><input type="text" placeholder=""></div>
									</div>
								</div>
								<div class="wraprow lastrowborderhide">
									<div class="seventycolom">D. Design and layout is creation ve and logical</div>
									<div class="thirtyycolom">
										<div class="halfcom borderhide">10</div>
										<div class="halfcom borderhide"><input type="text" placeholder=""></div>
									</div>
								</div>
							</div>
							<div class="subjectcolom">
								<div class="fulcolm">
									IV. Thoroughness
								</div>
								<div class="wraprow">
									<div class="seventycolom">A. Information on is useful and conclusive</div>
									<div class="thirtyycolom">
										<div class="halfcom">5</div>
										<div class="halfcom"><input type="text" placeholder=""></div>
									</div>
								</div>
								<div class="wraprow ">
									<div class="seventycolom">B. Bibliography and references correctly included</div>
									<div class="thirtyycolom">
										<div class="halfcom borderhide">10</div>
										<div class="halfcom borderhide"><input type="text" placeholder=""></div>
									</div>
								</div>
							</div>
							<div class="subjectcolom">
								<div class="fulcolm">
									V. Clarity
								</div>
								<div class="wraprow">
									<div class="seventycolom border-bottom">A. Exhibit is clearly and easily understood</div>
									<div class="thirtyycolom">
										<div class="halfcom ">5</div>
										<div class="halfcom"><input type="text" placeholder=""></div>
									</div>
								</div>
							</div>
							<div class="totals">
								<div class="seventycolom">TOTAL POINTS</div>
								<div class="thirtyycolom">
									<div class="halfcom borderhide">100</div>
									<div class="halfcom borderhide">0</div>
								</div>
							</div>
							<div class="comments">
								<textarea placeholder="COMMENT:"></textarea>
							</div>
							<div class="footerpart">
								<div class="judgename">
									Judge’s <br>Name:
								</div>
								<div class="judgename">
									Judge’s <br>Signature:
								</div>
							</div>
						</div>
					</div>
					<!-- enndd-->
				</div>
			</div>
			<!-- dashboard-section-1 end-->
		</main>
	</div>
</div>