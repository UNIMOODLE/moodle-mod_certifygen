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
use mod_certifygen\persistents\certifygen_model;

/**
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class mod_certifygen_generator extends testing_module_generator {

    private $model;
    public function create_model(int $type, int $mode, int $templateid, string $validation, string $report) {

        $data = [
            'name' => 'Modelo 1',
            'type' => $type,
            'mode' => $mode,
            'templateid' => $templateid,
            'timeondemmand' => 0,
            'langs' => 'en,es',
            'validation' => $validation,
            'report' => $report,

        ];
        $model = new certifygen_model(0,  (object)$data);
        return $model->create();
    }
    public function create_model_by_name(string $name, int $templateid) {

        $data = [
            'name' => $name,
            'type' => certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
            'mode' => certifygen_model::MODE_UNIQUE,
            'templateid' => $templateid,
            'timeondemmand' => 0,
            'langs' => 'en,es',
            'validation' => '',
            'report' => '',

        ];
        $model = new certifygen_model(0, (object)$data);
        return $model->create();
    }
}
