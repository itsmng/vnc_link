<?php

include("../../../inc/includes.php");

use GlpiPlugin\VncLink\PluginVncLinkConfig;

$plugin = new Plugin();

if ($plugin->isActivated("vnc_link")) {
    $config = new PluginVncLinkConfig();
    if (isset($_POST["update"])) {
        Session::checkRight("plugin_vnc_link_config", UPDATE);
        PluginVncLinkConfig::updateConfigValues($_POST);
    } else {
        if (!Session::haveRight("plugin_vnc_link_config", READ | UPDATE)) {
            Html::displayRightError();
            return;
        }
        Html::header("VNC Link", $_SERVER["PHP_SELF"], "config", Plugin::class);
        $config->showConfigForm();
    }
} else {
    Html::header("settings", '', "config", "plugins");
    echo "<div class='center'><br><br><img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt='warning'><br><br>";
    echo "<b>Please enable the plugin before configuring it</b></div>";
    Html::footer();
}

Html::footer();
