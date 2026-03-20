<?php
use Cake\ORM\TableRegistry;
$this->Crstudentevents = TableRegistry::getTableLocator()->get('Crstudentevents');
$this->Resultpositions = TableRegistry::getTableLocator()->get('Resultpositions');

$this->Crstudentevents = TableRegistry::getTableLocator()->get('Crstudentevents');
$this->Judgeevaluations = TableRegistry::getTableLocator()->get('Judgeevaluations');
$this->Judgeevaluationmarks = TableRegistry::getTableLocator()->get('Judgeevaluationmarks');
$this->Evaluationquestions = TableRegistry::getTableLocator()->get('Evaluationquestions');
$this->Eventsubmissions = TableRegistry::getTableLocator()->get('Eventsubmissions');
?>
<script type="text/javascript">
<!--
window.print();
//-->
</script>
<style>
@media print {
	.page-break {
		page-break-after: always;
		break-after: page;
	}
}
.pinyon-script-regular {
	font-family: "Pinyon Script", cursive;
	font-weight: 400;
	font-style: normal;
	}
@page {
	size: A4 landscape;
	margin:0cm;
}
</style>

<!-- create first page with some details -->
<?php echo $this->element('Judgeevaluations/firstpage'); ?>
<div class="page-break spacer-after-break"></br></div>

<?php echo $this->element('Judgeevaluations/indrespackprint'); ?>
<div class="page-break spacer-after-break"></br></div>

<?php echo $this->element('Judgeevaluations/participationcertificatepdf'); ?>
<!--<div class="page-break spacer-after-break"></br></div>-->

<?php echo $this->element('Judgeevaluations/placecertificatepdf'); ?>
<!--<div class="page-break spacer-after-break"></br></div>-->

<?php echo $this->element('Judgeevaluations/evaluationformpdf'); ?>







