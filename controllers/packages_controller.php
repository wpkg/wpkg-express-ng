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
include_once('../util.functions.php');
App::import('Sanitize');

class PackagesController extends AppController {

	var $name = 'Packages';
	var $layout = 'main';
	var $components = array('Session', 'RequestHandler');
	var $helpers = array('Html', 'Form', 'Navigate', 'Javascript', 'Number', 'Xmlpp');
	var $paginate = array('limit' => 25,
			      'page' => 1,
			      'fields' => array(
					  'Package.id',
					  'Package.enabled',
					  'Package.id_text',
					  'Package.name',
					  'Package.revision',
					  'Package.priority',
					  'Package.reboot',
					  'Package.execute',
					  'Package.notify',
					  'Package.modified'
			      ),
			      'order' => array('Package.priority' => 'desc', 'Package.name' => 'asc'),
			      'recursive' => -1
	);

	function index() {
		if ($this->RequestHandler->isXml()) {
			$this->set('formatxml', $this->Configuration->read('XMLFeed.formatxml'));
			$this->set('exportdisabled', $exportdisabled = $this->Configuration->read('XMLFeed.exportdisabled'));
			$this->set('packages', $this->Package->getAllForXML(null, $exportdisabled));
		} else {
			$this->set('isAjax', $this->RequestHandler->isAjax());
			$this->set('packages', $this->paginate());
		}
	}

	function view($type = null, $id = null) {
		if ($type != null && ((!ctype_alpha($type) && $id != null) || (ctype_alpha($type) && $id == null))) {
			$this->Session->setFlash('Invalid Internal Command.');
			$this->redirect(array('action'=>'index'));
		} else if (ctype_digit($type) && $id == null) {
			$id = $type;
			$type = null;
		} else if ($type == null && $id == null) {
			$this->Session->setFlash('Invalid Package.');
			$this->redirect(array('action'=>'index'));
		}

		switch ($type) {
			case null:
				if (($package = $this->Package->get($id)) !== false) {
					if ($this->RequestHandler->isXml()) {
						$this->set('formatxml', $this->Configuration->read('XMLFeed.formatxml'));
						$this->set('exportdisabled', $exportdisabled = $this->Configuration->read('XMLFeed.exportdisabled'));
						$package = $this->Package->getAllForXML($id, $exportdisabled);
					} else {
						App::import('Helper', 'Tree');
						$this->set('tree', new TreeHelper());
						$this->set('packageChecks', $this->Package->PackageCheck->getThreadedForPackage($id));
						$this->set('packageActions', $this->Package->PackageAction->getAllForPackage($id));
					}
					$this->set(compact('package'));
				} else {
					$this->Session->setFlash('Invalid Package.');
					$this->redirect(array('action'=>'index'));
				}
				break;
			case "action":
				if (($packageAction = $this->Package->PackageAction->get($id)) !== false) {
					$this->set(compact('packageAction'));
					$this->autoRender = false;
					$this->render(null, "main", "action-view");
				} else {
					$this->Session->setFlash('Invalid Package.');
					$this->redirect(array('action'=>'index'));
				}
				break;
			default:
				$this->Session->setFlash('Invalid Internal Command.');
				$this->redirect(array('action'=>'index'));
		}
	}

