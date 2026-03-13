<?php
namespace App\Model\Table;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class TransactionteachersTable extends Table{
    
    public function initialize(array $config)
    {
		
		// this is related to Student
		$this->belongsTo('Users', [
            'className' => 'Users',
            'foreignKey' => 'teacher_id',
            'propertyName' => 'Users'
        ]);
    }
    
     
    
    

}
?>