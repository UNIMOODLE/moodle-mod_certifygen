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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/certifygen/lib.php');
use coding_exception;
use context_module;
use core_table\local\filter\filter;
use core_table\local\filter\integer_filter;
use core_table\local\filter\string_filter;
use dml_exception;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\tables\activityteacher_table;
use mod_certifygen\tables\certificates_filterset;
use moodle_exception;
use moodle_url;
use renderable;
use stdClass;
use templatable;
use renderer_base;
/**
 * Activity view
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activity_view implements renderable, templatable {
    /** @var int $courseid */
    private int $courseid;
    /** @var bool $isteacher */
    private bool $isteacher;
    /** @var int $templateid */
    private int $templateid;
    /** @var int $pagesize */
    private int $pagesize;
    /** @var string $lang */
    private string $lang;
    /** @var stdClass $cm */
    private stdClass $cm;
    /** @var certifygen_model $certificatemodel */
    private certifygen_model $certificatemodel;
    /** @var bool $hasvalidator */
    private bool $hasvalidator;

    /**
     * __construct
     *
     * @param int $courseid
     * @param int $templateid
     * @param stdClass $cm
     * @param string $lang
     * @param int $pagesize
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function __construct(int $courseid, int $templateid, stdClass $cm, string $lang = "", int $pagesize = 10) {
        global $DB, $USER;

        $ccontext = context_module::instance($cm->id);
        $this->isteacher = is_siteadmin($USER->id) || !has_capability('mod/certifygen:emitmyactivitycertificate', $ccontext);
        $this->courseid = $courseid;
        $this->templateid = $templateid;
        $this->cm = $cm;
        $this->pagesize = $pagesize;
        $cmodel = $DB->get_record('certifygen_cmodels', ['certifygenid' => $cm->instance], '*', MUST_EXIST);
        $this->certificatemodel = new certifygen_model($cmodel->modelid);
        if (empty($this->certificatemodel->get_model_languages())) {
            $a = new stdClass();
            $a->lang = $this->certificatemodel->get('langs');
            throw new moodle_exception('lang_not_exists', 'mod_certifygen', '', $a);
        }
        $this->hasvalidator = !is_null($this->certificatemodel->get('validation'));
        $this->lang = $lang;
    }

    /**
     * export_for_template
     * @throws coding_exception
     * @throws moodle_exception
     * @throws dml_exception
     */
    public function export_for_template(renderer_base $output): stdClass {
        try {
            // Check if template exists.
            \mod_certifygen\template::instance($this->certificatemodel->get('templateid'));
            $url = new moodle_url('/mod/certifygen/view.php', ['id' => $this->cm->id]);
            $data = new stdClass();
            $data->table = $this->get_certificates_table();
            $modellangs = $this->certificatemodel->get_model_languages();
            if (count($modellangs) > 1) {
                $data->form = mod_certifygen_get_certificates_table_form($this->certificatemodel, $url, $this->lang, 'teacher');
            }
            if (!$this->isteacher) {
                $data->isstudent = true;
            }
        } catch (moodle_exception $exception) {
            $data = new stdClass();
            $data->haserror = true;
            $data->error = $exception->getMessage();
        }

        return $data;
    }

    /**
     * get_certificates_table
     * @return string
     * @throws coding_exception
     * @throws moodle_exception
     */
    private function get_certificates_table(): string {
        global $USER;
        $filters = new certificates_filterset();
        $lang = $this->lang;
        if (empty($this->lang)) {
            $lang = mod_certifygen_get_lang_selected($this->certificatemodel);
        }
        $filters->add_filter(new string_filter('lang', filter::JOINTYPE_DEFAULT, [$lang]));
        if (!$this->isteacher) {
            $filters->add_filter(new integer_filter('userid', filter::JOINTYPE_DEFAULT, [(int)$USER->id]));
        }
        if ($tifirst = optional_param('tifirst', '', PARAM_RAW)) {
            $filters->add_filter(new string_filter('tifirst', filter::JOINTYPE_DEFAULT, [$tifirst]));
        }
        if ($tilast = optional_param('tilast', '', PARAM_RAW)) {
            $filters->add_filter(new string_filter('tilast', filter::JOINTYPE_DEFAULT, [$tilast]));
        }
        $activityteachertable = new activityteacher_table($this->courseid, $this->templateid, $this->cm->instance);
        $activityteachertable->set_filterset($filters);
        $paramsurl = ['id' => $this->cm->id, 'lang' => $lang];
        if (!empty($tifirst)) {
            $paramsurl['tifirst'] = $tifirst;
        }
        if (!empty($tilast)) {
            $paramsurl['tilast'] = $tilast;
        }
        $activityteachertable->baseurl = new moodle_url('/mod/certifygen/view.php', $paramsurl);
        ob_start();
        $activityteachertable->out($this->pagesize, $this->isteacher);
        $out1 = ob_get_contents();
        ob_end_clean();
        return $out1;
    }
}
