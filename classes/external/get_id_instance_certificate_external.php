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
use external_multiple_structure;
use external_single_structure;
use external_value;
use mod_certifygen\persistents\certifygen;
use mod_certifygen\persistents\certifygen_context;

global $CFG;
require_once($CFG->dirroot.'/user/lib.php');
class get_id_instance_certificate_external extends external_api {
    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function get_id_instance_certificate_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'userid' => new external_value(PARAM_INT, 'user id'),
            ]
        );
    }
    public static function get_id_instance_certificate(int $userid): array {
        /**
         * Devuelve una lista de aquellas instancias de mod_certificate visibles,
         * con restricciones verificadas a las que el usuario puede acceder y generar el certificado de acuerdo
         * con la configuración de la instancia.
         */
        $params = self::validate_parameters(
            self::get_id_instance_certificate_parameters(), ['userid' => $userid]
        );
        $results = ['instances' => [], 'error' => []];
        $haserror = false;
        $instances = [];
        try {
            // User exists.
            $user = user_get_users_by_id([$params['userid']]);
            if (empty($user)) {
                $results['error']['code'] = 'user_not_found';
                $results['error']['message'] = 'User not found';
                return $results;
            }
            // Get all mod_certifygen activities;
            $allactivities = certifygen::get_records();
            $courseids = array_map(function($activity) {
                return $activity->get('course');
            }, $allactivities);

            // Get courses with mod_certifygen activity where the user is student.
            $enrolments = enrol_get_all_users_courses($params['userid'], true);
            foreach ($enrolments as $enrolment) {
                if (!in_array($enrolment->ctxinstance, $courseids)) {
                    continue;
                }
                $coursecontext = \context_course::instance( $enrolment->ctxinstance);
                $roles = get_users_roles($coursecontext, [$params['userid']]);
                $roles = reset($roles);
                foreach ($roles as $role) {
                    if ($role->shortname != 'student') {
                        continue;
                    }
                    $course = [
                        'shortname' => $enrolment->shortname,
                        'fullname' => $enrolment->fullname,
                        'categoryid' => $enrolment->category,
                    ];
                    $instance['course'] = $course;
                    $instance['instance'] = [
                        'name' => 'asd',
                        'modelname' => 'asd',
                        'modelmode' => 1,
                        'modeltimeondemmand' => 0,
                        'modeltype' => 1,
                        'modeltemplateid' => 1,
                        'modellangs' => 'asd,asd',
                        'modelvalidation' => 'asd',
                    ];
                    $instances[] = $instance;
                }
            }
            $results['instances'] = $instances;
        } catch (\moodle_exception $e) {
            unset($results['instances']);
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
    public static function get_id_instance_certificate_returns(): external_single_structure {
        return new external_single_structure(array(
                'instances' => new external_multiple_structure( new external_single_structure(
                        [
                            'course'   => new external_single_structure([
                                'shortname' => new external_value(PARAM_RAW, 'Course shortname', VALUE_OPTIONAL),
                                'fullname' => new external_value(PARAM_RAW, 'Course fullname', VALUE_OPTIONAL),
                                'categoryid' => new external_value(PARAM_INT, 'Category id', VALUE_OPTIONAL),
                            ], 'Course information', VALUE_OPTIONAL),
                            'instance'   => new external_single_structure([
                                'name' => new external_value(PARAM_RAW, 'Instance name', VALUE_OPTIONAL),
                                'modelname' => new external_value(PARAM_RAW, 'Model name', VALUE_OPTIONAL),
                                'modelmode' => new external_value(PARAM_INT, 'Model mode', VALUE_OPTIONAL),
                                'modeltimeondemmand' => new external_value(PARAM_INT, 'Model timeondemmand', VALUE_OPTIONAL),
                                'modeltype' => new external_value(PARAM_INT, 'Model type', VALUE_OPTIONAL),
                                'modeltemplateid' => new external_value(PARAM_INT, 'Model template id', VALUE_OPTIONAL),
                                'modellangs' => new external_value(PARAM_RAW, 'Model langs', VALUE_OPTIONAL),
                                'modelvalidation' => new external_value(PARAM_RAW, 'Model validation', VALUE_OPTIONAL),
                            ], 'Module Instance information', VALUE_OPTIONAL),
                        ], 'Module Instances list', VALUE_OPTIONAL)
                ),
                'error' => new external_single_structure([
                    'message' => new external_value(PARAM_RAW, 'Error message'),
                    'code' => new external_value(PARAM_RAW, 'Error code'),
                ], 'Errors information', VALUE_OPTIONAL),
            )
        );
    }
}