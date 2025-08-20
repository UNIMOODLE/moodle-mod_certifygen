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
 * @package   certifygenrepository_csv
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace certifygenrepository_csv;

use certifygenvalidation_csv\certifygenvalidation_csv;
use certifygenvalidation_csv\csv_configuration;
use certifygenvalidation_csv\persistents\certifygenvalidationcsv;
use \core\exception\coding_exception;
use dml_exception;
use mod_certifygen\interfaces\icertificaterepository;
use mod_certifygen\persistents\certifygen;
use mod_certifygen\persistents\certifygen_validations;
use \core\exception\moodle_exception;
use stored_file;
/**
 * certifygenrepository_csv
 * @package   certifygenrepository_csv
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class certifygenrepository_csv implements icertificaterepository {
    /** @var string $url */
    private string $url = '';
    /**
     * getFileUrl
     * @param certifygen_validations $validation
     * @return string
     * @throws coding_exception|dml_exception|moodle_exception
     */
    public function get_file_url(certifygen_validations $validation): string {
        if (empty($this->url)) {
            $code = certifygen_validations::get_certificate_code($validation);
            $this->url = $this->call_file_url_from_external_service($validation, $code);
        }
        return $this->url;
    }

    /**
     * get_file_url_from_external_service
     * @param certifygen_validations $validation
     * @param string $code
     * @return string
     * @throws moodle_exception
     * @throws coding_exception
     */
    private function call_file_url_from_external_service(certifygen_validations $validation, string $code): string {
        $params = ['validationid' => $validation->get('id')];
        $teacherrequest = certifygenvalidationcsv::get_record($params);
        if (!$teacherrequest) {
            throw new moodle_exception('certifygenvalidationcsvnotfound', 'certifygenrepository_csv');
        }
        $validationcsv = new certifygenvalidation_csv();
        $data = $validationcsv->get_file_url_from_external_service($code);
        if (array_key_exists('url', $data)) {
            return $data['url'];
        }
        return '';
    }

    /**
     * File already saved on external service.
     * This function does not need to be implemented.
     * @param stored_file $file
     * @return array
     */
    public function save_file(stored_file $file): array {
        return [
                'result' => true,
                'haserror' => false,
                'message' => get_string('ok', 'mod_certifygen'),
        ];
    }

    /**
     * is_enabled
     * @return bool
     * @throws dml_exception
     */
    public function is_enabled(): bool {
        $csvconfiguration = new csv_configuration();
        $cenabled = $csvconfiguration->is_enabled();
        if ($cenabled && get_config('certifygenrepository_csv', 'enabled')) {
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
     * @return string[]
     */
    public function get_consistent_validation_plugins(): array {
        return ['certifygenvalidation_csv'];
    }
    /**
     * Return file content (called by ws)
     *
     * @param certifygen_validations $trequest
     * @return string
     */
    public function get_file_content(certifygen_validations $validation): string {
        $filecontent = '';
        try {
            $validationcsv = new certifygenvalidation_csv();
            $courseid = 0;
            if (!empty($validation->get('certifygenid'))) {
                $certifygen = new certifygen($validation->get('certifygenid'));
                $courseid = $certifygen->get('course');
            }
            $data = $validationcsv->get_file($courseid, $validation->get('id'));
            if (empty($data['error']) && array_key_exists('file', $data)) {
                /** @var stored_file $file */
                $file = $data['file'];
                $filecontent = $file->get_content();
            }
        } catch (moodle_exception $e) {
            return $filecontent;
        }
        return $filecontent;
    }

    /**
     * Get file by code
     * Search for files named by $code
     *
     * @param string $code
     * @return string
     */
    public function get_file_by_code(string $code): string {
        $validationcsv = new certifygenvalidation_csv();
        $data = $validationcsv->get_file_url_from_external_service($code);
        if (array_key_exists('url', $data)) {
            return $data['url'];
        }
        return '';
    }
}
