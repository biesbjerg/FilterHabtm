<?php
App::uses('ModelBehavior', 'Model/Behavior');
class FilterHabtmBehavior extends ModelBehavior {

	public $defaultSettings = array(
		'automaticJoins' => true
	);

	public $settings = array();

	public function setup(Model $Model, $settings = array()) {
		$this->settings[$Model->alias] = $this->defaultSettings + $settings;
	}

	public function beforeFind(Model $Model, $query) {
		if ($this->settings[$Model->alias]['automaticJoins']) {
			foreach ((array)$query['conditions'] as $key => $val) {
				list($modelName, $field) = $this->_split($key);
				if (isset($Model->hasAndBelongsToMany[$modelName])) {
					$query['filter'][$modelName][$modelName . '.' . $field] = $val;
					unset($query['conditions'][$key]);
				}
			}
		}
		if (empty($query['filter'])) {
			return true;
		}
		foreach ($query['filter'] as $modelName => $conditions) {
			if (!isset($Model->hasAndBelongsToMany[$modelName])) {
				throw new Exception(sprintf('Model "%s" does not have an HABTM relation to "%s"', $Model->alias, $modelName));
			}

			$association = $Model->hasAndBelongsToMany[$modelName];
			list(, $with) = $this->_split($association['with']);

			$query['joins'][] = array(
				'table' => $Model->{$with}->useTable,
				'alias' => $Model->{$with}->alias,
				'type' => 'INNER',
				'foreignKey' => false,
				'conditions' => array(
					$Model->alias . '.' . $Model->primaryKey . ' = ' . $with . '.' . $association['foreignKey']
				)
			);
			$query['joins'][] = array(
				'table' => $Model->{$modelName}->table,
				'alias' => $Model->{$modelName}->alias,
				'type' => 'INNER',
				'foreignKey' => false,
				'conditions' => array(
					$Model->{$modelName}->alias . '.' . $Model->{$modelName}->primaryKey . ' = ' . $with . '.' . $association['associationForeignKey']
				)
			);

			if (!empty($conditions)) {
				$query['conditions'] = Hash::merge($query['conditions'], $conditions);
			}
		}
		unset($query['filter']);
		return $query;
	}

	protected function _split($name) {
		return pluginSplit($name);
	}

}
