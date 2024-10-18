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
 * The task that provides all the steps to perform a complete backup is defined here.
 * @package    mod_certifygen
 * @category    backup
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/mod/certifygen/backup/moodle2/backup_certifygen_stepslib.php');

/**
 * The class provides all the settings and steps to perform one complete backup of mod_certifygen.
 *
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_certifygen_activity_task extends backup_activity_task {
    /**
     * Defines particular settings for the plugin.
     */
    protected function define_my_settings() {
        return;
    }

    /**
     * Defines particular steps for the backup process.
     */
    protected function define_my_steps() {
        $this->add_step(new backup_certifygen_activity_structure_step(
            'certifygen_structure',
            'certifygen.xml'
        ));
    }

    /**
     * Codes the transformations to perform in the activity in order to get transportable (encoded) links.
     *
     * @param string $content content to encode.
     * @return string encoded string
     */
    public static function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

        // Link to the list of choices.
        $search = "/(" . $base . "\/mod\/certifygen\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@CERTIFYGENINDEX*$2@$', $content);

        // Link to choice view by moduleid.
        $search = "/(" . $base . "\/mod\/certifygen\/view.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@CERTIFYGENVIEWBYID*$2@$', $content);

        return $content;
    }
}
