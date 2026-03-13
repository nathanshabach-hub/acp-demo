<?php
namespace App\Model\Table;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class EventsTable extends Table{
    
    public function initialize(array $config)
    {
		$this->belongsTo('Divisions', [
            'className' => 'Divisions',
            'foreignKey' => 'division_id',
            'propertyName' => 'Divisions'
        ]);
    }
    
    public function validationAdd(Validator $validator){
        $validator
        ->notEmpty('event_id_number', 'Event ID number is required.') 
        ->add('event_id_number','custom',[
            'rule'=>  function($value, $context){
                $event_id_number =  $context['data']['event_id_number'];
                $isRecord =  $this->find()->where(['Events.event_id_number' => $event_id_number])->first();
                if($isRecord){
                    return false;
                }else{
                    return true;
                }
            },
            'message'=>'Event Id number already exist, please try with other number.',
        ])
        
        ;
        return $validator;
    }
    
    public function validationEdit(Validator $validator){
        $validator
        ->notEmpty('event_id_number', 'Event ID number is required.') 
        ->add('event_id_number','custom',[
            'rule'=>  function($value, $context){
                $event_id_number =  $context['data']['event_id_number'];
                $id =  $context['data']['id'];
                $isRecord =  $this->find()->where(['Events.event_id_number' => $event_id_number, 'Events.id <>' => $id])->first();
                if($isRecord){
                    return false;
                }else{
                    return true;
                }
            },
            'message'=>'Event ID number already exist, please try with other number.',
        ])
        ;
        return $validator;
    }
    
    

}
?>