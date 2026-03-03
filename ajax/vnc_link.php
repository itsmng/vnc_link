<?php

include("../../../inc/includes.php");

Html::header_nocache();

Session::checkLoginUser();

$plugin = new Plugin();

if (!$plugin->isActivated('vnc_link')) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Plugin not activated';
    exit;
}

$computers_id = isset($_GET['computers_id']) ? (int)$_GET['computers_id'] : 0;

if ($computers_id <= 0) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Invalid computer ID';
    exit;
}

$computer = new Computer();
if (!$computer->getFromDB($computers_id)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Computer not found';
    exit;
}

if (!$computer->can($computers_id, READ)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Not authorized';
    exit;
}

function vnc_link_isValidMac(?string $mac): bool
{
    if ($mac === null) {
        return false;
    }

    $normalized = strtolower(str_replace('-', ':', trim($mac)));

    if (!preg_match('/^([0-9a-f]{2}:){5}[0-9a-f]{2}$/', $normalized)) {
        return false;
    }

    if ($normalized === '00:00:00:00:00:00' || $normalized === 'ff:ff:ff:ff:ff:ff') {
        return false;
    }

    return true;
}

function vnc_link_isValidIp(?string $ip): bool
{
    if ($ip === null) {
        return false;
    }

    $trimmed = trim($ip);

    if ($trimmed === '') {
        return false;
    }

    if ($trimmed === '127.0.0.1' || $trimmed === '::1') {
        return false;
    }

    return true;
}

$iterator = $DB->request([
    'SELECT' => [
        'glpi_ipaddresses.name AS ip',
        'glpi_networkports.mac'
    ],
    'FROM' => 'glpi_networkports',
    'INNER JOIN' => [
        'glpi_networknames' => [
            'ON' => [
                'glpi_networknames' => 'items_id',
                'glpi_networkports' => 'id', [
                    'AND' => [
                        'glpi_networknames.itemtype' => 'NetworkPort',
                        'glpi_networknames.is_deleted' => 0
                    ]
                ]
            ]
        ],
        'glpi_ipaddresses' => [
            'ON' => [
                'glpi_ipaddresses' => 'items_id',
                'glpi_networknames' => 'id', [
                    'AND' => [
                        'glpi_ipaddresses.itemtype' => 'NetworkName',
                        'glpi_ipaddresses.is_deleted' => 0
                    ]
                ]
            ]
        ]
    ],
    'WHERE' => [
        'glpi_networkports.items_id' => $computers_id,
        'glpi_networkports.itemtype' => 'Computer',
        'glpi_networkports.is_deleted' => 0
    ],
    'ORDERBY' => [
        'glpi_networkports.id',
        'glpi_ipaddresses.id'
    ]
]);

$selectedIp = null;
$selectedMac = null;

while ($row = $iterator->next()) {
    $ip = $row['ip'] ?? null;
    $mac = $row['mac'] ?? null;

    if (!vnc_link_isValidIp($ip)) {
        continue;
    }

    if (!vnc_link_isValidMac($mac)) {
        continue;
    }

    $selectedIp = trim($ip);
    $selectedMac = $mac;
    break;
}

if ($selectedIp === null) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'No valid IP/MAC found for this computer';
    exit;
}

$filename = 'computer-' . $computers_id . '.vnc';
$content = "[connection]\n";
$content .= "host={$selectedIp}\n";
$content .= "port=5900\n";

header('Content-Type: application/x-vnc; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($content));

echo $content;
