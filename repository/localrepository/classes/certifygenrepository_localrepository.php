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
 * @package   certifygenrepository_localrepository
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace certifygenrepository_localrepository;

use coding_exception;
use context_course;
use context_system;
use dml_exception;
use mod_certifygen\interfaces\icertificatereport;
use mod_certifygen\interfaces\icertificaterepository;
use mod_certifygen\persistents\certifygen;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;
use core\url;
use stored_file;
/**
 *
 * @package   certifygenrepository_localrepository
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class certifygenrepository_localrepository implements icertificaterepository {
    /**
     * getFile
     * @param certifygen_validations $validation
     * @return stored_file
     * @throws coding_exception
     * @throws dml_exception
     */
    protected function get_file(certifygen_validations $validation): stored_file {
        $code = certifygen_validations::get_certificate_code($validation);
        $code .= '.pdf';
        $contextid = context_system::instance()->id;
        if (!empty($validation->get('certifygenid'))) {
            $cert = new certifygen($validation->get('certifygenid'));
            $contextid = context_course::instance($cert->get('course'))->id;
        }
        $itemid = (int) $validation->get('id');
        $fs = get_file_storage();
        $file = $fs->get_file(
            $contextid,
            icertificatereport::FILE_COMPONENT,
            icertificaterepository::FILE_AREA,
            $itemid,
            icertificaterepository::FILE_PATH,
            $code
        );

        return $file;
    }
    /**
     * getFileUrl
     * @param certifygen_validations $validation
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function get_file_url(certifygen_validations $validation): string {
        $code = certifygen_validations::get_certificate_code($validation);
        $code .= '.pdf';
        $contextid = context_system::instance()->id;
        if (!empty($validation->get('certifygenid'))) {
            $cert = new certifygen($validation->get('certifygenid'));
            $contextid = context_course::instance($cert->get('course'))->id;
        }
        $itemid = (int) $validation->get('id');
        $fs = get_file_storage();
        $file = $fs->get_file(
            $contextid,
            icertificatereport::FILE_COMPONENT,
            icertificaterepository::FILE_AREA,
            $itemid,
            icertificaterepository::FILE_PATH,
            $code
        );

        if (empty($file)) {
            return '';
        }
        return url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename()
        )->out();
    }

    /**
     * saveFile
     * @param stored_file $file
     * @return array
     */
    public function save_file(stored_file $file): array {
        $result = [
            'result' => true,
            'message' => get_string('ok', 'mod_certifygen'),
            'haserror' => false,
        ];

        try {
            $fs = get_file_storage();
            $filerecord = [
                'contextid' => $file->get_contextid(),
                'component' => self::FILE_COMPONENT,
                'filearea' => self::FILE_AREA,
                'itemid' => $file->get_itemid(),
                'filepath' => self::FILE_PATH,
                'filename' => $file->get_filename(),
            ];
            $fs->create_file_from_storedfile($filerecord, $file);
            $file->delete();
        } catch (moodle_exception $e) {
            $result['result'] = false;
            $result['haserror'] = true;
            $result['message'] = $e->getMessage();
        }
        return $result;
    }

    /**
     * is_enabled
     * @return bool
     * @throws dml_exception
     */
    public function is_enabled(): bool {
        $enabled = (int) get_config('certifygenrepository_localrepository', 'enabled');
        if ($enabled) {
            return true;
        }
        return false;
    }

    /**
     * saveFileUrl
     * @return bool
     */
    public function save_file_url(): bool {
        return false;
    }

    /**
     * get_consistent_validation_plugins
     * @return array
     */
    public function get_consistent_validation_plugins(): array {
        return [];
    }

    /**
     * Return file content (called by ws)
     *
     * @param certifygen_validations $trequest
     * @return string
     */
    public function get_file_content(certifygen_validations $validation): string {
        $result = '';
        try {
            $file = $this->get_file($validation);
            $result = $file->get_content();
        } catch (moodle_exception $e) {
            return $result;
        }
        return $result;
    }

    /**
     * Get file by code
     * Search for files named by $code
     *
     * @param string $code
     * @return string
     */
    public function get_file_by_code(string $code): string {
        global $DB;

        $url = '';
        try {
            $comparecomp = $DB->sql_compare_text('component');
            $comparecompplaceholder = $DB->sql_compare_text(':component');
            $comparefarea = $DB->sql_compare_text('filearea');
            $comparefareaplaceholder = $DB->sql_compare_text(':filearea');
            $comparefname = $DB->sql_compare_text('filename');
            $comparefnameplaceholder = $DB->sql_compare_text(':filename');
            $params = [
                    'component' => self::FILE_COMPONENT,
                    'filearea' => self::FILE_AREA,
                    'filename' => $code . '.pdf',
            ];
            $sql = "SELECT *
                  FROM {files}
                 WHERE {$comparecomp} = {$comparecompplaceholder}
                        AND {$comparefarea} = {$comparefareaplaceholder}
                        AND {$comparefname} = {$comparefnameplaceholder}";
            $result = $DB->get_record_sql($sql, $params);
            if ($result) {
                $url = url::make_pluginfile_url(
                    $result->contextid,
                    $result->component,
                    $result->filearea,
                    $result->itemid,
                    $result->filepath,
                    $result->filename
                )->out();
            }
        } catch (moodle_exception $e) {
            return $url;
        }

        return $url;
    }
}
