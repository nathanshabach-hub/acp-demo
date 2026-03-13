<?php
namespace App\Model\Table;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class EvaluationquestionsTable extends Table{
    
    public function initialize(array $config)
    { 
        $this->belongsTo('Evaluationcategories', [
            'className' => 'Evaluationcategories',
            'foreignKey' => 'evaluationcategory_id',
            'propertyName' => 'Evaluationcategories'
        ]);
    }
    
    public function validationAdd(Validator $validator){
        $validator
        ->notEmpty('question', 'Question is required') 
        ->add('question','custom',[
            'rule'=>  function($value, $context){
                $question =  $context['data']['question'];
                $evaluationcategory_id =  $context['data']['evaluationcategory_id'];
                $max_points =  $context['data']['max_points'];
                $isRecord =  $this->find()->where(['Evaluationquestions.question' => $question,'Evaluationquestions.evaluationcategory_id' => $evaluationcategory_id,'Evaluationquestions.max_points' => $max_points])->first();
                if($isRecord){
                    return false;
                }else{
                    return true;
                }
            },
            'message'=>'Question already exist in same category and same points, please try with other question',
        ])
        
        ;
        return $validator;
    }
    
    public function validationEdit(Validator $validator){
        $validator
        ->notEmpty('question', 'Question is required') 
        ->add('question','custom',[
            'rule'=>  function($value, $context){
                $question =  $context['data']['question'];
				$evaluationcategory_id =  $context['data']['evaluationcategory_id'];
				$max_points =  $context['data']['max_points'];
                $id =  $context['data']['id'];
                $isRecord =  $this->find()->where(['Evaluationquestions.question' => $question, 'Evaluationquestions.evaluationcategory_id' => $evaluationcategory_id, 'Evaluationquestions.max_points' => $max_points, 'Evaluationquestions.id <>' => $id])->first();
                if($isRecord){
                    return false;
                }else{
                    return true;
                }
            },
            'message'=>'Question already exist in same category and same points, please try with other question',
        ])
        ;
        return $validator;
    }
    
    

}
?>