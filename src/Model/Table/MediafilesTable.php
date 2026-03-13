<?php
namespace App\Model\Table;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class MediafilesTable extends Table{
    
    public function initialize(array $config)
    {        
        /* $this->belongsTo('Courses', [
            'className' => 'Courses',
            'foreignKey' => 'course_id',
            'propertyName' => 'courses'
        ]); */
		
		$this->hasMany('Advertisements', [
            'className' => 'Advertisements',
            'foreignKey' => 'ad_id',
            'propertyName' => 'Advertisements'
        ]);
    }
    
    public function validationAdd(Validator $validator){
        $validator
        ->notEmpty('mediafile_name', 'Image name is required') 
        ->add('mediafile_name','custom',[
            'rule'=>  function($value, $context){
                $job_title =  $context['data']['mediafile_name'];
                $isRecord =  $this->find()->where(['Mediafiles.mediafile_name' => $mediafile_name])->first();
                if($isRecord){
                    return false;
                }else{
                    return true;
                }
            },
            'message'=>'Media already exist, please try with other name',
        ])
        
        ;
        return $validator;
    }
    
    public function validationEdit(Validator $validator){
        $validator
        ->notEmpty('job_title', 'Job title is required') 
        ->add('job_title','custom',[
            'rule'=>  function($value, $context){
                $job_title =  $context['data']['job_title'];
                $id =  $context['data']['id'];
                $isRecord =  $this->find()->where(['Coursefiles.job_title' => $job_title, 'Printjobs.id <>' => $id])->first();
                if($isRecord){
                    return false;
                }else{
                    return true;
                }
            },
            'message'=>'Media already exist, please try with other name',
        ])
        ;
        return $validator;
    }
    
   

}
?>