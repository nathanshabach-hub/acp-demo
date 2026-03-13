<?php
namespace App\Model\Table;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class EvaluationareasTable extends Table{
    
    public function initialize(array $config)
    {
		$this->belongsTo('Evaluationforms', [
            'className' => 'Evaluationforms',
            'foreignKey' => 'evaluationform_id',
            'propertyName' => 'Evaluationforms'
        ]);
		
		$this->belongsTo('Evaluationcategories', [
            'className' => 'Evaluationcategories',
            'foreignKey' => 'evaluationcategory_id',
            'propertyName' => 'Evaluationcategories'
        ]);
    }
	
}
?>