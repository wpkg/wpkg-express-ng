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
include_once('../util.functions.php');

class Package extends AppModel {

	var $name = 'Package';
	var $displayField = 'name';
	var $actsAs = array('Containable', 'ExtendAssociations');
	var $validate = array(
		'id_text' => array(
			'alphanumeric' => array(
				'rule' => array('custom', '/^[\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]{1}[\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}_\-]*$/mu'), // '/^[a-z0-9]+[a-z0-9_\-]*$/i'
				'message' => "The package's id must start with a letter or number and only contain: letters, numbers, underscores, and hyphens.",
				'last' => true
			),
			'uniqueID' => array(
				'rule' => 'isUnique',
				'message' => "That package already exists.",
				'last' => true
			)
		),
		'revision' => array(
			'rule' => array('custom', '/^\d+(\.\d+)*$/'),
			'required' => false,
			'allowEmpty' => true,
			'message' => "The package's revision attribute is not formatted properly. It must start with a digit. If it contains periods, you must have at least one digit after each period.",
			'last' => true
		),
		'priority' => array(
			'rule' => 'int_ok',
			'required' => false,
			'allowEmpty' => true,
			'message' => "The package's priority attribute is not an integer.",
			'last' => true
		),
		'reboot' => array(
			'isinteger' => array(
				'rule' => 'int_ok',
				'message' => "The package's reboot attribute is invalid.",
				'last' => true
			),
			'validrange' => array(
				'rule' => 'checkRange',
				'message' => "The package's reboot attribute is invalid.",
				'last' => true
			)
		),
		'execute' => array(
			'isinteger' => array(
				'rule' => 'int_ok',
				'message' => "The package's execute attribute is invalid.",
				'last' => true
			),
			'validrange' => array(
				'rule' => 'checkRange',
				'message' => "The package's execute attribute is invalid.",
				'last' => true
			)
		),
		'notify' => array(
			'rule' => 'boolean',
			'message' => "The package's notify attribute must be true or false.",
			'last' => true
		)
	);
	
	/*function defaultOnEmpty($data, $fn, $fnparams, $default) {
		if (!is_null($fnparams) && !is_array($fnparams))
			$fnparams = array($fnparams);

		$field = array_shift(array_keys($data));
		if (trim($data[$field]) == "")
			$data[$field] = $this->data['Package'][$field] = $default;

		if (in_array($fn, get_class_methods('Package')))
			$ret = $this->dispatchMethod($fn, array_merge(array($data), array($fnparams)));
		else {
			$Validation =& Validation::getInstance();
			$ret = $Validation->dispatchMethod($fn, array_merge(array($data[$field]), $fnparams));
		}
		return $ret;
	}*/

	function checkRange($data) {
		$field = array_shift(array_keys($data));
		return in_array($data[$field], constsVals('PACKAGE_' . strtoupper($field) . '_'));
	}

	function isUnique($data) {
		$field = array_shift(array_keys($data));
		$conditions = array($field => $data[$field]);
		if (!empty($this->id))
			$conditions["id <>"] = $this->id;
		return ($this->find('count', array('conditions' => $conditions, 'recursive' => -1)) == 0);
	}

	function int_ok($data) {
		$data = array_shift($data);
		return ($data !== true) && ((string)(int) $data) === ((string) $data);
	}
	
	function paginate($conditions, $fields, $order, $limit, $page, $recursive, $extra) {
		if (!array_key_exists('name', $order) && !array_key_exists('Package.name', $order))
			$order['name'] = 'asc';
		$parameters = compact('conditions', 'fields', 'order', 'limit', 'page');
		if ($recursive != $this->recursive)
			$parameters['recursive'] = $recursive;
		return $this->find('all', array_merge($parameters, $extra));
	}

	//The Associations below have been created with all possible keys, those that are not needed can be removed
	var $hasMany = array(
			'PackageAction' => array('className' => 'PackageAction',
								'foreignKey' => 'package_id',
								'dependent' => true,
								'conditions' => '',
								'fields' => '',
								'order' => ''
			),
			'PackageCheck' => array('className' => 'PackageCheck',
								'foreignKey' => 'package_id',
								'dependent' => true,
								'conditions' => '',
								'fields' => '',
								'order' => ''
			)
	);

	var $hasAndBelongsToMany = array(
			'PackageDependency' => array('className' => 'Package',
						'joinTable' => 'packages_packages',
						'foreignKey' => 'package_id',
						'associationForeignKey' => 'dependency_id',
						'unique' => true,
						'conditions' => '',
						'fields' => '',
						'order' => 'PackageDependency.name ASC'
			),
			'Profile' => array('className' => 'Profile',
						'joinTable' => 'packages_profiles',
						'foreignKey' => 'package_id',
						'associationForeignKey' => 'profile_id',
						'unique' => true,
						'conditions' => '',
						'fields' => '',
						'order' => 'Profile.id_text ASC'
			)
	);

	function beforeDelete($cascade = true) {
		$this->bindModel(array('hasMany' => array(
			'Variable' => array('className' => 'Variable',
								'foreignKey' => 'ref_id',
								'dependent' => true,
								'conditions' => array('ref_type' => VARIABLE_TYPE_PACKAGE),
								'fields' => array('name', 'value')
			)
		)));
		return true;
	}

	function beforeFind($queryData) {
		if (isset($queryData['order'][0]) && is_array($queryData['order'][0]) && isset($queryData['order'][0]['Package.revision'])) {
			$queryData['order'][0]['Package.revision'] = "+0 " . $queryData['order'][0]['Package.revision'];
		}
		return $queryData;
	}
	
