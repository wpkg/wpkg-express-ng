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
class Installer extends AppModel {

	var $name = 'Installer';
	var $useTable = false;
	var $_schema = array(
		'driver' => array('type' => 'string'),
		'persistent' => array('type' => 'boolean'),
		'database' => array('type' => 'string'),
		'host' => array('type' => 'string'),
		'port' => array('type' => 'integer'),
		'login' => array('type' => 'string'),
		'password' => array('type' => 'string'),
		'user' => array('type' => 'string'),
		'secure' => array('type' => 'boolean'),
		'exportdisabled' => array('type' => 'boolean'),
		'protectxml' => array('type' => 'boolean'),
		'xmlpassword' => array('type' => 'string'),
		'xmluser' => array('type' => 'string'),
		'formatxml' => array('type' => 'boolean')
	);
	var $validate = array(
		array(
			'driver' => array(
				'rule' => 'valid_driver',
				'required' => true,
				'allowEmpty' => false
			),
			'persistent' => array(
				'rule' => 'valid_bool',
				'required' => true,
				'allowEmpty' => false
			),
			'database' => array(
				'rule' => 'notEmpty',
				'required' => true,
				'allowEmpty' => false
			),
			'host' => array(
				'rule' => 'host_ok',
				'required' => true,
				'message' => 'This field must contain a valid host',
			),
			'port' => array(
				'rule' => 'int_ok',
				'required' => false,
				'allowEmpty' => true,
				'message' => 'This field must contain a valid positive integer'
			),
			'login' => array(
				'rule' => 'valid_string',
				'required' => false,
				'allowEmpty' => true
			),
			'password' => array(
				'rule' => 'valid_string',
				'required' => false,
				'allowEmpty' => true
			)
		),
		array(
			'user' => array(
				'rule' => 'notEmpty',
				'required' => true,
				'allowEmpty' => false,
				'message' => 'You must enter a username',
				'type' => 'Auth'
			),
			'password' => array(
				'empty' => array(
					'rule' => 'notEmpty',
					'required' => true,
					'allowEmpty' => false,
					'last' => true,
					'message' => 'You must enter a password',
					'type' => 'Auth'
				),
				'minlength' => array(
					'rule' => array('minLength', 5),
					'required' => true,
					'allowEmpty' => false,
					'last' => true,
					'message' => 'Password length must be geater than 5 characters'
				),
				'maxlength' => array(
					'rule' => array('maxLength', 15),
					'required' => true,
					'allowEmpty' => false,
					'last' => true,
					'message' => 'Password length must be less than 15 characters'
				)
			),
			'secure' => array(
				'rule' => 'valid_bool',
				'required' => true,
				'message' => 'Invalid choice',
				'savetype' => 'int',
				'type' => 'System'
			),
			'protectxml' => array(
				'rule' => 'valid_bool',
				'required' => true,
				'message' => 'Invalid choice',
				'savetype' => 'int',
				'type' => 'XMLFeed'
			),
			'xmluser' => array(
				'rule' => 'valid_xmluser',
				'message' => 'You must enter a username',
				'type' => 'XMLFeed'
			),
			'xmlpassword' => array(
				'rule' => 'valid_xmlpwd',
				'message' => 'Password length must be between 3 and 15 characters',
				'type' => 'XMLFeed'
			),
			'exportdisabled' => array(
				'rule' => 'valid_bool',
				'required' => true,
				'message' => 'Invalid choice',
				'savetype' => 'int',
				'type' => 'XMLFeed'
			),
			'formatxml' => array(
				'rule' => 'valid_bool',
				'required' => true,
				'message' => 'Invalid choice',
				'savetype' => 'int',
				'type' => 'XMLFeed'
			)
		),
		array(
			'user' => array(
				'rule' => 'notEmpty',
				'required' => true,
				'allowEmpty' => false,
				'message' => 'You must enter a username'
			),
			'password' => array(
				'rule' => 'notEmpty',
				'required' => true,
				'allowEmpty' => false,
				'last' => true,
				'message' => 'You must enter a password'
			)
		)
	);
	
	var $configSections = array(
		'Auth' => 'Auth',
		'System' => 'System',
		'XMLFeed' => 'XMLFeed'
	);
	
	function getSettings($type = false) {
		$settings = array();
		$rules = (isset($this->validate[1]) ? $this->validate[1] : $this->validate);
		foreach ($rules as $field => $v) {
			if (!isset($v['type'])) {
				$vfirst = current($v);
				$fieldtype = $vfirst['type'];
			} else
				$fieldtype = $v['type'];
			if (($type === false) || ($type === true) || (strtoupper($type) == strtoupper($fieldtype))) {
				if ($type === true)
					$a = array($field);
				else
					$a = array($field => $v);
				if ($type === false || $type === true)
					$a = array($fieldtype => $a);
				$settings = array_merge_recursive($settings, $a);
			}
		}
		return $settings;
	}

	function getSaved(&$config, $type = false) {
		if ($type === false)
			$saved = $this->arrayMap(array($config, 'read'), $this->configSections);
		else
			$saved = $config->read($type);
		if (isset($saved['General']));
			unset($saved['General']);
		return $saved;
	}

	function step($step) {
		$this->validate = $this->validate[$step-1];
	}
	
