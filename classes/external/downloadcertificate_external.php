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
use mod_certifygen\interfaces\ICertificateRepository;
use mod_certifygen\interfaces\ICertificateValidation;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;
use mod_certifygen\template;
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
                'id' => new external_value(PARAM_INT, 'validation id'),
                'instanceid' => new external_value(PARAM_INT, 'instance id'),
                'modelid' => new external_value(PARAM_INT, 'model id'),
                'code' => new external_value(PARAM_RAW, 'certificate code'),
                'courseid' => new external_value(PARAM_RAW, 'course id'),
            ]
        );
    }

    /**
     * @param int $instanceid
     * @param int $validationid
     * @param int $modelid
     * @param string $lang
     * @param int $userid
     * @param int $courseid
     * @return array
     * @throws coding_exception
     * @throws invalid_parameter_exception
     * @throws invalid_persistent_exception
     */
    public static function downloadcertificate(int $validationid, int $instanceid, int $modelid, string $code, int $courseid): array {
        self::validate_parameters(
            self::downloadcertificate_parameters(), ['id' => $validationid, 'instanceid' => $instanceid, 'modelid' => $modelid, 'code' => $code,
                'courseid' => $courseid]
        );

        $result = ['result' => true, 'message' => 'OK', 'url' => ''];

        try {
            // Step 1: verified status finished.
            $validation = new certifygen_validations($validationid);
            if (is_null($validation)) {
                $result = ['result' => false, 'message' => 'notfound', 'url' => ''];
                return $result;
            }
            if ($validation->get('status') != certifygen_validations::STATUS_FINISHED) {
                $result = ['result' => false, 'message' => 'statusnotfinished', 'url' => ''];
                return $result;
            }
            // Step 2: call to getfile from repositoryplugin.
            $certifygenmodel = new certifygen_model($validation->get('modelid'));
            $repositoryplugin = $certifygenmodel->get('repository');
            $repositorypluginclass = $repositoryplugin . '\\' . $repositoryplugin;
            if (get_config($repositoryplugin, 'enabled') === '1') {
                /** @var ICertificateRepository $subplugin */
                $subplugin = new $repositorypluginclass();
                $result['url'] = $subplugin->getFileUrl($validation);
                if (empty($result['url'])) {
                    $result['result'] = false;
                    $result['message'] = 'empty_url';
                }
            } else {
                $result['result'] = false;
                $result['message'] = 'plugin_not_enabled';
            }
        } catch (moodle_exception $e) {
            $result['result'] = false;
            $result['message'] = $e->getMessage();
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
                'message' => new external_value(PARAM_RAW, 'meesage'),
            ]
        );
    }

}