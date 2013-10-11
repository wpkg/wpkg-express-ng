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
$outXML = "<packages>";
foreach ($packages as $package) {
	$XML_Checks = "";
	$XML_Actions = "";
	$XML_Depends = "";
	$XML_Variables = "";
	$pkg_attribs = array(
			'id'=>$package['Package']['id_text'],
			'name'=>$package['Package']['name'],
			'revision'=>$package['Package']['revision'],
			'priority'=>$package['Package']['priority']
	);
	switch ($package['Package']['reboot']) {
		case PACKAGE_REBOOT_TRUE:
			$reboot = "true";
			break;
		case PACKAGE_REBOOT_POSTPONED:
			$reboot = "postponed";
			break;
		case PACKAGE_REBOOT_FALSE:
		default:
			$reboot = "false";
			break;
	}
	$pkg_attribs['reboot'] = $reboot;
 	if ($package['Package']['notify'] == true)
		$pkg_attribs['notify'] = 'true';
	if ($package['Package']['execute'] != PACKAGE_EXECUTE_NORMAL)
		$pkg_attribs['execute'] = ($package['Package']['execute'] == PACKAGE_EXECUTE_ALWAYS ? "always" : "once");

	if  (isset($package['PackageCheck'])) {
		$numChecks = count($package['PackageCheck']);
		$refs = array();
		$checks = array();
		foreach ($package['PackageCheck'] as $check) {
			switch ($check['type']) {
				case CHECK_TYPE_LOGICAL:
					$type = "logical";
					switch ($check['condition']) {
						case CHECK_CONDITION_LOGICAL_NOT:
							$condition = "not";
							break;
						case CHECK_CONDITION_LOGICAL_AND:
							$condition = "and";
							break;
						case CHECK_CONDITION_LOGICAL_OR:
							$condition = "or";
							break;
						case CHECK_CONDITION_LOGICAL_AT_LEAST:
							$condition = "atleast";
							break;
						case CHECK_CONDITION_LOGICAL_AT_MOST:
							$condition = "atmost";
							break;
					}
					break;
				case CHECK_TYPE_REGISTRY:
					$type = "registry";
					switch ($check['condition']) {
						case CHECK_CONDITION_REGISTRY_EXISTS:
							$condition = "exists";
							break;
						case CHECK_CONDITION_REGISTRY_EQUALS:
							$condition = "equals";
							$value = $check['value'];
							break;
					}
					$path = $check['path'];
					break;
				case CHECK_TYPE_FILE:
					$type = "file";
					switch ($check['condition']) {
						case CHECK_CONDITION_FILE_EXISTS:
							$condition = "exists";
							break;
						case CHECK_CONDITION_FILE_SIZE_EQUALS:
							$condition = "sizeequals";
							break;
						case CHECK_CONDITION_FILE_VERSION_SMALLER_THAN:
							$condition = "versionsmallerthan";
							break;
						case CHECK_CONDITION_FILE_VERSION_LESS_THAN_OR_EQUAL_TO:
							$condition = "versionlessorequal";
							break;
						case CHECK_CONDITION_FILE_VERSION_EQUAL_TO:
							$condition = "versionequalto";
							break;
						case CHECK_CONDITION_FILE_VERSION_GREATER_THAN_OR_EQUAL_TO:
							$condition = "versiongreaterorequal";
							break;
						case CHECK_CONDITION_FILE_VERSION_GREATER_THAN:
							$condition = "versiongreaterthan";
							break;
					}
					if ($check['condition'] !== CHECK_CONDITION_FILE_EXISTS)
						$value = $check['value'];
					$path = $check['path'];
					break;
				case CHECK_TYPE_EXECUTE:
					$type = "execute";
					switch ($check['condition']) {
						case CHECK_CONDITION_EXECUTE_EXIT_CODE_SMALLER_THAN:
							$condition = "exitcodesmallerthan";
							break;
						case CHECK_CONDITION_EXECUTE_EXIT_CODE_LESS_THAN_OR_EQUAL_TO:
							$condition = "exitcodelessorequal";
							break;
						case CHECK_CONDITION_EXECUTE_EXIT_CODE_EQUAL_TO:
							$condition = "exitcodeequalto";
							break;
						case CHECK_CONDITION_EXECUTE_EXIT_CODE_GREATER_THAN_OR_EQUAL_TO:
							$condition = "exitcodegreaterorequal";
							break;
						case CHECK_CONDITION_EXECUTE_EXIT_CODE_GREATER_THAN:
							$condition = "exitcodegreaterthan";
							break;
					}
					$path = $check['path'];
					$value = $check['value'];
					break;
				case CHECK_TYPE_UNINSTALL:
					$type = "uninstall";
					switch ($check['condition']) {
						case CHECK_CONDITION_UNINSTALL_EXISTS:
							$condition = "exists";
							break;
						case CHECK_CONDITION_UNINSTALL_VERSION_SMALLER_THAN:
							$condition = "versionsmallerthan";
							$value = $check['value'];
							break;
						case CHECK_CONDITION_UNINSTALL_LESS_THAN_OR_EQUAL_TO:
							$condition = "versionlessorequal";
							$value = $check['value'];
							break;
						case CHECK_CONDITION_UNINSTALL_EQUAL_TO:
							$condition = "versionequalto";
							$value = $check['value'];
							break;
						case CHECK_CONDITION_UNINSTALL_GREATER_THAN:
							$condition = "versiongreaterthan";
							$value = $check['value'];
							break;
						case CHECK_CONDITION_UNINSTALL_GREATER_THAN_OR_EQUAL_TO:
							$condition = "versiongreaterorequal";
							$value = $check['value'];
							break;
					}					
					$path = $check['path'];
					break;
				case CHECK_TYPE_HOST:
					$type = "host";
					switch ($check['condition']) {
						case CHECK_CONDITION_HOST_HOSTNAME:
							$condition = "hostname";
							break;
						case CHECK_CONDITION_HOST_OS:
							$condition = "os";
							break;
						case CHECK_CONDITION_HOST_ARCHITECTURE:
							$condition = "architecture";
							break;
						case CHECK_CONDITION_HOST_ENVIRONMENT:
							$condition = "environment";
							break;
					}					
					$value = $check['value'];
					break;
				default:
					$type = "unknown";
					$condition = "unknown";
			}
			$chk_attribs = array('_name_' => 'check', 'type' => $type, 'condition' => $condition);
			if (isset($path)) {
				$chk_attribs['path'] = $path;
				unset($path);
			}
			if (isset($value)) {
				$chk_attribs['value'] = $value;
				unset($value);
			}

			$thisref = &$refs[$check['id']];
			foreach ($chk_attribs as $key => $attrib)
				$thisref[$key] = $chk_attribs[$key];

			if (empty($check['parent_id']))
				$checks[$check['id']] = &$thisref;
			else
				$refs[$check['parent_id']]['check'][$check['id']] = &$thisref;

		}
		$XML_Checks = $xml->serialize($checks, array('tags' => array('check')));
		
		// A bit of a crude hack for now, something funky must have changed with CakePHP's XML handling in 1.2.5 final :-(
		$XML_Checks = str_replace("<condition type=\"", "<check type=\"", $XML_Checks);
	}
	if (isset($package['PackageAction'])) {
		$actions = array();
		foreach ($package['PackageAction'] as $action) {
			$XML_ExitCodes = "";
			switch ($action['type']) {
				case ACTION_TYPE_INSTALL:
					$type = "install";
					break;
				case ACTION_TYPE_UPGRADE:
					$type = "upgrade";
					break;
				case ACTION_TYPE_DOWNGRADE:
					$type = "downgrade";
					break;
				case ACTION_TYPE_REMOVE:
					$type = "remove";
					break;
				default:
					$type = "unknown_action";
			}

			$action_attribs = array('cmd' => $action['command']);
			if (!empty($action['workdir']) && $action['workdir'] != "NULL")
				$action_attribs['workdir'] = $action['workdir'];
			if (!empty($action['timeout']) && $action['timeout'] != "NULL")
				$action_attribs['timeout'] = $action['timeout'];

			if (isset($action['ExitCode'])) {
				$exitcodes = array();
				foreach ($action['ExitCode'] as $exitcode) {
					switch ($exitcode['reboot']) {
						case EXITCODE_REBOOT_TRUE:
							$reboot = "true";
							break;
						case EXITCODE_REBOOT_DELAYED:
							$reboot = "delayed";
							break;
						case EXITCODE_REBOOT_POSTPONED:
							$reboot = "postponed";
							break;
					}
					$exitcode_attribs = array('_name_' => 'exit', 'code' => $exitcode['code']);
					if (isset($reboot))
						$exitcode_attribs['reboot'] = $reboot;
					$exitcodes[] = $exitcode_attribs;
				}
				if (!empty($exitcodes))
					$XML_ExitCodes = $xml->serialize($exitcodes);
			}
			$xmlaction = $xml->elem($type, $action_attribs, $XML_ExitCodes, true);

			if (strpos($action['command'], '"') !== false)
				$xmlaction = preg_replace('/<(.+) cmd="(.*?)"([\s]+|>)/i', '<\1 cmd=\'\2\'\3', $xmlaction);
				
			$xmlaction = htmlspecialchars_decode($xmlaction, (strpos($action['command'], '"') !== false ? ENT_COMPAT : ENT_QUOTES));
			$XML_Actions .= $xmlaction;
		}
	}
	if  (isset($package['PackageDependency'])) {
		$depends = array();
		foreach ($package['PackageDependency'] as $depend) {
			$depend_attribs = array('_name_' => 'depends', 'package-id' => $depend['id_text']);
			$depends[] = $depend_attribs;
		}
		if (!empty($depends))
			$XML_Depends = $xml->serialize($depends);
	}
	if (isset($package['Variable'])) {
		$variables = array();
		foreach ($package['Variable'] as $var)
			$variables[] = array('_name_' => 'variable', 'name' => $var['name'], 'value' => $var['value']);
		if (!empty($variables))
			$XML_Variables = $xml->serialize($variables);
	}
	$outXML .= ($exportdisabled && $package['Package']['enabled'] == false ? "<!--" . ($formatxml ? "\n" : " ") : "") . $xml->elem('package', $pkg_attribs, $XML_Variables . $XML_Depends . $XML_Checks . $XML_Actions, true) . ($exportdisabled && $package['Package']['enabled'] == false ? ($formatxml ? "\n" : " ") . "-->" . ($formatxml ? "\n" : "") : "");
}
$outXML .= "</packages>";
echo ($formatxml ? "\n" . $xmlpp->indent($outXML) : $outXML);
?>