	function valid_xmluser($data) {
		if ((bool)$this->data['Installer']['protectxml'] == true)
			return trim(array_shift($data)) != "";
		else
			return true;
	}
	
	function valid_xmlpwd($data) {
		if ((bool)$this->data['Installer']['protectxml'] == true) {
			$data = trim(array_shift($data));
			if (strlen($data) < 4 || strlen($data) > 14)
				return false;
			else
				return true;
		} else
			return true;
	}

	function valid_driver($data) {
		$data = array_shift($data);
		return (in_array($data, $this->getDBDrivers()));
	}

	function valid_bool($data) {
		$data = array_shift($data);
		return (in_array($data, array(true, false)));
	}

	function int_ok($data) {
		$data = array_shift($data);
		return ($data !== true) && ((string)(int)$data) === ((string)$data) && (int)$data > 0;
	}

	function host_ok($data) {
		$data = trim(array_shift($data));
		// easy way out
		return (empty($data) || gethostbynamel($data) !== false);
	}
	
	function valid_string($data) {
		return true;
	}

	function writeSalt() {
		/*if ($fh = fopen(CONFIGS . 'core.php', 'r')) {
			$cfg = '';
			while (!feof($fh))
				$cfg .= fgets($fh, 4096);
			fclose($fh);
			$salt = sha1(uniqid(mt_rand(), true));
			$cfg = preg_replace("/Configure::write\('Security\.salt', '(.*?)'\);/", "Configure::write('Security.salt', '$salt');", $cfg);
			$fh = fopen(CONFIGS . 'core.php', 'w');
			fwrite($fh, $cfg);
			fclose($fh);
			return true;
		}
		return false;*/
		if (($cfg = file_get_contents(CONFIGS . 'core.php')) !== false) {
			preg_match("/Configure::write\('Security\.salt', '(.*?)'\);/", $cfg, $matches);
			// only insert a new salt if there currently is no salt
			if (empty($matches[1])) {
				$salt = sha1(uniqid(mt_rand(), true));
				$cfg = preg_replace("/Configure::write\('Security\.salt', '(.*?)'\);/", "Configure::write('Security.salt', '$salt');", $cfg);
				if (file_put_contents(CONFIGS . 'core.php', $cfg) !== false)
					return true;
			}
		}
		return false;
	}

	function getDBDrivers() {
		$drivers = array();
		$dbo_path = APP . LIBS . "model" . DS . "datasources" . DS . "dbo";
		$files = Configure::listObjects('file', $dbo_path, false);
		foreach ($files as $fname) {
			$name = substr(substr($fname, strpos($fname, "_") + 1), 0, -4);
			$drivers[$name] = $name;
		}		
		return $drivers;
	}

	function checkTables() {
		if ($fh = fopen(CONFIGS . 'sql' . DS . 'wpkgExpress.sql', 'r')) {
			$schemadata = '';
			while (!feof($fh))
				$schemadata .= fgets($fh, 4096);
			$schemadata = trim($schemadata);
			fclose($fh);
			$this->schemadata = $schemadata;

			if (!preg_match_all("/DROP TABLE IF EXISTS `(.+)`;/", $schemadata, $tables))
				return "Could not find any database tables to check";
			else {
				$db = ConnectionManager::getDataSource('default');
				$tables = $tables[1];
				$notfound = array();
				/*$errors = array();
				foreach ($tables as $table) {
					$db->query("SELECT COUNT(*) FROM $table WHERE 1=1");
					if ($db->error != null) {
						$errors[$table] = $db->error;
						$db->error = null;
					}
				}*/
				$diff = array_diff($tables, $db->listSources());
				foreach ($diff as $v)
					$notfound[$v] = null;
				return (!empty($notfound) ? $notfound : true); //(!empty($errors) ? $errors : true);
			}
		} else
			return "Could not open database schema file for reading";
	}

	function getCreateTableSQL($tables, $dbconf) {
		$tableschemas = array();
		if (!isset($this->schemadata))
			return false;
		$single = (!is_array($tables));
		if ($single)
			$tables = array($tables);
		foreach ($tables as $table) {
			preg_match("/CREATE TABLE `$table` \((.+?)\);/s", $this->schemadata, $match);
			$tableschemas[$table] = substr($match[0], 0, -1);
			if (strpos($dbconf['default']['driver'], 'sqlite') === 0) {
				$tableschemas[$table] = preg_replace('/` (.*?)int\(/i', '` INTEGER(', $tableschemas[$table]);
				$tableschemas[$table] = str_ireplace("`", "\"", $tableschemas[$table]);
				$tableschemas[$table] = preg_replace('/,(.+?)PRIMARY KEY(.+?)\((.+?)\)/i', '', $tableschemas[$table]);
				$tableschemas[$table] = preg_replace('/INTEGER\((\d+?)\) NOT NULL AUTO_INCREMENT/i', 'INTEGER PRIMARY KEY', $tableschemas[$table]);
				$tableschemas[$table] = preg_replace('/UNIQUE KEY (.+?) \((.+?)\)/i', 'UNIQUE (\1)', $tableschemas[$table]);
			}
		}
		return ($single ? current($tableschemas) : $tableschemas);
	}

}
?>