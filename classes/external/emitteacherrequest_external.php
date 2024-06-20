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
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace mod_certifygen\external;

global $CFG;
require_once($CFG->dirroot . '/lib/pdflib.php');

use coding_exception;
use core\invalid_persistent_exception;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use invalid_parameter_exception;
use mod_certifygen\certifygen;
use mod_certifygen\certifygen_file;
use mod_certifygen\interfaces\ICertificateValidation;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_teacherrequests;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;

class emitteacherrequest_external extends external_api {
    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function emitteacherrequest_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'id'),
            ]
        );
    }

    /**
     * @param int $id
     * @return array
     * @throws coding_exception
     * @throws invalid_parameter_exception
     * @throws invalid_persistent_exception
     */
    public static function emitteacherrequest(int $id): array {

        self::validate_parameters(
            self::emitteacherrequest_parameters(), ['id' => $id]
        );

        $result = ['result' => true, 'message' => 'OK'];

        // Step 1: Change status to in progress.
        $teacherrequest = new certifygen_teacherrequests($id);
        $teacherrequest->set('status', certifygen_teacherrequests::STATUS_IN_PROGRESS);
        $teacherrequest->save();

//        try {
//            // TODO: esto dependerá del report
//            // Step 2: Create pdf.
//            $doc = new \pdf();
//            $doc->setPrintHeader(false);
//            $doc->setPrintFooter(false);
//            $doc->AddPage();
//            $doc->Write(5, 'Certificado profesor');
//            $doc->Write(5, 'Listado de cursos a certificar:');
//            $courses = $teacherrequest->get('courses');
//            $courses = explode(',', $courses);
//            foreach ($courses as $courseid) {
//                $course = get_course($courseid);
//                $doc->Write(5, $course->fullname);
//            }
//            $res = $doc->Output('teacherrequest_'.$id, 'S');
//            $fs = get_file_storage();
//            $context = \context_system::instance();
//            $filerecord = [
//                'contextid' => $context->id,
//                'component' => 'mod_certifygen',
//                'filearea' => 'teacherrequest',
//                'itemid' => $id,
//                'filename' => 'teacherrequest_'.$id,
//                'filepath' => '/',
//            ];
//            $file = $fs->create_file_from_string($filerecord, $res);
//            $userid = $teacherrequest->get('userid');
//            $lang = $teacherrequest->get('lang');
//            $modelid = $teacherrequest->get('modelid');
//            $certifygenfile = new certifygen_file($file, $userid, $lang, $modelid, new \stdClass(), $id);
//            // Step 3: Call to validation plugin.
//            $certifygenmodel = new certifygen_model($teacherrequest->get('modelid'));
//            $validationplugin = $certifygenmodel->get('validation');
//            $validationpluginclass = $validationplugin . '\\' . $validationplugin;
//            if (get_config($validationplugin, 'enabled') === '1') {
//                error_log(__FUNCTION__ . ' ' . __LINE__);
//                /** @var ICertificateValidation $subplugin */
//                $subplugin = new $validationpluginclass();
//                $response = $subplugin->sendFile($certifygenfile);
//                if ($response['haserror']) {
//                    if (!array_key_exists('message', $result)) {
//                        error_log(__FUNCTION__ . ' ' . __LINE__);
//                        $result['message'] = 'validation_plugin_send_file_error';
//                    }
//                    $teacherrequest->set('status', certifygen_validations::STATUS_FINISHED_ERROR);
//                    $teacherrequest->save();
//                }
//            } else {
//                $result['result'] = false;
//                $result['message'] = 'plugin_not_enabled';
//                $teacherrequest->set('status', certifygen_validations::STATUS_FINISHED_ERROR);
//                $teacherrequest->save();
//            }
//        } catch (moodle_exception $e) {
//            error_log(__FUNCTION__ . ' ' . ' error: '.var_export($e->getMessage(), true));
//            $result['result'] = false;
//            $result['message'] = $e->getMessage();
//            $teacherrequest->set('status', certifygen_teacherrequests::STATUS_FINISHED_ERROR);
//            $teacherrequest->save();
//        }

        return $result;
    }
    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function emitteacherrequest_returns(): external_single_structure {
        return new external_single_structure(
            [
                'result' => new external_value(PARAM_BOOL, 'model deleted'),
                'message' => new external_value(PARAM_RAW, 'meesage'),
            ]
        );
    }

}