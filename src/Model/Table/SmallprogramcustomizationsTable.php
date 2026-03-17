<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class SmallprogramcustomizationsTable extends Table {

	public function initialize(array $config) {
		$this->table('smallprogramcustomizations');
		$this->primaryKey('id');
		$this->displayField('report_title');
		$this->addBehavior('Timestamp');

		$this->belongsTo('Conventionseasons', [
			'className' => 'Conventionseasons',
			'foreignKey' => 'conventionseasons_id',
			'propertyName' => 'Conventionseasons'
		]);
	}

	public function validationDefault(Validator $validator) {
		$validator
			->allowEmpty('report_title')
			->allowEmpty('report_subtitle')
			->allowEmpty('intro_note')
			->allowEmpty('footer_note')
			->allowEmpty('morning_label')
			->allowEmpty('afternoon_label')
			->allowEmpty('lunch_label')
			->allowEmpty('logo_path')
			->allowEmpty('logo_alt_text')
			->allowEmpty('custom_css');

		$validator->add('primary_color', 'validColor', [
			'rule' => function ($value) {
				return $this->isValidHexColor($value);
			},
			'message' => 'Use a hex color like #1a3a5c.'
		]);

		$validator->add('secondary_color', 'validColor', [
			'rule' => function ($value) {
				return $this->isValidHexColor($value);
			},
			'message' => 'Use a hex color like #2e6da4.'
		]);

		$validator->add('table_header_color', 'validColor', [
			'rule' => function ($value) {
				return $this->isValidHexColor($value);
			},
			'message' => 'Use a hex color like #ddeeff.'
		]);

		return $validator;
	}

	private function isValidHexColor($value) {
		if ($value === null || $value === '') {
			return true;
		}

		return (bool)preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $value);
	}
}

?>