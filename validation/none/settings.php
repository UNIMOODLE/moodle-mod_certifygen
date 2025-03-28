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
 *
 * @package   certifygenvalidation_none
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

// Enlace principal de settings.
$ADMIN->add(
    'modsettingcertifygencat',
    new admin_category(
        'certifygenvalidation_none_cat',
        get_string('pluginname', 'certifygenvalidation_none')
    )
);

// Certifygenreport_basic settings.
$settings = new admin_settingpage(
    'modsettingcertifygenvalidationnone',
    get_string('pluginnamesettings', 'certifygenvalidation_none'),
    'moodle/site:config'
);
if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox(
        'certifygenvalidation_none/enabled',
        new lang_string('enable', 'certifygenvalidation_none'),
        new lang_string('enable_help', 'certifygenvalidation_none'),
        0
    ));
    $settings->add(new admin_setting_configcheckbox(
        'certifygenvalidation_none/wsoutput',
        new lang_string('wsoutput', 'certifygenvalidation_none'),
        new lang_string('wsoutput_help', 'certifygenvalidation_none'),
        0
    ));
}
