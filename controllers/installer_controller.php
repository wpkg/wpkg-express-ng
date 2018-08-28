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
class InstallerController extends AppController {
	var $name = 'Installer';
	var $layout = 'installer';
	var $uses = array('Installer');
	var $components = array('Configuration', 'Session');
	var $helpers = array('Html');

	function __getSteps($var) {
		return strpos($var, 'setup_') !== false;
	}
	
	function checkPreReqs() {
		/* Require PHP5 for now for a couple of reasons:
			 1. CakePHP has (as of this writing) been slowly transitioning the framework to PHP5 or newer only.
			 2. The XSD validation method uses PHP5's schemaValidate() which does not exist in PHP4. Initially
			    I had relied on using external utilities (namely xmllint for Windows and Linux) for performing
				validation for both PHP4 and PHP5, but decided relying on making "system" calls to third party
				utilities such as this wasn't a very good solution.
		*/
		if (version_compare(PHP_VERSION, '5.0.2', '<')) {
			echo "<center><h2>wpkgExpress requires PHP 5.0.2 or newer.</h2></center>";
			exit;
		}
	}
	
	function beforeFilter() {
		$this->Security->blackHoleCallback = 'bypass';
		// Do not continue if there is an indication of a previous successful installation
		if (file_exists(APP . 'do_not_remove'))
			$this->redirect(array('controller' => 'home', 'action' => 'index'));

		$this->checkPreReqs();
		
		if (!empty($this->data))
			$this->Installer->data = $this->data;

		// auto-calculate the total number of steps using some convention magic
		$steps = count(array_filter(get_class_methods('InstallerController'), array($this, "__getSteps")));

		$this->action = strtolower($this->action);
		switch($this->action) {
			case 'setup_config':
				$this->pageTitle = "Configuration";
				$step = 2;
				break;
			case 'thanks':
				$this->pageTitle = "Installation Complete!";
				return;
			case 'setup_db':
			default:
				$this->action = "setup_db";
				$this->pageTitle = "Database Setup";
				$step = 1;
				break;
		}

		// set the right set of form validation rules for the current step
		$this->Installer->step($step);

		$this->pageTitle .= " [$step/$steps]";
		$this->set('submit', ($step < $steps ? 'Next >>' : 'Finish'));
		$this->set('url', array('url' => array('controller' => 'installer', 'action' => $this->action)));
		$this->set(compact('step', 'steps'));
	}
	
	function bypass($error) {
		return true;
	}

