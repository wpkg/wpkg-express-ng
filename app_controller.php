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
class AppController extends Controller {
	var $components = array('Security', 'Session', 'Configuration', 'RequestHandler');
	var $helpers = array('Html', 'Form', 'Session');
	var $_cancelAction = false;

	function beforeFilter() {
		if ($this->Configuration->read('System.secure') == true && !$this->RequestHandler->isSSL())
			$this->__forceSSL();
		if ($this->RequestHandler->isAjax() || $this->RequestHandler->isXml())
			Configure::write('debug', 0);
		if ($this->name != 'Installer' && !$this->Session->check('loggedIn')) {
			if ($this->RequestHandler->isXml()) {
				if ($this->Configuration->read('Auth.protectxml') == true) {
					$this->Security->loginOptions = array(
						'type'=>'basic',
						'login'=>'authHTTP',
						'realm'=>'wpkgExpress XML Export'
					);
					$this->Security->blackHoleCallback = null;
					//$this->Security->loginUsers = array();
					$this->Security->requireLogin();
				}
			} else {
				$url = $this->_getRequestedURL();

				if (!empty($this->data) && isset($this->data[Inflector::singularize($this->name)]['user']) && isset($this->data[Inflector::singularize($this->name)]['password'])) {
					$data = array_pop($this->data);
					$logvalid =& ClassRegistry::init('Installer');
					$logvalid->data = array('Installer' => $data);
					$logvalid->step(3);
					if ($logvalid->validates()) {
						if (!$this->auth($data['user'], $data['password']))
							$this->set('criticalerrors', array('Incorrect username or password'));
						else {
							$this->Session->write('loggedIn', true);
							return;
						}
					}
				}
				$this->set('url', $url);
				$this->set('dest', Inflector::singularize($this->name));
				$this->layout = 'login';
				$this->autoRender = false;
				$this->render(ELEMENTS . 'login.ctp');
				$this->_cancelAction = true;
			}
		}
	}
	
	function beforeRender() {
		// Default search type
		$this->set('curType', (in_array($this->name, array('Packages', 'Profiles', 'Hosts')) ? strtolower($this->name) : 'all'));
	}

	/* Generate a CakePHP url array containing the originally requested url -- useful for knowing where to redirect after login */
	function _getRequestedURL() {
		$params = $this->params['pass'];
		$namedparams = array();
		foreach ($this->params['named'] as $k => $v)
			$namedparams[] = "$k:$v";
		$params = (!empty($params) ? implode("/", $params) : "") . (!empty($namedparams) ? "/" . implode("/", $namedparams) : "");
		if (empty($params))
			$url = array('url' => array('controller' => strtolower($this->name), 'action' => $this->action));
		else
			$url = array('url' => array('controller' => strtolower($this->name), 'action' => $this->action, $params));
		return $url;
	}
	
	function dispatchMethod($method, $params = array()) {
		if ($this->_cancelAction === true)
			return false;
		return parent::dispatchMethod($method, $params);
	}