	function add($type = null, $id = null) {
		if ($type && (!ctype_alpha($type) || (ctype_alpha($type) && $id == null))) {
			$this->Session->setFlash('Invalid Internal Command.');
			$this->redirect(array('action'=>'index'));
		} else {
			if (ctype_digit($type) && $id == null) {
				$id = $type;
				$type = null;
			}
			$which = strtolower($type);
			$pkgId = $id;
			if (!empty($this->data)) {
				$success = false;
				switch ($which) {
					case null: // saving a new package
						$this->Package->create(false);
						$success = $this->Package->save($this->data);
						$pkgId = $id = $this->Package->id;
						$which = "package";
						break;
					case "action":
						$this->Package->PackageAction->create(false);
						$this->data = array_merge($this->data, $this->Package->find('first', array('recursive' => 0, 'conditions' => array('Package.id' => $id))));
						$success = $this->Package->PackageAction->saveAll($this->data);
						$id = $this->Package->PackageAction->id;
						$which = "package action";
						break;
					case "check":
						$this->Package->PackageCheck->create(false);
						if ($this->data['PackageCheck']['parent_id'] == 0)
							$this->data['PackageCheck']['parent_id'] = null;
						$this->data = array_merge($this->data, $this->Package->find('first', array('recursive' => 0, 'conditions' => array('Package.id' => $id))));
						$success = $this->Package->PackageCheck->saveAll($this->data);
						$id = $this->Package->PackageCheck->id;
						$which = "package check";
						break;
					case "exitcode":
						$this->Package->PackageAction->ExitCode->create(false);
						$this->data = array_merge($this->data, $this->Package->PackageAction->find('first', array('recursive' => 0, 'conditions' => array('PackageAction.id' => $id))));
						$success = $this->Package->PackageAction->ExitCode->saveAll($this->data);
						$id = $this->Package->PackageAction->ExitCode->field('package_action_id');
						$which = "exit code";
						break;
					default:
						$this->Session->setFlash('An Error Occurred While Processing Your Input.');
						$this->redirect(array('action'=>'index'));					
				}
				if ($success) {
					$this->Session->setFlash('The ' . ucwords($which) . ' has been saved.');
					if ($which != "exit code")
						$url = array('action'=>'view', $pkgId);
					else
						$url = array('action'=>'view', 'action', $id);
					$this->redirect($url);
				} else
					$this->Session->setFlash('The ' . ucwords($which) . ' could not be saved. Please, try again.');
			}
			$this->set('pkgId', $pkgId);
			switch ($which) {
				case null: // packages
				case "package":
					break;
				case "action":
				case "package action":
					if (isset($success))
						$curPkg = $this->data;
					else
						$curPkg = $this->Package->find('first', array('recursive' => 0, 'conditions' => array('Package.id' => $id), 'fields' => array('Package.name')));
					$this->set('pkgName', $curPkg['Package']['name']);
					$this->autoRender = false;
					$this->render(null, "main", "action-add");
					break;
				case "check":
				case "package check":
					if (isset($success))
						$curPkg = $this->data;
					else
						$curPkg = $this->Package->find('first', array('recursive' => 0, 'conditions' => array('Package.id' => $id), 'fields' => array('Package.name')));

					// The selected check condition in the condition combo box
					$checkTypeCond = (isset($this->passedArgs['cond']) ? $this->passedArgs['cond'] : -1);

					if (isset($this->passedArgs['type'])) {
						$strCheckType = constValToLCSingle('check_type', $this->passedArgs['type'], true);
						if ($strCheckType == null)
							$strCheckType = "uninstall"; // default check type if invalid one passed in
					} else
						$strCheckType = "uninstall"; // default check type if one wasn't passed in

					// The list of conditions to be shown in the condition combo box
					$checkCond = constsToWords('check_condition_' . $strCheckType);

					// The selected check type in the type combo box
					$checkType = constant('CHECK_TYPE_' . strtoupper($strCheckType));

					// Bounds checking on the selected check condition
					$keys = array_keys($checkCond);
					if ($checkTypeCond < min($keys) || $checkTypeCond > max($keys))
						$checkTypeCond = min($keys); // default check condition if invalid one passed in

					// A hierarchy of logical checks for this package id for the parent check combo box
					$logicalChecks = $this->Package->PackageCheck->getLogicalChecksList($curPkg['Package']['id']);

					$this->set('pkgName', $curPkg['Package']['name']);
					$this->set('pkgId', $curPkg['Package']['id']);
					$this->set(compact('checkType', 'checkTypeCond', 'checkCond', 'logicalChecks'));
					$this->autoRender = false;
					$this->render(null, "main", "check-add");
					break;
				case "exitcode":
				case "exit code":
					$this->Package->PackageAction->unbindModel(array('belongsTo' => array('Package')), false);
					if (isset($success))
						$curPkgAct = $this->data;
					else
						$curPkgAct = $this->Package->PackageAction->find('first', array('recursive' => 0, 'conditions' => array('PackageAction.id' => $id), 'fields' => array('PackageAction.type', 'PackageAction.command')));
					$actType = ucwords(constValToLCSingle('action_type', $curPkgAct['PackageAction']['type']));
					$this->set('pkgActType', ucwords($actType));
					$this->set('pkgActId', $id);
					$this->set('pkgActCmd', $curPkgAct['PackageAction']['command']);
					$this->autoRender = false;
					$this->render(null, "main", "exitcode-add");
					break;
				default:
					$this->Session->setFlash('An Error Occurred While Processing Your Input.');
					$this->redirect(array('action'=>'index'));
			}
		}
	}

