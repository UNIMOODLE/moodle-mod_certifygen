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
                'idinstance' => new external_value(PARAM_INT, 'instance id'),
                'lang' => new external_value(PARAM_RAW, 'lang'),
                'customfields' => new external_value(PARAM_RAW, 'customfields'),
            ]
        );
    }
    public static function get_pdf_certificate(int $userid, int $idinstance, string $lang, string $customfields): array {
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
            ['userid' => $userid, 'idinstance' => $idinstance, 'lang' => $lang, 'customfields' => $customfields]
        );
        $result = ['file' => '', 'error' => []];
        $haserror = false;
        try {
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
            $mycourses = enrol_get_users_courses($params['userid'], true, 'id');
            if (!in_array($certifygen->get('course'), array_keys($mycourses))) {
                unset($result['file']);
                $result['error']['code'] = 'user_not_enrolled_on_idinstance_course';
                $result['error']['message'] = 'User not enrolled on idinstance course';
                return $result;
            }

            // Model info.
            $model = new certifygen_model($certifygen->get('modelid'));
            if (is_null($model->get('validation'))) {
                unset($result['file']);
                $result['error']['code'] = 'model_has_no_validation';
                $result['error']['message'] = 'This model is configured with no validation.';
                return $result;
            }

            // Process status
            // TODO: me tendrian  especificar el idioma??.
            $validations = certifygen_validations::get_records(['userid' => $params['userid'], 'modelid' => $model->get('id')]);
            foreach ($validations as $validation) {
                if ($validation->get('status') != certifygen_validations::STATUS_IN_PROGRESS) {
                    continue;
                }
                $file = \mod_certifygen\certifygen::get_user_certificate_file($model->get('templateid'), $userid,
                    $certifygen->get('course'), $validation->get('lang'));
                if (is_null($file)) {
                    $haserror = true;
                    $result['error']['code'] = 'file_not_found';
                    $result['error']['message'] = 'File not found';
                } else {
                    $result['file'] = $file->get_contenthash(); // PARAM_FILE
                }
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