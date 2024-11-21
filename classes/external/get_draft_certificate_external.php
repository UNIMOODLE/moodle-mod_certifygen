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
 * WS get draft certificate
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_certifygen\external;
use context_course;
use context_system;
use dml_exception;
use invalid_parameter_exception;
use mod_certifygen\persistents\certifygen;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\template;
use moodle_exception;
use required_capability_exception;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/mod/certifygen/lib.php');
/**
 * Get studetns certificate (issue it if it is not already created)
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_draft_certificate_external extends \core_external\external_api {
    /**
     * Describes the external function parameters.
     *
     * @return \core_external\external_function_parameters
     */
    public static function get_draft_certificate_parameters(): \core_external\external_function_parameters {
        return new \core_external\external_function_parameters(
            [
                'userid' => new \core_external\external_value(PARAM_INT, 'user id'),
                'userfield' => new \core_external\external_value(PARAM_RAW, 'user field'),
                'idinstance' => new \core_external\external_value(PARAM_INT, 'instance id'),
                'lang' => new \core_external\external_value(PARAM_LANG, 'lang'),
                'customfields' => new \core_external\external_value(PARAM_RAW, 'customfields'),
            ]
        );
    }

    /**
     * Get student certificate (issue certificate if it is necessary)
     * @param int $userid
     * @param string $userfield
     * @param int $idinstance
     * @param string $lang
     * @param string $customfields
     * @return array
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     */
    public static function get_draft_certificate(
        int $userid,
        string $userfield,
        int $idinstance,
        string $lang,
        string $customfields
    ): array {

        $params = self::validate_parameters(
            self::get_draft_certificate_parameters(),
            ['userid' => $userid, 'userfield' => $userfield, 'idinstance' => $idinstance, 'lang' => $lang,
                'customfields' => $customfields]
        );
        $context = context_system::instance();
        require_capability('mod/certifygen:manage', $context);
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
                $result['error']['code'] = 'user_not_found';
                $result['error']['message'] = get_string('user_not_found', 'mod_certifygen');
                return $result;
            }
            // Activity exists?.
            $certifygen = new certifygen($params['idinstance']);

            // Is user enrolled on this course as student?
            $context = context_course::instance($certifygen->get('course'));
            if (!has_capability('mod/certifygen:emitmyactivitycertificate', $context, $userid)) {
                $result['error']['code'] = 'user_not_enrolled_on_idinstance_course_as_student';
                $result['error']['message'] = get_string(
                    'student_not_enrolled',
                    'mod_certifygen',
                    $certifygen->get('course')
                );
                return $result;
            }

            // Model info.
            $model = new certifygen_model(certifygen::get_modelid_from_certifygenid($params['idinstance']));
            $template = template::instance($model->get('templateid'), (object) ['lang' => $lang]);
            $pdfcontent = $template->generate_pdf(true, (object)['userid' => $userid], true);
            $filecontent = base64_encode($pdfcontent);
        } catch (moodle_exception $e) {
            $result['error']['code'] = $e->errorcode;
            $result['error']['message'] = $e->getMessage();
            return $result;
        }
        $certificate = [
            'file' => $filecontent,
        ];

        return ['certificate' => $certificate];
    }
    /**
     * Describes the data returned from the external function.
     *
     * @return \core_external\external_single_structure
     */
    public static function get_draft_certificate_returns(): \core_external\external_single_structure {
        return new \core_external\external_single_structure([
                'certificate' => new \core_external\external_single_structure(
                    [
                        'file' => new \core_external\external_value(PARAM_RAW, 'certificate'),
                    ],
                    'Certificate info',
                    VALUE_OPTIONAL
                ),
                'error' => new \core_external\external_single_structure(
                    [
                        'message' => new \core_external\external_value(PARAM_CLEANFILE, 'Error message'),
                        'code' => new \core_external\external_value(PARAM_RAW, 'Error code'),
                    ],
                    'Errors information',
                    VALUE_OPTIONAL
                ),
            ]);
    }
}
