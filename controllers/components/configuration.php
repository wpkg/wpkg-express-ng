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
class ConfigurationComponent extends Object {

	var $name = 'Configuration';

	// the base filename used when loading/saving
	var $__basename = 'wpkgExpress';

	// the namespace to use to distinguish this app's settings from the rest of CakePHP's
	var $__namespace = 'wpkgExpress';

	// autosave on shutdown (if modified)
	var $__autosave = true;

	// last hash of config
	var $__cfghash = null;

	// called before Controller::beforeFilter()
	function initialize(&$controller, $settings = array()) {
		if (!empty($settings['filename']))
			$this->__basename = $settings['filename'];
		if (!empty($settings['namespace']))
			$this->__namespace = $settings['namespace'];
		if (!empty($settings['autosave']))
			$this->__autosave = (bool)$settings['autosave'];

		$this->__filename = CONFIGS . $this->__basename . ".php";
		if (file_exists($this->__filename)) {
			include_once($this->__filename);
			if (isset($config))
				if (Configure::load($this->__basename) !== false)
					$this->__cfghash = md5($this->__getConfig());
		}
	}

	// called after Controller::beforeFilter()
	function startup(&$controller) {}

	// called after Controller::beforeRender()
	function beforeRender(&$controller) {
		if ($this->__autosave)
			$this->save();
	}

	// called after Controller::render()
	function shutdown(&$controller) {}

	// called before Controller::redirect()
	function beforeRedirect(&$controller, $url, $status=null, $exit=true) {
		if ($this->__autosave)
			$this->save();
	}

	// frontend to Configure::write
	function write($config, $value = null) {
		if (!is_array($config))
			$config = array($config => $value);
		foreach ($config as $names => $value) {
			unset($config[$names]);
			$config[$this->__namespace . "." . $names] = $value;
		}
		Configure::write($config, $value);
	}

	// frontend to Configure::read
	function read($var) {
		if (!file_exists($this->__filename))
			return null;
		return Configure::read($this->__namespace . "." . $var);
	}

	// frontend to Configure::delete
	function delete($var = null) {
		if ($var == null)
			$var = $this->__namespace;
		else
			$var = $this->__namespace . "." . $var;

		return Configure::delete($var);
	}

	// saves the entire current configuration to disk (if config has been modified)
	function save() {
		$curCfg = $this->__getConfig();
		if ($this->__cfghash == md5($curCfg))
			return true;
		$fh = fopen($this->__filename, "wb");
		if ($fh === false)
			return false;
		if (fwrite($fh, "<?php" . PHP_EOL . $curCfg . "?>") === false)
			return false;
		if (fclose($fh)) {
			$this->__cfghash = md5($curCfg);
			return true;
		} else
			return false;
	}

	function __getConfig() {
		$cfgInst =& Configure::getInstance();
		$content = '';
		if (!isset($cfgInst->{$this->__namespace}))
			return $content;
		$config = $cfgInst->{$this->__namespace};
		foreach ($config as $key => $value) {
			$content .= "\$config['" . $this->__namespace . "']['$key']";
			if (is_array($value)) {
				$content .= " = array(";
				foreach ($value as $key1 => $value2) {
					$value2 = addslashes($value2);
					if (is_bool($value2) && $value2 === true)
						$value2 = 'true';
					else if (is_bool($value2) && $value2 === false)
						$value2 = 'false';
					else if (is_numeric($value2))
						$value2 = $value2;
					else
						$value2 = "'$value2'";
					$content .= "'$key1' => $value2, ";
				}
				$content = substr($content, 0, -2);
				$content .= ");" . PHP_EOL;
			} else {
				$value = addslashes($value);
				if (is_bool($value) && $value === true)
					$value = 'true';
				else if (is_bool($value) && $value === false)
					$value = 'false';
				else if (is_numeric($value))
					$value = $value;
				else
					$value = "'$value'";
				$content .= " = $value;" . PHP_EOL;
			}
		}
		return $content;
	}
}
?>