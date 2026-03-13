<?php
namespace App\Model\Table;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class TransactionstudentsTable extends Table{
    
    public function initialize(array $config)
    {
		
		// this is related to Student
		$this->belongsTo('Users', [
            'className' => 'Users',
            'foreignKey' => 'student_id',
            'propertyName' => 'Users'
        ]);
    }
    
     
    
    

}
?>