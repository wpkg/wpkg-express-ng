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
include_once('../wpkg_constants.php');
App::import('Sanitize');

class ProfilesController extends AppController {
	var $name = 'Profiles';
	var $layout = 'main';
	var $components = array('Session', 'RequestHandler');
	var $helpers = array('Html', 'Form', 'Navigate', 'Javascript', 'Xmlpp');
	var $paginate = array('limit' => 25,
			      'page' => 1,
			      'fields'=> array(
					  'Profile.id',
					  'Profile.enabled',
					  'Profile.id_text',
					  'Profile.modified'
			       ),
			       'order' => array('Profile.id_text' => 'asc'),
			       'recursive' => -1
	);

	function index() {
		if ($this->RequestHandler->isXml()) {
			$this->set('formatxml', $this->Configuration->read('XMLFeed.formatxml'));
			$this->set('exportdisabled', $exportdisabled = $this->Configuration->read('XMLFeed.exportdisabled'));
			$this->set('profiles', $this->Profile->getAllForXML(null, $exportdisabled));
		} else {
			$this->set('isAjax', $this->RequestHandler->isAjax());
			$this->set('profiles', $this->paginate());
		}
	}

	function view($id = null) {
		if ($id && ctype_digit($id)) {
			if (($profile = $this->Profile->get($id, true)) !== false) {
				if ($this->RequestHandler->isXml()) {
					$this->set('formatxml', $this->Configuration->read('XMLFeed.formatxml'));
					$this->set('exportdisabled', $exportdisabled = $this->Configuration->read('XMLFeed.exportdisabled'));
					$profile = $this->Profile->getAllForXML(null, $exportdisabled);
				}
				$this->set(compact('profile'));
				return;
			}
		}
		$this->Session->setFlash('Invalid Profile.');
		$this->redirect(array('action'=>'index'));
	}

	function add($type = null, $id = null) {
		$which = null;
		if ($type == null && $id == null)
			$which = "profile";
		else if ($id != null && ctype_digit($id) && $id > 0 && $type == "package")
			$which = "package";
		else {
			$this->Session->setFlash('Invalid Internal Command.');
			$this->redirect(array('action'=>'index'));
		}
		if (!empty($this->data)) {
			$success = false;
			switch ($which) {
				case "package":
					$this->Profile->habtmDeleteAll('Package', $id);
					$success = $this->Profile->habtmAdd('Package', $id, $this->data['Profile']['Package']);
					break;
				case "profile":
					$this->Profile->create(false);
					$success = $this->Profile->save($this->data);
					$id = $this->Profile->getLastInsertID();
					break;
				default:
					$this->Session->setFlash('An Error Occurred While Processing Your Input.');
					$this->redirect(array('action'=>'index'));
			}
			if ($success) {
				$this->Session->setFlash('The Profile has been saved.');
				$this->redirect(array('action'=>'view', $id));
			} else
				$this->Session->setFlash('The Profile could not be saved. Please, try again.');
		}
		if ($which == "package") {
			$packages = $this->Profile->Package->getAll();
			$profile = $this->Profile->get($id, false, array('id', 'id_text'), array('hasAndBelongsToMany' => array('ProfileDependency')));
			$selected = Set::combine($profile['Package'], array('{0} ({1})', '{n}.name', '{n}.id_text'), '{n}.id');
			$packages = Set::combine($packages, '{n}.Package.id', array('{0} ({1})', '{n}.Package.name', '{n}.Package.id_text'));
			$profileIDText = $profile['Profile']['id_text'];
			$profileID = $id;
			
			if (empty($packages)) {
				$this->Session->setFlash("There are no Packages to add/remove");
				$this->redirect(array('action' => 'view', $profileID));
			}

			$this->set(compact('packages', 'profileIDText' , 'profileID', 'selected'));
			$this->autoRender = false;
			$this->render(null, "main", "package-add");
		} else if ($which == "profile")
			$this->set('profileDependencies', $this->Profile->ProfileDependency->getList(null, true));
	}

	function edit($id = null) {
		if (!$id || !ctype_digit($id)) {
			$this->Session->setFlash('Invalid Profile.');
			$this->redirect(array('action'=>'index'));
		}
		if (!empty($this->data)) {
			$message = "";
			$this->data['Profile']['id'] = $this->Profile->id = $id;
			// Check to make sure we are allowed to disable this profile
			if (($this->Profile->field('enabled') == 1) && ($this->data['Profile']['enabled'] == 0) && ($msg = $this->_canDisable($id)) !== true) {
				$message = $msg;
				unset($this->data['Profile']['enabled']);
			}
			if ($this->Profile->save($this->data)) {
				$this->Session->setFlash((!empty($message) ? "$message<br />" : '') . 'The Profile has been saved.');
				$this->redirect(array('action'=>'view', $id));
			} else {
				$this->Session->setFlash((!empty($message) ? "$message<br />" : '') . 'The Profile could not be saved. Please, try again.');
				if (in_array('id_text', array_keys($this->Profile->validationErrors)))
					$name = $this->Profile->field('id_text');
			}
		} else {
			$this->data = $this->Profile->get($id, false, null, array('hasAndBelongsToMany' => array('Package')));
			$name = $this->data['Profile']['id_text'];
		}
		$this->set(compact('name'));
		$this->set('profileDependencies', $this->Profile->ProfileDependency->getList(array('ProfileDependency.id <>' => $id), true));
	}

