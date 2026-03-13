<?php
namespace App\Model\Table;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class ConventionsTable extends Table{
    
    public function initialize(array $config)
    {

        
        /* $this->belongsTo('ParentCategories', [
            'foreignKey' => 'parent_id',
            'className' => 'Conventions'
        ]); */
    }
    
    public function validationAdd(Validator $validator){
        $validator
        ->notEmpty('name', 'Convention name is required') 
        ->add('name','custom',[
            'rule'=>  function($value, $context){
                $name =  $context['data']['name'];
                $isRecord =  $this->find()->where(['Conventions.name' => $name])->first();
                if($isRecord){
                    return false;
                }else{
                    return true;
                }
            },
            'message'=>'Convention name already exist, please try with other name',
        ])
        
        ;
        return $validator;
    }
    
    public function validationEdit(Validator $validator){
        $validator
        ->notEmpty('name', 'Convention name is required') 
        ->add('name','custom',[
            'rule'=>  function($value, $context){
                $name =  $context['data']['name'];
                $id =  $context['data']['id'];
                $isRecord =  $this->find()->where(['Conventions.name' => $name, 'Conventions.id <>' => $id])->first();
                if($isRecord){
                    return false;
                }else{
                    return true;
                }
            },
            'message'=>'Convention name already exist, please try with other name',
        ])
        ;
        return $validator;
    }
    
    

}
?>