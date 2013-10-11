<?php
/*
 * wpkgExpress : A web-based frontend to wpkg
 * Copyright 2009 Brian White
 *
 * This file is part of wpkgExpress.
 *
 * wpkgExpress is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * wpkgExpress is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with wpkgExpress.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
?>
<?php
include_once('../wpkg_constants.php');

class Profile extends AppModel {

	var $name = 'Profile';
	var $displayField = 'id_text';
	var $actsAs = 'ExtendAssociations';
	var $validate = array(
		'id_text' => array(
			'alphanumeric' => array(
				'rule' => array('custom', '/^[\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]{1}[\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}_\-]*$/mu'), //array('custom', '/^[a-z0-9]+[a-z0-9_\-]*$/i'),
				'message' => "The profile's id must start with a letter or number and only contain: letters, numbers, underscores, and hyphens.",
				'last' => true
			),
			'uniqueID' => array(
				'rule' => array('isUnique'),
				'message' => "That profile already exists.",
				'last' => true
			)
		)
	);

	function isUnique($data) {
		$field = array_shift(array_keys($data));
		$conditions = array($field => $data[$field]);
		if (!empty($this->id))
			$conditions["id <>"] = $this->id;
		return ($this->find('count', array('conditions' => $conditions, 'recursive' => -1)) == 0);
	}

	//The Associations below have been created with all possible keys, those that are not needed can be removed
	var $hasAndBelongsToMany = array(
			'Package' => array('className' => 'Package',
						'joinTable' => 'packages_profiles',
						'foreignKey' => 'profile_id',
						'associationForeignKey' => 'package_id',
						'unique' => true,
						'conditions' => '',
						'fields' => array('Package.id', 'Package.name', 'Package.id_text'),
						'order' => 'Package.name ASC'
			),
			'ProfileDependency' => array('className' => 'Profile',
						'joinTable' => 'profiles_profiles',
						'foreignKey' => 'profile_id',
						'associationForeignKey' => 'dependency_id',
						'unique' => true,
						'conditions' => '',
						'fields' => array('ProfileDependency.id', 'ProfileDependency.id_text'),
						'order' => 'ProfileDependency.id_text ASC'
			)
	);

	function get($id, $inclVariables = false, $fields = array(), $unbind = array()) {
		if ($fields == null)
			$fields = array();
		if ($unbind == null)
			$unbind = array();

		$this->recursive = 1;

		if (!empty($unbind))
			$this->unbindModel($unbind, false);

		if (!$inclVariables)
			$this->unbindModel(array('hasMany' => array('Variable')), false);

		return $this->read((empty($fields) ? null : $fields), $id);
	}
	
	function getAssocPackages($profId) {
		$this->unbindAll(array('hasAndBelongsToMany' => array('Package')));
		$this->recursive = 1;
		return $this->find('first', array('conditions' => array('Profile.id' => $profId), 'fields' => array('Profile.id')));
	}

	function getAllForXML($id = null, $getDisabled = false) {
		$this->recursive = 1;
		
		$this->bindModel(array('hasMany' => array(
			'Variable' => array('className' => 'Variable',
								'foreignKey' => 'ref_id',
								'dependent' => true,
								'conditions' => array('ref_type' => VARIABLE_TYPE_PROFILE),
								'fields' => array('name', 'value')
			)
		)));
		
		$this->hasAndBelongsToMany['Package']['conditions'] = array('Package.enabled' => true);
		$this->hasAndBelongsToMany['Package']['fields'] = array('Package.id', 'Package.id_text');

		$conditions = ($getDisabled ? array() : array('Profile.enabled' => true));
		$fields = array('Profile.id', 'Profile.id_text', 'Profile.enabled');
		$order = array('Profile.id_text' => 'asc');

		if ($id)
			return $this->find('first', array('conditions' => array('Profile.id' => $id) + $conditions, 'fields' => $fields, 'order' => $order));
		else
			return $this->find('all', array('conditions' => $conditions, 'fields' => $fields, 'order' => $order));
	}

	function getList($conditions = array(), $isDepend = false) {
		if ($conditions == null)
			$conditions = array();

		$this->recursive = -1;
		$default_conditions = ($isDepend ? array('ProfileDependency.enabled' => true) : array('Profile.enabled' => true));
		$conditions = array_merge($default_conditions, $conditions);
		$fields = ($isDepend ? array('ProfileDependency.id', 'ProfileDependency.id_text') : array('Profile.id', 'Profile.id_text'));
		$order = ($isDepend ? 'ProfileDependency.id_text ASC' : 'Profile.id_text ASC');

		return $this->find('list', array('conditions' => $conditions, 'fields' => $fields, 'order' => $order));
	}
}
?>