	function __forceSSL() {
		$url = 'https://' . (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW'] . '@' : '') . $_SERVER['SERVER_NAME'] . ($_SERVER['SERVER_PORT'] != 80 ? ':' . $_SERVER['SERVER_PORT'] : '') . $_SERVER['REQUEST_URI'];
		$this->redirect($url);
	}

	function authHTTP($args) {
		$valid = $this->auth($args['username'], $args['password'], true);
		if (!$valid)
			$this->Security->blackHole($this, 'login');
		return $valid;
	}

	/* Authenticate either web or XML access credentials */
	function auth($user, $pwd, $isXML = false) {
		if ($this->Configuration->read(($isXML == true ? 'XMLFeed' : 'Auth') . '.' . ($isXML == true ? 'xml' : '') . 'user') == $user) {
			if ($this->Configuration->read(($isXML == true ? 'XMLFeed' : 'Auth') . '.' . ($isXML == true ? 'xml' : '') . 'password') == $this->__hashPwd($pwd))
				return true;
		}
		return false;
	}

	/* Hashes the password using the previously auto-generated salt */
	function __hashPwd($pwd) {
		$textHash = sha1($pwd);
		$saltHash = Configure::read('Security.salt');
		$saltStart = strlen($pwd);
	    if($saltStart > 0 && $saltStart < strlen($saltHash)) {
			$textHashStart = substr($textHash,0,$saltStart);
			$textHashEnd = substr($textHash,$saltStart,strlen($saltHash));
			$outHash = sha1($textHashEnd.$saltHash.$textHashStart);
	    } elseif($saltStart > (strlen($saltHash)-1))
			$outHash = sha1($textHash.$saltHash);
	    else
			$outHash = sha1($saltHash.$textHash);
	    return ($saltHash.$outHash);
	}

	/* Catches all site errors, including HTTP errors */
	function appError($method, $params) {
		if (is_array($params) && is_array($params[0]) && !empty($params[0]['url']) && strtolower($params[0]['url']) == 'logout') { // && $this->Session->check('loggedIn')) {
			$this->Session->delete('loggedIn');
			$this->Session->setFlash('Logged out successfully.');
			$this->redirect("/");
			return;
		} else if (is_array($params) && isset($params[0]['className']) && $params[0]['className'] == 'ConnectionManager' && !file_exists(APP . "do_not_remove")) {
			$this->redirect(array('controller' => 'installer'));
			return;
		} else if (!file_exists(APP . "do_not_remove")) {
            $this->redirect(array('controller' => 'installer'));
            return;
        }

		echo "<h3>Unrecoverable error:</h3><br />Error details dump:<br />\$method = ";
		var_dump($method);
		echo "<br />\$params = ";
		var_dump($params);
		exit;
	}
	
	/* Taken from: http://book.cakephp.org/view/548/Validating-Uploads */
	function isUploadedFile($val){
		if ((isset($val['error']) && $val['error'] == 0) || (!empty($val['tmp_name']) && $val['tmp_name'] != 'none'))
			return is_uploaded_file($val['tmp_name']);
		else
			return false;
	} 
	
	/* Used to satisfy ajax requests -- allows element rendering only (i.e. no meddling with current action rendering settings) */
	function element($name, $data, $extraHelpers = array()) {
		$v = new View($this);
		$v->layout = 'plain';
		$v->helpers = array('Html') + $extraHelpers;
		return $v->element($name, $data, true);
	}
	
	function array_compare($array1, $array2) {
		$diff = array();
		foreach ($array1 as $key => $value) {
			if (!array_key_exists($key,$array2))
				$diff[0][$key] = $value;
			else if (is_array($value)) {
				if (!is_array($array2[$key])) {
					$diff[0][$key] = $value;
					$diff[1][$key] = $array2[$key];
				} else {
					$new = $this->array_compare($value, $array2[$key]);
					if (!empty($new)) {
						if (isset($new[0]))
							$diff[0][$key] = $new[0];
						if (isset($new[1]))
							$diff[1][$key] = $new[1];
					}
				}
			} else if ($array2[$key] !== $value) {
				 $diff[0][$key] = $value;
				 $diff[1][$key] = $array2[$key];
			}
		}
		foreach ($array2 as $key => $value) {
			if (!array_key_exists($key,$array1))
				$diff[1][$key] = $value;
		}
		
		return $diff;
	}
	
	function arrayFlip($array) {
		if (empty($array))
			return (array)$array;
		foreach ((array)$array as $k => $v) {
			if (is_array($v)) {
				$array[$k] = $this->arrayFlip($v);
			} else {
				$array[] = $k;
				unset($array[$k]);
			}
		}
		return (array)$array;
	}
	
	function extractChildren($data) {
		if (empty($data))
			return (array)$data;
		else
			return array_combine(array_map(create_function('$key', 'return substr($key, strrpos($key, ".") + 1);'), array_keys(Set::flatten($data))), array_values(Set::flatten($data)));
	}
}
?>
