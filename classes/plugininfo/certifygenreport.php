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
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Subplugin info class.
 *
 * @package   mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_certifygen\plugininfo;
use admin_settingpage;
use coding_exception;
use core\plugininfo\base;
use core_plugin_manager;
use dml_exception;
use moodle_exception;
use moodle_url;
use part_of_admin_tree;

defined('MOODLE_INTERNAL') || die();

class certifygenreport extends base {

    /**
     * Finds all enabled plugins, the result may include missing plugins.
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function get_enabled_plugins() : array {
        global $DB;

        $plugins = core_plugin_manager::instance()->get_installed_plugins('certifygenreport');
        if (!$plugins) {
            return array();
        }
//        $installed = array();
//        foreach ($plugins as $plugin => $version) {
//            $installed[] = 'certifygenreport_'.$plugin;
//        }
//
//        list($installed, $params) = $DB->get_in_or_equal($installed, SQL_PARAMS_NAMED);
//        $disabled = $DB->get_records_select('config_plugins', "plugin $installed AND name = 'enabled'", $params, 'plugin ASC');
//
//        foreach ($disabled as $conf) {
//            if (!empty($conf->value)) {
//                continue;
//            }
//            list($type, $name) = explode('_', $conf->plugin, 2);
//            unset($plugins[$name]);
//        }
//
        $enabled = array();
        foreach ($plugins as $plugin => $version) {
            $enabled[$plugin] = $plugin;
        }

        return $enabled;
    }

    /**
     * Return URL used for management of plugins of this type.
     * @return moodle_url
     * @throws moodle_exception
     */
    public static function get_manage_url(): moodle_url
    {
        return new moodle_url('/mod/certifygen/adminmanageplugins.php', array('subtype'=>'certifygenreport'));
    }

    /**
     * @param part_of_admin_tree $adminroot
     * @param $parentnodename
     * @param $hassiteconfig
     * @return void
     */
    public function load_settings(part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig): void
    {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE; // In case settings.php wants to refer to them.
        $ADMIN = $adminroot; // May be used in settings.php.
        $plugininfo = $this; // Also can be used inside settings.php.

        if (!$this->is_installed_and_upgraded()) {
            return;
        }

        if (!$hassiteconfig or !file_exists($this->full_path('settings.php'))) {
            return;
        }
        $settings = new admin_settingpage($this->component, $this->displayname, 'moodle/site:config', $this->is_enabled() === false);
        include($this->full_path('settings.php')); // This may also set $settings to null.

        if ($settings) {
            $ADMIN->add($parentnodename, $settings);
        }
    }

    /**
     * @return string
     */
    public function get_settings_section_name(): string
    {
        return 'certifygenreport';
    }
}