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
$outXML = "";
$XML_Profiles = "";
$XML_Variables = "";
$host_attribs = array('name' => $host['Host']['name'], 'profile-id' => $host['MainProfile']['id_text']);

if (isset($host['Variable'])) {
	$variables = array();
	foreach ($host['Variable'] as $var)
		$variables[] = array('_name_' => 'variable', 'name' => $var['name'], 'value' => $var['value']);
	if (!empty($variables))
		$XML_Variables = $xml->serialize($variables);
}

if (isset($host['Profile'])) {
	$profiles = array();
	foreach ($host['Profile'] as $profile)
		$profiles[] = array('_name_' => 'profile', 'id' => $profile['id_text']);
	if (!empty($profiles))
		$XML_Profiles = $xml->serialize($profiles);
}
$outXML .= ($exportdisabled && $host['Host']['enabled'] == false ? "<!--" . ($formatxml ? "\n" : " ") : "") . $xml->elem('host', $host_attribs, $XML_Variables . $XML_Profiles, true) . ($exportdisabled && $host['Host']['enabled'] == false ? ($formatxml ? "\n" : " ") . "-->" . ($formatxml ? "\n" : "") : "");

if (!$exportdisabled && $profile['Profile']['enabled'] == false)
	echo "";
else
	echo ($formatxml ? "\n" . $xmlpp->indent($outXML) : $outXML);
?>