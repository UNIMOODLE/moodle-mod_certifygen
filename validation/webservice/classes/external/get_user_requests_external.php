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
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos..

/**
 *
 * @package    certifygenvalidation_webservice
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace certifygenvalidation_webservice\external;

use certifygenfilter;
use certifygenvalidation_webservice\certifygenvalidation_none;
use certifygenvalidation_webservice\certifygenvalidation_webservice;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use invalid_parameter_exception;
use mod_certifygen\persistents\certifygen;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot.'/mod/certifygen/lib.php');
require_once($CFG->dirroot.'/mod/certifygen/classes/filters/certifygenfilter.php');
/**
 * get_user_requests_external
 * @package    certifygenvalidation_webservice
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_user_requests_external extends external_api {
    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function get_user_requests_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'userid' => new external_value(PARAM_INT, 'user id'),
                'userfield' => new external_value(PARAM_RAW, 'user field'),
                'lang' => new external_value(PARAM_RAW, 'language'),
            ]
        );
    }

    /**
     * Returns only in progress user request.
     * @param int $userid
     * @param string $userfield
     * @param string $lang
     * @return array[]
     * @throws invalid_parameter_exception
     */
    public static function get_user_requests(int $userid, string $userfield, string $lang): array {
        $params = self::validate_parameters(
            self::get_user_requests_parameters(), ['userid' => $userid, 'userfield' => $userfield, 'lang' => $lang]
        );
        try {
            $context = \context_system::instance();
            require_capability('mod/certifygen:manage', $context);
            // Is plugin enabled?
            $wsplugin = new certifygenvalidation_webservice();
            if (!$wsplugin->is_enabled()) {
                return ['error' => [
                    'message' => 'ws validation plugin not enabled',
                    'code' => 'pluginnotenabled',
                    ],
                ];
            }
            // Choose user parameter.
            $uparam = mod_certifygen_validate_user_parameters_for_ws($params['userid'], $params['userfield']);
            if (array_key_exists('error', $uparam)) {
                return $uparam;
            }
            // Filter to return course names in $lang language.
            $filter = new certifygenfilter(\context_system::instance(), [], $lang);
            $requests = certifygen_validations::get_records(['userid' => $params['userid']]);
            $userrequest = [];
            foreach ($requests as $request) {
                $urequest = [];
                $model = new certifygen_model($request->get('modelid'));
                if ($model->get('validation') != 'certifygenvalidation_webservice') {
                    continue;
                }
                if ($request->get('status') != certifygen_validations::STATUS_IN_PROGRESS) {
                    continue;
                }
                $urequest['id'] = $request->get('id');
                if (!empty($request->get('name'))) {
                    $urequest['name'] = $request->get('name');
                }
                $courses = [];
                if (!empty($request->get('courses'))) {
                    $coursesids = explode(',', $request->get('courses'));
                    foreach ($coursesids as $courseid) {
                        $course = get_course($courseid);
                        $coursefullname = $filter->filter($course->fullname);
                        $coursefullname = strip_tags($coursefullname);
                        $courseshortname = $filter->filter($course->shortname);
                        $courseshortname = strip_tags($courseshortname);
                        $courses[] = [
                            'id' => $courseid,
                            'fullname' => $coursefullname,
                            'shortname' => $courseshortname,
                        ];
                    }
                    $urequest['courses'] = $courses;
                }
                $urequest['code'] = certifygen_validations::get_certificate_code($request);
                if (!empty($request->get('certifygenid'))) {
                    $urequest['role'] = 'student';
                    $instance = [
                        'id' => (int)$request->get('certifygenid'),
                    ];
                    $certifygen = new certifygen($request->get('certifygenid'));
                    $actvname = $filter->filter($certifygen->get('name'));
                    $actvname = strip_tags($actvname);
                    $instance['name'] = $actvname;
                    $urequest['instance'] = $instance;
                }
                $modeldata = array_merge((array)$model->to_record(),
                    ['typedesc' => get_string('type_'. $model->get('type'), 'mod_certifygen')]);
                $urequest['model'] = $modeldata;
                $urequest['lang'] = $request->get('lang');
                $urequest['status'] = (int)$request->get('status');
                $urequest['statusdesc'] = get_string('status_'. $request->get('status'), 'mod_certifygen');
                $userrequest[] = $urequest;
            }
        } catch (moodle_exception $exception) {
            return ['error' => [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
            ]];
        }

        return ['requests' => $userrequest];
    }
    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function get_user_requests_returns(): external_single_structure {
        return new external_single_structure([
            'requests' => new external_multiple_structure( new external_single_structure(
                    [
                        'id' => new external_value(PARAM_RAW, 'Request id'),
                        'name' => new external_value(PARAM_RAW, 'Request name - (only on teacher requests)', VALUE_OPTIONAL),
                        'code' => new external_value(PARAM_RAW, 'Certificate code'),
                        'lang' => new external_value(PARAM_RAW, 'Certificate lang'),
                        'status' => new external_value(PARAM_RAW, 'Certificate status'),
                        'statusdesc' => new external_value(PARAM_RAW, 'Certificate status'),
                        'courses'   => new external_multiple_structure(
                            new external_single_structure([
                            'id' => new external_value(PARAM_INT, 'Course id', VALUE_OPTIONAL),
                            'shortname' => new external_value(PARAM_RAW, 'Course shortname', VALUE_OPTIONAL),
                            'fullname' => new external_value(PARAM_RAW, 'Course fullname', VALUE_OPTIONAL),
                            ], 'Course information', VALUE_OPTIONAL),
                        'only for teachers requests', VALUE_OPTIONAL),
                        'instance'   => new external_single_structure([
                            'id' => new external_value(PARAM_INT, 'Instance id', VALUE_OPTIONAL),
                            'name' => new external_value(PARAM_RAW, 'Instance name', VALUE_OPTIONAL),
                        ], 'Module Instance information', VALUE_OPTIONAL),
                        'model'   => new external_single_structure([
                            'id' => new external_value(PARAM_INT, 'Instance id', VALUE_OPTIONAL),
                            'name' => new external_value(PARAM_RAW, 'Instance name', VALUE_OPTIONAL),
                            'idnumber' => new external_value(PARAM_RAW, 'Instance name', VALUE_OPTIONAL),
                            'type' => new external_value(PARAM_INT, 'Instance name', VALUE_OPTIONAL),
                            'typedesc' => new external_value(PARAM_RAW, 'Instance name', VALUE_OPTIONAL),
                            'mode' => new external_value(PARAM_INT, 'Instance name', VALUE_OPTIONAL),
                            'templateid' => new external_value(PARAM_INT, 'Instance name', VALUE_OPTIONAL),
                            'timeondemmand' => new external_value(PARAM_INT, 'Instance name', VALUE_OPTIONAL),
                            'langs' => new external_value(PARAM_RAW, 'Instance name', VALUE_OPTIONAL),
                            'validation' => new external_value(PARAM_RAW, 'Instance name', VALUE_OPTIONAL),
                            'report' => new external_value(PARAM_RAW, 'Instance name', VALUE_OPTIONAL),
                            'repository' => new external_value(PARAM_RAW, 'Instance name', VALUE_OPTIONAL),
                        ], 'Model information'),
                    ], 'User requests list', VALUE_OPTIONAL)
                , '', VALUE_OPTIONAL),
            'error' => new external_single_structure([
                'message' => new external_value(PARAM_RAW, 'Error message'),
                'code' => new external_value(PARAM_RAW, 'Error code'),
            ], 'Errors information', VALUE_OPTIONAL),
            ]);
    }
}
