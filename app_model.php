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
class AppModel extends Model {

	function afterFind($results, $primary = false) {
	    	if($primary == true) {
	    	   if(Set::check($results, '0.0')) {
	    	      $fieldName = key($results[0][0]);
	    	       foreach($results as $key=>$value) {
	    	          $results[$key][$this->alias][$fieldName] = $value[0][$fieldName];
	    	          unset($results[$key][0]);
	    	       }
	    	    }
	    	}	
	    	return $results;
	}
	
	function arrayMap($callback, $arr) {
		$results = array();
		$args = array();
		if(func_num_args() > 2)
			$args = (array)array_shift(array_slice(func_get_args(), 2));
		foreach($arr as $key => $value) {
			$temp = $args;
			array_unshift($temp, $value);
			$results[$key] = call_user_func_array($callback, $temp);
		}
		return $results;
	}

}
?>