	function delete($type = null, $id = null) {
		$msg = "Invalid Internal Command";
		if ($type != null && $id == null && ctype_digit($type) && $type > 0) {
			// deleting a profile: $type = profile id
			$id = $type;
			if (($msg = $this->_canDisable($id)) === true) {
				if ($this->Profile->delete($type)) {
					$this->Session->setFlash('Profile deleted.');
					$redirect = true;
				} else
					$this->Session->setFlash('The Profile could not be deleted. Please, try again.');
			}
			if ($this->RequestHandler->isAjax()) {
				$this->autoRender = true;
				$this->autoLayout = true;
				return $this->setAction('index');
			} else if (isset($redirect))
				$this->redirect(array('action'=>'index'));
		} else if ($type != null && $id != null && ctype_digit($type) && ctype_digit($id) && $type > 0 && $id > 0) {
			// deleting a package association with a profile: $type = profile id, $id = associated package id
			$this->Profile->Package->id = $id;
			$name = $this->Profile->Package->field('name');
			if ($this->Profile->habtmDelete('Package', $type, $id)) {
				if ($this->RequestHandler->isAjax()) {
					$packages = $this->Profile->getAssocPackages($type);
					$profile = array('Profile' => $packages['Profile']);
					$packages = $packages['Package'];
					$this->set(compact('packages', 'profile'));
					$this->render('/elements/profilepackages', 'plain');
					return;
				} else {
					$this->Session->setFlash("Profile's association with Package '$name' deleted.");
					$this->redirect(array('action'=>'view', $type));
				}
			} else
				$this->Session->setFlash("The Profile's association with Package '$name' could not be deleted.");
		}// else {
			$this->Session->setFlash($msg);
			$this->redirect(array('action'=>'index'));
		//}
	}

	function enable($id = null) {
		$isAjax = $this->RequestHandler->isAjax();
		if ($id == null || !ctype_digit($id))
			$msg = 'Invalid Profile';
		else {
			$this->Profile->id = $id;
			if ($this->Profile->saveField('enabled', true) === false)
				$msg = 'Invalid Profile';
		}
		if (isset($msg) && !$isAjax)
				$this->Session->setFlash($msg);
		if ($isAjax) {
			$this->set('data', array('success' => !isset($msg), 'message' => (isset($msg) && !is_bool($msg) ? $msg : "")));
			$this->render('/elements/json', 'plain');
		} else
			$this->redirect(array('action'=>'index'));
	}

	function disable($id = null) {
		$isAjax = $this->RequestHandler->isAjax();
		if ($id == null || !ctype_digit($id))
			$msg = 'Invalid Profile';
		else {
			if (($msg = $this->_canDisable($id)) === true) {
				if ($this->Profile->saveField('enabled', false) === false)
					$msg = 'Invalid Profile';
			}
		}
		if (isset($msg) && !is_bool($msg) && !$isAjax)
				$this->Session->setFlash($msg);
		if ($isAjax) {
			$this->set('data', array('success' => !isset($msg) || (isset($msg) && $msg === true), 'message' => (isset($msg) && !is_bool($msg) ? $msg : "")));
			$this->render('/elements/json', 'plain');
		} else
			$this->redirect(array('action'=>'index'));
	}

	function _canDisable($id) {
		// Find any hosts that use this profile as their main profile
		$hosts = ClassRegistry::init('Host')->find('all', array('fields' => array('Host.id', 'Host.name'), 'conditions' => array('Host.mainprofile_id' => $id), 'order' => 'Host.name'));
		// Below will add any other hosts that use this profile (not as their main profile).
		// If uncommenting, don't forget to also change the message returned below from 'is the main profile' to 'is a profile'
		//$hosts = array_merge($hosts, ClassRegistry::init('Host')->find('all', array('conditions' => array('HostProfile.profile_id' => $id, 'Host.enabled' => true), 'fields' => array('Host.id', 'Host.name'), 'order' => 'Host.name', 'joins' => array(array('table' => 'hosts_profiles', 'alias' => 'HostProfile', 'foreignKey' => false, 'conditions' => 'HostProfile.host_id = Host.id')), 'recursive' => -1)));
		$this->Profile->id = $id;
		if (is_array($hosts) && count($hosts) > 0)
			return "The Profile '" . $this->Profile->field('id_text') . "' cannot be deleted or disabled because it is the main profile for the following host(s): " . $this->element('RecordInUseByLinks', array('records' => $hosts, 'field' => 'name'));
		else
			return true;
	}

}
?>
