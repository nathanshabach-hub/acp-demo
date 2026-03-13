<?php
namespace App\Model\Table;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class HearteventsTable extends Table{
    
    public function initialize(array $config)
    {
		$this->belongsTo('Conventionregistrations', [
            'className' => 'Conventionregistrations',
            'foreignKey' => 'conventionregistration_id',
            'propertyName' => 'Conventionregistrations'
        ]);
		
		$this->belongsTo('Conventions', [
            'className' => 'Conventions',
            'foreignKey' => 'convention_id',
            'propertyName' => 'Conventions'
        ]);
		
		$this->belongsTo('Users', [
            'className' => 'Users',
            'foreignKey' => 'user_id',
            'propertyName' => 'Users'
        ]);
		
		$this->belongsTo('Seasons', [
            'className' => 'Seasons',
            'foreignKey' => 'season_id',
            'propertyName' => 'Seasons'
        ]);
		
		$this->belongsTo('Students', [
            'className' => 'Users',
            'foreignKey' => 'student_id',
            'propertyName' => 'Students'
        ]);
		
		$this->belongsTo('Uploadeduser', [
            'className' => 'Users',
            'foreignKey' => 'uploaded_by_user_id',
            'propertyName' => 'Uploadeduser'
        ]);
    }
    
     
    
    

}
?>