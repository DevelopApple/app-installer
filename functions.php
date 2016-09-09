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

function generateiOSPlist($filepath) {
	   $CFProperties = getCFProperties($filepath);
	   header('Content-type: text/xml');
print '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
	<key>items</key>
	<array>
		<dict>
			<key>assets</key>
			<array>
				<dict>
					<key>kind</key>
					<string>software-package</string>
					<key>url</key>
					<string>http://'.$_SERVER['HTTP_HOST'].'/'.$filepath.'</string>
				</dict>
			</array>
			<key>metadata</key>
			<dict>
				<key>bundle-identifier</key>
				<string>'.$CFProperties[1].'</string>
				<key>bundle-version</key>
				<string>'.$CFProperties[2].'</string>
				<key>kind</key>
				<string>software</string>
				<key>title</key>
				<string>'.$CFProperties[0].'</string>
			</dict>
		</dict>
	</array>
</dict>
</plist>
';
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
	$apk = new \ApkParser\Parser($apk);
	$manifest = $apk->getManifest();
	return array($manifest->getPackageName(), $manifest->getVersionName());
}
?>
