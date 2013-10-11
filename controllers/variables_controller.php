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
require_once('../wpkg_constants.php');

class VariablesController extends AppController {

	var $name = 'Variables';
	var $layout = 'main';
	var $components = array('Session');
	var $helpers = array('Html', 'Form', 'Navigate', 'Javascript');

	function view($type = null, $recordId = null) {
		if (!ctype_alpha($type) || !ctype_digit($recordId) || ($model =& ClassRegistry::init(strtolower($type))) == false)
			$this->_showCritError('Invalid type and/or record id.');
		else {
			$type = ucwords(strtolower($type));
			$model->id = $recordId;
			$name = $model->field($model->displayField);
			if ($name === false) {
				$this->Session->setFlash("Invalid $type.");
				$this->redirect(array('controller' => Inflector::pluralize(strtolower($type)), 'action' => 'index'));
			}
			$variables = $this->Variable->getAllFor($type, $recordId);
			$this->set(compact('variables', 'type', 'recordId', 'name'));
		}
	}

	function add($type = null, $recordId = null) {
		if (!ctype_digit($recordId) || !ctype_alpha($type) || ($model =& ClassRegistry::init(strtolower($type))) == false)
			$this->_showCritError('Invalid type and/or record id');
		else {
			$type = ucwords(strtolower($type));
			if (!empty($this->data)) {
				if (($type_id = constant('VARIABLE_TYPE_' . strtoupper($type))) === false)
					$type_id = -1; // Will cause validation to fail, since we only use positive integers
				$this->Variable->create();
				$this->data['Variable']['ref_id'] = $recordId;
				$this->data['Variable']['ref_type'] = $type_id;
				if ($this->Variable->save($this->data)) {
					$this->Session->setFlash('The Variable has been saved');
					$this->redirect(array('action'=>'view', $type, $recordId));
					//$this->redirect(array('controller' => Inflector::pluralize(strtolower($type)), 'action'=>'view', $recordId));
				} else {
					$this->Session->setFlash('The Variable could not be saved');
					echo $recordId;
					echo "<pre>" . print_r($this->Variable->validationErrors, true) . "</pre>";
				}
			}

			$model->id = $recordId;
			$name = $model->field($model->displayField);
			if ($name === false) {
				$this->Session->setFlash("Invalid $type id.");
				$this->redirect(array('controller' => strtolower($type), 'action' => 'index'));
			}

			$this->set(compact('type', 'recordId', 'name'));
		}
	}

	function edit($id = null) {
		if (!ctype_digit($id))
			$this->_showCritError('Invalid Variable');
		if (!empty($this->data)) {
			$this->data['Variable']['id'] = $id;
			$var = $this->Variable->find('first', array('conditions' => array('Variable.id' => $id), 'fields' => array('Variable.ref_type', 'Variable.ref_id')));
			if ($this->Variable->save($this->data)) {
				$type = constValToLCSingle('VARIABLE_TYPE_', $var['Variable']['ref_type']);
				$recordId = $var['Variable']['ref_id'];

				$this->Session->setFlash('The Variable has been saved');
				$this->redirect(array('action'=>'view', $type, $recordId));
			} else {
				$message = 'The Variable could not be saved';
				if ($var === false)
					$this->_showCritError($message);
				else {
					$this->Session->setFlash($message);
					$this->data['Variable']['ref_type'] = $var['ref_type'];
					$this->data['Variable']['ref_id'] = $var['ref_id'];
				}
			}
		}
		if (empty($this->data))
			$this->data = $this->Variable->read(null, $id);

		if (($type = ucwords(constValToLCSingle('VARIABLE_TYPE_', $this->data['Variable']['ref_type']))) === null) {
			$this->Session->setFlash("The Variable could not be saved");
			return;
		}
			
		$recordId = $this->data['Variable']['ref_id'];
		$model =& ClassRegistry::init($type);
		$model->id = $recordId;
		$name = $model->field($model->displayField);

		$this->set(compact('type', 'recordId', 'name'));
	}

	function delete($id = null) {
		if (!ctype_digit($id))
			$this->_showCritError('Invalid id for Variable');
		$var = $this->Variable->find('first', array('conditions' => array('Variable.id' => $id), 'fields' => array('Variable.ref_type', 'Variable.ref_id')));
		if ($var && $this->Variable->del($id)) {
			if (($type = constValToLCSingle('VARIABLE_TYPE_', $var['Variable']['ref_type'])) === null) {
				$this->Session->setFlash("The Variable could not be deleted");
				return;
			}
			$recordId = $var['Variable']['ref_id'];

			if ($this->RequestHandler->isAjax()) {
				$this->set('variables', $this->Variable->getAllFor($type, $recordId));
				$this->render('/elements/variables', 'plain');
			} else {
				$this->Session->setFlash('Variable deleted');
				$this->redirect(array('action'=>'view', $type, $recordId));
			}
		} else
			$this->_showCritError('Invalid id for Variable');
	}

	function _showCritError($message) {
		$this->set(compact('message'));
		$this->render('/errors/unrecoverable', 'main');
	}

}
?>
