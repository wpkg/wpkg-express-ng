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
require_once('../util.functions.php');
class Variable extends AppModel {

	var $name = 'Variable';
	var $validate = array(
		'ref_type' => array(
			'rule' => 'checkRange',
			'message' => 'Invalid variable type.'
		),
		'ref_id' => array(
			'rule' => 'refExists',
			'message' => 'Invalid variable reference ID.'
		)
	);

	function checkRange($data) {
		$field = array_shift(array_keys($data));
		return in_array($data[$field], constsVals('VARIABLE_TYPE_'));
	}
	
	function refExists($data) {
		$field = array_shift(array_keys($data));
		$typeName = constValToLCSingle('VARIABLE_TYPE_', $this->data['Variable']['ref_type'], false, false, false);
		//return ($typeName != null && ClassRegistry::init(ucwords($typeName))->find('count', array('conditions' => array(strtolower($typeName) . '.id' => $data[$field]))) > 0);
		return ($typeName != null && ClassRegistry::init(ucwords($typeName))->find('count', array('conditions' => array($typeName . '.id' => $data[$field]))) > 0);
	}

	function getAllFor($type, $recordId) {
		$type = constant('VARIABLE_TYPE_' . strtoupper($type));
		if ($type === null)
			return false;
		else
			return $this->find('all', array('conditions' => array('Variable.ref_type' => $type, 'Variable.ref_id' => $recordId),
											'fields' => array('Variable.id', 'Variable.name', 'Variable.value')
			));
	}
}
?>
