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
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos..
/**
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace mod_certifygen\external;


use external_api;
use external_function_parameters;
use external_single_structure;
use external_multiple_structure;
use external_value;
use mod_certifygen\persistents\certifygen_context;
use mod_certifygen\persistents\certifygen_model;

global $CFG;
require_once($CFG->dirroot.'/user/lib.php');
class get_courses_as_teacher_external extends external_api {
    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function get_courses_as_teacher_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'userid' => new external_value(PARAM_INT, 'user id'),
            ]
        );
    }

    /**
     * @param int $userid
     * @return array
     * @throws \invalid_parameter_exception
     */
    public static function get_courses_as_teacher(int $userid): array {
        /**
         * OLD
         * Devuelve un json con la información necesaria para el anterior servicio para
         * confeccionar el certificado. El objetivo de este servicio es independizar el proceso de
         * obtención de los datos del proceso de generación del documento con la presentación
         * final.
         */
        /**
         * NEW:
         * Devuelve un json con la lista de cursos en los cuales figura como profesor la persona indicada
         * por su identificador (userid).
         * Este servicio permitirá a un sistema externo mostrar los cursos certificables.
         * El servicio devolverá como mínimo los siguientes atributos de cada curso y se valorará que se ofrezca un
         * servicio para configurar otros atributos de los disponibles para el profesor y los cursos en moodle:
         * a. course.shortname
         * b. course.fullname
         * c. course.categoryid.
         * d. reportype asociado al curso: [model type]
 */
        $params = self::validate_parameters(
            self::get_courses_as_teacher_parameters(), ['userid' => $userid]
        );
        $results = ['courses' => [], 'teacher' => [], 'error' => []];
        $haserror = false;
        $courses = [];
        try {
            // User exists.
            $users = user_get_users_by_id([$params['userid']]);
            if (empty($users)) {
                unset($results['courses']);
                unset($results['teacher']);
                $results['error']['code'] = 'user_not_found';
                $results['error']['message'] = 'User not found';
                return $results;
            }
            $results['teacher'] = [
                'id' => $params['userid'],
                'fullname' => fullname(reset($users)),
            ];
            // Get courses with a certifygen_model asociated where the user is editingteacher.
            $enrolments = enrol_get_all_users_courses($params['userid'], true);
            foreach ($enrolments as $enrolment) {
                $coursecontext = \context_course::instance($enrolment->ctxinstance);
                $roles = get_users_roles($coursecontext, [$params['userid']]);
                $roles = reset($roles);
                $reporttypes = [];
                foreach ($roles as $role) {
                    if ($role->shortname != 'editingteacher') {
                        continue;
                    }
                    $modelsids = certifygen_context::get_course_valid_modelids($enrolment->ctxinstance);
                    foreach ($modelsids as $modelid) {
                        $model = new certifygen_model($modelid);
                        $reporttypes[] = [
                            'type' => $model->get('type'),
                            'modelid' => $modelid,
                        ];
                    }
                }
                if (empty($reporttypes)) {
                    continue;
                }
                $courses[] = [
                    'shortname' => $enrolment->shortname,
                    'fullname' => $enrolment->fullname,
                    'categoryid' => $enrolment->category,
                    'reporttypes'   => $reporttypes,
                ];
            }
            $results['courses'] = $courses;
        } catch (\moodle_exception $e) {
            $haserror = true;$results['error']['code'] = $e->getCode();
            $results['error']['message'] = $e->getMessage();
        }
        if (!$haserror) {
            unset($results['error']);
        }
        return $results;
    }
    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function get_courses_as_teacher_returns(): external_single_structure {
        return new external_single_structure(array(
                'courses' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'id'   => new external_value(PARAM_RAW, 'Course id', VALUE_OPTIONAL),
                            'shortname'   => new external_value(PARAM_RAW, 'Course shortname', VALUE_OPTIONAL),
                            'fullname' => new external_value(PARAM_RAW, 'Course fullname', VALUE_OPTIONAL),
                            'categoryid' => new external_value(PARAM_INT, 'Course category id', VALUE_OPTIONAL),
                            'reporttypes' => new external_multiple_structure(
                                new external_single_structure(
                                    [
                                        'type'   => new external_value(PARAM_INT, 'model type', VALUE_OPTIONAL),
                                        'modelid'   => new external_value(PARAM_INT, 'model id', VALUE_OPTIONAL),
                                    ], 'courses list')
                                , '', VALUE_OPTIONAL),
                        ],
                        'courses list', VALUE_OPTIONAL)
                , '', VALUE_OPTIONAL),
                'teacher' => new external_single_structure (
                    [
                        'fullname' => new external_value(PARAM_RAW, 'User fullname', VALUE_OPTIONAL),
                        'id' => new external_value(PARAM_INT, 'User id', VALUE_OPTIONAL),
                    ], 'Student info', VALUE_OPTIONAL),
                'error' => new external_single_structure([
                    'message' => new external_value(PARAM_RAW, 'Error message', VALUE_OPTIONAL),
                    'code' => new external_value(PARAM_RAW, 'Error code', VALUE_OPTIONAL),
                ], 'Errors information', VALUE_OPTIONAL),
            )
        );
    }
}