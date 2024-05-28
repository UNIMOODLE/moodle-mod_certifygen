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
 * @package   certifygenvalidation_cmd
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace certifygenvalidation_cmd;

use coding_exception;
use core\invalid_persistent_exception;
use mod_certifygen\interfaces\ICertificateValidation;
use stdClass;

class certifygenvalidation_cmd implements ICertificateValidation
{

    public function sendFile(): array
    {
        // TODO: Implement sendFile() method.
        return [];
    }

    public function getState(): array
    {
        // TODO: Implement getState() method.
        return [];
    }

    public function getFile(): array
    {
        // TODO: Implement getFile() method.
        return [];
    }

    /**
     * @throws coding_exception
     * @throws invalid_persistent_exception
     */
    public function addRecord(stdClass $data): int {
        $cmd = new persistent\cmd(0, $data);
        $cmd->create();
        return $cmd->get('id');

    }

    /**
     * @param stdClass $data
     * @return bool
     * @throws coding_exception
     */
    public function deleteRecord(stdClass $data): bool {
        $cmd = new persistent\cmd($data->modelid);
        return $cmd->delete();
    }
}