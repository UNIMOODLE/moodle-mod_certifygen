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
 * @package   certifygenvalidation_none
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace certifygenvalidation_none;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/soaplib.php');
require_once($CFG->libdir . '/pdflib.php');

use certifygenvalidation_none\persistents\certifygenvalidationwebservice;
use coding_exception;
use context_course;
use context_system;
use dml_exception;
use mod_certifygen\certifygen_file;
use mod_certifygen\interfaces\ICertificateValidation;
use mod_certifygen\persistents\certifygen;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;
/**
 * @package   certifygenvalidation_none
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class certifygenvalidation_none implements ICertificateValidation {
    /**
     * Send file
     * @param certifygen_file $file
     * @return array
     */
    public function send_file(certifygen_file $file): array {
        try {
            // Change context.
            $fs = get_file_storage();
            $context = context_system::instance();
            $cv = new certifygen_validations($file->get_validationid());
            if (!empty($cv->get('certifygenid'))) {
                $cert = new certifygen($cv->get('certifygenid'));
                $context = context_course::instance($cert->get('course'));
            }
            $filerecord = [
                'contextid' => $context->id,
                'component' => self::FILE_COMPONENT,
                'filearea' => self::FILE_AREA_VALIDATED,
                'itemid' => $file->get_validationid(),
                'filepath' => self::FILE_PATH,
                'filename' => $file->get_file()->get_filename(),
            ];
            $newfile = $fs->create_file_from_storedfile($filerecord, $file->get_file());
            // Change status.
            $validation = new certifygen_validations($file->get_validationid());
            $validation->set('status', certifygen_validations::STATUS_IN_PROGRESS);
            return [
                'haserror' => false,
                'message' => get_string('ok', 'mod_certifygen'),
                'newfile' => $newfile,
            ];
        } catch (moodle_exception $e) {
            return [
                'haserror' => true,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get file
     * @param int $courseid
     * @param int $validationid
     * @return array
     * @throws coding_exception
     */
    public function get_file(int $courseid, int $validationid): array {
        $fs = get_file_storage();
        $file = null;
        $haserror = false;
        $code = '';
        $message = get_string('ok', 'mod_certifygen');
        try {
            $context = context_system::instance();
            $cv = new certifygen_validations($validationid);
            if (!empty($cv->get('certifygenid'))) {
                $context = context_course::instance($courseid);
            }
            $code = certifygen_validations::get_certificate_code($cv);
            $filerecord = [
                    'contextid' => $context->id,
                    'component' => self::FILE_COMPONENT,
                    'filearea' => self::FILE_AREA_VALIDATED,
                    'itemid' => $validationid,
                    'filepath' => self::FILE_PATH,
                    'filename' => $code . '.pdf',
            ];
            $file = $fs->get_file(
                $filerecord['contextid'],
                $filerecord['component'],
                $filerecord['filearea'],
                $filerecord['itemid'],
                $filerecord['filepath'],
                $filerecord['filename'],
            );
        } catch (moodle_exception $e) {
            $haserror = true;
            $message = $e->getMessage();
            $code = $e->getCode();
        }
        if ($haserror) {
            $result['error']['code'] = $code;
            $result['error']['message'] = $message;
        } else {
            $result['error'] = [];
            $result['file'] = $file;
        }
        return $result;
    }

    /**
     * Can revoke
     * @param int $courseid
     * @return bool
     * @throws coding_exception
     */
    public function can_revoke(int $courseid): bool {
        if ($courseid) {
            return has_capability('tool/certificate:issue', context_course::instance($courseid));
        } else {
            return true;
        }
    }

    /**
     * No es necesario hacer nada, ya que el servicio revoke llama al tool_certificate.
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
     * Is enabled
     * @return bool
     * @throws dml_exception
     */
    public function is_enabled(): bool {
        return (int)get_config('certifygenvalidation_none', 'enabled');
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
        return certifygen_validations::STATUS_VALIDATION_OK;
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
}
