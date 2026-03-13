<?php
namespace App\Model\Table;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class DivisionsTable extends Table{
    
    public function initialize(array $config)
    {
		$this->belongsTo('Eventcategories', [
            'className' => 'Eventcategories',
            'foreignKey' => 'eventcategory_id',
            'propertyName' => 'Eventcategories'
        ]);
    }
    
    public function validationAdd(Validator $validator){
        $validator
        ->notEmpty('name', 'Division name is required') 
        ->add('name','custom',[
            'rule'=>  function($value, $context){
                $name =  $context['data']['name'];
                $isRecord =  $this->find()->where(['Divisions.name' => $name])->first();
                if($isRecord){
                    return false;
                }else{
                    return true;
                }
            },
            'message'=>'Division name already exist, please try with other name',
        ])
        
        ;
        return $validator;
    }
    
    public function validationEdit(Validator $validator){
        $validator
        ->notEmpty('name', 'Division name is required') 
        ->add('name','custom',[
            'rule'=>  function($value, $context){
                $name =  $context['data']['name'];
                $id =  $context['data']['id'];
                $isRecord =  $this->find()->where(['Divisions.name' => $name, 'Divisions.id <>' => $id])->first();
                if($isRecord){
                    return false;
                }else{
                    return true;
                }
            },
            'message'=>'Division name already exist, please try with other name',
        ])
        ;
        return $validator;
    }
    
    

}
?>