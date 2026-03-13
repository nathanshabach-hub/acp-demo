<?php
namespace App\Model\Table;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class EvaluationcategoriesTable extends Table{
    
    public function initialize(array $config)
    {

        
        /* $this->belongsTo('ParentCategories', [
            'foreignKey' => 'parent_id',
            'className' => 'Categories'
        ]); */
    }
    
    public function validationAdd(Validator $validator){
        $validator
        ->notEmpty('name', 'Category name is required') 
        ->add('name','custom',[
            'rule'=>  function($value, $context){
                $name =  $context['data']['name'];
                $isRecord =  $this->find()->where(['Evaluationcategories.name' => $name])->first();
                if($isRecord){
                    return false;
                }else{
                    return true;
                }
            },
            'message'=>'Category name already exist, please try with other name',
        ])
        
        ;
        return $validator;
    }
    
    public function validationEdit(Validator $validator){
        $validator
        ->notEmpty('name', 'Category name is required') 
        ->add('name','custom',[
            'rule'=>  function($value, $context){
                $name =  $context['data']['name'];
                $id =  $context['data']['id'];
                $isRecord =  $this->find()->where(['Evaluationcategories.name' => $name, 'Evaluationcategories.id <>' => $id])->first();
                if($isRecord){
                    return false;
                }else{
                    return true;
                }
            },
            'message'=>'Category name already exist, please try with other name',
        ])
        ;
        return $validator;
    }
    
    

}
?>