<?php
$magicword = "changeme"; //This lists all apps
$applicationsfolder = "applications/";

//////////////////////////////////////////////////////////////
$appstolist = preg_split("/,/", $_GET["apps"]);
require_once("functions.php");
$appsFolders = scanDirBlacklist($applicationsfolder, array(".", ".."));
$count = 0;
$name = "";
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Unc Inc App Installer</title>
        <meta name="viewport" content="width=device-width" />
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    </head>
    <body class="browser-desktop">
        <div id="container" class="container">
                           <div class='new-ios'>

                    <h1>Install Apps</h1>

                    <p>If installation of an application fails, your device might not have been approved yet.</p>
                   <p class="bordertop"></p>
                    </div>
                    <div style='clear:both;'><br/></div>
        <div class="row">
        <h2>iOS Apps</h2>
            <?php if (disableIfNotiDevice() == "disabled") {?>
        <p>Please visit this page on your smart device to start the installation.</p>
<?php }

foreach (getiOSApps($appsFolders, $applicationsfolder) as $app) :
    replaceManifestURL($app);
if(in_array($app["name"], $appstolist) || in_array($magicword, $appstolist)) {
    $count = $count + 1;
    if($count == 3) {
        echo '</div><div class="row">';
        $count = 0;
    }
    $name = $app["name"];
?>
        <div class="col-sm-4">
                    <div class="version">
                        <p class="borderbottom"></p>
                        <h2><?php echo $app["name"]." (".$app["version"].")"; ?></h2>
                        <div style='clear:both;'></div>
                        <a class="btn btn-info <?php echo disableIfNotiDevice();?>" role="button" href="itms-services://?action=download-manifest&amp;url=<?php echo urlencode("https://apps.uncinc.nl/".$app["ipa"]."?format=plist") ?>">Install Application</a>
                    </div>
        </div>
<?php }
endforeach;
if ($count == 2) {
    echo "</div>";
}
if (disableIfNotiDevice() == "disabled") {
?>
        </div><div class="row">
        <hr>
        <h2>Android Apps</h2>
<?php
$count = 0;
    foreach (getAndroidApps($appsFolders, $applicationsfolder) as $app) :
        if(in_array($app["name"], $appstolist) || in_array($magicword, $appstolist)) {
            $count = $count + 1;
            if($count == 3) {
                echo '</div><div class="row">';
                $count = 0;
            }
            $name = $app["name"];
?>
        <div class="col-sm-4">
                    <div class="version">
                        <p class="borderbottom"></p>
                        <h2><?php echo $app["name"]." (".$app["version"].")"; ?></h2>
                        <div style='clear:both;'></div>
                        <a class="btn btn-info" role="button" href="<?php echo "https://apps.uncinc.nl/".$app["filepath"]; ?>">Download Application</a>
                    </div>
        </div>
<?php }
endforeach;
if ($count == 2) {
    echo "</div>";
}
}?>

                </div>
              </body>
</html>
