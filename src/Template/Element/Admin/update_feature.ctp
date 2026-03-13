<?php
if($status=='1'){
	echo $this->Html->link('<button class="btn btn-success btn-xs">Featured</button>', $action, ["escape"=>false,'title' => 'Remove From Feature Deal']);
}else{
	echo $this->Html->link('<button class="btn btn-danger btn-xs">Feature</button>', $action, ["escape"=>false,'title' => 'Make As Feature Deal']);
}
?>