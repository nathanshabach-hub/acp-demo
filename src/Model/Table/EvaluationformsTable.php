<?php
namespace App\Model\Table;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class EvaluationformsTable extends Table{
    
    public function initialize(array $config)
    { 
        /* $this->belongsTo('Evaluationcategories', [
            'className' => 'Evaluationcategories',
            'foreignKey' => 'evaluationcategory_id',
            'propertyName' => 'Evaluationcategories'
        ]); */
    }
    
    public function validationAdd(Validator $validator){
        $validator
        ->notEmpty('name', 'Form name is required') 
        ->add('name','custom',[
            'rule'=>  function($value, $context){
                $name =  $context['data']['name'];
                $isRecord =  $this->find()->where(['Evaluationforms.name' => $name])->first();
                if($isRecord){
                    return false;
                }else{
                    return true;
                }
            },
            'message'=>'Form name already exist, please try with other name',
        ])
        
        ;
        return $validator;
    }
    
    public function validationEdit(Validator $validator){
        $validator
        ->notEmpty('name', 'Form name is required') 
        ->add('name','custom',[
            'rule'=>  function($value, $context){
                $name =  $context['data']['name'];
                $id =  $context['data']['id'];
                $isRecord =  $this->find()->where(['Evaluationforms.name' => $name, 'Evaluationforms.id <>' => $id])->first();
                if($isRecord){
                    return false;
                }else{
                    return true;
                }
            },
            'message'=>'Form name already exist, please try with other name',
        ])
        ;
        return $validator;
    }
    
    

}
?>