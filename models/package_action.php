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
class PackageAction extends AppModel {

	var $name = 'PackageAction';
	var $actsAs = array('Ordered' => array(
					'field' => 'position',
					'foreign_key' => 'package_id'
				  	)
	);
	var $validate = array(
		'command' => array(
			'rule' => 'notEmpty',
			'required' => true,
			'allowEmpty' => false,
			'message' => "The package action command attribute is invalid."
		),
		'type' => array(
			'rule' => 'checkRange',
			'required' => true,
			'allowEmpty' => false,
			'message' => "The package action type attribute is invalid."
		),
		'timeout' => array(
			'rule' => 'int_ok',
			'required' => false,
			'allowEmpty' => true,
			'message' => "The package action timeout attribute is invalid."
		)
		
	);

	function checkRange($data) {
		$field = array_shift(array_keys($data));
		return in_array($data[$field], constsVals('ACTION_' . strtoupper($field) . '_'));
	}

	function int_ok($data) {
		$data = array_shift($data);
		return ($data !== true) && ((string)(int)$data) === ((string)$data);
	}

	//The Associations below have been created with all possible keys, those that are not needed can be removed
	var $belongsTo = array(
			'Package' => array('className' => 'Package',
								'foreignKey' => 'package_id',
								'conditions' => '',
								'fields' => '',
								'order' => ''
			)
	);

	var $hasMany = array(
			'ExitCode' => array('className' => 'ExitCode',
								'foreignKey' => 'package_action_id',
								'dependent' => true,
								'conditions' => '',
								'fields' => '',
								'order' => ''
			)
	);
	
	function getPackageID($pkgCheckID) {
		$this->recursive = -1;
		return $this->field('package_id', array('PackageAction.id' => $pkgCheckID));
	}

	function getAllForPackage($id) {
		$this->recursive = -1;
		$conditions = array('PackageAction.package_id' => $id);
		$fields = array('PackageAction.id', 'PackageAction.type', 'PackageAction.command');
		$order = array('PackageAction.type ASC', 'PackageAction.position ASC');

		return $this->find('all', array('conditions' => $conditions, 'fields' => $fields, 'order' => $order));
	}

	function get($id, $isEdit = false) {
		$this->recursive = 1;
		$this->belongsTo['Package']['fields'] = array('Package.id', 'Package.name');

		if ($isEdit)
			$this->unbindModel(array('hasMany' => array('ExitCode')), false);

		$action = $this->read(null, $id);

		if (!$isEdit) {
			$action['PackageAction']['timeout'] = (empty($packageAction['PackageAction']['timeout']) ? "3600" : $packageAction['PackageAction']['timeout']);
			$action['PackageAction']['workdir'] = (empty($packageAction['PackageAction']['workdir']) ? "&lt;None&gt;" : $packageAction['PackageAction']['workdir']);
		}

		return $action;
	}

}
?>