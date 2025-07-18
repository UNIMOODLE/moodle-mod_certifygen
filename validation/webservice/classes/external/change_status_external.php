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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/mod/certifygen/classes/filters/certifygenfilter.php');
require_once($CFG->dirroot . '/mod/certifygen/lib.php');

use certifygenvalidation_webservice\certifygenvalidation_webservice;
use coding_exception;
use context_system;
use \core_external\external_api;
use \core_external\external_function_parameters;
use \core_external\external_single_structure;
use \core_external\external_value;
use invalid_parameter_exception;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_repository;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;

/**
 * Change status
 * @package    certifygenvalidation_webservice
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class change_status_external extends external_api {
    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function change_status_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'userid' => new external_value(PARAM_INT, 'user id'),
                'userfield' => new external_value(PARAM_RAW, 'user field'),
                'requestid' => new external_value(PARAM_INT, 'instance id'),
                'url' => new external_value(PARAM_URL, 'certificate url'),
            ]
        );
    }

    /**
     * Change status
     * @param int $userid
     * @param string $userfield
     * @param int $requestid
     * @return array|array[]
     * @throws coding_exception
     * @throws invalid_parameter_exception
     */
    public static function change_status(int $userid, string $userfield, int $requestid, string $url): array {
        global $USER;
        $params = self::validate_parameters(
            self::change_status_parameters(),
            ['userid' => $userid, 'userfield' => $userfield, 'requestid' => $requestid, 'url' => $url]
        );
        try {
            $context = context_system::instance();
            require_capability('mod/certifygen:manage', $context);
            $results = [];
            // Choose user parameter.
            $uparam = mod_certifygen_validate_user_parameters_for_ws($params['userid'], $params['userfield']);
            if (array_key_exists('error', $uparam)) {
                return $uparam;
            }
            $userid = $uparam['userid'];
            // User exists.
            $users = user_get_users_by_id([$userid]);
            if (empty($users)) {
                $results['error']['code'] = 'user_not_found';
                $results['error']['message'] = get_string('user_not_found', 'mod_certifygen');
                return $results;
            }
            // Request exists.
            $request = certifygen_validations::get_record(['id' => $requestid]);
            if (!$request) {
                $results['error']['code'] = 'request_not_found';
                $results['error']['message'] = get_string('request_not_found', 'certifygenvalidation_webservice');
                return $results;
            }
            // Model must have ws validation.
            $model = new certifygen_model($request->get('modelid'));
            if ($model->get('validation') != 'certifygenvalidation_webservice') {
                $results['error']['code'] = 'validationplugin_not_accepted';
                $results['error']['message'] =
                        get_string('validationplugin_not_accepted', 'certifygenvalidation_webservice');
                return $results;
            }
            if ($model->get('repository') != 'certifygenrepository_url') {
                $results['error']['code'] = 'repositoryplugin_not_accepted';
                $results['error']['message'] =
                        get_string('repositoryplugin_not_accepted', 'certifygenvalidation_webservice');
                return $results;
            }
            // Request user.
            if ($userid != $request->get('userid')) {
                $results['error']['code'] = 'request_user_not_matched';
                $results['error']['message'] = 'This is not the user\'s request';
                return $results;
            }
            // Request status.
            if ($request->get('status') != certifygen_validations::STATUS_VALIDATION_OK) {
                $results['error']['code'] = 'request_status_not_accepted';
                $results['error']['message'] = 'The status request is not accepted';
                return $results;
            }
            // Save certificate url.
            $data  = [
                'validationid' => $requestid,
                'userid' => $userid,
                'url' => $url,
                //'usermodifed' => $USER->id,
                //'data' => '',
            ];
            $rep = certifygen_repository::get_record([
                    'validationid' => $requestid,
                    'userid' => $userid
            ]);
            if (!$rep) {
                $rep = new certifygen_repository(0, (object)$data);
                $rep->save();
            } else {
                $rep->set('url', $url);
                $rep->update();
            }
            // Change status.
            $request->set('status', certifygen_validations::STATUS_FINISHED);
            $request->save();
        } catch (moodle_exception $e) {
            return ['error' => [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                ],
            ];
        }
        return [
            'requestid' => $params['requestid'],
            'newstatus' => certifygen_validations::STATUS_FINISHED,
            'newstatusdesc' => get_string('status_' . $request->get('status'), 'mod_certifygen'),
        ];
    }
    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function change_status_returns(): external_single_structure {
        return new external_single_structure([
            'requestid' => new external_value(PARAM_INT, 'request id', VALUE_OPTIONAL),
            'newstatus' => new external_value(PARAM_INT, 'status', VALUE_OPTIONAL),
            'newstatusdesc' => new external_value(PARAM_RAW, 'status description', VALUE_OPTIONAL),
            'error' => new external_single_structure([
                'message' => new external_value(PARAM_RAW, 'Error message', VALUE_OPTIONAL),
                'code' => new external_value(PARAM_RAW, 'Error code', VALUE_OPTIONAL),
            ], 'Errors information', VALUE_OPTIONAL),
            ]);
    }
}
