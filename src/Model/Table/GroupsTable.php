<?php
namespace App\Model\Table;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Auth\DefaultPasswordHasher;


class GroupsTable extends Table
{
    
    public function initialize(array $config) {
        
        $this->setTable('events');
		//$this->addBehavior('Timestamp');
    }
    
    
}
?>