	function edit($type = null, $id = null) {
		if ($type && ((!ctype_alpha($type) && $id) || (ctype_alpha($type) && $id == null))) {
			$this->Session->setFlash('Invalid Internal Command.');
			$this->redirect(array('action'=>'index'));
		} else if ($type == null) {
			$this->Session->setFlash('Invalid id.');
			$this->redirect(array('action'=>'index'));
		} else {
			if (ctype_digit($type) && $id == null) {
				$id = $type;
				$type = null;
			}
			$which = strtolower($type);
			if (!empty($this->data)) {
				$success = false;
				switch ($which) {
					case null: // saving a modified existing package
						$this->Package->id = $this->data['Package']['id'] = $id;
						$success = $this->Package->save($this->data);
						$pkgId = $id;
						$which = "package";
						break;
					case "action":
						$this->data["PackageAction"]["id"] = $id;
						$success = $this->Package->PackageAction->save($this->data);
						$pkgId = $this->Package->PackageAction->field('package_id', array('PackageAction.id' => $id));
						$which = "package action";
						break;
					case "check":
						$this->Package->PackageCheck->id = $id;
						$prevParent = $this->Package->PackageCheck->field('parent_id');
						$pkgId = $this->Package->PackageCheck->field('package_id');
						$this->data['PackageCheck']['id'] = $id;
						if ($this->data['PackageCheck']['parent_id'] == 0)
							$this->data['PackageCheck']['parent_id'] = null;
						$success = $this->Package->PackageCheck->save($this->data);
						if ($success && ($prevParent != $this->data['PackageCheck']['parent_id']))
							$this->moveup("check", $id, 1);
						$which = "package check";
						break;
					case "exitcode":
						$this->Package->PackageAction->ExitCode->id = $id;
						$success = $this->Package->PackageAction->ExitCode->save($this->data);
						$pkgActId = $this->Package->PackageAction->ExitCode->field('package_action_id', array('ExitCode.id' => $id));
						$url = array('action'=>'view', 'action', $pkgActId);
						$which = "exit code";
						break;
					default:
						$this->Session->setFlash('An Error Occurred While Processing Your Input.');
						$this->redirect(array('action'=>'index'));
				}
				if ($success) {
					$this->Session->setFlash('The ' . ucwords($which) . ' has been saved.');
					if (!isset($url))
						$url = array('action'=>'view', $pkgId);
					$this->redirect($url);
				} else
					$this->Session->setFlash('The ' . ucwords($which) . ' could not be saved. Please, try again.');
			}
			switch ($which) {
				case null: // for packages
				case "package":
					$this->data = (isset($success) ? $this->data : $this->Package->get($id, true));
					$this->set('packageDependencies', $this->Package->getAllBut($id));
					break;
				case "action":
				case "package action":
					$this->data = (isset($success) ? $this->data : $this->Package->PackageAction->get($id, true));
					$pkgId = (isset($pkgId) ? $pkgId : $this->data['Package']['id']);
					$this->set('pkgName', $this->Package->field('name', array('Package.id' => $pkgId)));
					$this->set('pkgId', $pkgId);
					$this->set('pkgActId', $this->data['PackageAction']['id']);
					$this->autoRender = false;
					$this->render(null, "main", "action-edit");
					break;
				case "check":
				case "package check":
					$this->data = (isset($success) ? $this->data : $this->Package->PackageCheck->get($id));
					if (!isset($this->passedArgs['type']))
						$this->passedArgs['type'] = $this->data["PackageCheck"]["type"];
					$checkTypeCond = (isset($this->passedArgs['cond']) ? $this->passedArgs['cond'] : $this->data["PackageCheck"]["condition"]);
					if (isset($this->passedArgs['type'])) {
						$strCheckType = constValToLCSingle('check_type', $this->passedArgs['type'], true);
						if ($strCheckType == null)
							$strCheckType = "uninstall"; // default check type if invalid one passed in
					} else
						$strCheckType = "uninstall"; // default check type if one wasn't passed in

					// The list of conditions to be shown in the condition combo box
					$checkCond = constsToWords('check_condition_' . $strCheckType);

					// The selected check type in the type combo box
					$checkType = constant('CHECK_TYPE_' . strtoupper($strCheckType));

					// Bounds checking on the selected check condition
					$keys = array_keys($checkCond);
					if ($checkTypeCond < min($keys) || $checkTypeCond > max($keys))
						$checkTypeCond = min($keys); // default check condition if invalid one passed in

					// A hierarchy of logical checks for this package id for the parent check combo box
					if ($this->passedArgs['type'] != CHECK_TYPE_LOGICAL || $this->data["PackageCheck"]["type"] != CHECK_TYPE_LOGICAL)
						$logicalChecks = $this->Package->PackageCheck->getLogicalChecksList($this->data['Package']['id']);

					$this->set('pkgName', $this->data['Package']['name']);
					$this->set('pkgId', $this->data['Package']['id']);
					$this->set('pkgChkId', $this->data['PackageCheck']['id']);
					$this->set(compact('checkType', 'checkTypeCond', 'checkCond', 'logicalChecks'));
					$this->autoRender = false;
					$this->render(null, "main", "check-edit");
					break;
				case "exitcode":
				case "exit code":
					$this->data = (isset($success) ? $this->data : $this->Package->PackageAction->ExitCode->get($id));
					$actType = ucwords(constValToLCSingle('action_type', $this->data['PackageAction']['type']));
					$this->set('pkgActType', ucwords($actType));
					$this->set('pkgActId', $this->data['ExitCode']['package_action_id']);
					$this->set('pkgActCmd', $this->data['PackageAction']['command']);
					$this->autoRender = false;
					$this->render(null, "main", "exitcode-edit");
					break;
				default:
					$this->Session->setFlash('An Error Occurred While Processing Your Input.');
					$this->redirect(array('action'=>'index'));					
			}
		}
	}

