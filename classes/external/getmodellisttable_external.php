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
 * WS Get model list table
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_certifygen\external;
use context_system;
use dml_exception;
use external_api;
use invalid_parameter_exception;
use mod_certifygen\tables\modellist_table;
use moodle_url;
use external_function_parameters;
use external_single_structure;
use external_value;
use required_capability_exception;
/**
 * Get model list table
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class getmodellisttable_external extends external_api {
    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function getmodellisttable_parameters(): external_function_parameters {
        return new external_function_parameters([]);
    }

    /**
     * Get model list table
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     */
    public static function getmodellisttable(): array {
        global $PAGE;
        self::validate_parameters(
            self::getmodellisttable_parameters(),
            []
        );
        $context = context_system::instance();
        $PAGE->set_context($context);
        require_capability('mod/certifygen:manage', $context);
        $tablelist = new modellist_table();
        $tablelist->baseurl = new moodle_url('/mod/certifygen/modelmanager.php');
        ob_start();
        // Optional_params 10 and true.
        $tablelist->out(10, true);
        $out1 = ob_get_contents();
        ob_end_clean();
        return [
            'table' => $out1,
        ];
    }
    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function getmodellisttable_returns(): external_single_structure {
        return new external_single_structure(
            [
                'table' => new external_value(PARAM_RAW, 'model list table'),
            ]
        );
    }
}
