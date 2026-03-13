<?php
namespace App\Model\Table;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class EventsubmissionsTable extends Table{
    
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
		
		$this->belongsTo('Events', [
            'className' => 'Events',
            'foreignKey' => 'event_id',
            'propertyName' => 'Events'
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
		
		$this->belongsTo('Judge', [
            'className' => 'Users',
            'foreignKey' => 'guideline_breach_by_judge_id',
            'propertyName' => 'Judge'
        ]);
		
		$this->belongsTo('Judgecommand', [
            'className' => 'Users',
            'foreignKey' => 'mark_command_by_judge_id',
            'propertyName' => 'Judgecommand'
        ]);
    }
    
     
    
    

}
?>