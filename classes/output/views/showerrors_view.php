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
use core_table\local\filter\filter;
use core_table\local\filter\string_filter;
use mod_certifygen\forms\errorfiltersform;
use mod_certifygen\tables\errors_filterset;
use mod_certifygen\tables\showerrors_table;
use renderable;
use templatable;
use renderer_base;
/**
 *
 * @package    showerrors_view
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class showerrors_view  implements renderable, templatable {
    /**
     * export_for_template
     * @param renderer_base $output
     * @return \stdClass
     * @throws \coding_exception
     */
    public function export_for_template(renderer_base $output) {
        $userfullname = optional_param('userfullname', '', PARAM_RAW);
        $modelname = optional_param('modelname', '', PARAM_RAW);
        $customdata = [
            'userfullname' => $userfullname,
            'modelname' => $modelname,
        ];
        // Form.
        $url = new \moodle_url('/mod/certifygen/showerrors.php');
        $mform = new errorfiltersform($url->out(), $customdata);
        $data = $mform->get_data();

        // Table.
        $tablelist = new showerrors_table();
        $filters = new errors_filterset();
        if (isset($data->userfullname) && !empty($data->userfullname)) {
            $filters->add_filter(new string_filter('userfullname', filter::JOINTYPE_DEFAULT, [$data->userfullname]));
        }
        if (isset($data->modelname) && !empty($data->modelname)) {
            $filters->add_filter(new string_filter('modelname', filter::JOINTYPE_DEFAULT, [$data->modelname]));
        }
        $tablelist->baseurl = new \moodle_url('/mod/certifygen/showerrors.php');
        $tablelist->set_filterset($filters);
        ob_start();
        $tablelist->out(10, true);
        $out1 = ob_get_contents();
        ob_end_clean();
        $data = new \stdClass();
        $data->table = $out1;
        $data->form = $mform->render();
        return $data;
    }
}
