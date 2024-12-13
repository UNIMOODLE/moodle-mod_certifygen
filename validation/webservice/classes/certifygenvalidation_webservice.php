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
 *
 * @package   certifygenvalidation_webservice
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace certifygenvalidation_webservice;

use coding_exception;
use context_course;
use context_system;
use dml_exception;
use file_exception;
use mod_certifygen\certifygen_file;
use mod_certifygen\interfaces\ICertificateValidation;
use mod_certifygen\persistents\certifygen;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;
use stored_file_creation_exception;
/**
 * certifygenvalidation_webservice
 * @package    certifygenvalidation_webservice
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class certifygenvalidation_webservice implements ICertificateValidation {
    /**
     * sendFile
     * @param certifygen_file $file
     * @return array
     */
    public function send_file(certifygen_file $file): array {
        try {
            // No tiene sentido enviar el fichero a ninguna parte.
            $validation = new certifygen_validations($file->get_validationid());
            $validation->set('status', certifygen_validations::STATUS_IN_PROGRESS);
            return [
                'haserror' => false,
                'message' => get_string('ok', 'mod_certifygen'),
            ];
        } catch (moodle_exception $e) {
            return [
                'haserror' => true,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * getFile
     *
     * @param int $courseid
     * @param int $validationid
     * @return array
     * @throws coding_exception
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
                $code . '.pdf'
            );
            if (!$file) {
                $result['error']['code'] = 'file_not_found';
                $result['error']['message'] = 'file_not_found';
                return $result;
            }
            $result['file'] = $file;
        } catch (moodle_exception $exception) {
            $result['error']['code'] = $exception->getCode();
            $result['error']['message'] = $exception->getMessage();
        }
        return $result;
    }

    /**
     * canRevoke
     * @param int $courseid
     * @return bool
     */
    public function can_revoke(int $courseid): bool {
        return false;
    }

    /**
     * revoke
     * @param string $code
     * @return array
     */
    public function revoke(string $code): array {
        return [
            'haserror' => false,
            'message' => '',
        ];
    }

    /**
     * is_enabled
     * @return bool
     * @throws dml_exception
     */
    public function is_enabled(): bool {
        return (int)get_config('certifygenvalidation_webservice', 'enabled');
    }

    /**
     * checkStatus
     * @return bool
     */
    public function check_status(): bool {
        return true;
    }

    /**
     * getStatus
     * @param int $validationid
     * @param string $code
     * @return int
     */
    public function get_status(int $validationid, string $code): int {
        return certifygen_validations::STATUS_IN_PROGRESS;
    }

    /**
     * checkfile
     * @return bool
     */
    public function checkfile(): bool {
        return true;
    }

    /**
     * save_file_moodledata
     * @param int $validationid
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws file_exception
     * @throws stored_file_creation_exception
     */
    public function save_file_moodledata(int $validationid): void {
        $validation = new certifygen_validations($validationid);
        $code = certifygen_validations::get_certificate_code($validation);
        $context = context_system::instance();
        $filearea  = 'certifygenreport';
        $itemid = $validationid;
        if (!empty($validation->get('certifygenid'))) {
            $cert = new certifygen($validation->get('certifygenid'));
            $context = context_course::instance($cert->get('course'));
            $filearea  = 'issues';
            $itemid = $validation->get('issueid');
        }
        // Search for original certificate.
        $filerecord = [
            'contextid' => $context->id,
            'component' => self::FILE_COMPONENT,
            'filearea' => self::FILE_AREA,
            'itemid' => $validationid,
            'filepath' => self::FILE_PATH,
            'filename' => $code . '.pdf',
        ];
        $fs = get_file_storage();
        $original = $fs->get_file(
            context_system::instance()->id,
            self::FILE_COMPONENT,
            $filearea,
            $itemid,
            self::FILE_PATH,
            $code . '.pdf'
        );
        $fs->create_file_from_storedfile($filerecord, $original);
    }
    /**
     * Returns an array of strings associated to certifiacte status to be shown on
     * activityteacher_table and profile_my_certificates_table
     */
    public function get_status_messages(): array {
        return [
            certifygen_validations::STATUS_IN_PROGRESS => get_string('inprogress_msg', 'certifygenvalidation_webservice'),
        ];
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
        return (int)get_config('certifygenvalidation_webservice', 'wsoutput');
    }
}
