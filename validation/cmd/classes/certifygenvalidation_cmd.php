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
use moodle_url;
use stored_file;
use stored_file_creation_exception;

global $CFG;
require_once($CFG->dirroot. '/user/lib.php');
class certifygenvalidation_cmd implements ICertificateValidation
{

    /**
     * @param certifygen_file $file
     * @return array
     * @throws coding_exception
     * @throws dml_exception|exception
     */
    public function sendFile(certifygen_file $file): array
    {
        $newfile  = null;
        $path = get_config('certifygenvalidation_cmd', 'path');
        $originalfilespath = get_config('certifygenvalidation_cmd', 'originalfilespath');
        $validatedfilespath = get_config('certifygenvalidation_cmd', 'validatedfilespath');
        if (!$this->is_enabled()) {
            throw new exception('cmdnotconfigured', 'certifygenvalidation_cmd');
        }

        // Step 1: upload file to $originalfilespath
        $originalfilename = $originalfilespath . $file->get_file()->get_filename();
        $validatedfilename = $validatedfilespath . $file->get_file()->get_filename();
        $tempdir = make_temp_directory('certifygen');
        $tempfile = tempnam($tempdir, 'certifygen') . '.pdf';
        file_put_contents($tempfile, $file->get_file()->get_content());
        if (!file_exists($tempfile)) {
            return [
                'haserror' => true,
                'message' => 'temp_file_not_exists',
            ];
        }
        $copied = copy($tempfile, $originalfilename);
        if (!$copied) {
            return [
                'haserror' => true,
                'message' => 'missing directory permissions',
            ];
        }

        // Step 2: call external endpoint
        $data = $file->get_metadata();
        $datajson = json_encode($data);
        // Construye el comando
        $command = "$path $originalfilename '$datajson'";

        // Ejecuta el comando y captura la salida
        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);

        $haserror = false;
        $message = 'ok';
        // Muestra la salida del comando
        if ($return_var !== 0) {
            $haserror = true;
            $message = " Error ejecutando el comando. Código de salida: " . $return_var;
        } else {
            if (!empty($output)) {
                try {
                    $newfile = $this->save_file_on_moodledata($validatedfilename, $file);
                    unlink($tempfile);
                } catch (moodle_exception $e) {
                    error_log(__FUNCTION__ . '-CMD e: '.var_export($e->getMessage(), true));
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
     * @param certifygen_file $file
     * @param $content
     * @return void
     * @throws dml_exception
     * @throws file_exception
     * @throws stored_file_creation_exception
     */
    private function save_file_on_moodledata(string $validatedfile, certifygen_file $file) :stored_file {
        // Get validated file
        $validatedfile = file_get_contents($validatedfile);

        // Save pdf on moodledata.
        $fs = get_file_storage();
        $context = context_system::instance();
        $cv = new certifygen_validations($file->get_validationid());
        if (!empty($cv->get('certifygenid'))) {
            $cert = new certifygen($cv->get('certifygenid'));
            $context = \context_course::instance($cert->get('course'));
        }
        $filerecord = self::get_filerecord_array(
            $context->id,
            $file->get_validationid(),
            $file->get_file()->get_filename());

        return $fs->create_file_from_string($filerecord, $validatedfile);
    }

    /**
     * @param int $courseid
     * @param int $validationid
     * @param string $filename
     * @return array
     */
    private function get_filerecord_array(int $contextid, int $validationid, string $filename) : array {
        return [
            'contextid' => $contextid,
            'component' => self::FILE_COMPONENT,
            'filearea' => self::FILE_AREA,
            'itemid' => $validationid,
            'filepath' => self::FILE_PATH,
            'filename' => $filename
        ];
    }

    /**
     * @param int $courseid
     * @param int $validationid
     * @param string $code
     * @return stored_file
     */
    public function getFile(int $courseid, int $validationid): array
    {
        $result = ['error' => [], 'message' => 'ok'];
        try {
            $validation = new certifygen_validations($validationid);
            $code = certifygen_validations::get_certificate_code($validation);
            $fs = get_file_storage();
            $contextid = context_system::instance()->id;
            if (!empty($courseid)) {
                $contextid = context_course::instance($courseid)->id;
            }
            $file = $fs->get_file($contextid, self::FILE_COMPONENT,
                self::FILE_AREA, $validationid, self::FILE_PATH, $code);
            $result['file'] = $file;
        } catch(moodle_exception $exception) {
            $result['error']['code'] = $exception->getCode();
            $result['error']['message'] = $exception->getMessage();
        }
        return $result;
    }

    /**
     * @param int $courseid
     * @return bool
     */
    public function canRevoke(int $courseid): bool
    {
        return false;
    }

    /**
     * @param int $courseid
     * @param int $validationid
     * @param string $code
     * @return string
     */
    public function getFileUrl(int $courseid, int $validationid, string $code): string
    {
        $newfile = $this->getFile($courseid, $validationid, $code);
        if (!$newfile) {
            return '';
        }
        $url = moodle_url::make_pluginfile_url(
            $newfile->get_contextid(),
            $newfile->get_component(),
            $newfile->get_filearea(),
            $newfile->get_itemid(),
            $newfile->get_filepath(),
            $newfile->get_filename(),
            false                     // Do not force download of the file.
        );
        return $url->out();
    }

    /**
     * @return bool
     * @throws dml_exception
     */
    public function is_enabled(): bool
    {
        $enabled = (int) get_config('certifygenvalidation_cmd', 'enabled');
        $pathenabled = get_config('certifygenvalidation_cmd', 'path');
        if ($enabled && !empty($pathenabled)) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function checkStatus(): bool
    {
        return false;
    }

    /**
     * @param int $validationid
     * @param string $code
     * @return int
     */
    public function getStatus(int $validationid, string $code): int
    {
        return certifygen_validations::STATUS_IN_PROGRESS;
    }

    /**
     * @return bool
     */
    public function checkfile(): bool
    {
        return false;
    }
    /**
     * Returns an array of strings associated to certifiacte status to be shown on
     * activityteacher_table and profile_my_certificates_table
     */
    public function getStatusMessages(): array {
        return [];
    }
}