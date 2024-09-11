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
// Project implemented by the "Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * @package    certifygenrepository_onedrive
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

// Main link.
$ADMIN->add('modsettingcertifygencat',
    new admin_category('certifygenrepository_onedrive_cat',
        get_string('pluginname', 'certifygenrepository_onedrive')));

$settings = new admin_settingpage(
    'modsettingcertifygenrepositoryonedrive',
    get_string('pluginnamesettings', 'certifygenrepository_onedrive'),
    'moodle/site:config');
if ($ADMIN->fulltree) {

    // Enable.
    $settings->add(new admin_setting_configcheckbox('certifygenrepository_onedrive/enabled',
        new lang_string('enable', 'certifygenrepository_onedrive'),
        new lang_string('enable_help', 'certifygenrepository_onedrive'), 0));
    //  Onedrive folder.
    $settings->add(new admin_setting_configtext(
        'certifygenrepository_onedrive/folder',
        get_string('settings_folder', 'certifygenrepository_onedrive'),
        get_string('settings_folder_desc', 'certifygenrepository_onedrive'),
        'certifygen_certificates',
        PARAM_TEXT
    ));
}
