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
include_once('../import.functions.php');
include_once('../wpkg_constants.php');

class AdminController extends AppController {
	var $name = 'Admin';
	var $layout = 'main';
	var $uses = array();
	var $components = array('Session', 'RequestHandler', 'Configuration');
	var $helpers = array('Html', 'Form', 'Navigate', 'Javascript');

	function index() {
		$installer =& ClassRegistry::init('Installer');
		$types = array_keys($installer->getSettings());
		foreach ($types as $v)
				$this->set($v . "Errs", array());
		
		if (!empty($this->data)) {
			$msg = '';
			$files = array('packages' => XSD_PATH_PACKAGES, 'profiles' => XSD_PATH_PROFILES, 'hosts' => XSD_PATH_HOSTS);
			if (isset($this->data['Import'])) {
				while (current($files) !== false) {
					$type = key($files);
					$xsdFileName = current($files);
					$xmlFileName = $this->data['Import'][$type]['tmp_name'];
					if ($this->isUploadedFile($this->data['Import'][$type]) === true) {
						$result = WPKGImporter::getInstance()->validateXML($xmlFileName, $xsdFileName);
						if ($result === true) {
							$result = WPKGImporter::getInstance()->{'import' . ucwords($type)}($xmlFileName);
							$msg .= $this->element('importResults', array('data' => $result, 'type' => strtolower($type)));
						} else
							$msg .= $this->element('importValidateMessages', array('data' => $result, 'type' => strtolower($type)));
					}
					next($files);
				}
			} else {
				$verboseTypes = array(
					'XMLFeed' => 'XML Feed',
					'Auth' => 'Authentication'
				);

				$type = ucwords(next(array_keys($this->data)));
				$installer->validate = $installer->getSettings($type);
				$installer->data = array('Installer' => $this->data[$type]);
				
				// Let the user leave any password field blank if they do not wish to change the existing password
				foreach ($installer->validate as $field => $v) {
					if (stripos($field, "password") !== false && empty($this->data[$type][$field])) {
						unset($this->data[$type][$field]);
						unset($installer->validate[$field]);
					}
				}

				if ($installer->validates()) {
					foreach ($installer->validate as $field => $v) {
						if (isset($installer->validate[$field]['savetype']))
							settype($this->data[$type][$field], $installer->validate[$field]['savetype']);

						if (stripos($field, "password") !== false)
	 						$this->data[$type][$field] = $this->__hashPwd($this->data[$type][$field]);
						
						$this->Configuration->write("$type.$field", $this->data[$type][$field]);
					}
					$this->Configuration->save();
					$msg = (isset($verboseTypes[$type]) ? $verboseTypes[$type] : $type) . " settings saved";
					$errors = array();
				} else
					$errors = $installer->validationErrors;
					
				$this->set($type . 'Errs', $errors);
			}
			$this->Session->setFlash((empty($msg) ? 'An error occurred while processing your input' : $msg));
		}

		$this->data = $installer->getSaved($this->Configuration);
		foreach ($this->data as $t => $aVals) {
			foreach ($aVals as $k => $v) {
				if (stripos($k, "password") !== false)
					unset($this->data[$t][$k]);
			}
		}
	}
}
?>