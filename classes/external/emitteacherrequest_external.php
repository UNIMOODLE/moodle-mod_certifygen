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

namespace mod_certifygen\external;
use coding_exception;
use context_system;
use core\invalid_persistent_exception;
use dml_exception;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use invalid_parameter_exception;
use mod_certifygen\certifygen;
use mod_certifygen\certifygen_file;
use mod_certifygen\event\certificate_issued;
use mod_certifygen\interfaces\ICertificateReport;
use mod_certifygen\persistents\certifygen_error;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;
use stdClass;
use stored_file;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/lib/datalib.php');
/**
 * Issue teacher certificate
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
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
     * Issue teacher certificate
     * @param int $id
     * @return array
     * @throws invalid_persistent_exception
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    public static function emitteacherrequest(int $id): array {

        global $PAGE, $USER;
        $PAGE->set_context(context_system::instance());
        self::validate_parameters(
            self::emitteacherrequest_parameters(),
            ['id' => $id]
        );
        $result = ['result' => true, 'message' => get_string('ok', 'mod_certifygen')];

        // Step 1: Change status to in progress.
        $teacherrequest = new certifygen_validations($id);
        if (!mod_certifygen_lang_is_installed($teacherrequest->get('lang'))) {
            $a = new stdClass();
            $a->lang = $teacherrequest->get('lang');
            $result['result'] = false;
            $result['message'] = get_string('lang_not_exists', 'mod_certifygen', $a);
            return $result;
        }
        if ($USER->id != $teacherrequest->get('userid')) {
            $context = context_system::instance();
            if (!has_capability('mod/certifygen:viewcontextcertificates', $context)) {
                $result['result'] = false;
                $result['message'] = get_string('nopermissiontoemitothercerts', 'mod_certifygen');
                return $result;
            }
        }
        $teacherrequest->set('status', certifygen_validations::STATUS_IN_PROGRESS);
        $teacherrequest->save();
        try {
            $certifygenmodel = new certifygen_model($teacherrequest->get('modelid'));
            $reportplugin = $certifygenmodel->get('report');
            if (empty($reportplugin)) {
                $result['result'] = false;
                $result['message'] = 'report plugin must be set on the model';
                return $result;
            }
            // Step 2: Create certificate file.
            $reportpluginclass = $reportplugin . '\\' . $reportplugin;
            /** @var ICertificateReport $subplugin */
            $subplugin = new $reportpluginclass();
            $result = $subplugin->create_file($teacherrequest);
            if (!$result['result']) {
                $teacherrequest->set('status', certifygen_validations::STATUS_TEACHER_ERROR);
                $teacherrequest->save();
                $data = [
                    'validationid' => $teacherrequest->get('id'),
                    'status' => $teacherrequest->get('status'),
                    'code' => 'teacher_certificate_error',
                    'message' => $result['message'],
                    'usermodified' => $USER->id,
                ];
                certifygen_error::manage_certifygen_error(0, (object)$data);
                return $result;
            }
            /** @var stored_file $file */
            $file = $result['file'];
            $userid = $teacherrequest->get('userid');
            $lang = $teacherrequest->get('lang');
            $modelid = $teacherrequest->get('modelid');
            $certifygenfile = new certifygen_file($file, $userid, $lang, $modelid, $id);
            $coursesids = $teacherrequest->get('courses');
            $coursesids = explode(',', $coursesids);
            $courses = self::get_courses_information($coursesids);
            $data = [
                'lang' => $lang,
                'user_id' => $userid,
                'user_fullname' => fullname($certifygenfile->get_user()),
                'code' => str_replace('.pdf', '', $file->get_filename()),
                'filename' => $file->get_filename(),
                'courses' => $courses,
            ];
            $certifygenfile->set_metadata($data);
            // Step 3: Call to validation plugin.
            $result = certifygen::start_emit_certificate_proccess($teacherrequest, $certifygenfile, $certifygenmodel);
            unset($result['file']);

            // Step 4: event trigger.
            certificate_issued::create_from_validation($teacherrequest)->trigger();
        } catch (moodle_exception $e) {
            debugging(__FUNCTION__ . ' e: ' . $e->getMessage());
            $result['result'] = false;
            $result['message'] = $e->getMessage();
            $teacherrequest->set('status', certifygen_validations::STATUS_ERROR);
            $teacherrequest->save();
            $data = [
                'validationid' => $teacherrequest->get('id'),
                'status' => $teacherrequest->get('status'),
                'code' => $e->getCode(),
                'message' => $result['message'],
                'usermodified' => $USER->id,
            ];
            certifygen_error::manage_certifygen_error(0, (object)$data);
        }
        return $result;
    }

    /**
     * @param array $ids
     * @return array
     * @throws dml_exception
     */
    private static function get_courses_information(array $ids): array {
        $courses = [];
        foreach ($ids as $id) {
            $course = get_course($id);
            $courses[] = [
                'id' => $id,
                'name' => $course->fullname,
            ];
        }
        return $courses;
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
                'message' => new external_value(PARAM_RAW, 'meesage', VALUE_OPTIONAL),
            ]
        );
    }
}