	function delete($type = null, $id = null) {
		if ($id == null && $type == null) {
			$this->Session->setFlash('Invalid id.');
			$this->redirect(array('action'=>'index'));
		} else if ($type == null) {
			$this->Session->setFlash('Invalid id.');
			$this->redirect(array('action'=>'index'));
		}
		if (is_numeric($type)) {
			$id = $type;
			$type = "package";
		}

		switch ($type) {
			case "check":
				$this->Package->PackageCheck->id = $id;
				$pkgId = $this->Package->PackageCheck->field('package_id');
				$success = $this->Package->PackageCheck->delete($id);
				break;
			case "action":
				$this->Package->PackageAction->id = $id;
				$pkgId = $this->Package->PackageAction->field('package_id');
				$success = $this->Package->PackageAction->delete($id);
				break;
			case "exitcode":
				$this->Package->PackageAction->ExitCode->id = $id;
				$pkgActId = $this->Package->PackageAction->ExitCode->field('package_action_id');
				$success = $this->Package->PackageAction->ExitCode->delete($id);
				break;
			case "package":
				// For now, enforce dependencies. Later on we may want to prompt the user for the
				// desired course of action.
				$dependsOnThisPkg = $this->Package->getDependedOnBy($id);
				if ($dependsOnThisPkg === false || empty($dependsOnThisPkg))
					$success = $this->Package->delete($id);
				else {
					$success = false;
					$message = "It is depended on by: " . $this->element('RecordInUseByLinks', array('records' => $dependsOnThisPkg));
				}
				break;
			default:
				$this->Session->setFlash('Invalid type.');
				$this->redirect(array('action'=>'index'));
		}
		if ($success)
			$this->Session->setFlash(($type != "package" ? "Package " : "") . ucwords($type) . ' deleted.');
		else
			$this->Session->setFlash('An error occurred while attempting to delete that ' . ($type != "package" ? "Package " : "") . ucwords($type) . "." . (isset($message) ? " " . $message : ""));
		if ($type == "package") {
			if ($this->RequestHandler->isAjax()) {
				$this->autoRender = true;
				$this->autoLayout = true;
				return $this->setAction('index');
			} else
				$this->redirect(array('action'=>'index'));
		} else {
			if ($this->RequestHandler->isAjax()) {
				if ($type == 'check') {
					$this->set('packageChecks', $this->Package->PackageCheck->getThreadedForPackage($pkgId)); //$this->set('packageChecks', $this->Package->PackageCheck->getThreadedForPackage($this->Package->PackageCheck->getPackageID($id)));
					$this->render('/elements/packagechecks', 'plain');
				} else if ($type == 'action') {
					$this->set('packageActions', $this->Package->PackageAction->getAllForPackage($pkgId)); //$this->set('packageActions', $this->Package->PackageAction->getAllForPackage($this->Package->PackageAction->getPackageID($id)));
					$this->render('/elements/packageactions', 'plain');
				} else if ($type == 'exitcode') {
					$this->set('exitcodes', $this->Package->PackageAction->ExitCode->getAllForAction($pkgActId));
					$this->render('/elements/exitcodes', 'plain');
				}
			} else if ($type == 'exitcode')
				$this->redirect(array('action'=>'view', 'action', $pkgActId));
			else
				$this->redirect(array('action'=>'view', $pkgId));
		}
	}

