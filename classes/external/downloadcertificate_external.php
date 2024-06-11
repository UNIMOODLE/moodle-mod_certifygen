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
require_once($CFG->dirroot . '/user/lib.php');

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
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;

class downloadcertificate_external extends external_api {
    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function downloadcertificate_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'modelid' => new external_value(PARAM_INT, 'model id'),
                'lang' => new external_value(PARAM_RAW, 'model lang'),
                'userid' => new external_value(PARAM_RAW, 'user id'),
                'courseid' => new external_value(PARAM_RAW, 'course id'),
            ]
        );
    }

    /**
     * @param int $id
     * @param int $modelid
     * @param string $lang
     * @param int $userid
     * @param int $courseid
     * @return array
     * @throws coding_exception
     * @throws invalid_parameter_exception
     * @throws invalid_persistent_exception
     */
    public static function downloadcertificate(int $modelid, string $lang, int $userid, int $courseid): array {

//        self::validate_parameters(
//            self::downloadcertificate_parameters(), ['modelid' => $modelid, 'lang' => $lang, 'userid' => $userid, 'courseid' => $courseid]
//        );

        $result = ['result' => true, 'message' => 'OK', 'url' => '', 'codetag' => ''];
//        $returndata = ['id' => 0, 'code' => ''];

        // Step 1: Change status to in progress.
//        $data = [
//            'userid' => $userid,
//            'lang' => $lang,
//            'modelid' => $modelid,
//            'status' => certifygen_validations::STATUS_IN_PROGRESS,
//            'issueid' => null,
//            'usermodified' => $userid,
//        ];
//        $validation = certifygen_validations::manage_validation($id, (object) $data);

        try {
            // Step 2: Generate issue.
            $users = user_get_users_by_id([$userid]);
            $user = reset($users);
            $certifygenmodel = new certifygen_model($modelid);
            $course = get_course($courseid);
            certifygen::issue_certificate($user, $certifygenmodel->get('templateid'), $course, $lang);
            $fileurl = certifygen::get_user_certificate_file_url($certifygenmodel->get('templateid'), $userid, $courseid, $lang);
            $issue = certifygen::get_user_certificate( $userid, $courseid, $certifygenmodel->get('templateid'), $lang);
            $codelink =  new \moodle_url('/admin/tool/certificate/index.php', ['code' => $issue->code]);
            // Step 3: Generate the tool_certificate certificate.
            $result['url'] = $fileurl;
            $result['codetag'] = '<a href="'.$codelink->out().'" target="_blank">' . $issue->code .'</a>';
            if (empty($fileurl)) {
                $result['result'] = false;
                $result['message'] = 'File not found';
            }
//            else {
//                $certifygenfile = new certifygen_file($file, $userid, $lang, $modelid, $course, $validation->get('id'));
//                // Step 4: Call to validation plugin.
//                $validationplugin = $certifygenmodel->get('validation');
//                $validationpluginclass = $validationplugin . '\\' . $validationplugin;
//                if (get_config($validationplugin, 'enable') === '1') {
//                    /** @var ICertificateValidation $subplugin */
//                    $subplugin = new $validationpluginclass();
//                    $subplugin->sendFile($certifygenfile);
//                }

//            }

        } catch (moodle_exception $e) {
            $result['result'] = false;
            $result['message'] = $e->getMessage();
//            $validation->set('status', certifygen_validations::STATUS_FINISHED_ERROR);
//            $validation->save();
        }

        return $result;
    }
    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function downloadcertificate_returns(): external_single_structure {
        return new external_single_structure(
            [
                'result' => new external_value(PARAM_BOOL, 'file url created'),
                'url' => new external_value(PARAM_RAW, 'file url'),
                'codetag' => new external_value(PARAM_RAW, 'codetag url'),
                'message' => new external_value(PARAM_RAW, 'meesage'),
            ]
        );
    }

}