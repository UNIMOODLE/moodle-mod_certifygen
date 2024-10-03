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
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * @package   certifygenvalidation_cmd
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace certifygenvalidation_cmd;

use coding_exception;
use context_course;
use context_system;
use core\session\exception;
use dml_exception;
use file_exception;
use mod_certifygen\certifygen_file;
use mod_certifygen\interfaces\ICertificateValidation;
use mod_certifygen\persistents\certifygen;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;
use stored_file;
use stored_file_creation_exception;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/user/lib.php');
/**
 * CMD
 * @package   certifygenvalidation_cmd
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class certifygenvalidation_cmd implements ICertificateValidation {
    /**
     * Send File
     * @param certifygen_file $file
     * @return array
     * @throws dml_exception
     * @throws exception
     */
    public function send_file(certifygen_file $file): array {
        $newfile  = null;
        $path = get_config('certifygenvalidation_cmd', 'path');
        $originalfilespath = get_config('certifygenvalidation_cmd', 'originalfilespath');
        $validatedfilespath = get_config('certifygenvalidation_cmd', 'validatedfilespath');
        if (!$this->is_enabled()) {
            throw new exception('cmdnotconfigured', 'certifygenvalidation_cmd');
        }

        // Step 1: upload file to $originalfilespath.
        $originalfilename = $originalfilespath . $file->get_file()->get_filename();
        $validatedfilename = $validatedfilespath . $file->get_file()->get_filename();
        $tempdir = make_temp_directory('certifygen');
        $tempfile = tempnam($tempdir, 'certifygen') . '.pdf';
        file_put_contents($tempfile, $file->get_file()->get_content());
        if (!file_exists($tempfile)) {
            return [
                'haserror' => true,
                'message' => get_string('temp_file_not_exists', 'certifygenvalidation_cmd'),
            ];
        }
        $copied = copy($tempfile, $originalfilename);
        if (!$copied) {
            return [
                'haserror' => true,
                'message' => get_string('missing_directory_permissions', 'certifygenvalidation_cmd'),
            ];
        }

        // Step 2: call external endpoint.
        $data = $file->get_metadata();
        $datajson = json_encode($data);
        // Construye el comando.
        $command = "$path $originalfilename '$datajson'";

        // Ejecuta el comando y captura la salida.
        $output = [];
        $returnvar = 0;
        exec($command, $output, $returnvar);

        $haserror = false;
        $message = get_string('ok', 'mod_certifygen');
        // Muestra la salida del comando.
        if ($returnvar !== 0) {
            $haserror = true;
            $message = get_string('error_cmd_code', 'certifygenvalidation_cmd', $returnvar);
        } else {
            if (!empty($output)) {
                try {
                    $newfile = $this->save_file_on_moodledata($validatedfilename, $file);
                    unlink($tempfile);
                } catch (moodle_exception $e) {
                    debugging(__FUNCTION__ . ' e: ' . $e->getMessage());
                    $haserror = true;
                    $message = $e->getMessage();
                }
            }
        }
        return [
            'haserror' => $haserror,
            'message' => $message,
            'newfile' => $newfile,
        ];
    }

    /**
     * Save file
     * @param string $validatedfile
     * @param certifygen_file $file
     * @return stored_file
     * @throws coding_exception
     * @throws dml_exception
     * @throws file_exception
     * @throws stored_file_creation_exception
     */
    private function save_file_on_moodledata(string $validatedfile, certifygen_file $file): stored_file {
        // Get validated file.
        $validatedfile = file_get_contents($validatedfile);

        // Save pdf on moodledata.
        $fs = get_file_storage();
        $context = context_system::instance();
        $cv = new certifygen_validations($file->get_validationid());
        if (!empty($cv->get('certifygenid'))) {
            $cert = new certifygen($cv->get('certifygenid'));
            $context = context_course::instance($cert->get('course'));
        }
        $filerecord = self::get_filerecord_array(
            $context->id,
            $file->get_validationid(),
            $file->get_file()->get_filename()
        );

        return $fs->create_file_from_string($filerecord, $validatedfile);
    }

    /**
     * Get file record
     * @param int $contextid
     * @param int $validationid
     * @param string $filename
     * @return array
     */
    private function get_filerecord_array(int $contextid, int $validationid, string $filename): array {
        return [
            'contextid' => $contextid,
            'component' => self::FILE_COMPONENT,
            'filearea' => self::FILE_AREA,
            'itemid' => $validationid,
            'filepath' => self::FILE_PATH,
            'filename' => $filename,
        ];
    }

    /**
     * Get File
     * @param int $courseid
     * @param int $validationid
     * @return array
     */
    public function get_file(int $courseid, int $validationid): array {
        $result = ['error' => [], 'message' => get_string('ok', 'mod_certifygen')];
        try {
            $validation = new certifygen_validations($validationid);
            $code = certifygen_validations::get_certificate_code($validation);
            $fs = get_file_storage();
            $contextid = context_system::instance()->id;
            if (!empty($courseid)) {
                $contextid = context_course::instance($courseid)->id;
            }
            $file = $fs->get_file(
                $contextid,
                self::FILE_COMPONENT,
                self::FILE_AREA,
                $validationid,
                self::FILE_PATH,
                $code
            );
            $result['file'] = $file;
        } catch (moodle_exception $exception) {
            $result['error']['code'] = $exception->getCode();
            $result['error']['message'] = $exception->getMessage();
        }

        return $result;
    }

    /**
     * Can revoke
     * @param int $courseid
     * @return bool
     */
    public function can_revoke(int $courseid): bool {
        return false;
    }

    /**
     * enable
     * @return bool
     * @throws dml_exception
     */
    public function is_enabled(): bool {
        $enabled = (int) get_config('certifygenvalidation_cmd', 'enabled');
        $pathenabled = get_config('certifygenvalidation_cmd', 'path');
        $path = get_config('certifygenvalidation_cmd', 'path');
        $pathexists = false;
        if (!empty($path) && file_exists($path)) {
            $pathexists = true;
        }
        if ($enabled && !empty($pathenabled) && $pathexists) {
            return true;
        }
        return false;
    }

    /**
     * Check status
     * @return bool
     */
    public function check_status(): bool {
        return false;
    }

    /**
     * Get status
     * @param int $validationid
     * @param string $code
     * @return int
     */
    public function get_status(int $validationid, string $code): int {
        return certifygen_validations::STATUS_IN_PROGRESS;
    }

    /**
     * Check file
     * @return bool
     */
    public function checkfile(): bool {
        return false;
    }
    /**
     * Returns an array of strings associated to certifiacte status to be shown on
     * activityteacher_table and profile_my_certificates_table
     */
    public function get_status_messages(): array {
        return [];
    }

    /**
     * If true, the certifygen activities related with this type of validation will be part
     * of the output of get_id_instance_certificate_external ws.
     * If true, the teacher requests with models with this type of validation will be part
     *  of the output of get_courses_as_teacher ws.
     *
     * @return bool
     * @throws dml_exception
     */
    public function is_visible_in_ws(): bool {
        return (int)get_config('certifygenvalidation_cmd', 'wsoutput');
    }
}
