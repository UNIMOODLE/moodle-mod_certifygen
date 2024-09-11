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
// Project implemented by the "Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 *
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace mod_certifygen\output\views;

use dml_exception;
use invalid_parameter_exception;
use mod_certifygen\external\getmodellisttable_external;
use renderable;
use stdClass;
use templatable;
use renderer_base;

class model_view implements renderable, templatable {
    private int $pagesize;
    private bool $useinitialsbar;

    /**
     * @param int $pagesize
     * @param bool $useinitialsbar
     */
    public function __construct(int $pagesize = 10, bool $useinitialsbar = true) {
        $this->pagesize = $pagesize;
        $this->useinitialsbar = $useinitialsbar;
    }

    /**
     * @param renderer_base $output
     * @return stdClass
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    public function export_for_template(renderer_base $output): stdClass {

        return (object) getmodellisttable_external::getmodellisttable();
    }
}