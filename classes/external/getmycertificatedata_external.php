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
 * @package    mod_certifygen
 * * @copyright  2024 Proyecto UNIMOODLE
 * * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * * @author     3IPUNT <contacte@tresipunt.com>
 * * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace mod_certifygen\external;

global $CFG;
require_once($CFG->dirroot . '/lib/formslib.php');
use coding_exception;
use context_system;
use dml_exception;
use external_api;
use external_multiple_structure;
use invalid_parameter_exception;
use mod_certifygen\output\views\activity_view;
use mod_certifygen\output\views\mycertificates_view;
use mod_certifygen\output\views\profile_my_certificates_view;
use mod_certifygen\output\views\student_view;
use mod_certifygen\persistents\certifygen_model;
use external_function_parameters;
use external_single_structure;
use external_value;
use moodle_exception;
use moodle_url;

class getmycertificatedata_external extends external_api {
    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function getmycertificatedata_parameters(): external_function_parameters {
        return new external_function_parameters([
            'modelid' => new external_value(PARAM_INT, 'model id'),
            'courseid' => new external_value(PARAM_INT, 'course id'),
            'cmid' => new external_value(PARAM_INT, 'cm id'),
            'lang' => new external_value(PARAM_RAW, 'lang'),
        ]);
    }

    /**
     * @param int $modelid
     * @param int $courseid
     * @param int $cmid
     * @param string $lang
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public static function getmycertificatedata(int $modelid, int $courseid, int $cmid, string $lang): array {
        global $PAGE, $DB;
        self::validate_parameters(
            self::getmycertificatedata_parameters(), ['modelid' => $modelid, 'courseid' => $courseid, 'cmid' => $cmid,
                'lang' => $lang]
        );
        $PAGE->set_context(context_system::instance());
        $model = new certifygen_model($modelid);
        if ($cmid > 0) {
            $cm = get_coursemodule_from_id('certifygen', $cmid, 0, false, MUST_EXIST);
            $certifygen = $DB->get_record('certifygen', ['id' => $cm->instance], '*', MUST_EXIST);
            $certifygenmodel = $DB->get_record('certifygen_model', ['id' => $certifygen->modelid], '*', MUST_EXIST);
            $view = new activity_view($courseid, $certifygenmodel->templateid, $cm, $lang);
        } else {
//            $view = new profile_my_certificates_view();
            // Creo q este caso no se usa en este serivicio. mando error para controlarlo.
            throw new moodle_exception('cmid must be greater than 0.');
        }
        
        $output = $PAGE->get_renderer('mod_certifygen');
        $data = $view->export_for_template($output);

        return (array) $data;
    }
    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function getmycertificatedata_returns(): external_single_structure {
        return new external_single_structure(
            [
                'table' => new external_value(PARAM_RAW, 'table html'),
                'form' => new external_value(PARAM_RAW, 'form html', VALUE_OPTIONAL),
                'isstudent' => new external_value(PARAM_BOOL, 'user is student', VALUE_OPTIONAL),
            ]
        );
    }
}