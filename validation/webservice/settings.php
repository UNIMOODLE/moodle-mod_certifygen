<?php
// This file is part of Moodle - http://moodle.org/
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

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

// Enlace principal de settings.
$ADMIN->add('modsettingcertifygencat',
    new admin_category('certifygenvalidation_webservice_cat',
        get_string('pluginname', 'certifygenvalidation_webservice')));

// Certifygenreport_basic settings.
$settings = new admin_settingpage(
    'modsettingcertifygenvalidationwebservice',
    get_string('pluginnamesettings', 'certifygenvalidation_webservice'),
    'moodle/site:config');
if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox('certifygenvalidation_webservice/enabled',
        new lang_string('enable', 'certifygenvalidation_webservice'),
        new lang_string('enable_help', 'certifygenvalidation_webservice'), 0));

}
