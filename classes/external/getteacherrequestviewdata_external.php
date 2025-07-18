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
 * WS Get teacher request view data
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_certifygen\external;

use coding_exception;
use context_system;
use dml_exception;
use \core_external\external_api;
use invalid_parameter_exception;
use mod_certifygen\output\views\profile_my_certificates_view;
use \core_external\external_function_parameters;
use \core_external\external_single_structure;
use \core_external\external_value;
use moodle_exception;

/**
 * Get teacher request view data
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class getteacherrequestviewdata_external extends external_api {
    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function getteacherrequestviewdata_parameters(): external_function_parameters {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'user id'),
        ]);
    }

    /**
     * Get teacher request view data
     * @param int $userid
     * @return array
     * @throws coding_exception
     * @throws moodle_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    public static function getteacherrequestviewdata(int $userid): array {
        global $PAGE;
        self::validate_parameters(
            self::getteacherrequestviewdata_parameters(),
            ['userid' => $userid]
        );
        $PAGE->set_context(context_system::instance());
        $view = new profile_my_certificates_view($userid);
        $output = $PAGE->get_renderer('mod_certifygen');
        $data = $view->export_for_template($output);
        return (array) $data;
    }
    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function getteacherrequestviewdata_returns(): external_single_structure {
        return new external_single_structure(
            [
                'userid' => new external_value(PARAM_INT, 'model deleted'),
                'table' => new external_value(PARAM_RAW, 'table data'),
                'title' => new external_value(PARAM_RAW, 'title', VALUE_OPTIONAL),
                'mycertificates' => new external_value(PARAM_BOOL, 'table data', VALUE_OPTIONAL),
                'cancreaterequest' => new external_value(PARAM_BOOL, 'cancreaterequest', VALUE_OPTIONAL),
            ]
        );
    }
}
