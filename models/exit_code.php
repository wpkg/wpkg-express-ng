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

class ExitCode extends AppModel {

	var $name = 'ExitCode';
	var $validate = array(
		'code' => array(
			'isvalidcode' => array(
				'rule' => 'isvalid',
				'message' => "The exit code's code attribute must be an integer or the string, 'any'.",
				'last' => true
			),
			'isunique' => array(
				'rule' => 'isUniqueCode',
				'on' => 'create',
				'message' => "The exit code's code already exists for this package action.",
				'last' => true
			)
		),
		'reboot' => array(
			'isinteger' => array(
				'rule' => 'int_ok',
				'message' => "The exit code's reboot attribute is invalid.",
				'last' => true
			),
			'validrange' => array(
				'rule' => 'checkRange',
				'message' => "The exit code's reboot attribute is invalid.",
				'last' => true
			)
		)
	);

	function isUniqueCode($data) {
		$conditions = array('ExitCode.id' => $this->id, 'ExitCode.code' => array_shift($data));
		return ($this->find('count', array('conditions' => $conditions, 'recursive' => -1)) == 0);
	}

	function checkRange($data) {
		$field = array_shift(array_keys($data));
		return in_array($data[$field], constsVals('EXITCODE_' . strtoupper($field) . '_'));
	}

	function int_ok($data) {
		$data = array_shift($data);
		return ($data !== true) && ((string)(int) $data) === ((string) $data);
	}

	function isvalid($data) {
		$data = array_shift($data);
		return ($this->int_ok(array($data)) || strtolower($data) == "any");
	}

	//The Associations below have been created with all possible keys, those that are not needed can be removed
	var $belongsTo = array(
			'PackageAction' => array('className' => 'PackageAction',
								'foreignKey' => 'package_action_id',
								'conditions' => '',
								'fields' => '',
								'order' => ''
			)
	);

	function get($id) {
		$this->belongsTo['PackageAction']['fields'] = array('PackageAction.type', 'PackageAction.command');
		return $this->read(null, $id);
	}
	
	function getAllForAction($pkgActId) {
		return $this->find('all', array('conditions' => array('package_action_id' => $pkgActId), 'fields' => array('ExitCode.id', 'ExitCode.reboot', 'ExitCode.code')));
	}

}
?>
