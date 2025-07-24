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
 * WS Get kson certificate
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_certifygen\external;
use coding_exception;
use context_course;
use context_system;
use dml_exception;
use \core_external\external_api;
use \core_external\external_function_parameters;
use \core_external\external_single_structure;
use \core_external\external_value;
use invalid_parameter_exception;
use mod_certifygen\persistents\certifygen;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;
use certifygenfilter;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/mod/certifygen/lib.php');
require_once($CFG->dirroot . '/mod/certifygen/classes/filters/certifygenfilter.php');
/**
 * Get certificate elements
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_json_certificate_external extends external_api {
    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function get_json_certificate_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'userid' => new external_value(PARAM_INT, 'user id'),
                'userfield' => new external_value(PARAM_RAW, 'user field'),
                'idinstance' => new external_value(PARAM_INT, 'instance id'),
                'customfields' => new external_value(PARAM_RAW, 'customfields'),
                'lang' => new external_value(PARAM_LANG, 'lang'),
            ]
        );
    }

    /**
     * get_json_certificate
     *
     * @param int $userid
     * @param string $userfield
     * @param int $idinstance
     * @param string $customfields
     * @param string $lang
     * @return array
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws coding_exception
     */
    public static function get_json_certificate(
        int $userid,
        string $userfield,
        int $idinstance,
        string $customfields,
        string $lang
    ): array {
        global $USER, $DB;
        $params = self::validate_parameters(
            self::get_json_certificate_parameters(),
            ['userid' => $userid, 'userfield' => $userfield, 'idinstance' => $idinstance,
                'customfields' => $customfields, 'lang' => $lang]
        );

        if ($USER->id != $userid) {
            $context = context_system::instance();
            if (!has_capability('mod/certifygen:manage', $context)) {
                $result['result'] = false;
                $result['message'] = get_string('nopermissiontoemitothercerts', 'mod_certifygen');
                return $result;
            }
        }
        $result = ['json' => [], 'error' => []];
        $haserror = false;
        try {
            // Choose user parameter.
            $uparam = mod_certifygen_validate_user_parameters_for_ws($params['userid'], $params['userfield']);
            if (array_key_exists('error', $uparam)) {
                return $uparam;
            }
            $userid = $uparam['userid'];

            // User exists.
            $user = user_get_users_by_id([$userid]);
            if (empty($user)) {
                unset($result['json']);
                $result['error']['code'] = 'user_not_found';
                $result['error']['message'] = get_string('user_not_found', 'mod_certifygen');
                return $result;
            }
            // Activity exists?
            $certifygen = new certifygen($params['idinstance']);

            // Is user enrolled on this course as student?
            $context = context_course::instance($certifygen->get('course'));
            if (!has_capability('mod/certifygen:emitmyactivitycertificate', $context, $userid)) {
                unset($result['json']);
                $result['error']['code'] = 'student_not_enrolled';
                $result['error']['message'] = get_string(
                    'student_not_enrolled',
                    'mod_certifygen',
                    $certifygen->get('course')
                );
                return $result;
            }

            // Model info.
            $model = new certifygen_model(certifygen::get_modelid_from_certifygenid($params['idinstance']));

            // Already emtited?
            $validation = certifygen_validations::get_validation_by_lang_and_instance($lang, $idinstance, $userid);
            if (is_null($validation)) {
                // Emit certificate.
                $result = emitcertificate_external::emitcertificate(
                    0,
                    $idinstance,
                    $model->get('id'),
                    $lang,
                    $userid,
                    $certifygen->get('course')
                );
                if (!$result['result']) {
                    $result['error']['code'] = 'certificate_can_not_be_emited';
                    $result['error']['message'] = $result['message'];
                    return $result;
                }
                $validation = certifygen_validations::get_validation_by_lang_and_instance($lang, $idinstance, $userid);
            }

            // Get json.
            $issue = \mod_certifygen\certifygen::get_user_certificate(
                $idinstance,
                $userid,
                $certifygen->get('course'),
                $model->get('templateid'),
                $validation->get('lang')
            );
            if (is_null($issue)) {
                $haserror = true;
                $result['error']['code'] = 'issue_not_found';
                $result['error']['message'] = get_string('issue_not_found', 'mod_certifygen');
            } else {
                // Filter multilang course name.
                // Filter to return course names in $lang language.
                $filter = new certifygenfilter(context_system::instance(), [], $lang);
                // Static data.
                $json = json_decode($issue->data);
                $json->courseshortname = $filter->filter($json->courseshortname);
                $json->coursefullname = $filter->filter($json->coursefullname);
                // Dynamic data.
                $pages = $DB->get_records('tool_certificate_pages', ['templateid' => $model->get('templateid')]);
                $elements = [];
                $result['json']['elements'] = [];
                foreach ($pages as $page) {
                    $elements = $DB->get_records('tool_certificate_elements', ['pageid' => $page->id]);
                    $elements[] = $elements;
                    $result['json']['elements'][] = [
                            'pageid' => $page->id,
                            'elements' => $elements,
                    ];
                }
                $output['data'] = $json;
                $output['elements'] = $elements;
                $result['json'] = json_encode($output);
            }
        } catch (moodle_exception $e) {
            unset($result['json']);
            $haserror = true;
            $result['error']['code'] = $e->errorcode;
            $result['error']['message'] = $e->getMessage();
        }
        if (!$haserror) {
            unset($result['error']);
        } else {
            unset($result['json']);
        }
        return $result;
    }
    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function get_json_certificate_returns(): external_single_structure {
        return new external_single_structure([
                'json' => new external_value(PARAM_RAW, 'Certificate elements in a json', VALUE_OPTIONAL),
                'error' => new external_single_structure([
                    'message' => new external_value(PARAM_CLEANFILE, 'Error message'),
                    'code' => new external_value(PARAM_RAW, 'Error code'),
                ], 'Errors information', VALUE_OPTIONAL),
            ]);
    }
}
