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
// Project implemented by the "Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * @package    certifygenreport_basic
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

// Enlace principal de settings.
$ADMIN->add('modsettingcertifygencat',
    new admin_category('certifygenreport_basic_cat',
        get_string('pluginname', 'certifygenreport_basic')));

// Certifygenreport_basic settings.
$settings = new admin_settingpage(
    'modsettingcertifygenreportbasic',
    get_string('pluginnamesettings', 'certifygenreport_basic'),
    'moodle/site:config');
if ($ADMIN->fulltree) {

    // Habilitar.
    $settings->add(new admin_setting_configcheckbox('certifygenreport_basic/enabled',
        new lang_string('enable', 'certifygenreport_basic'),
        new lang_string('enable_help', 'certifygenreport_basic'), 0));

    // Logo.
    $name = new lang_string('logo', 'certifygenreport_basic');
    $desc = new lang_string('logo_desc', 'certifygenreport_basic');
    $setting = new admin_setting_configstoredfile('certifygenreport_basic/logo',
        $name,
        $desc, 'logo', 0, ['accepted_types' => ['.png']]);
    $settings->add($setting);

    // Footer.
    $name = new lang_string('footer', 'certifygenreport_basic');
    $desc = new lang_string('footer_desc', 'certifygenreport_basic');
    $setting = new admin_setting_confightmleditor('certifygenreport_basic/footer',
        $name,
        $desc, '');
    $settings->add($setting);
    $settings->add($setting);


}
//$ADMIN->add('modsettingcertifygencat', $settings);
