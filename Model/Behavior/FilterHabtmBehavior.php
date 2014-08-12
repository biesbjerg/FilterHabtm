<?php
App::uses('ModelBehavior', 'Model');
class FilterHabtmBehavior extends ModelBehavior {

	public $defaultSettings = array(
		'automaticJoins' => true
	);

	public $settings = array();

	public function setup(Model $Model, $settings = array()) {
		$this->settings[$Model->alias] = $settings + $this->defaultSettings;
	}

	public function beforeFind(Model $Model, $query) {
		if ($this->settings[$Model->alias]['automaticJoins']) {
			$joins = $this->extractJoins($Model, $query['conditions']);
			foreach ($joins as $join) {
				$query['joins'][] = $join;
			}
		}
		return $query;
	}

	public function extractJoins(Model $Model, $conditions) {
		if (!is_array($conditions)) {
			return array();
		}

		$joins = array();
		foreach ($conditions as $key => $val) { 
		

			if (is_numeric($key) || in_array($key, array('OR', 'AND'), true)) {
				$joins = array_merge($joins, $this->extractJoins($Model, $val));
				continue;
			}

			list($habtmModel) = pluginSplit($key);
			
			
			$association = false ;
			
			$associations = $Model->getAssociated('hasAndBelongsToMany');
			if ( in_array($habtmModel, $associations, true) ) {
				$association = $Model->hasAndBelongsToMany[$habtmModel];
				list($plugin, $withModel) = pluginSplit($association['with']);				
			}
		
			
			$associations = $Model->getAssociated('hasMany');
			if ( in_array($habtmModel, $associations, true) ) {
				$association = $Model->hasMany[$habtmModel];

				$withModel = $association['className'] ;
			}
						
			if ( ! $association )
				continue;
		
				

			if (!isset($joins[$withModel])) {
				$joins[$withModel] = array(
					'table' => $Model->{$withModel}->useTable,
					'alias' => $Model->{$withModel}->alias,
					'type' => 'INNER',
					'foreignKey' => false,
					'conditions' => array(
						$Model->alias . '.' . $Model->primaryKey . ' = ' . $Model->{$withModel}->alias . '.' . $association['foreignKey']
					)
				);
				if (!empty($association['conditions'])) {
					$joins[$withModel]['conditions'] = Hash::merge($association['conditions'], $joins[$withModel]['conditions']);
				}
			}
			if (!isset($joins[$habtmModel])) {
				$joins[$habtmModel] = array(
					'table' => $Model->{$habtmModel}->table,
					'alias' => $Model->{$habtmModel}->alias,
					'type' => 'INNER',
					'foreignKey' => false,
					'conditions' => array(
						$Model->{$habtmModel}->alias . '.' . $Model->{$habtmModel}->primaryKey . ' = ' . $Model->{$withModel}->alias . '.' . $association['associationForeignKey']
					)
				);
				if (!empty($association['conditions'])) {
					$joins[$habtmModel]['conditions'] = Hash::merge($association['conditions'], $joins[$habtmModel]['conditions']);
				}
			}
		}
		return $joins;
	}

}
