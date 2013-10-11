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
include_once('wpkg_constants.php');
include_once('util.functions.php');

class WPKGImporter {

	// Caches that store id_text => pk values, for quick lookups and inserting new relationships between various models
	var $pkgIdCache = array();
	var $profIdCache = array();
	var $hostIdCache = array();
	
	var $pkgReplace = array();
	var $pkgNotUpd = array();
	
	// Single instance variable
	private static $instance;
	
	private function __construct() {}
	
	public static function getInstance() {
		if (!self::$instance)
			self::$instance = new WPKGImporter();
		return self::$instance;
	}

	function _array_change_key($orig, $new, &$array) {
		foreach ($array as $k => $v)
			$return[$k === $orig ? $new : $k] = $v;
		return $return;
	}

	function _array_change_key_r($orig, $new, &$array) {
		foreach ($array as $k => $v)
			$return[$k === $orig ? $new : $k] = (is_array($v) ? $this->_array_change_key_r($orig, $new, $v) : $v);
		return $return;
	}
	
	function _arrayify(&$array) {
		if (!is_array($array) || isset($array[0]) || empty($array)) return;
		$array = array($array);
	}
	
	function _convert2array(&$object) {
		if (gettype($object) == 'object') {
			$object = (array)$object;
		}
		if (gettype($object) == 'array') {
			foreach ($object as &$val)
				$this->_convert2array($val);
		}
	}
	
	function _versionCompare($a, $b) {
		$as = explode(".", $a);
		$bs = explode(".", $b);
		$al = count($as);
		$bl = count($bs);
		$length = ($al > $bl ? $al : $bl);
		$ret = 0;
		for ($i = 0; $i < $length; $i++) {
			$av = (isset($as[$i]) && !empty($as[$i]) ? (int)$as[$i] : 0);
			$bv = (isset($bs[$i]) && !empty($bs[$i]) ? (int)$bs[$i] : 0);
			if ($av < $bv) {
				$ret = -1;
				break;
			} else if ($av > $bv) {
				$ret = 1;
				break;
			}
		}
		return $ret;
	}
	
	function _moveAttribs(&$array) {
		if (!is_array($array) || isset($array[0]) || empty($array) || !isset($array['@attributes'])) return;
		$array = array_merge($array, $array['@attributes']);
		unset($array['@attributes']);
	}

	function _saveVariables(&$variables, $ref_id, $ref_type) {
		$varInst =& ClassRegistry::init('Variable');
		
		$this->_arrayify($variables);
		for ($i=0; $i<count($variables); $i++) {
			$variables[$i]['ref_id'] = $ref_id;
			$variables[$i]['ref_type'] = $ref_type;
		}
		
		$varInst->create(false);
		$varInst->saveAll($variables, array('validate' => false));
	}
	
	/*******************************************************************************************************
	 * Begin Package-specific import methods                                                               *
	 *******************************************************************************************************/
	
