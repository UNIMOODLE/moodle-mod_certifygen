<?php
// This file is part of the mod_certifygen plugin for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.
/**
 * @package   certifygenvalidation_webservice
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


use certifygenvalidation_webservice\external\change_status_external;
use certifygenvalidation_webservice\external\get_user_requests_external;

defined('MOODLE_INTERNAL') || die();

$functions = [
    'certifygenvalidation_webservice_change_status' => [
        'classname' => change_status_external::class,
        'methodname' => 'change_status',
        'description' => 'change_status',
        'type' => 'read',
        'capabilities' => 'mod/certifygen:manage',
    ],
    'certifygenvalidation_webservice_get_user_requests' => [
        'classname' => get_user_requests_external::class,
        'methodname' => 'get_user_requests',
        'description' => 'get_user_requests',
        'type' => 'read',
        'capabilities' => 'mod/certifygen:manage',
    ],
];
$services = [
    'Unimoodle Certifygen - WS Validation' => [
        'functions' => [
            'certifygenvalidation_webservice_change_status',
            'certifygenvalidation_webservice_get_user_requests',
        ]
    ]
];