	function beforeSave() {
		if (isset($this->data['Package']['revision'])) {
			if (trim($this->data['Package']['revision']) == "")
				$this->data['Package']['revision'] = 0;
		}
		if (isset($this->data['Package']['priority'])) {
			if (trim($this->data['Package']['priority']) == "")
				$this->data['Package']['priority'] = 0;
		}
		return true;
	}

	function getAll() {
		$this->recursive = -1;
		$fields = array('Package.id', 'Package.name', 'Package.id_text');
		$order = 'Package.name ASC';
		
		return $this->find('all', array('fields' => $fields, 'order' => $order));
	}

	function get($id, $isEdit = false) {
		$this->recursive = 0;
		$contain = array('PackageDependency');

		// Only retrieve the essential data for creating a Dependency hyperlink
		$this->hasAndBelongsToMany['PackageDependency']['fields'] = array('PackageDependency.id', 'PackageDependency.id_text', 'PackageDependency.name');

		if (!$isEdit) {
			$contain[] = 'Profile';
			$contain[] = 'DependedOnBy';

			// Temporarily bind an additional new 'reverse' HABTM relationship, which gives us which packages depend on _this_ package
			$this->bindModel(array('hasAndBelongsToMany' => array(
						    'DependedOnBy' => array(
							'className' => 'Package',
							'joinTable' => 'packages_packages',
							'foreignKey' => 'dependency_id',
							'associationForeignKey' => 'package_id',
							'fields' => array('DependedOnBy.id', 'DependedOnBy.id_text', 'DependedOnBy.name'), 
							'order' => 'DependedOnBy.name ASC'
						     )
					       )
					)
			);
		}

		return $this->find('first', array('conditions' => array('Package.id' => $id), 'contain' => $contain));
	}

	function getDependedOnBy($id) {
		if ($id == null || !ctype_digit($id))
			return false;
		
		return $this->find('all', array('conditions' => array('PackagePackage.dependency_id' => $id), 'fields' => array('Package.id', 'Package.id_text'), 'order' => 'Package.id_text', 'joins' => array(array('table' => 'packages_packages', 'alias' => 'PackagePackage', 'foreignKey' => false, 'conditions' => 'PackagePackage.package_id = Package.id')), 'recursive' => -1));
	}

	function getAllBut($id) {
		$conditions = array('PackageDependency.id <>' => $id);
		$fields = array('PackageDependency.id', 'PackageDependency.name');
		$order = 'PackageDependency.name ASC';
		
		return $this->PackageDependency->find('list', array('fields' => $fields, 'order' => $order, 'conditions' => $conditions));
	}

	function getAllForXML($id = null, $getDisabled = false) {
		$this->recursive = 2;
		
		$this->bindModel(array('hasMany' => array(
			'Variable' => array('className' => 'Variable',
								'foreignKey' => 'ref_id',
								'dependent' => true,
								'conditions' => array('ref_type' => VARIABLE_TYPE_PACKAGE),
								'fields' => array('name', 'value')
			)
		)));

		$this->hasMany['PackageAction']['order'] = array('PackageAction.type', 'PackageAction.position');
		$this->hasMany['PackageCheck']['order'] = 'PackageCheck.lft ASC';

		// TODO: look into setting the default fields for (some of) the below associations/models using the values shown
		$this->hasMany['PackageAction']['fields'] = array('PackageAction.type',
								  'PackageAction.command',
								  'PackageAction.timeout',
								  'PackageAction.workdir',
								  'PackageAction.id'
		);
		$this->hasMany['PackageCheck']['fields'] = array('PackageCheck.type',
								 'PackageCheck.condition',
								 'PackageCheck.path',
								 'PackageCheck.value',
								 'PackageCheck.id',
								 'PackageCheck.parent_id'
		);
		$this->PackageCheck->hasMany['Childcheck']['fields'] = array('Childcheck.type',
									     'Childcheck.condition',
									     'Childcheck.path',
									     'Childcheck.value',
									     'Childcheck.id',
									     'Childcheck.parent_id'
		);
		$this->PackageCheck->belongsTo['Parentcheck']['fields'] = array('Parentcheck.type',
										'Parentcheck.condition',
										'Parentcheck.path',
										'Parentcheck.value',
										'Parentcheck.id',
										'Parentcheck.parent_id'
		);
		$this->hasAndBelongsToMany['PackageDependency']['fields'] = array('PackageDependency.id_text');

		$conditions = ($getDisabled ? array() : array('Package.enabled' => true));
		$fields = array('Package.enabled',
				'Package.name',
				'Package.revision',
				'Package.priority',
				'Package.execute',
				'Package.notify',
				'Package.id_text',
				'Package.reboot'
		);
		$order = array('Package.priority' => 'desc', 'Package.name' => 'asc');
		$contain = array('PackageAction', 'PackageAction.ExitCode', 'Variable', 'PackageCheck', 'PackageCheck.Childcheck', 'PackageCheck.Parentcheck', 'PackageDependency');

		if ($id)
			return $this->find('first', array('conditions' => array('Package.id' => $id) + $conditions, 'fields' => $fields, 'order' => $order, 'contain' => $contain));
		else
			return $this->find('all', array('conditions' => $conditions, 'fields' => $fields, 'order' => $order, 'contain' => $contain));
	}

}
?>
