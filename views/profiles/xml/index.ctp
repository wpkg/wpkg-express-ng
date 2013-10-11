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
$outXML = "<profiles>";
foreach ($profiles as $profile) {
	$XML_Depends = "";
	$XML_Packages = "";
	$XML_Variables = "";
	$profile_attribs = array('id'=>$profile['Profile']['id_text']);

	if  (isset($profile['Package'])) {
		$packages = array();
		foreach ($profile['Package'] as $package)
			$packages[] = array('_name_' => 'package', 'package-id' => $package['id_text']);
		if (!empty($packages))
			$XML_Packages = $xml->serialize($packages);
	}

	if  (isset($profile['ProfileDependency'])) {
		$depends = array();
		foreach ($profile['ProfileDependency'] as $depend)
			$depends[] = array('_name_' => 'depends', 'profile-id' => $depend['id_text']);
		if (!empty($depends))
			$XML_Depends = $xml->serialize($depends);
	}

	if (isset($profile['Variable'])) {
		$variables = array();
		foreach ($profile['Variable'] as $var)
			$variables[] = array('_name_' => 'variable', 'name' => $var['name'], 'value' => $var['value']);
		if (!empty($variables))
			$XML_Variables = $xml->serialize($variables);
	}
	$outXML .= ($exportdisabled && $profile['Profile']['enabled'] == false ? "<!--" . ($formatxml ? "\n" : " ") : "") . $xml->elem('profile', $profile_attribs, $XML_Variables . $XML_Depends . $XML_Packages, true) . ($exportdisabled && $profile['Profile']['enabled'] == false ? ($formatxml ? "\n" : " ") . "-->" . ($formatxml ? "\n" : "") : "");
}
$outXML .= "</profiles>";

echo ($formatxml ? "\n" . $xmlpp->indent($outXML) : $outXML);
?>