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
class PackageCheck extends AppModel {

	var $name = 'PackageCheck';
	var $actsAs = array('Tree');
	var $validate = array(
		'type' => array(
			'rule' => array('checkRange'),
			'message' => "The package check type attribute is invalid."
		),
		'condition' => array(
			'rule' => array('checkRange'),
			'message' => "The package check condition attribute is invalid."
		)
	);

	function checkRange($data) {
		$field = array_shift(array_keys($data));
		return in_array($data[$field], constsVals('CHECK_' . strtoupper($field) . '_'));
	}

	//The Associations below have been created with all possible keys, those that are not needed can be removed
	var $belongsTo = array(
			'Package' => array('className' => 'Package',
								'foreignKey' => 'package_id',
								'conditions' => '',
								'fields' => '',
								'order' => ''
			),
			'Parentcheck' => array('className' => 'PackageCheck',
								'foreignKey' => 'parent_id',
								'conditions' => '',
								'fields' => '',
								'order' => ''
			)
	);

	var $hasMany = array(
			'Childcheck' => array('className' => 'PackageCheck',
								'foreignKey' => 'parent_id',
								'dependent' => true,
								'conditions' => '',
								'fields' => '',
								'order' => ''
			)
	);
	
	function getPackageID($pkgCheckID) {
		$this->recursive = -1;
		return $this->field('package_id', array('PackageCheck.id' => $pkgCheckID));
	}

	function getThreadedForPackage($packageID) {
		$this->recursive = -1;
		$conditions = array('PackageCheck.package_id' => $packageID);
		$fields = array('PackageCheck.id',
				'PackageCheck.type',
				'PackageCheck.condition',
				'PackageCheck.path',
				'PackageCheck.value',
				'PackageCheck.lft',
				'PackageCheck.parent_id',
				'PackageCheck.package_id'
		);
		$order = 'PackageCheck.lft ASC';

		return $this->find('threaded', array('conditions' => $conditions, 'fields' => $fields, 'order' => $order));
	}

	function get($id, $omitParent = true, $omitChildren = true) {
		$conditions = array('PackageCheck.id' => $id);
		$fields = array('PackageCheck.id',
				'PackageCheck.type',
				'PackageCheck.condition',
				'PackageCheck.path',
				'PackageCheck.value',
				'PackageCheck.parent_id',
				'Package.id',
				'Package.name',
				'Parentcheck.id',
				'Parentcheck.condition'
		);

		if ($omitParent) {
			$this->unbindModel(array('belongsTo' => array('Parentcheck')));
			array_pop($fields);
			array_pop($fields);
		}
		if ($omitChildren)
			$this->unbindModel(array('hasMany' => array('Childcheck')));

		return $this->find('first', array('conditions' => $conditions, 'fields' => $fields));
	}

	function getLogicalChecksList($packageID) {
		$conditions = array('PackageCheck.package_id' => $packageID, 'PackageCheck.type' => CHECK_TYPE_LOGICAL);

		$this->unbindModel(array('belongsTo' => array('Package')), false);
		$this->belongsTo['Parentcheck']['fields'] = array('Parentcheck.condition', 'Parentcheck.id');

		$logicalChecks = $this->generatetreelist($conditions, '{n}.PackageCheck.id', '{n}.PackageCheck.condition', '-', 0);

		foreach ($logicalChecks as $k => $v) {
			if (($logicalType = constValToLCSingle('check_condition_logical', $v)) == null)
				$logicalType = "Unknown";
			$logicalChecks[$k] = str_replace($v, "", $logicalChecks[$k]);
			$logicalChecks[$k] .= "-Logical " . ucwords($logicalType);
		}
		$logicalChecks = array(0 => 'Root Node') + $logicalChecks;

		return $logicalChecks;
	}

}
?>