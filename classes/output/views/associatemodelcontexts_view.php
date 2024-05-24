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
namespace mod_certifygen\output\views;
use mod_certifygen\tables\modelcontext_table;
use moodle_url;
use renderable;
use stdClass;
use templatable;
use renderer_base;
class associatemodelcontexts_view implements renderable, templatable {

    /**
     * @param $pagesize
     * @param $useinitialsbar
     */
    public function __construct($pagesize = 10, $useinitialsbar = true) {
        $this->pagesize = $pagesize;
        $this->useinitialsbar = $useinitialsbar;
    }

    /**
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output): stdClass {
        $modelcontexttable = new modelcontext_table();
        $modelcontexttable->baseurl = new moodle_url('/mod/certifygen/associatemodels.php');
        ob_start();

        $modelcontexttable->out($this->pagesize, $this->useinitialsbar);

        $out1 = ob_get_contents();
        ob_end_clean();
        $data = new stdClass();
        $data->table = $out1;
        return $data;
    }
}

