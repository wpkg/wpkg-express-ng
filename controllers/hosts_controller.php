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

class HostsController extends AppController {

	var $name = 'Hosts';
	var $layout = 'main';
	var $components = array('Session', 'RequestHandler');
	var $helpers = array('Html', 'Form', 'Navigate', 'Javascript', 'Xmlpp');
	var $paginate = array('limit' => 25,
			      'page' => 1,
			      'fields'=> array(
					  'Host.id',
					  'Host.enabled', 
					  'Host.name', 
					  'Host.modified', 
					  'Host.position',
					  'MainProfile.id',
					  'MainProfile.id_text'
			       ), 
			       'order' => array('Host.position' => 'asc'), 
			       'recursive' => 0
	);
	
	function index() {
		if ($this->RequestHandler->isXml()) {
			$this->set('formatxml', $this->Configuration->read('XMLFeed.formatxml'));
			$this->set('exportdisabled', $exportdisabled = $this->Configuration->read('XMLFeed.exportdisabled'));
			$this->set('hosts', $this->Host->getAllForXML(null, $exportdisabled));
		} else {
			$this->paginate['contain'] = array('MainProfile');
			$this->set('isAjax', $this->RequestHandler->isAjax());
			$this->set('hosts', $this->paginate());
		}
	}

	function view($id = null) {
		if ($id && ctype_digit($id)) {
			if (($host = $this->Host->get($id)) !== false) {
				if ($this->RequestHandler->isXml()) {
					$this->set('formatxml', $this->Configuration->read('XMLFeed.formatxml'));
					$this->set('exportdisabled', $exportdisabled = $this->Configuration->read('XMLFeed.exportdisabled'));
					$host = $this->Host->getAllForXML(null, $exportdisabled);
				}
				$this->set(compact('host'));
				return;
			}
		}
		$this->Session->setFlash('Invalid Host.');
		$this->redirect(array('action'=>'index'));		
	}

	function add($type = null, $id = null) {
		$which = null;
		if ($type == null && $id == null)
			$which = "host";
		else if ($id != null && ctype_digit($id) && $id > 0 && $type == "profile")
			$which = "profile";
		else {
			$this->Session->setFlash('Invalid Internal Command.');
			$this->redirect(array('action'=>'index'));
		}
		if ($which == "host" && $this->Host->Profile->find('count') == 0) {
			$this->Session->setFlash("You must add at least one Profile first");
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			$success = false;
			switch ($which) {
				case "profile":
					$this->Host->habtmDeleteAll('Profile', $id);
					$success = $this->Host->habtmAdd('Profile', $id, $this->data['Host']['Profile']);
					break;
				case "host":
					$this->Host->create(false);
					$success = $this->Host->save($this->data);
					$id = $this->Host->getLastInsertID();
					break;
				default:
					$this->Session->setFlash('An Error Occurred While Processing Your Input.');
					$this->redirect(array('action'=>'index'));
			}
			if ($success) {
				$this->Session->setFlash('The Host has been saved');
				$this->redirect(array('action'=>'view', $id));
			} else
				$this->Session->setFlash('The Host could not be saved. Please, try again.');
		}
		if ($which == "profile") {
			$host = $this->Host->get($id, false);
			$hostName = $host['Host']['name'];
			$hostID = $id;

			$profiles = $this->Host->Profile->getList(array('Profile.id <>' => $host['MainProfile']['id']));
			$selected = Set::combine($host['Profile'], '{n}.id_text', '{n}.id');
			
			if (empty($profiles)) {
				$this->Session->setFlash("There are no other Profiles to add/remove");
				$this->redirect(array('action' => 'view', $hostID));
			}

			$this->set(compact('profiles', 'hostName' , 'hostID', 'selected'));
			$this->autoRender = false;
			$this->render(null, "main", "profile-add");
		} else if ($which == "host")
			$this->set('profiles', $this->Host->Profile->getList());
	}

	function edit($id = null) {
		if (!$id || !ctype_digit($id)) {
			$this->Session->setFlash('Invalid Host');
			$this->redirect(array('action'=>'index'));
		}
		if (!empty($this->data)) {
			$this->data['Host']['id'] = $id;
			if ($this->Host->save($this->data)) {
				$this->Session->setFlash('The Host has been saved');
				$this->redirect(array('action'=>'view', $id));
			} else
				$this->Session->setFlash('The Host could not be saved. Please, try again.');
			$selected = array($this->data['Host']['mainprofile_id']);
			$this->data['Profile'] = $this->Host->find('first', array('conditions' => array('Host.id' => $id), 'contain' => array('Profile.id')));
		} else {
			$this->data = $this->Host->get($id);
			$selected = array($this->data['MainProfile']['id']);
		}
		$profiles = $this->Host->Profile->getList(array('NOT' => array('Profile.id' => Set::extract($this->data['Profile'], '{n}.id'))));
		$this->set(compact('profiles', 'selected'));
	}