	function importPackages($XMLfilename) {
		$xml = simplexml_load_file($XMLfilename);
		if (!$xml) {
			$messages['Errors']['General'] = "No Packages to import.";
			return $messages;
		}
		
		$this->_convert2array($xml);
		$xml['package'] = $this->_array_change_key_r("id", "id_text", $xml['package']);
		$xml['package'] = $this->_array_change_key_r("check", "packagecheck", $xml['package']);
		$xml['package'] = $this->_array_change_key_r("exit", "exitcode", $xml['package']);

		$messages = array();

		$this->_arrayify($xml['package']);
		foreach ($xml['package'] as &$package) {
			$this->_modifyPackage($package);

			$depends[$package['@attributes']['id_text']] = $this->_cutPackageDepends($package);

			if (array_key_exists('packagecheck', $package)) {
				$checks = $package['packagecheck'];
				unset($package['packagecheck']);
			}
			if (array_key_exists('packageaction', $package)) {
				$actions = $package['packageaction'];
				unset($package['packageaction']);
			}

			$newPackage = array('Package' => $package['@attributes']);

			if (isset($checks)) {
				$newPackage['packagecheck'] = $checks;
				unset($checks);
			}
			if (isset($actions)) {
				$newPackage['packageaction'] = $actions;
				unset($actions);
			}

			$package = $newPackage;

			// Package validation
			$messages = array_merge_recursive($messages, $this->_validatePackage($package));

			// Package Action(s) validation
			$position = 1;
			if (array_key_exists('packageaction', $package)) {
				$this->_arrayify($package['packageaction']);
				foreach ($package['packageaction'] as $action) {
					$messages = array_merge_recursive($messages, $this->_validateAction($action, $package['Package']['id_text'], $position));
					$position++;
				}
			}

			// Package Check(s) validation
			$position = 1;
			if (array_key_exists('packagecheck', $package)) {
				$this->_arrayify($package['packagecheck']);
				foreach ($package['packagecheck'] as $check) {
					$messages = array_merge_recursive($messages, $this->_validateCheck($check, $package['Package']['id_text'], $position));
					$position++;
				}
			}
		}

		// (All) Package dependencies validation
		$messages = array_merge_recursive($messages, $this->_validatePackageDepends($depends));

		$saved = 0;
		$packages = $xml['package'];
		$bad = (isset($messages['Errors']) ? array_keys($messages['Errors']) : array());
		$bad = array_merge($bad, $this->pkgNotUpd);
		for ($i = 0; $i < count($packages); $i++) {
			$pkgdepends = (isset($depends[$packages[$i]['Package']['id_text']]) ? $depends[$packages[$i]['Package']['id_text']] : array());
			if (count(array_intersect(array_merge($pkgdepends, array($packages[$i]['Package']['id_text'])), $bad)) == 0) {
				$this->_savePackage($packages[$i]);
				$saved++;
			} else if (isset($depends[$packages[$i]['Package']['id_text']]))
				unset($depends[$packages[$i]['Package']['id_text']]);
		}
		$this->_savePackageDepends($depends);

		$messages = array('saved' => $saved, 'total' => count($packages), 'messages' => $messages);
		return $messages;
	}
	
	function _createActionType(&$package, $type) {
		static $exitEnum;
		if (empty($exitEnum)) {
			foreach (constsToWords("EXITCODE_REBOOT_") as $val => $word)
				$exitEnum[strtolower($word)] = $val;
		}
		if (isset($package[$type])) {
			$this->_arrayify($package[$type]);
			for ($i=0; $i<count($package[$type]); $i++) {
				$this->_moveAttribs($package[$type][$i]);
				$newaction = array('type' => constant('ACTION_TYPE_' . strtoupper($type)), 'command' => (stripos($package[$type][$i]['cmd'], '&quot;') !== false ? htmlspecialchars_decode($package[$type][$i]['cmd']) : $package[$type][$i]['cmd']));
				if (isset($package[$type][$i]['exitcode'])) {
					$this->_arrayify($package[$type][$i]['exitcode']);
					for ($j=0; $j<count($package[$type][$i]['exitcode']); $j++) {
						$this->_moveAttribs($package[$type][$i]['exitcode'][$j]);
						$val = EXITCODE_REBOOT_FALSE; // default exit code reboot action
						if (isset($package[$type][$i]['exitcode'][$j]['reboot']) && in_array(strtolower($package[$type][$i]['exitcode'][$j]['reboot']), array_keys($exitEnum)))
							$val = $exitEnum[strtolower($package[$type][$i]['exitcode'][$j]['reboot'])];
						$package[$type][$i]['exitcode'][$j]['reboot'] = $val;
					}
					$newaction['exitcode'] = $package[$type][$i]['exitcode'];
				}
				$package['packageaction'][] = $newaction;
			}
			unset($package[$type]);
		}
	}

	function _createAllActions(&$package) {
		static $actionTypes;
		if (empty($actionTypes)) {
			foreach (constsToWords("ACTION_TYPE_") as $type)
				$actionTypes[] = strtolower($type);
		}
		foreach ($actionTypes as $type)
			$this->_createActionType($package, $type);
	}

	function _cutPackageDepends(&$package) {
		$depends = array();
		if (isset($package['depends'])) {
			$this->_arrayify($package['depends']);
			for ($i=0; $i<count($package['depends']); $i++) {
				$this->_moveAttribs($package['depends'][$i]);
				$depends[] = $package['depends'][$i]['package-id'];
			}
			unset($package['depends']);
		}
		return $depends;
	}

