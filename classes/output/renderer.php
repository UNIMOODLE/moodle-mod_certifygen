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
namespace mod_certifygen\output;
use \core\exception\coding_exception;
use dml_exception;
use mod_certifygen\output\views\activity_view;
use mod_certifygen\output\views\model_view;
use mod_certifygen\output\views\profile_my_certificates_view;
use mod_certifygen\output\views\showerrors_view;
use mod_certifygen\output\views\associatemodelcontexts_view;
use \core\exception\moodle_exception;
use plugin_renderer_base;
/**
 * Renderer
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {
    /**
     * Show errors view renderer
     * @param showerrors_view $view
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function render_showerrors_view(showerrors_view $view): string {
        $data = $view->export_for_template($this);
        return $this->render_from_template('mod_certifygen/showerrors', $data);
    }
    /**
     * Activity view renderer
     * @param activity_view $view
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function render_activity_view(activity_view $view): string {
        $data = $view->export_for_template($this);
        return $this->render_from_template('mod_certifygen/activity', $data);
    }

    /**
     * Model view renderer
     * @param model_view $view
     * @return string
     * @throws moodle_exception
     */
    public function render_model_view(model_view $view): string {

        $data = $view->export_for_template($this);
        return $this->render_from_template('mod_certifygen/model_list', $data);
    }

    /**
     * Associate model contexts view renderer
     * @param associatemodelcontexts_view $view
     * @return string
     * @throws moodle_exception
     */
    public function render_associatemodelcontexts_view(associatemodelcontexts_view $view): string {

        $data = $view->export_for_template($this);
        return $this->render_from_template('mod_certifygen/associatemodelcontexts', $data);
    }

    /**
     * My certificates view renderer
     * @param profile_my_certificates_view $view
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function render_profile_my_certificates_view(profile_my_certificates_view $view): string {

        $data = $view->export_for_template($this);
        return $this->render_from_template('mod_certifygen/profile_my_certificates', $data);
    }
}
