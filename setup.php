<?php

global $CFG_GLPI;

define('VNC_LINK_VERSION', '1.0.0');
define('VNC_LINK_ITSMNG_MIN_VERSION', '2.0');

$hostLoader = require __DIR__ . '/../../vendor/autoload.php';
$hostLoader->addPsr4('GlpiPlugin\\VncLink\\', __DIR__ . '/src/');

use GlpiPlugin\VncLink\PluginVncLinkProfile;
use GlpiPlugin\VncLink\PluginVncLinkConfig;

function plugin_version_vnc_link(): array
{
    return [
        'name'           => 'Vnc_link Plugin',
        'version'        => VNC_LINK_VERSION,
        'author'         => 'ITSMNG Team',
        'homepage'       => 'https://github.com/itsmng/plugin-vnc_link',
        'license'        => '<a href="../plugins/plugin-vnc_link/LICENSE" target="_blank">GPLv3</a>',
    ];
}

function plugin_init_vnc_link(): void
{
    global $PLUGIN_HOOKS, $CFG_GLPI;

    $PLUGIN_HOOKS['csrf_compliant']['vnc_link'] = true;

    Plugin::registerClass(PluginVncLinkProfile::class, ['addtabon' => 'Profile']);
    $PLUGIN_HOOKS['change_profile']['vnc_link'] = [PluginVncLinkProfile::class, 'changeProfile'];

    if (Session::haveRight('plugin_vnc_link_config', UPDATE)) {
        $PLUGIN_HOOKS['config_page']['vnc_link'] = 'front/config.form.php';
    }

    if (plugin_vnc_link_isComputerPage()) {
        $PLUGIN_HOOKS['add_javascript']['vnc_link'] = ['js/vnc_link.js'];
    }
}

function plugin_vnc_link_isComputerPage(): bool
{
    if (!isset($_SERVER['REQUEST_URI'])) {
        return false;
    }

    $uri = $_SERVER['REQUEST_URI'];

    if (strpos($uri, '/front/computer.form.php') === false) {
        return false;
    }

    if (!isset($_GET['id']) || (int)$_GET['id'] <= 0) {
        return false;
    }

    if (isset($_GET['withtemplate']) && (int)$_GET['withtemplate'] > 0) {
        return false;
    }

    return true;
}


function vnc_link_check_prerequisites(): bool
{
    $prerequisitesSuccess = true;

    if (version_compare(ITSM_VERSION, VNC_LINK_ITSMNG_MIN_VERSION, 'lt')) {
        echo "This plugin requires ITSM >= " . VNC_LINK_ITSMNG_MIN_VERSION . "<br>";
        $prerequisitesSuccess = false;
    }

    return $prerequisitesSuccess;
}

function vnc_link_check_config($verbose = false): bool
{
    if ($verbose) {
        echo "Checking plugin configuration<br>";
    }
    return true;
}
