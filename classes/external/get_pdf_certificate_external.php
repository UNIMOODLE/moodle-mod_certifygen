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
use external_value;
use mod_certifygen\persistents\certifygen;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;

global $CFG;
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot.'/mod/certifygen/lib.php');
class get_pdf_certificate_external extends external_api {
    /**
     * Describes the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function get_pdf_certificate_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'userid' => new external_value(PARAM_INT, 'user id'),
                'userfield' => new external_value(PARAM_RAW, 'user field'),
                'idinstance' => new external_value(PARAM_INT, 'instance id'),
                'lang' => new external_value(PARAM_LANG, 'lang'),
                'customfields' => new external_value(PARAM_RAW, 'customfields'),
            ]
        );
    }
    public static function get_pdf_certificate(int $userid, string $userfield, int $idinstance, string $lang, string $customfields): array {
        /**
         * Devuelve el PDF del certificado identificado con los parámetros de entrada validando su acceso en base a
         * las restricciones de la configuración de la instancia.
         * El parámetro opcional customfields, permitirá al sistema externo sobreescribir cualquier “customfield”
         * del contexto de generación del pdf. No se realizará ninguna comprobación como podría ser si el customfield
         * es “readonly” o no.
        */
        // PENDIENTE:
        // TODO:customfields... que modifica el fichero.
        $params = self::validate_parameters(
            self::get_pdf_certificate_parameters(),
            ['userid' => $userid, 'userfield' => $userfield, 'idinstance' => $idinstance, 'lang' => $lang, 'customfields' => $customfields]
        );
        $result = ['file' => '', 'error' => []];
        $haserror = false;
        try {
            // Choose user parameter.
            $uparam = mod_certifygen_validate_user_parameters_for_ws($params['userid'], $params['userfield']);
            if (array_key_exists('error', $uparam)) {
                return $uparam;
            }
            $userid = $uparam['userid'];

            // User exists.
            $user = user_get_users_by_id([$params['userid']]);
            if (empty($user)) {
                unset($result['file']);
                $result['error']['code'] = 'user_not_found';
                $result['error']['message'] = 'User not found';
                return $result;
            }
            // Activity exists?
            $certifygen = new certifygen($params['idinstance']);

            // Is user enrolled on this course as student?
            $context = \context_course::instance($certifygen->get('course'));
            if (has_capability('moodle/course:managegroups', $context, $userid)) {
                unset($result['json']);
                $result['error']['code'] = 'user_not_enrolled_on_idinstance_course_as_student';
                $result['error']['message'] = 'User not enrolled on idinstance course as student';
                return $result;
            }

            // Model info.
            $model = new certifygen_model($certifygen->get('modelid'));

            // Already emtited?
            $validation = certifygen_validations::get_validation_by_lang_and_instance($lang, $idinstance, $userid);
            if (is_null($validation)) {
                // Emit certificate.
                $result = emitcertificate_external::emitcertificate(0, $idinstance, $model->get('id'), $lang,
                    $userid, $certifygen->get('course'));
                if (!$result['result']) {
                    $result['error']['code'] = 'certificate_can_not_be_emited';
                    $result['error']['message'] = $result['message'];
                    return $result;
                }
            }

            // Process status
            $file = \mod_certifygen\certifygen::get_user_certificate_file($idinstance, $model->get('templateid'), $userid,
                $certifygen->get('course'), $lang);
            if (is_null($file)) {
                $haserror = true;
                $result['error']['code'] = 'file_not_found';
                $result['error']['message'] = 'File not found';
            } else {
                $result['file'] = $file->get_contenthash(); // PARAM_FILE
            }
        } catch (\moodle_exception $e) {
            error_log("error: ".var_export($e, true));
            unset($result['file']);
            $haserror = true;
            $result['error']['code'] = $e->errorcode;
            $result['error']['message'] = $e->getMessage();
        }

        if (!$haserror) {
            unset($result['error']);
        } else {
            unset($result['file']);
        }

        return $result;
    }
    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function get_pdf_certificate_returns(): external_single_structure {
        return new external_single_structure([
                'file' => new external_value(PARAM_RAW, 'Certificate file', VALUE_OPTIONAL),
                'error' => new external_single_structure([
                    'message' => new external_value(PARAM_CLEANFILE, 'Error message'),
                    'code' => new external_value(PARAM_RAW, 'Error code'),
                ], 'Errors information', VALUE_OPTIONAL),
            ]
        );
    }
}