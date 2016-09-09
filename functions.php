<?php
function scanDirBlacklist($dir, $blacklist = array(".", "..")) {
    $values = array();

    foreach (scanDir($dir) as $scannedDir) {
        if (!in_array($scannedDir, $blacklist)) {
            array_push($values, $scannedDir);
        }
    }
    return $values;
}

function getiOSApps($appFolders, $basepath, $blacklist = array(".", "..")) {
    $values = array();
    foreach ($appFolders as $appFolder) {
    	$ipas = glob($basepath.$appFolder."/*.ipa");
        foreach($ipas as $ipa) {
                $tempArray = array();
                $CFProperties = getCFProperties($ipa);
                $tempArray["name"] = $CFProperties[0];
                $tempArray["bundle"] = $CFProperties[1];
                $tempArray["version"] = $CFProperties[2];
                $tempArray["filepath"] = $ipa;
                $tempArray["file"] = basename($ipa);
                array_push($values, $tempArray);
        }
    }
    return $values;
}

function getAndroidApps($appFolders, $basepath, $blacklist = array(".", "..")) {
    $values = array();
    foreach ($appFolders as $appFolder) {
    	$apks = glob($basepath.$appFolder."/*.apk");
        foreach($apks as $apk) {
                $tempArray = array();
                $APKProperties = getApkManifestProperties($apk);
                $tempArray["name"] = $appFolder;
                $tempArray["bundle"] = $APKProperties[0];
                $tempArray["version"] = $APKProperties[1];
                $tempArray["filepath"] = $apk;
                array_push($values, $tempArray);
        }
    }
    return $values;
}

function disableIfNotiDevice() {
    if (stripos($_SERVER['HTTP_USER_AGENT'],"iPod") || stripos($_SERVER['HTTP_USER_AGENT'],"iPhone") || stripos($_SERVER['HTTP_USER_AGENT'],"iPad")) {
        return "";
    } else {
        return "disabled";
    }
}

function disableIfNotAndroidDevice() {
	$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
	if(stripos($ua,'android') !== false) {
		return "";
	} else {
		return "disabled";
	}
}

function getCFProperties($ipa) {
    require_once(__DIR__.'/libraries/CFPropertyList/CFPropertyList.php');

    $plist = new CFPropertyList\CFPropertyList();

    $zipHandler = zip_open($ipa);
    if ($zipHandler) {
        while ($zip_entry = zip_read($zipHandler)) {
            if (strpos(zip_entry_name($zip_entry), "Info.plist") !== false) {
                if (zip_entry_open($zipHandler, $zip_entry, "r")) {
                    $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                    zip_entry_close($zip_entry);
                }
            }
        }

        $plist->parse($buf);
        $e = $plist->toArray();
        return array($e["CFBundleName"], $e["CFBundleIdentifier"], $e["CFBundleShortVersionString"]);
    }
}

function getApkManifestProperties($apk) {
spl_autoload_register(function ($className) {
    // Fix for OSX and *nix
    $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
    require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . $className . ".php");
});
	$apk = new \ApkParser\Parser($apk);
	$manifest = $apk->getManifest();
	return array($manifest->getPackageName(), $manifest->getVersionName());
}
?>