	function delete($type = null, $id = null) {
		if ($type != null && $id == null && ctype_digit($type) && $type > 0) {
			// deleting a host: $type = host id
			if ($this->Host->del($type)) {
				$this->Session->setFlash('Host deleted');
				if ($this->RequestHandler->isAjax()) {
					$this->autoRender = true;
					$this->autoLayout = true;
					return $this->setAction('index');
				} else
					$this->redirect(array('action'=>'index'));
			} else
				$this->Session->setFlash('The Host could not be deleted. Please, try again.');
		} else if ($type != null && $id != null && ctype_digit($type) && ctype_digit($id) && $type > 0 && $id > 0) {
			// deleting a profile association with a host: $type = host id, $id = associated profile id
			$this->Host->Profile->id = $id;
			$id_text = $this->Host->Profile->field('id_text');
			if ($this->Host->habtmDelete('Profile', $type, $id)) {
				if ($this->RequestHandler->isAjax()) {
					$profiles = $this->Host->getAssocProfiles($type);
					$host = array('Host' => $profiles['Host']);
					$profiles = $profiles['Profile'];
					$this->set(compact('profiles', 'host'));
					$this->render('/elements/hostprofiles', 'plain');
				} else {
					$this->Session->setFlash("Host's association with Profile '$id_text' deleted");
					$this->redirect(array('action'=>'view', $type));
				}
			} else
				$this->Session->setFlash("The Host's association with Profile '$id_text' could not be deleted. Please, try again.");
		} else {
			$this->Session->setFlash('Invalid Internal Command.');
			$this->redirect(array('action'=>'index'));
		}
	}

	function movetop($id = null) {
		$this->moveup($id, true);
	}

	function movebottom($id = null) {
		$this->movedown($id, true);
	}

	function moveup($id = null, $delta = null) {
	    if ($delta == null || (is_int($delta) && $delta < 0))
			$delta = 1;
		if ($id == null)
			$this->Session->setFlash('Please provide the ID of the Host to be moved');
		else {
			$host = $this->Host->get($id, false);
			if (empty($host))
				$this->Session->setFlash("There is no Host with an ID of $id");
		    else
				$this->Host->moveUp($id, $delta);
		}
		if ($this->RequestHandler->isAjax()) {
			$this->autoRender = true;
			$this->autoLayout = true;
			return $this->setAction('index');
		} else
			$this->redirect(array('action'=>'index'));
	}

	function movedown($id = null, $delta = null) {
	    if ($delta == null || (is_int($delta) && $delta < 0))
			$delta = 1;
		if ($id == null)
			$this->Session->setFlash('Please provide the ID of the Host to be moved');
		else {
			$host = $this->Host->get($id, false);
			if (empty($host))
				$this->Session->setFlash("There is no Host with an ID of $id");
		    else
				$this->Host->moveDown($id, $delta);
		}
		if ($this->RequestHandler->isAjax()) {
			$this->autoRender = true;
			$this->autoLayout = true;
			return $this->setAction('index');
		} else
			$this->redirect(array('action'=>'index'));
	}

	function enable($id = null) {
		$isAjax = $this->RequestHandler->isAjax();
		if ($id == null || !ctype_digit($id))
			$msg = 'Invalid Host';
		else {
			$this->Host->id = $id;
			if ($this->Host->saveField('enabled', true) === false)
				$msg = 'Invalid Host';
		}
		if ($isAjax) {
			$this->set('data', array('success' => !isset($msg), 'message' => (isset($msg) && !is_bool($msg) ? $msg : "")));
			$this->render('/elements/json', 'plain');
			return;
		} else if (isset($msg))
			$this->Session->setFlash($msg);
		$this->redirect(array('action'=>'index'));
	}

	function disable($id = null) {
		$isAjax = $this->RequestHandler->isAjax();
		if ($id == null || !ctype_digit($id))
			$msg = 'Invalid Host';
		else {
			$this->Host->id = $id;
			if ($this->Host->saveField('enabled', false) === false)
				$msg = 'Invalid Host';
		}
		if ($isAjax) {
			$this->set('data', array('success' => !isset($msg) || (isset($msg) && $msg === true), 'message' => (isset($msg) && !is_bool($msg) ? $msg : "")));
			$this->render('/elements/json', 'plain');
			return;
		} else if (isset($msg))
			$this->Session->setFlash($msg);
		$this->redirect(array('action'=>'index'));
	}

}
?>