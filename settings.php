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
 *
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

global $CFG, $ADMIN;
// Enlace principal de settings.
$ADMIN->add(
    'modsettings',
    new admin_category(
        'modsettingcertifygencat',
        get_string('modulename', 'certifygen'),
        $module->is_enabled() === false
    )
);

// Certifygen settings.
$settings = new admin_settingpage('modsettingcertifygen', get_string('pluginnamesettings', 'mod_certifygen'), 'moodle/site:config');
if ($ADMIN->fulltree) {
    // Userfield.
    $customfields = \availability_profile\condition::get_custom_profile_fields();
    $options = [
        '' => get_string('chooseuserfield', 'mod_certifygen'),
        'username' => get_string('username'),
        'idnumber' => get_string('idnumber'),
        'email' => get_string('email'),
    ];
    foreach ($customfields as $customfield) {
        if ($customfield->datatype == 'text') {
            $options['profile_' . $customfield->id] = $customfield->name;
        }
    }
    $settings->add(new admin_setting_configselect(
        'mod_certifygen/userfield',
        new lang_string('userfield', 'mod_certifygen'),
        new lang_string('userfield_desc', 'mod_certifygen'),
        '',
        $options
    ));
}
$ADMIN->add('modsettingcertifygencat', $settings);

// Category certifygencat for extra links.
$ADMIN->add(
    'root',
    new admin_category(
        'certifygencat',
        get_string('pluginname', 'mod_certifygen'),
        $module->is_enabled() === false
    ),
    'location'
);
// Model manager page access.
$modelsmanagersettings = new admin_externalpage(
    'certifygenmodelsmanager',
    get_string('modelsmanager', 'mod_certifygen'),
    $CFG->wwwroot . '/mod/certifygen/modelmanager.php',
    'moodle/site:config',
    $module->is_enabled() === false
);
$ADMIN->add('certifygencat', $modelsmanagersettings);
// See teacher requests.
$teacherrequestreportsettings = new admin_externalpage(
    'certifygenteacherrequestreport',
    get_string('certifygenteacherrequestreport', 'mod_certifygen'),
    $CFG->wwwroot . '/mod/certifygen/teacherrequestreport.php',
    'mod/certifygen:viewcontextcertificates',
    $module->is_enabled() === false
);
$ADMIN->add('certifygencat', $teacherrequestreportsettings);
// Search for ccertificates by code.
$searchforcerts = new admin_externalpage(
    'certifygensearchfor',
    get_string('certifygensearchfor', 'mod_certifygen'),
    $CFG->wwwroot . '/mod/certifygen/code.php',
    'mod/certifygen:manage',
    $module->is_enabled() === false
);
$ADMIN->add('certifygencat', $searchforcerts);

$errorspage = new admin_externalpage(
    'certifygenerrors',
    get_string('certifygenerrors', 'mod_certifygen'),
    $CFG->wwwroot . '/mod/certifygen/showerrors.php',
    'mod/certifygen:manage'
);
$ADMIN->add('modsettingcertifygencat', $errorspage);

// Subplugins settings.
foreach (core_plugin_manager::instance()->get_plugins_of_type('certifygenvalidation') as $plugin) {
    /** @var \mod_certifygen\plugininfo\certifygenvalidation $plugin */
    $plugin->load_settings($ADMIN, 'modsettingcertifygencat', true);
}

foreach (core_plugin_manager::instance()->get_plugins_of_type('certifygenreport') as $plugin) {
    /** @var \mod_certifygen\plugininfo\certifygenreport $plugin */
    $plugin->load_settings($ADMIN, 'modsettingcertifygencat', $hassiteconfig);
}
foreach (core_plugin_manager::instance()->get_plugins_of_type('certifygenrepository') as $plugin) {
    /** @var \mod_certifygen\plugininfo\certifygenrepository $plugin */
    $plugin->load_settings($ADMIN, 'modsettingcertifygencat', $hassiteconfig);
}

$settings = null;
