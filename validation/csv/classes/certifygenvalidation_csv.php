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
 * @package   certifygenvalidation_csv
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace certifygenvalidation_csv;


use context_course;
use core\invalid_persistent_exception;
use mod_certifygen\certifygen_file;
use mod_certifygen\interfaces\ICertificateValidation;
use mod_certifygen\persistents\certifygen_validations;
use stdClass;
use stored_file;

class certifygenvalidation_csv implements ICertificateValidation
{

    public function sendFile(certifygen_file $file): array
    {
        // TODO: Implement sendFile() method.
        return [];
    }

    /**
     * @param int $courseid
     * @param int $validationid
     * @param string $code
     * @return int
     */
    public function getState(int $courseid, int $validationid, string $code): int {
        return certifygen_validations::STATUS_IN_PROGRESS;
    }

    /**
     * @param int $courseid
     * @param int $validationid
     * @param string $code
     * @return stored_file
     */
    public function getFile(int $courseid, int $validationid, string $code)
    {
        $fs = get_file_storage();
        $contextid = context_course::instance($courseid)->id;
        return $fs->get_file($contextid, self::FILE_COMPONENT,
            self::FILE_AREA, $validationid, self::FILE_PATH, $code);
    }

    /**
     * @param stdClass $data
     * @return bool
     * @throws coding_exception
     */
    public function deleteRecord(stdClass $data): bool {
        $csv = new persistent\csv($data->modelid);
        return $csv->delete();
    }

    /**
     * @throws coding_exception
     * @throws invalid_persistent_exception
     */
    public function addRecord(stdClass $data): int {
        $csv = new persistent\csv(0, $data);
        $csv->create();
        return $csv->get('id');

    }

    /**
     * @return bool
     */
    public function canRevoke(): bool
    {
        return false;
    }

    public function getFileUrl(int $courseid, int $validationid, string $code): string
    {
        // TODO: Implement getFileUrl() method.
        return '';
    }
}