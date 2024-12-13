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
 * @package   certifygenvalidation_csv
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
        'certifygenvalidation_csv_cat',
        get_string('pluginname', 'certifygenvalidation_csv')
    )
);

// Certifygenreport_basic settings.
$settings = new admin_settingpage(
    'modsettingcertifygenvalidationcsv',
    get_string('pluginnamesettings', 'certifygenvalidation_csv'),
    'moodle/site:config'
);
if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox(
        'certifygenvalidation_csv/enabled',
        new lang_string('enable', 'certifygenvalidation_csv'),
        new lang_string('enable_help', 'certifygenvalidation_csv'),
        0
    ));

    // FirmaCatalogService?wsdl.
    $settings->add(new admin_setting_configtext(
        'certifygenvalidation_csv/firmacatalogserviceurl',
        new lang_string('firmacatalogserviceurl', 'certifygenvalidation_csv'),
        new lang_string('firmacatalogserviceurl_help', 'certifygenvalidation_csv'),
        ""
    ));

    // FirmaQueryCatalogService?wsdl.
    $settings->add(new admin_setting_configtext(
        'certifygenvalidation_csv/firmaquerycatalogserviceurl',
        new lang_string('firmaquerycatalogserviceurl', 'certifygenvalidation_csv'),
        new lang_string('firmaquerycatalogserviceurl_help', 'certifygenvalidation_csv'),
        ""
    ));

    // AppId.
    $settings->add(new admin_setting_configtext(
        'certifygenvalidation_csv/appID',
        new lang_string('appID', 'certifygenvalidation_csv'),
        new lang_string('appID_help', 'certifygenvalidation_csv'),
        ""
    ));
    // WS output.
    $settings->add(new admin_setting_configcheckbox(
        'certifygenvalidation_csv/wsoutput',
        new lang_string('wsoutput', 'certifygenvalidation_csv'),
        new lang_string('wsoutput_help', 'certifygenvalidation_csv'),
        0
    ));
}
