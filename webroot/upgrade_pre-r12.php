<?php
function createConfig($array) {
	$out = "<?php" . PHP_EOL;
	foreach ($array['wpkgExpress'] as $type => $vals) {
		foreach ($vals as $k => $v)
			$txtvals[] = "'$k' => " . (is_string($v) ? "'$v'" : "$v");
		$out .= "\$config['wpkgExpress']['$type'] = array(" . implode(", ", $txtvals) . ");" . PHP_EOL;
		unset($txtvals);
	}
	$out .= "?>";
	
	return $out;
}

$msg = "Successfully upgraded configuration file.";
$configfile = realpath("../config/wpkgExpress.php");
if (file_exists($configfile)) {
	if ($cfgdefs = file($configfile)) {
		array_shift($cfgdefs);
		array_pop($cfgdefs);
		eval(implode("\n", $cfgdefs));

		if (isset($config)) {
			if (isset($config['wpkgExpress']['Auth']['salted'])) {
				$config['wpkgExpress']['General']['salted'] = $config['wpkgExpress']['Auth']['salted'];
				unset($config['wpkgExpress']['Auth']['salted']);
			}
			foreach ($config['wpkgExpress']['Auth'] as $k => $v) {
				if (stripos($k, "xml") !== false) {
					$config['wpkgExpress']['XMLFeed'][$k] = $config['wpkgExpress']['Auth'][$k];
					unset($config['wpkgExpress']['Auth'][$k]);
				}
			}
			$config['wpkgExpress']['XMLFeed']['exportdisabled'] = 0;
			$config['wpkgExpress']['XMLFeed']['formatxml'] = 0;
			
			if ($fh = fopen($configfile, "wb")) {
				if (fwrite($fh, createConfig($config)))
					fclose($fh);
				else
					$msg = "Could not save new configuration file while upgrading.";
			} else
				$msg = "Could not open configuration file for writing while upgrading.";
		} else
			$msg = "Nothing to upgrade.";
	} else
		$msg = "Could not open and read existing configuration file for upgrading.";
} else
	$msg = "Could not find configuration file for upgrading.";
	
echo "<h3>$msg</h3>";
?>