	function setup_db() {
		$criticalerrors = array();
		$continue = false;
		
		// Check to see if we've generated a random salt yet, if not, do so
		if ($this->Configuration->read('General.salted') === null || $this->Configuration->read('General.salted') == false) {
			$this->Configuration->write('General.salted', true);
			$this->Installer->writeSalt();
		}
		if ($this->Configuration->read('General.ciphered') === null || $this->Configuration->read('General.ciphered') == false) {
            $this->Configuration->write('General.ciphered', true);
            $this->Installer->writeCipher();
        }

		$dbconf = $this->__getDBConfig();
		if (empty($dbconf) || empty($dbconf['default'])) {
			if (!empty($this->data) && $this->Installer->validates()) {
				// Remove any submitted form elements not originally on the form
				/*foreach (array_keys($this->data['Installer']) as $k => $v) {
					if (!in_array($k, array_keys($this->Installer->validate)))
						unset($this->data['Installer'][$k]);
				}*/

				// TODO: use installer schema as a basis for (all) converting variable types (i.e. '0' and '1' to boolean where appropriate)
				$this->data['Installer']['persistent'] = (bool)(int)$this->data['Installer']['persistent'];

				// Render and write the database config file using the submitted information
				//$this->set('dbconf', $this->data['Installer']);
				$conf = $this->element('installer-dbconf', array('dbconf' => $this->data['Installer']));
				if ($fh = fopen(CONFIGS . 'database.php', 'w')) {
					if (fwrite($fh, $conf) === FALSE)
						$criticalerrors[] = "Could not write to database config file";
					if (!fclose($fh))
						$criticalerrors[] = "Could not save the database config file";
				} else
					$criticalerrors[] = "Could not open database config file for writing";

				if (empty($criticalerrors))
					$this->redirect(array('action' => $this->action)); //return $this->setAction($this->action);
			}
		} else {
			App::Import('ConnectionManager');
			$db = ConnectionManager::getDataSource('default');
			if (!$db->isConnected()) {
				$criticalerrors[] = "Could not connect to the database";
				@unlink(CONFIGS . 'database.php');
			} else {
				if (($error = $this->Installer->checkTables()) !== true) {
					if (is_array($error)) {
						$sqlerror = false;
						foreach ($error as $table => $err) {
							$db->query($this->Installer->getCreateTableSQL($table, $dbconf));
							if ($db->error != null) {
								$criticalerrors[] = "Could not execute SQL. Reason: " . $db->error;
								$db->error = null;
								$sqlerror = true;
							}
						}
						if (!$sqlerror)
							$continue = true;
					} else if (is_string($error))
						$criticalerrors[] = $error;
				} else
					$continue = true;
			}
		}

		if (empty($criticalerrors) && $continue === true)
			$this->redirect(array('action' => 'setup_config'));
		else {
			$this->set(compact('criticalerrors'));
			if (empty($this->data))
				$this->data = array('Installer' => Set::filter(current($this->__getDBConfig())));
		}
		$this->set('drivers', $this->Installer->getDBDrivers());
	}

	function setup_config() {
		$criticalerror = "";
		$continue = true;
		$dovalidate = true;
		$settings = $this->Installer->getSettings(true);
		$savedSettings = $this->Installer->getSaved($this->Configuration);
		$complete = (count(array_diff($settings, $this->arrayFlip($savedSettings))) == 0);
		
		// Auto-populate the form with existing config options if a missing config option is found
		if (empty($this->data) && !$complete) {
			$dovalidate = false;
			$continue = false;
			foreach ($savedSettings as $type => $aVals)
				foreach ((array)$aVals as $k => $v)
					if (stripos($k, "password") === false)
						$this->data[$type][$k] = $this->Installer->data[$type][$k] = $v;
		}
		if (!empty($this->data) && $dovalidate && !$complete) {
			$securitydata = array(key($this->data) => array_shift($this->data));
			$data = $securitydata + array('Installer' => $this->extractChildren($this->data));
			if ($this->Installer->saveAll($data, array('validate' => 'only'))) {
				if (count(array_diff($this->extractChildren($settings), $this->extractChildren($this->arrayFlip($this->data)))) == 0) {
					foreach ($settings as $type => $aFields) {
						foreach ($aFields as $field) {
							if (isset($this->Installer->validate[$field]['savetype']))
								settype($this->data[$type][$field], $this->Installer->validate[$field]['savetype']);
							if (stripos($field, "password") !== false)
								$this->data[$type][$field] = $this->__hashPwd($this->data[$type][$field]);
							$this->Configuration->write("$type.$field", $this->data[$type][$field]);
						}
					}
					$this->Configuration->save();
				} else
					$criticalerror = "You must submit all information";
			} else
				$continue = false;
		}

		if (empty($criticalerror) && $continue === true)
			$this->redirect(array('action' => 'thanks'));
		else if (!empty($criticalerror))
			$this->set(compact('criticalerror'));
	}

	function thanks() {
		if (!file_exists(APP . '/do_not_remove'))
			file_put_contents(APP . '/do_not_remove', 'Remove me for fresh install');
	}

	function __getDBConfig() {
		$dbconf = array();
		if (file_exists(CONFIGS . 'database.php')) {
			require_once(CONFIGS . 'database.php');
			$dbconf = get_class_vars('DATABASE_CONFIG');
		}
		return $dbconf;
	}

}
?>