	function _modifyChecks_r(&$array) {
		static $condEnum;
		if (empty($condEnum)) {
			foreach (constsToWords("CHECK_CONDITION_") as $val => $words) {
				$arrWords = explode(' ', strtolower($words));
				$type = array_shift($arrWords);
				$words = implode('', $arrWords);
				$words = str_replace("thanor", "or", $words);
				$words = str_replace("orequalto", "orequal", $words);
				if (!isset($condEnum[$type]))
					$condEnum[$type] = array();
				$condEnum[$type][$words] = $val;
			}
		}

		$array['condition'] = Set::enum(strtolower($array['condition']), $condEnum[strtolower($array['type'])]);
		$array['type'] = constant('CHECK_TYPE_' . strtoupper($array['type']));
		if (isset($array['childcheck']) && ($array['type'] == CHECK_TYPE_LOGICAL)) {
			$this->_arrayify($array['childcheck']);
			for ($i=0; $i<count($array['childcheck']); $i++) {
				$this->_moveAttribs($array['childcheck'][$i]);
				$this->_modifyChecks_r($array['childcheck'][$i]);
			}
		}
	}

	function _modifyPackage(&$package) {
		$package['@attributes']['reboot'] = (isset($package['@attributes']['reboot']) ? constant('PACKAGE_REBOOT_' . strtoupper($package['@attributes']['reboot'])) : PACKAGE_REBOOT_FALSE);

		if (isset($package['@attributes']['execute']))
			$package['@attributes']['execute'] = constant('PACKAGE_EXECUTE_' . strtoupper($package['@attributes']['execute']));

		if (isset($package['@attributes']['notify']))
			$package['@attributes']['notify'] = constant('PACKAGE_NOTIFY_' . strtoupper($package['@attributes']['notify']));

		if (isset($package['packagecheck'])) {
			$package['packagecheck'] = $this->_array_change_key_r("packagecheck", "childcheck", $package['packagecheck']);
			$this->_arrayify($package['packagecheck']);
			for ($j=0; $j<count($package['packagecheck']); $j++) {
				$this->_moveAttribs($package['packagecheck'][$j]);
				$this->_modifyChecks_r($package['packagecheck'][$j]);
			}
		}

		// TODO: handle download tags, for now just strip them if they exist
		if (isset($package['download']))
			unset($package['download']);
		$this->_createAllActions($package);
	}

	function _saveAction(&$action, $pkgId) {
		$pkgActInst =& ClassRegistry::init('PackageAction');

		$action['package_id'] = $pkgId;
		$pkgActInst->create(false);
		$pkgActInst->save($action, array('validate' => false));

		if (isset($action['exitcode'])) {
			$exitCodeInst = ClassRegistry::init('ExitCode');

			$pkgActId = $pkgActInst->id;
			$exitCodes = $action['exitcode'];
			$this->_arrayify($exitCodes);
			for ($i=0; $i<count($exitCodes); $i++) {
				$exitCodes[$i]['package_action_id'] = $pkgActId;
			}
			$exitCodeInst->saveAll($exitCodes, array('validate' => false));
		}
	}

	function _saveCheck(&$check, $pkgId, $parentCheckId) {
		$pkgChkInst =& ClassRegistry::init('PackageCheck');

		$check['package_id'] = $pkgId;
		if ($parentCheckId != null)
			$check['parent_id'] = $parentCheckId;

		$pkgChkInst->create(false);
		$pkgChkInst->save(array('PackageCheck' => $check), array('validate' => false));
		$pkgChkId = $pkgChkInst->id;

		if ($check['type'] == CHECK_TYPE_LOGICAL && isset($check['childcheck'])) {
			$this->_arrayify($check['childcheck']);
			foreach ($check['childcheck'] as $child)
				$this->_saveCheck($child, $pkgId, $pkgChkId);
		}
	}

	function _savePackageDepends(&$pkgDepends) {
		$depends = array();
		$pkgDepInst =& ClassRegistry::init('PackagesPackage');

		if (!empty($pkgDepends)) {
			foreach ($pkgDepends as $pkgIdText => $dependlist) {
				foreach ($dependlist as $k => $dependency)
					$depends[] = array('package_id' => $this->pkgIdCache[$pkgIdText], 'dependency_id' => $this->pkgIdCache[$dependency]);
			}

			if (!empty($depends)) {
				$pkgDepInst->create(false);
				$pkgDepInst->saveAll($depends, array('validate' => false));
			}
		}
	}

