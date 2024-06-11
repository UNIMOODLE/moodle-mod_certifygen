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
// Project implemented by the "Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU".
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


namespace mod_certifygen\output\views;
global $CFG;
require_once($CFG->dirroot . '/mod/certifygen/lib.php');
use coding_exception;
use core_table\local\filter\filter;
use core_table\local\filter\integer_filter;
use core_table\local\filter\string_filter;
use dml_exception;
use mod_certifygen\persistents\certifygen;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\tables\activityteacherview_table;
use mod_certifygen\tables\activityteacherviewnovalidator_table;
use mod_certifygen\tables\certificates_filterset;
use moodle_exception;
use moodle_url;
use renderable;
use stdClass;
use templatable;
use renderer_base;
class teacher_view implements renderable, templatable {
    private int $courseid;
    private int $templateid;
    private int $pagesize;
    private stdClass $cm;
    private bool $useinitialsbar;
    private certifygen_model $certificatemodel;
    private bool $hasvalidator;

    /**
     * @param int $courseid
     * @param int $templateid
     * @param stdClass $cm
     * @param int $pagesize
     * @param bool $useinitialsbar
     * @throws coding_exception
     */
    public function __construct(int $courseid, int $templateid, stdClass $cm, int $pagesize = 10, bool $useinitialsbar = true) {
        $this->courseid = $courseid;
        $this->templateid = $templateid;
        $this->cm = $cm;
        $this->pagesize = $pagesize;
        $this->useinitialsbar = $useinitialsbar;
        $certificate = new certifygen($cm->instance);
        $this->certificatemodel = new certifygen_model($certificate->get('modelid'));
        $this->hasvalidator = !is_null($this->certificatemodel->get('validation'));
    }

    /**
     * @throws coding_exception
     * @throws moodle_exception
     * @throws dml_exception
     */
    public function export_for_template(renderer_base $output) : stdClass {
        $url = new moodle_url('/mod/certifygen/view.php', ['id' => $this->cm->id]);
        $data = new stdClass();
        $data->table = $this->get_certificates_table();
        $data->form = mod_certifygen_get_certificates_table_form($this->certificatemodel, $url);
        return $data;
    }

    /**
     * @return string
     * @throws coding_exception
     * @throws moodle_exception
     */
    private function get_certificates_table() : string {
        $filters = new certificates_filterset();
        $lang = mod_certifygen_get_lang_selected($this->certificatemodel);
        $filters->add_filter(new string_filter('lang',filter::JOINTYPE_DEFAULT, [$lang]));
        if ($userid = optional_param('userid', 0, PARAM_INT)) {
            $filters->add_filter(new integer_filter('userid',filter::JOINTYPE_DEFAULT, [$userid]));
        }

        if ($this->hasvalidator) {
            $activityteachertable = new activityteacherview_table($this->courseid, $this->templateid, $this->cm->instance);
        } else {
            $activityteachertable = new activityteacherviewnovalidator_table($this->courseid, $this->templateid, $this->certificatemodel->get('id'));
        }
        $activityteachertable->set_filterset($filters);
        $paramsurl = ['id' => $this->cm->id, 'lang' => $lang];
        $activityteachertable->baseurl = new moodle_url('/mod/certifygen/view.php', $paramsurl);
        ob_start();
        $activityteachertable->out($this->pagesize, $this->useinitialsbar);
        $out1 = ob_get_contents();
        ob_end_clean();
        return $out1;
    }
}