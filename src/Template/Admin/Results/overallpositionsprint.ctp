<?php echo $this->Html->script('ajax-pagging.js'); ?>
<div class="content-wrapper">
     

    <section class="content">
        <div class="box box-info">
            <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
             
            <div class="m_content" id="listID">
                <?php echo $this->element("Admin/Results/overallpositions"); ?>
            </div>
            
        </div>
    </section>
</div>
<script type="text/javascript">
<!--
window.print();
//-->
</script>