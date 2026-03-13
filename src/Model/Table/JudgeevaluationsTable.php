<?php
namespace App\Model\Table;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class JudgeevaluationsTable extends Table{
    
    public function initialize(array $config)
    {
		$this->belongsTo('Eventsubmissions', [
            'className' => 'Eventsubmissions',
            'foreignKey' => 'eventsubmission_id',
            'propertyName' => 'Eventsubmissions'
        ]);
		
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
		
		$this->belongsTo('Schools', [
            'className' => 'Users',
            'foreignKey' => 'user_id',
            'propertyName' => 'Schools'
        ]);
		
		$this->belongsTo('Judge', [
            'className' => 'Users',
            'foreignKey' => 'uploaded_by_user_id',
            'propertyName' => 'Judge'
        ]);
		
		$this->hasMany('Judgeevaluationmarks', [
            'className' => 'Judgeevaluationmarks',
            'foreignKey' => 'judgeevaluation_id',
            'propertyName' => 'Judgeevaluationmarks'
        ]);
		
		/* $this->belongsTo('Conventions', [
            'className' => 'Conventions',
            'foreignKey' => 'convention_id',
            'propertyName' => 'Conventions'
        ]); */
    }
}
?>