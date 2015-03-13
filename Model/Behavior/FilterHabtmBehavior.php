<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2014 Kim Biesbjerg
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
App::uses('ModelBehavior', 'Model');
class FilterHabtmBehavior extends ModelBehavior {

	public $defaultSettings = array(
		'automaticJoins' => true
	);

	public $settings = array();

	public function setup(Model $Model, $settings = array()) {
		$this->settings[$Model->alias] = $settings + $this->defaultSettings;
	}

/**
 * Add required joins to query
 */
	public function beforeFind(Model $Model, $query) {
		if ($this->settings[$Model->alias]['automaticJoins']) {
			$joins = $this->extractJoins($Model, $query['conditions']);
			foreach ($joins as $join) {
				$query['joins'][] = $join;
			}
		}
		return $query;
	}

/**
 * Extract and construct required joins based on conditions
 */
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
					'table' => $this->_modelTable($Model->{$withModel}),
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
					'table' => $this->_modelTable($Model->{$habtmModel}),
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

/**
 * Get Model table including prefix
 */
	protected function _modelTable(Model $Model) {
		$table = $Model->table;
		if (!empty($Model->tablePrefix)) {
			$table = $Model->tablePrefix . $Model->table;
		}
		return $table;
	}

}