	function _savePackage($pkgArray) {
		$pkgInst =& ClassRegistry::init('Package');

		if (isset($pkgArray['packagecheck'])) {
			$checks = $pkgArray['packagecheck'];
			unset($pkgArray['packagecheck']);
		}
		if (isset($pkgArray['packageaction'])) {
			$actions = $pkgArray['packageaction'];
			unset($pkgArray['packageaction']);
		}
		if (isset($pkgArray['variable'])) {
			$vars = $pkgArray['variable'];
			unset($pkgArray['variable']);
		}

		// First delete any pre-existing package with the same ID that was previously found to have a lower revision
		if (isset($this->pkgReplace[$pkgArray['Package']['id_text']])) {
			$oldID = $this->pkgReplace[$pkgArray['Package']['id_text']];
			$oldhabtm = $pkgInst->hasAndBelongsToMany;
			$pkgInst->unbindModel(array('hasAndBelongsToMany' => array('Profile', 'PackageDependency')), false);
			$pkgInst->delete($oldID);
			$pkgInst->bindModel(array('hasAndBelongsToMany' => $oldhabtm), false);
		}
		
		$pkgInst->create(false);
		$pkgInst->save($pkgArray, array('validate' => false));
		$pkgId = $pkgInst->id;

		// Cache the new package ID
		$this->pkgIdCache[$pkgArray['Package']['id_text']] = $pkgId;

		if (isset($checks)) {
			$this->_arrayify($checks);
			for ($i = 0; $i < count($checks); $i++)
				$this->_saveCheck($checks[$i], $pkgId, null);
			unset($checks);
		}
		if (isset($actions)) {
			$this->_arrayify($actions);
			for ($i = 0; $i < count($actions); $i++)
				$this->_saveAction($actions[$i], $pkgId);
			unset($actions);
		}
		if (isset($vars)) {
			$this->_saveVariables($vars, $pkgId, VARIABLE_TYPE_PACKAGE);
			unset($vars);
		}
		
		// If the Package was replaced, update old associations to point to the new Package
		if (isset($oldID)) {
			$pkgInst->PackagesProfile->updateAll(
				array('package_id' => $pkgId),
				array('package_id' => $oldID)
			);
			$pkgInst->PackagesPackage->updateAll(
				array('dependency_id' => $pkgId),
				array('dependency_id' => $oldID)
			);
		}
	}

	function _validateAction(&$action, $pkgIdText, $position) {
		$messages = array();
		$pkgActInst =& ClassRegistry::init('PackageAction');

		$pkgActInst->set(array('PackageAction' => $action));
		$result = $pkgActInst->validates();
		if (!$result)
			$messages['Errors'][$pkgIdText]['Actions'][$position] = array_values($pkgActInst->invalidFields());

		if (array_key_exists('exitcode', $action)) {
			$exitCodePosition = 1;
			$this->_arrayify($action['exitcode']);
			foreach ($action['exitcode'] as $exitCode) {
				$msgs = $this->_validateExitCode($exitCode, $pkgIdText, $exitCodePosition);
				if (!empty($msgs))
					$messages['Errors'][$pkgIdText]['Actions'][$position]['Exit Codes'][$exitCodePosition] = $msgs;
				$exitCodePosition++;
			}
		}

		return $messages;
	}

	function _validateCheck(&$check, &$pkgIdText, &$position) {
		$messages = array();
		$pkgChkInst =& ClassRegistry::init('PackageCheck');

		$pkgChkInst->set(array('PackageCheck' => $check));
		$result = $pkgChkInst->validates();
		if (!$result)
			$messages['Errors'][$pkgIdText]['Checks'][$position] = array_values($pkgChkInst->invalidFields());
		if ($check['type'] == CHECK_TYPE_LOGICAL && isset($check['childcheck'])) {
			$this->_arrayify($check['childcheck']);
			foreach ($check['childcheck'] as $child) {
				$position++;
				$messages = array_merge_recursive($messages, $this->_validateCheck($child, $pkgIdText, $position));
			}
		}
		return $messages;
	}

	function _validateExitCode(&$exitCode, $pkgIdText, $position) {
		$messages = array();
		$exitCodeInst =& ClassRegistry::init('ExitCode');

		$exitCodeInst->set(array('ExitCode' => $exitCode));
		$result = $exitCodeInst->validates();
		if (!$result)
			$messages[$position] = array_values($exitCodeInst->invalidFields());
		return $messages;
	}

