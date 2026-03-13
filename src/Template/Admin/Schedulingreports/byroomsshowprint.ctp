<style>
  @media print {
    .page-break-after {
      page-break-after: always;
    }
  }
  .topn {display:none;}
</style>
<script type="text/javascript">
<!--
window.print();
//-->
</script>

<h2 style="padding:20px 0px 20px 5px;"><?php echo $roomD->room_name; ?></h2> 

<div class="m_content" id="listID">
	<?php echo $this->element("Admin/Schedulingreports/byroomsshow"); ?>
</div>