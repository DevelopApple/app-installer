<?php
namespace CFPropertyList;
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
        foreach(scanDirBlacklist($basepath.$appFolder) as $versionFolder) {
            $ipa = glob($basepath.$appFolder."/".$versionFolder."/*.ipa");
            if (count($ipa) > 0) {
                $tempArray = array();
                $tempArray["name"] = $appFolder;
                $tempArray["version"] = $versionFolder;
                $tempArray["filepath"] = $ipa[0];
                array_push($values, $tempArray);
            }

        }
    }
    return $values;
}

function getAndroidApps($appFolders, $basepath, $blacklist = array(".", "..")) {
    $values = array();
    foreach ($appFolders as $appFolder) {
        foreach(scanDirBlacklist($basepath.$appFolder) as $versionFolder) {
            $apk = glob($basepath.$appFolder."/".$versionFolder."/*.apk");
            if (count($apk) > 0) {
                $tempArray = array();
                $tempArray["name"] = $appFolder;
                $tempArray["version"] = $versionFolder;
                $tempArray["filepath"] = $apk[0];
                array_push($values, $tempArray);
            }

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

function getCFProperties($ipa) {
    require_once(__DIR__.'/libraries/CFPropertyList/CFPropertyList.php');

    $plist = new CFPropertyList();

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
        return array($e["CFBundleIdentifier"], $e["CFBundleVersion"]);
    }
}
?>
