<?php 
/* SVN FILE: $Id$ */
/* Wpkgexpress schema generated on: 2009-05-07 03:05:36 : 1241682396*/
class WpkgexpressSchema extends CakeSchema {
	var $name = 'Wpkgexpress';

	function before($event = array()) {
		return true;
	}

	function after($event = array()) {
	}

	var $exit_codes = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'package_action_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10),
		'code' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'reboot' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 4),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
	);
	var $hosts = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'enabled' => array('type' => 'boolean', 'null' => false, 'default' => '1'),
		'name' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 100),
		'notes' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'mainprofile_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10),
		'position' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 5),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
	);
	var $hosts_profiles = array(
		'host_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'profile_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'indexes' => array('PRIMARY' => array('column' => array('host_id', 'profile_id'), 'unique' => 1))
	);
	var $package_actions = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'package_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10),
		'type' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 3),
		'command' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 500),
		'timeout' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 8),
		'workdir' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 500),
		'position' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 5),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
	);
	var $package_checks = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'package_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10),
		'type' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 3),
		'condition' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 3),
		'path' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 200),
		'value' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 200),
		'parent_id' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 10),
		'lft' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 10),
		'rght' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 10),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
	);
	var $packages = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 100),
		'id_text' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 100, 'key' => 'unique'),
		'enabled' => array('type' => 'boolean', 'null' => false, 'default' => '1'),
		'revision' => array('type' => 'string', 'null' => false, 'default' => '0', 'length' => 35),
		'priority' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'reboot' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'execute' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'notify' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'notes' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'id_text' => array('column' => 'id_text', 'unique' => 1))
	);
	var $packages_packages = array(
		'package_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'dependency_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'indexes' => array('PRIMARY' => array('column' => array('package_id', 'dependency_id'), 'unique' => 1))
	);
	var $packages_profiles = array(
		'profile_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'package_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'indexes' => array('PRIMARY' => array('column' => array('profile_id', 'package_id'), 'unique' => 1))
	);
	var $profiles = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'enabled' => array('type' => 'boolean', 'null' => false, 'default' => '1'),
		'id_text' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 100, 'key' => 'unique'),
		'notes' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'id_text' => array('column' => 'id_text', 'unique' => 1))
	);
	var $profiles_profiles = array(
		'profile_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'dependency_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'indexes' => array('PRIMARY' => array('column' => array('profile_id', 'dependency_id'), 'unique' => 1))
	);
	var $variables = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'ref_id' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'ref_type' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 3),
		'name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 80),
		'value' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 500),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
	);
}
?>