	function _validatePackage(&$package) {
		$messages = array();
		$pkgInst =& ClassRegistry::init('Package');

		$pkgInst->set($package);
		$result = $pkgInst->validates();
		if (!$result) {
			$errors = $pkgInst->invalidFields();
			if (($key = array_search("That package already exists.", $errors)) !== false) {
				unset($errors[$key]);
				$existing = $pkgInst->find('first', array('conditions' => array('id_text' => $package['Package']['id_text']), 'fields' => array('id', 'revision'), 'recursive' => -1));
				if ($this->_versionCompare($package['Package']['revision'], $existing['Package']['revision']) > 0) {
					$this->pkgReplace[$package['Package']['id_text']] = $existing['Package']['id'];
					$messages['Information'][$package['Package']['id_text']][] = "The package was upgraded from revision {$existing['Package']['revision']} to {$package['Package']['revision']}";
				} else {
					$this->pkgNotUpd[] = $package['Package']['id_text'];
					$messages['Warnings'][$package['Package']['id_text']][] = "Found a pre-existing package (of the same ID) in the database with a higher revision than the imported one. Skipping.";
				}
			}
			if (!empty($errors))
				$messages['Errors'][$package['Package']['id_text']] = array_values($errors);
		}
		return $messages;
	}
	
	function _validatePackageDepends(&$pkgDepends) {
		$messages = array();
		$pkgInst =& ClassRegistry::init('Package');

		foreach ($pkgDepends as $pkgIdText => $dependlist) {
			foreach ($dependlist as $k => $dependency) {
				// check that we don't depend on ourself
				if ($dependency == $pkgIdText) {
					$messages['Warnings'][$pkgIdText][] = "Detected package dependency on self. Skipping.";
					unset($pkgDepends[$pkgIdText][$k]);
				} else if (empty($dependency)) {
					$messages['Warnings'][$pkgIdText][] = "Found package dependency with empty/invalid name. Skipping.";
					unset($pkgDepends[$pkgIdText][$k]);
				} else {
					// Check dependencies against locally defined packages
					if (!array_key_exists($dependency, $pkgDepends)) {
						// Check pre-existing packages in the database
						if (!($pkg = $pkgInst->find('first', array('fields' => array('Package.id'), 'conditions' => array('Package.id_text' => $dependency), 'recursive' => -1))))
							$messages['Errors'][$pkgIdText][] = "Could not find existing dependency package: $dependency";
						else {
							// Cache the package ID of this dependency from the DB
							$this->pkgIdCache[$dependency] = $pkg['Package']['id'];
						}
					}
				}
			}
		}

		return $messages;
	}
	
	/*******************************************************************************************************
	 * Begin Profile-specific import methods                                                               *
	 *******************************************************************************************************/
	
	function importProfiles($XMLfilename) {
		$xml = simplexml_load_file($XMLfilename);
		if (!$xml) {
			$messages['Errors']['General'] = "No Profiles to import.";
			return $messages;
		}
		
		$this->_convert2array($xml);
		$xml['profile'] = $this->_array_change_key_r("id", "id_text", $xml['profile']);
		
		$messages = array();
		
		$this->_arrayify($xml['profile']);
		foreach ($xml['profile'] as &$profile) {
			$this->_moveAttribs($profile);
			
			$depends[$profile['id_text']] = $this->_cutProfileDepends($profile);
			$packages = $this->_cutProfilePackages($profile);
			
			// Validate Profile
			$messages = array_merge_recursive($messages, $this->_validateProfile($profile));
			
			// Validate Profile Packages
			$messages = array_merge_recursive($messages, $this->_validateProfilePackages($packages, $profile['id_text']));
			
			if (!empty($packages)) {
				$profile['Package'] = $packages;
				unset($packages);
			}
		}
		
		// (All) Profile dependencies validation
		$messages = array_merge_recursive($messages, $this->_validateProfileDepends($depends));
		
		$saved = 0;
		$profiles = $xml['profile'];
		$bad = (isset($messages['Errors']) ? array_keys($messages['Errors']) : array());
		for ($i = 0; $i < count($profiles); $i++) {
			$profdepends = (isset($depends[$profiles[$i]['id_text']]) ? $depends[$profiles[$i]['id_text']] : array());
			if (count(array_intersect(array_merge($profdepends, array($profiles[$i]['id_text'])), $bad)) == 0) {
				$this->_saveProfile($profiles[$i]);
				$saved++;
			} else if (isset($depends[$profiles[$i]['id_text']]))
				unset($depends[$profiles[$i]['id_text']]);
		}
		$this->_saveProfileDepends($depends);
		
		$messages = array('saved' => $saved, 'total' => count($profiles), 'messages' => $messages);
		return $messages;
	}

