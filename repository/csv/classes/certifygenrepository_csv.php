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
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.
/**
 * @package   certifygenrepository_csv
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace certifygenrepository_csv;

use certifygenvalidation_csv\certifygenvalidation_webservice;
use certifygenvalidation_csv\csv_configuration;
use coding_exception;
use dml_exception;
use mod_certifygen\interfaces\ICertificateRepository;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;
use stored_file;

class certifygenrepository_csv implements ICertificateRepository
{
    private $url = '';
    /**
     * @param certifygen_validations $validation
     * @return string
     * @throws coding_exception|dml_exception|moodle_exception
     */
    public function getFileUrl(certifygen_validations $validation): string
    {
        if (empty($this->url)) {
            $code = certifygen_validations::get_certificate_code($validation);
            $this->url = $this->getFileUrlFromExternalService($validation, $code);
        }
        return $this->url;
    }

    /**
     * @param certifygen_validations $validation
     * @param string $code
     * @return string
     * @throws moodle_exception
     * @throws coding_exception
     */
    private function getFileUrlFromExternalService(certifygen_validations $validation, string $code) : string {
        $validationcsv = new certifygenvalidation_webservice();
        $data = $validationcsv->getFileUrlFromExternalService($validation->get('id'), $code);
        if (array_key_exists('url', $data)){
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
    public function saveFile(stored_file $file): array
    {
        $result = [
            'result' => true,
            'haserror' => false,
            'message' => 'ok',
        ];
        return $result;
    }

    /**
     * @return bool
     * @throws dml_exception
     */
    public function is_enabled(): bool
    {
        $csvconfiguration = new csv_configuration();
        $cenabled = $csvconfiguration->is_enabled();
        if ($cenabled && get_config('certifygenrepository_csv', 'enabled')) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function saveFileUrl(): bool
    {
        return false;
    }
}