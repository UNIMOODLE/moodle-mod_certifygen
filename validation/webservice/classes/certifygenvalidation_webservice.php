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
 * @package   certifygenvalidation_webservice
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace certifygenvalidation_webservice;
global $CFG;
require_once $CFG->libdir . '/soaplib.php';
require_once $CFG->libdir . '/pdflib.php';

use certifygenvalidation_webservice\persistents\certifygenvalidationwebservice;
use coding_exception;
use core\session\exception;
use dml_exception;
use file_exception;
use mod_certifygen\certifygen_file;
use mod_certifygen\interfaces\ICertificateValidation;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;
use stored_file_creation_exception;

class certifygenvalidation_webservice implements ICertificateValidation
{
    public function __construct()
    {

    }


    /**
     * @throws exception
     */
    public function sendFile(certifygen_file $file): array
    {
        // No tiene sentido enviar el fichero a ninguna parte
        //igua hay q guardarlo enmoodledata oara seguir una logica.

        $validation = new certifygen_validations($file->get_validationid());
        $validation->set('status', certifygen_validations::STATUS_IN_PROGRESS);
        return [
            'haserror' => false,
            'message' => 'ok',
        ];
    }

    /**
     * @param int $courseid
     * @param int $validationid
     * @param string $code
     * @return array
     * @throws dml_exception
     * @throws file_exception
     * @throws stored_file_creation_exception
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function getFile(int $courseid, int $validationid, string $code)
    {
        // TODO.
        return [
            'haserror' => true,
            'message' => ''
        ];
    }

    /**
     * @return bool
     */
    public function canRevoke(): bool
    {
        return false;
    }

    /**
     * @param string $code
     * @return array
     */
    public function revoke(string $code) : array {
        //TODO.
        return [
            'haserror' => false,
            'message' => '',
        ];
    }
    /**
     * @param int $courseid
     * @param int $validationid
     * @param string $code
     * @return string
     */
    public function getFileUrl(int $courseid, int $validationid, string $code): string
    {
        // TODO.
        return '';
    }

    /**
     * @return bool
     */
    public function is_enabled(): bool
    {
        return (int)get_config('certifygenvalidation_webservice', 'enabled');
    }

    /**
     * @return bool
     */
    public function checkStatus(): bool
    {
        return true;
    }

    /**
     *
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
}