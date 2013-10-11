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
class HomeController extends AppController {
	var $name = 'Home';
	var $layout = 'main';
	var $components = array();
	var $uses = array('Package', 'Profile', 'Host');
	var $helpers = array('Html', 'Navigate', 'Javascript', 'Text');

	function index() {
		$packageCountTotal = $this->Package->find('count');
		$packageCountEnabled = $this->Package->find('count', array('conditions' => array('Package.enabled' => '1'), 'recursive' => -1));
		$packageCountDisabled = $packageCountTotal - $packageCountEnabled;
		$packageRecent = $this->Package->find('all', array('order' => 'Package.modified DESC', 'limit' => 5, 'fields' => array('Package.id', 'Package.name', 'Package.modified'), 'recursive' => -1));

		$profileCountTotal = $this->Profile->find('count');
		$profileCountEnabled = $this->Profile->find('count', array('conditions' => array('Profile.enabled' => '1'), 'recursive' => -1));
		$profileCountDisabled = $profileCountTotal - $profileCountEnabled;
		$profileRecent = $this->Profile->find('all', array('order' => 'Profile.modified DESC', 'limit' => 5, 'fields' => array('Profile.id', 'Profile.id_text', 'Profile.modified'), 'recursive' => -1));

		$hostCountTotal = $this->Host->find('count');
		$hostCountEnabled = $this->Host->find('count', array('conditions' => array('Host.enabled' => '1'), 'recursive' => -1));
		$hostCountDisabled = $hostCountTotal - $hostCountEnabled;
		$hostRecent = $this->Host->find('all', array('order' => 'Host.modified DESC', 'limit' => 5, 'fields' => array('Host.id', 'Host.name', 'Host.modified'), 'recursive' => -1));

		$this->set(compact('packageCountTotal', 'packageCountEnabled', 'packageCountDisabled', 'packageRecent',
				   'profileCountTotal', 'profileCountEnabled', 'profileCountDisabled', 'profileRecent',
				   'hostCountTotal', 'hostCountEnabled', 'hostCountDisabled', 'hostRecent'
		));
	}
	
	function search($type = null, $query = null) {
		if (isset($this->data['Search']['query']))
			$this->redirect(array('controller' => null, 'action' => 'search', $this->data['Search']['type'], $this->data['Search']['query']));
		if ($type != null && $query != null) {
			$searchFields = array(
				'Package' => array('Package.name', 'Package.id_text', 'Package.notes'),
				'Profile' => array('Profile.id_text', 'Profile.notes'),
				'Host' => array('Host.name', 'Host.notes')
			);

			if (strtolower($type) == 'all')
				$kind = array_keys($searchFields);
			else {
				$kind = explode(",", $type);
				foreach ($kind as $k=>$v)
					$kind[$k] = Inflector::singularize(ucwords($v));
				// Filter out invalid/unallowed models
				$kind = array_intersect($kind, array_keys($searchFields));
			}

			$results = array();
			$total = 0;
			foreach ($kind as $k=>$model) {
				$likeConditions = array();
				foreach ($searchFields[$model] as $field)
					$likeConditions["$field LIKE"] = "%$query%";
				if (in_array("$model.name", $searchFields[$model]))
					$sortField = "$model.name";
				else if (in_array("$model.id_text", $searchFields[$model]))
					$sortField = "$model.id_text";
				else
					$sortField = "$model.id";
				$result = $this->{$model}->find('all', array('conditions' => array('or' => $likeConditions), 'fields' => array_merge(array("$model.id"), $searchFields[$model]), 'order' => "$sortField ASC", 'recursive' => -1));
				$modelresults = array();
				foreach ($result as $k=>$v)
					$modelresults[] = $v[$model];
				$total += count($modelresults);
				$results[$model] = $modelresults;
			}
			foreach ($kind as $k=>$model)
				$kind[$k] = Inflector::pluralize($model);
		}
		$this->set(compact('kind', 'query', 'results', 'total'));
	}
}
?>