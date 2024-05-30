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
 * @package    mod_certifygen
 * * @copyright  2024 Proyecto UNIMOODLE
 * * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * * @author     3IPUNT <contacte@tresipunt.com>
 * * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace mod_certifygen\external;


use coding_exception;
use context_system;
use dml_exception;
use external_api;
use external_multiple_structure;
use invalid_parameter_exception;
use mod_certifygen\output\views\context_certificate_view;
use mod_certifygen\persistents\certifygen_model;
use external_function_parameters;
use external_single_structure;
use external_value;
class getcontextcertificatedata_external extends external_api {
    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function getcontextcertificatedata_parameters(): external_function_parameters {
        return new external_function_parameters([
            'modelid' => new external_value(PARAM_INT, 'model id'),
            'courseid' => new external_value(PARAM_INT, 'course id'),
        ]);
    }

    /**
     * @param int $modelid
     * @param int $courseid
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    public static function getcontextcertificatedata(int $modelid, int $courseid): array {
        global $PAGE;
        self::validate_parameters(
            self::getcontextcertificatedata_parameters(), ['modelid' => $modelid, 'courseid' => $courseid]
        );
        $PAGE->set_context(context_system::instance());
        $model = new certifygen_model($modelid);
        $view = new context_certificate_view($model, $courseid);
        $output = $PAGE->get_renderer('mod_certifygen');
        $data = $view->export_for_template($output);

        return (array) $data;
    }
    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function getcontextcertificatedata_returns(): external_single_structure {
        return new external_single_structure(
            [
                'list' => new external_multiple_structure(
                    new external_single_structure([
                        'code' => new external_value(PARAM_RAW, 'model deleted', VALUE_OPTIONAL),
                        'status' => new external_value(PARAM_RAW, 'model deleted'),
                        'modelid' => new external_value(PARAM_INT, 'model deleted'),
                        'lang' => new external_value(PARAM_RAW, 'model deleted'),
                        'langstring' => new external_value(PARAM_RAW, 'model deleted'),
                        'id' => new external_value(PARAM_INT, 'model deleted', VALUE_OPTIONAL),
                        'courseid' => new external_value(PARAM_INT, 'model deleted'),
                        'userid' => new external_value(PARAM_INT, 'model deleted'),
                        'canemit' => new external_value(PARAM_BOOL, 'model deleted', VALUE_OPTIONAL),
                        'candownload' => new external_value(PARAM_BOOL, 'model deleted', VALUE_OPTIONAL),
                    ])
                )
            ]
        );
    }
}