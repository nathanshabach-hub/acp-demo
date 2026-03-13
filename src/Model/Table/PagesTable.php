<?php
namespace App\Model\Table;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class PagesTable extends Table{
    
    /*
    @description: This function used to validate data on edit exsting records.
    @author: info@demo.com
    @version: 1.0.0
    @since: 2016-12-30
    */ 
    public function validationEdit(Validator $validator){
        $validator
        ->notEmpty('static_page_title', 'Page title is required') 
        ->notEmpty('static_page_description', 'Page description is required');
        return $validator;
    }

}
?>