	function _cutProfileDepends(&$profile) {
		$depends = array();

		if (isset($profile['depends'])) {
			$this->_arrayify($profile['depends']);
			for ($i=0; $i<count($profile['depends']); $i++) {
				$this->_moveAttribs($profile['depends'][$i]);
				$depends[] = $profile['depends'][$i]['profile-id'];
			}
			unset($profile['depends']);
		}

		return $depends;
	}
	
	function _cutProfilePackages(&$profile) {
		$packages = array();

		if (isset($profile['package'])) {
			$this->_arrayify($profile['package']);
			for ($i=0; $i<count($profile['package']); $i++) {
				$this->_moveAttribs($profile['package'][$i]);
				$packages[] = $profile['package'][$i]['package-id'];
			}
			unset($profile['package']);
		}

		return $packages;
	}
	
	function _saveProfile(&$profArray) {
		$profInst =& ClassRegistry::init('Profile');

		if (isset($profArray['package'])) {
			$packages = $profArray['package'];
			unset($profArray['package']);
		}
		if (isset($profArray['variable'])) {
			$vars = $profArray['variable'];
			unset($profArray['variable']);
		}

		$profInst->create(false);
		$profInst->save($profArray, array('validate' => false));
		$profId = $profInst->id;

		// Cache the new profile ID
		$this->profIdCache[$profArray['id_text']] = $profId;

		if (isset($packages)) {
			$this->_saveProfilePackages($packages, $profArray['id_text']);
			unset($packages);
		}
		if (isset($vars)) {
			$this->_saveVariables($vars, $profId, VARIABLE_TYPE_PROFILE);
			unset($vars);
		}
	}
	
	function _saveProfileDepends(&$profDepends) {
		$depends = array();
		$profDepInst =& ClassRegistry::init('ProfilesProfile');

		if (!empty($profDepends)) {
			foreach ($profDepends as $profIdText => $dependlist) {
				foreach ($dependlist as $dependency)
					$depends[] = array('profile_id' => $this->profIdCache[$profIdText], 'dependency_id' => $this->profIdCache[$dependency]);
			}

			if (!empty($depends)) {
				$profDepInst->create(false);
				$profDepInst->saveAll($depends, array('validate' => false));
			}
		}
	}
	
	function _saveProfilePackages(&$profPackages, $profIdText) {
		$profPkgs = array();
		$profPkgInst =& ClassRegistry::init('PackagesProfile');

		if (!empty($profPackages)) {
			foreach ($profPackages as $pkgIdText)
				$profPkgs[] = array('profile_id' => $this->profIdCache[$profIdText], 'package_id' => $this->pkgIdCache[$pkgIdText]);

			if (!empty($profPkgs)) {
				$profPkgInst->create(false);
				$profPkgInst->saveAll($profPkgs, array('validate' => false));
			}
		}
	}
	
	function _validateProfile(&$profile) {
		$messages = array();
		$profInst =& ClassRegistry::init('Profile');
		
		$profInst->set($profile);
		$result = $profInst->validates();
		if (!$result)
			$messages['Errors'][$profile['id_text']] = array_values($profInst->invalidFields());
			
		return $messages;
	}
	
