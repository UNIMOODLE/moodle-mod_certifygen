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
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 *
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_certifygen\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/lib/completionlib.php');
use context_module;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use invalid_parameter_exception;
use mod_certifygen\event\certificate_downloaded;
use mod_certifygen\interfaces\ICertificateRepository;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;
/**
 * Download student certificate
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
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
     * Download student certificate
     * @param int $validationid
     * @param int $instanceid
     * @param int $modelid
     * @param string $code
     * @param int $courseid
     * @return array
     * @throws invalid_parameter_exception
     */
    public static function downloadcertificate(
        int $validationid,
        int $instanceid,
        int $modelid,
        string $code,
        int $courseid
    ): array {
        self::validate_parameters(
            self::downloadcertificate_parameters(),
            ['id' => $validationid, 'instanceid' => $instanceid,
                'modelid' => $modelid, 'code' => $code, 'courseid' => $courseid]
        );
        global $USER;
        $result = ['result' => true, 'message' => get_string('ok', 'mod_certifygen'), 'url' => ''];

        try {
            // Step 1: verified status finished.
            $validation = new certifygen_validations($validationid);
            [$course, $cm] = get_course_and_cm_from_instance($instanceid, 'certifygen');
            $context = context_module::instance($cm->id);
            if (
                $USER->id != $validation->get('userid')
                && !has_capability('mod/certifygen:canemitotherscertificates', $context)
            ) {
                $result['result'] = false;
                $result['message'] = get_string('nopermissiontodownloadothercerts', 'mod_certifygen');
                return $result;
            }
            if (is_null($validation)) {
                return [
                    'result' => false,
                    'message' => get_string('validationnotfound', 'mod_certifygen'),
                    'url' => '',
                ];
            }
            if ($validation->get('status') != certifygen_validations::STATUS_FINISHED) {
                return [
                    'result' => false,
                    'message' => get_string('statusnotfinished', 'mod_certifygen'),
                    'url' => '',
                ];
            }
            // Step 2: call to getfile from repositoryplugin.
            $certifygenmodel = new certifygen_model($validation->get('modelid'));
            $repositoryplugin = $certifygenmodel->get('repository');
            $repositorypluginclass = $repositoryplugin . '\\' . $repositoryplugin;
            if (get_config($repositoryplugin, 'enabled') === '1') {
                /** @var ICertificateRepository $subplugin */
                $subplugin = new $repositorypluginclass();
                $result['url'] = $subplugin->get_file_url($validation);
                if (empty($result['url'])) {
                    $result['result'] = false;
                    $result['message'] = get_string('empty_repository_url', 'mod_certifygen');
                } else {
                    // Trigger event.
                    certificate_downloaded::create_from_validation($validation)->trigger();
                    // Trigger completion event.
                    $completion = new \completion_info($course);
                    if (
                        $completion->is_enabled($cm)
                        && $cm->customdata
                        && !empty($cm->customdata)
                        && array_key_exists('customcompletionrules', $cm->customdata)
                        && array_key_exists('completiondownload', $cm->customdata['customcompletionrules'])
                        && $cm->customdata['customcompletionrules']['completiondownload']
                    ) {
                        $validation->set('isdownloaded', 1);
                        $validation->update();
                        //\course_modinfo::purge_course_module_cache($courseid, $cm->id);
                        \course_modinfo::purge_course_cache($courseid);
                    }
                }
            } else {
                $result['result'] = false;
                $result['message'] = get_string('repository_plugin_not_enabled', 'mod_certifygen');
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
                'message' => new external_value(PARAM_RAW, 'message'),
            ]
        );
    }
}
