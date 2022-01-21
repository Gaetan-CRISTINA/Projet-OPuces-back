<?php
/**
 * Plugin Name: OPuces
 * Description: Plugin Team Opuces
 * Version: 1.0
 * Author: Team Back
 */

use OPuces\Plugin;
use OPuces\API;
// use OPuces\API;

require __DIR__ . '/vendor-opuces/autoload.php';

$oPuces = new Plugin();
$api = new API();

register_activation_hook(
    __FILE__,
    [$oPuces, 'activate']
);

register_deactivation_hook(
    __FILE__,
    [$oPuces, 'deactivate']
);