	function _validateProfileDepends(&$profDepends) {
		$messages = array();
		$profInst =& ClassRegistry::init('Profile');

		foreach ($profDepends as $profIdText => $dependlist) {
			foreach ($dependlist as $k => $dependency) {
				// check that we don't depend on ourself
				if ($dependency == $profIdText) {
					$messages['Warnings'][$profIdText][] = "Detected profile dependency on self. Skipping.";
					unset($profDepends[$profIdText][$k]);
				} else if (empty($dependency)) {
					$messages['Warnings'][$profIdText][] = "Found profile dependency with empty/invalid name. Skipping.";
					unset($profDepends[$profIdText][$k]);
				} else {
					// Check dependencies against locally defined profiles
					if (!array_key_exists($dependency, $profDepends)) {
						// Check pre-existing profiles in the database
						if (!($prof = $profInst->find('first', array('fields' => array('Profile.id'), 'conditions' => array('Profile.id_text' => $dependency), 'recursive' => -1))))
							$messages['Errors'][$profIdText][] = "Could not find existing dependency profile: $dependency";
						else {
							// Cache the profile ID of this dependency from the DB
							$this->profIdCache[$dependency] = $prof['Profile']['id'];
						}
					}
				}
			}
		}

		return $messages;
	}
	
	function _validateProfilePackages(&$profPackages, $profIdText) {
		$messages = array();
		$pkgInst =& ClassRegistry::init('Package');

		foreach ($profPackages as $pkgIdText) {
			if (empty($pkgIdText))
				$messages['Warnings'][$profIdText][] = "Found profile package with empty/invalid name. Skipping.";
			else {
				// Check profile packages against package id cache
				if (!array_key_exists($pkgIdText, $this->pkgIdCache)) {
					// Check pre-existing packages in the database
					if (!($pkg = $pkgInst->find('first', array('fields' => array('Package.id'), 'conditions' => array('Package.id_text' => $pkgIdText), 'recursive' => -1))))
						$messages['Errors'][$profIdText][] = "Could not find existing profile package: $pkgIdText";
					else {
						// Cache the package ID of this profile package from the DB
						$this->pkgIdCache[$pkgIdText] = $pkg['Package']['id'];
					}
				}
			}
		}

		return $messages;
	}
	
	/*******************************************************************************************************
	 * Begin Host-specific import methods                                                                  *
	 *******************************************************************************************************/

	function importHosts($XMLfilename) {
		$xml = simplexml_load_file($XMLfilename);
		if (!$xml) {
			$messages['Errors']['General'] = "No Hosts to import.";
			return $messages;
		}
		
		$this->_convert2array($xml);
		
		$messages = array();

		$this->_arrayify($xml['host']);
		foreach ($xml['host'] as &$host) {
			$this->_moveAttribs($host);
			
			$profiles = $this->_cutHostProfiles($host);
			
			// Validate Host
			$messages = array_merge_recursive($messages, $this->_validateHost($host));
			
			// Validate Host Profiles
			$messages = array_merge_recursive($messages, $this->_validateHostProfiles($profiles, $host['name']));
			
			if (!empty($profiles)) {
				$host['Profile'] = $profiles;
				unset($profiles);
			}
		}

		$saved = 0;
		$hosts = $xml['host'];
		$bad = (isset($messages['Errors']) ? array_keys($messages['Errors']) : array());
		for ($i = 0; $i < count($hosts); $i++) {
			if (!in_array($hosts[$i]['name'], $bad)) {
				$this->_saveHost($hosts[$i]);
				$saved++;
			}
		}

		$messages = array('saved' => $saved, 'total' => count($hosts), 'messages' => $messages);
		return $messages;
	}
	
	function _cutHostProfiles(&$host) {
		$packages = array();

		if (isset($host['profile'])) {
			$this->_arrayify($host['profile']);
			for ($i=0; $i<count($host['profile']); $i++) {
				$this->_moveAttribs($host['profile'][$i]);
				$packages[] = $host['profile'][$i]['id'];
			}
			unset($host['profile']);
		}

		return $packages;
	}
	
	function _saveHost(&$hostArray) {
		$hostInst =& ClassRegistry::init('Host');

		if (isset($hostArray['profile'])) {
			$profiles = $hostArray['profile'];
			unset($hostArray['profile']);
		}
		if (isset($hostArray['variable'])) {
			$vars = $hostArray['variable'];
			unset($hostArray['variable']);
		}

		$hostInst->create(false);
		$hostInst->save($hostArray, array('validate' => false));
		$hostId = $hostInst->id;

		// Cache the new host ID
		$this->hostIdCache[$hostArray['name']] = $hostId;

		if (isset($profiles)) {
			$this->_saveHostProfiles($profiles, $hostArray['name']);
			unset($profiles);
		}
		if (isset($vars)) {
			$this->_saveVariables($vars, $hostId, VARIABLE_TYPE_HOST);
			unset($vars);
		}
	}
	
