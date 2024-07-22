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
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.
/**
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_certifygen\interfaces;

use mod_certifygen\certifygen_file;

interface ICertificateValidation {
    const FILE_COMPONENT = 'mod_certifygen';
    const FILE_AREA = 'certifygenvalidation';
    const FILE_AREA_VALIDATED = 'certifygenvalidationvalidated';
    const FILE_PATH = '/';
    const FILE_NAME_STARTSWITH = 'certifygenvalidation_';

    public function is_enabled(): bool;
    public function sendFile(certifygen_file $file): array;
//    public function getState(int $courseid, int $validationid, string $code): int;
    public function getFile(int $courseid, int $validationid, int $teacherrequestid, string $code);
    public function getFileUrl(int $courseid, int $validationid, string $code): string;
    public function canRevoke(): bool;

    /**
     * This method is called by checkstatus task to check if the certificate status has changed.
     * Return true if validation plugin does not return the certificate validated inmediately on sendFile function.
     * @return bool
     */
    public function checkStatus(): bool;
    /**
     * This method is called by checkfIle task to check if the certificate file is already on external app.
     * Return true if validation plugin must be called to obtain the certificate file.
     * @return bool
     */
    public function checkfile(): bool;

    public function getStatus(int $validationid, int $teacherrequestid): int;
}

