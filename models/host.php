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
// Use a PHP error handler to do easy validation of Host name regex validation
$hasRegexError = false;
function regexErrorHandler($errno, $errstr, $errfile, $errline) {
	global $hasRegexError;
	return ($hasRegexError = true);
}

class Host extends AppModel {

	var $name = 'Host';
	var $displayField = 'name';
	var $actsAs = array('ExtendAssociations', 'Containable', 'Ordered' => array('field' => 'position', 'foreign_key' => false));
	var $validate = array(
		'name' => array(
			'validregex' => array(
				'rule' => array('checkRegex'),
				'required' => true,
				'allowEmpty' => false,
				'message' => "The host's name is an invalid regular expression.",
				'last' => true
			),
			'uniqueName' => array(
				'rule' => array('isUnique'),
				'message' => "That host already exists.",
				'last' => true
			)
		),
		'mainprofile_id' => array(
			'rule' => array('checkProfile'),
			'required' => true,
			'message' => "The host's main profile could not be found.",
			'last' => true
		)
	);

	function checkRegex($data) {
		global $hasRegexError;
		set_error_handler("regexErrorHandler");
		$error = preg_match("/" . (string)$data['name'] . "/", 'foo');
		restore_error_handler();

		// do two checks to be absolutely sure
		return ($hasRegexError !== true && $error !== false);
	}
	
	function isUnique($data) {
		$field = array_shift(array_keys($data));
		$conditions = array($field => $data[$field]);
		if (!empty($this->id))
			$conditions["id <>"] = $this->id;
		return ($this->find('count', array('conditions' => $conditions, 'recursive' => -1)) == 0);
	}
	
	function checkProfile($data) {
		return ClassRegistry::init('Profile')->find('count', array('conditions' => array('Profile.id' => $data['mainprofile_id']))) > 0;
	}

	// The Associations below have been created with all possible keys, those that are not needed can be removed
	var $hasAndBelongsToMany = array(
			'Profile' => array('className' => 'Profile',
						'joinTable' => 'hosts_profiles',
						'foreignKey' => 'host_id',
						'associationForeignKey' => 'profile_id',
						'unique' => true,
						'conditions' => '',
						'fields' => array('Profile.id', 'Profile.id_text'),
						'order' => 'Profile.id_text ASC'
			)
	);

	var $hasOne = array(
			'MainProfile' => array('className' => 'Profile',
						'foreignKey' => false,
						'conditions' => 'Host.mainprofile_id = MainProfile.id',
						'fields' => array('MainProfile.id', 'MainProfile.id_text'),
			)
	);


	function get($id, $inclVariables = true) {
		$this->recursive = 1;

		if (!$inclVariables)
			$this->unbindModel(array('hasMany' => array('Variable')));

		return $this->read(null, $id);
	}
	
	function getAssocProfiles($hostId) {
		$this->unbindAll(array('hasAndBelongsToMany' => array('Profile')));
		$this->hasAndBelongsToMany['Profile']['order'] = 'Profile.id_text ASC';
		$this->recursive = 1;
		return $this->find('first', array('conditions' => array('Host.id' => $hostId), 'fields' => array('Host.id')));
	}

	function getAllForXML($id = null, $getDisabled = false) {
		$this->recursive = 1;
		
		$this->bindModel(array('hasMany' => array(
			'Variable' => array('className' => 'Variable',
								'foreignKey' => 'ref_id',
								'dependent' => true,
								'conditions' => array('ref_type' => VARIABLE_TYPE_HOST),
								'fields' => array('name', 'value')
			)
		)));

		$this->hasAndBelongsToMany['Profile']['conditions'] = array('Profile.enabled' => true);
		$this->hasAndBelongsToMany['Profile']['fields'] = array('Profile.id', 'Profile.id_text');
		$this->hasAndBelongsToMany['Profile']['order'] = 'Profile.id_text ASC';
		$this->hasOne['MainProfile']['fields'] = array('MainProfile.id', 'MainProfile.id_text');

		$conditions = ($getDisabled ? array() : array('Host.enabled' => true));
		$fields = array('Host.id', 'Host.name', 'Host.enabled', 'MainProfile.id_text');
		$order = array('Host.position' => 'asc');

		if ($id)
			return $this->find('all', array('conditions' => array('Host.id' => $id) + $conditions, 'fields' => $fields, 'order' => $order));
		else
			return $this->find('all', array('conditions' => $conditions, 'fields' => $fields, 'order' => $order));
	}



}
?>