	function _saveHostProfiles(&$hostProfiles, $hostName) {
		$hostProfs = array();
		$hostProfInst =& ClassRegistry::init('HostsProfile');

		if (!empty($hostProfiles)) {
			foreach ($hostProfiles as $profIdText)
				$hostProfs[] = array('host_id' => $this->hostIdCache[$hostName], 'profile_id' => $this->profIdCache[$profIdText]);

			if (!empty($hostProfs)) {
				$hostProfInst->create(false);
				$hostProfInst->saveAll($hostProfs, array('validate' => false));
			}
		}
	}
	
	function _validateHost(&$host) {
		$messages = array();
		$hostInst =& ClassRegistry::init('Host');
		$profInst =& ClassRegistry::init('Profile');
		
		// Validate host's main profile
		if (isset($host['profile-id'])) {
			$mainprofile = $host['profile-id'];
			unset($host['profile-id']);
			
			// Check host main profile against profile id cache
			if (!array_key_exists($mainprofile, $this->profIdCache)) {
				// Check pre-existing profiles in the database
				if (!($prof = $profInst->find('first', array('fields' => array('Profile.id'), 'conditions' => array('Profile.id_text' => $mainprofile), 'recursive' => -1))))
					$messages['Errors'][$host['name']][] = "Could not find existing host's main profile: $mainprofile";
				else {
					// Cache the profile ID of this host's main profile from the DB
					$host['mainprofile_id'] = $this->profIdCache[$mainprofile] = $prof['Profile']['id'];
				}
			} else
				$host['mainprofile_id'] = $this->profIdCache[$mainprofile];
		}
		
		$hostInst->set($host);
		$result = $hostInst->validates();
		if (!$result) {
			if (!isset($messages['Errors'][$host['name']]))
				$messages['Errors'][$host['name']] = array();
			$messages['Errors'][$host['name']] += array_values($hostInst->invalidFields());
		}
			
		return $messages;
	}
	
	function _validateHostProfiles(&$hostProfiles, $hostName) {
		$messages = array();
		$profInst =& ClassRegistry::init('Profile');

		foreach ($hostProfiles as $profIdText) {
			if (empty($profIdText))
				$messages['Warnings'][$hostName][] = "Found host profile with empty/invalid name. Skipping.";
			else {
				// Check host profiles against profile id cache
				if (!array_key_exists($profIdText, $this->profIdCache)) {
					// Check pre-existing profiles in the database
					if (!($prof = $profInst->find('first', array('fields' => array('Profile.id'), 'conditions' => array('Profile.id_text' => $profIdText), 'recursive' => -1))))
						$messages['Errors'][$hostName][] = "Could not find existing host profile: $profIdText";
					else {
						// Cache the profile ID of this host profile from the DB
						$this->profIdCache[$profIdText] = $prof['Profile']['id'];
					}
				}
			}
		}

		return $messages;
	}
	
	/*******************************************************************************************************
	 * Begin XML validation methods                                                                        *
	 *******************************************************************************************************/
	
	function libxml_format_error($error) {
		$ret = array();
		$type = 'Other Messages';
		switch ($error->level) {
			case LIBXML_ERR_WARNING:
				$type = 'Warnings';
				break;
			case LIBXML_ERR_ERROR:
				$type = 'Errors';
				break;
			case LIBXML_ERR_FATAL:
				$type = 'Fatal Errors';
				break;
		}
		$ret['code'] = $error->code;
		$ret['message'] = $error->message;
		if ($error->file)
			$ret['file'] = $error->file;
		$ret['line'] = $error->line;

		return array($type => array($ret));
	}

	function libxml_formatted_errors() {
		$ret = array();
		$errors = libxml_get_errors();
		foreach ($errors as $error)
			$ret = array_merge_recursive($ret, $this->libxml_format_error($error));
		libxml_clear_errors();
		
		return $ret;
	} 

	function validateXML($xmlFileName, $xsdFileName) {
		libxml_use_internal_errors(true);
		$xml = new DOMDocument();
		$xml->load($xmlFileName);
		if (!$xml->schemaValidate($xsdFileName))
			$ret = $this->libxml_formatted_errors();
		else
			$ret = true;
		
		return $ret;
	}
}
?>