	function movetop($type = null, $id = null) {
		$this->moveup($type, $id, true);
	}

	function movebottom($type = null, $id = null) {
		$this->movedown($type, $id, true);
	}

	function moveup($type = null, $id = null, $delta = null) {
		if ($delta == null || (is_int($delta) && $delta < 0)) $delta = 1;
		if (($ret = $this->_getMoveModel($type, $id)) !== null) {
			extract($ret);
			$model->moveUp($id, $delta);
			if ($this->RequestHandler->isAjax()) {
				if (strtolower($type) == 'check') {
					App::import('Helper', 'Tree');
					$this->set('tree', new TreeHelper());
					$this->set('packageChecks', $this->Package->PackageCheck->getThreadedForPackage($pkgId));
				} else if (strtolower($type) == 'action')
					$this->set('packageActions', $this->Package->PackageAction->getAllForPackage($pkgId));
				$this->render('/elements/package' . (strtolower($type) == 'check' ? 'checks' : 'actions'), 'plain');
			} else
				$this->redirect(array('action' => 'view', $pkgId));
		} else if (!$this->RequestHandler->isAjax())
			$this->redirect(array('action' => 'index'));
	}

	function movedown($type = null, $id = null, $delta = null) {
		if ($delta == null || (is_int($delta) && $delta < 0)) $delta = 1;
		if (($ret = $this->_getMoveModel($type, $id)) !== null) {
			extract($ret);
			$model->moveDown($id, $delta);
			if ($this->RequestHandler->isAjax()) {
				if (strtolower($type) == 'check') {
					App::import('Helper', 'Tree');
					$this->set('tree', new TreeHelper());
					$this->set('packageChecks', $this->Package->PackageCheck->getThreadedForPackage($pkgId));
				} else if (strtolower($type) == 'action')
					$this->set('packageActions', $this->Package->PackageAction->getAllForPackage($pkgId));
				$this->render('/elements/package' . (strtolower($type) == 'check' ? 'checks' : 'actions'), 'plain');
			} else
				$this->redirect(array('action' => 'view', $pkgId));
		} else if (!$this->RequestHandler->isAjax())
			$this->redirect(array('action' => 'index'));
	}

	function enable($id = null) {
		$isAjax = $this->RequestHandler->isAjax();
		if ($id == null || !ctype_digit($id))
			$msg = 'Invalid Package';
		else {
			$this->Package->id = $id;
			if ($this->Package->saveField('enabled', true) === false)
				$msg = 'Invalid Package';
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
			$msg = 'Invalid Package';
		else {
			$this->Package->id = $id;
			if (($msg = $this->_canDisable($id)) === true) {
				if ($this->Package->saveField('enabled', false) === false)
					$msg = 'Invalid Package';
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

	function _getMoveModel($type, $id) {
		$type = ucwords(strtolower($type));
		if (!in_array($type, array("Check", "Action"))) {
			$this->Session->setFlash("An attempt to move an unknown type has occurred.");
			return null;
		}
		if ($id == null) {
			$this->Session->setFlash("Please provide the ID of the Package " . $type . " to be moved.");
			return null;
		}

		$model = get_object_vars($this->Package);
		$model = (isset($model["Package$type"]) ? $model["Package$type"] : null);

		if ($model !== null) {
			$record = $model->find('first', array('conditions' => array("Package$type.id" => $id), 'fields' => array("Package$type.package_id")));
			if (empty($record))
				$this->Session->setFlash("There is no Package $type with an ID of $id.");
			else
				return array('model' => $model, 'pkgId' => $record["Package$type"]['package_id']);
		}
		return null;
	}

	function _canDisable($id) {
		$dependsOnThisPkg = $this->Package->getDependedOnBy($id);
		if ($dependsOnThisPkg === false)
			return "Invalid Package.";
		else if (!empty($dependsOnThisPkg)) {
			$this->Package->id = $id;
			return "The Package '" . $this->Package->field('name') . "' cannot be disabled because it is depended on by the following package(s): " . $this->element('RecordInUseByLinks', array('records' => $dependsOnThisPkg));
		}
		return true;
	}

}
?>
