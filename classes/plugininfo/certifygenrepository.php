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
 * Certifygenrepository class.
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

/**
 * Certifygenrepository class.
 *
 * @package   mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class certifygenrepository extends base {

    /**
     * Finds all enabled plugins, the result may include missing plugins.
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function get_enabled_plugins(): array {

        $plugins = core_plugin_manager::instance()->get_installed_plugins('certifygenrepository');
        if (!$plugins) {
            return [];
        }
        $enabled = [];
        foreach ($plugins as $plugin => $version) {
            $enabled[$plugin] = $plugin;
        }

        return $enabled;
    }

    /**
     * load_settings
     * @param part_of_admin_tree $adminroot
     * @param $parentnodename
     * @param $hassiteconfig
     * @return void
     */
    public function load_settings(part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig): void {
        $ADMIN = $adminroot;
        $plugininfo = $this; // Also can be used inside settings.php.

        if (!$this->is_installed_and_upgraded()) {
            return;
        }

        if (!$hassiteconfig || !file_exists($this->full_path('settings.php'))) {
            return;
        }
        $settings = new admin_settingpage($this->component, $this->displayname, 'moodle/site:config',
            $this->is_enabled() === false);
        include($this->full_path('settings.php')); // This may also set $settings to null.

        if ($settings) {
            $ADMIN->add($parentnodename, $settings);
        }
    }

    /**
     * get_settings_section_name
     * @return string
     */
    public function get_settings_section_name(): string {
        return 'certifygenrepository';
    }
}
