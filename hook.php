<?php

use GlpiPlugin\VncLink\PluginVncLinkConfig;
use GlpiPlugin\VncLink\PluginVncLinkProfile;

function plugin_vnc_link_install(): bool
{
    set_time_limit(900);
    ini_set('memory_limit', '2048M');

    $classesToInstall = [
        PluginVncLinkConfig::class,
        PluginVncLinkProfile::class,
    ];

    echo "<center>";
    echo "<table class='tab_cadre_fixe'>";
    echo "<tr><th>".__("MySQL tables installation", "vnc_link")."<th></tr>";

    echo "<tr class='tab_bg_1'>";
    echo "<td align='center'>";

    foreach ($classesToInstall as $class) {
        if (isPluginItemType($class)) {
            if (!call_user_func([$class, 'install'])) {
                return false;
            }
        }
    }

    echo "</td>";
    echo "</tr>";
    echo "</table></center>";

    return true;
}

function plugin_vnc_link_uninstall(): bool
{
    echo "<center>";
    echo "<table class='tab_cadre_fixe'>";
    echo "<tr><th>".__("MySQL tables uninstallation", "vnc_link")."<th></tr>";

    echo "<tr class='tab_bg_1'>";
    echo "<td align='center'>";

    $classesToUninstall = [
        PluginVncLinkConfig::class,
        PluginVncLinkProfile::class,
    ];

    foreach ($classesToUninstall as $class) {
        if (isPluginItemType($class)) {
            if (!call_user_func([$class, 'uninstall'])) {
                return false;
            }
        }
    }

    echo "</td>";
    echo "</tr>";
    echo "</table></center>";

    return true;
}
