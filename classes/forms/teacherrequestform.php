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


namespace mod_certifygen\forms;
global $CFG;
require_once("$CFG->dirroot/mod/certifygen/lib.php");

use coding_exception;
use context;
use context_system;
use core\invalid_persistent_exception;
use core_form\dynamic_form;
use dml_exception;
use html_writer;
use mod_certifygen\persistents\certifygen;
use mod_certifygen\persistents\certifygen_context;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_teacherrequests;
use moodle_exception;
use moodle_url;
use tool_certificate\certificate;
use MoodleQuickForm;
use tool_certificate\permission;
use function get_string_manager;

class teacherrequestform extends dynamic_form {

    /**
     * @throws coding_exception|dml_exception
     */
    protected function definition()
    {
        $mform =& $this->_form;

        // Model list.
        [$modelids, $langs] = certifygen_context::get_system_context_modelids_and_langs();
        $mform->addElement('select', 'modelid',
            get_string('model', 'mod_certifygen'), $modelids);
        $mform->setType('modelid', PARAM_INT);
        $mform->addRule('modelid', get_string('required'), 'required');

        // Language.
        foreach ($langs as $modelid => $lang) {
            // Language: from model list languages.
            $mform->addElement('select', 'lang_'.$modelid, get_string('language'), $lang);
            $mform->setType('lang_'.$modelid, PARAM_RAW);
//            $mform->addRule('lang_'.$modelid, get_string('required'), 'required');
            $mform->hideIf('lang_'.$modelid, 'modelid', 'noteq', $modelid);
        }

        // Course list.
        $options = [
            'ajax' => 'mod_certifygen/form_course_selector',
            'multiple' => true,
            'valuehtmlcallback' => function($courseid) : string {
                $course = get_course($courseid);
                return $course->fullname;
            }
        ];
        $mform->addElement('autocomplete', 'courses',
            get_string('courseslist', 'mod_certifygen'), [], $options);

        // Hidden elements.
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'userid', 0);
        $mform->setType('userid', PARAM_INT);
    }

    /**
     * @throws dml_exception
     */
    protected function get_context_for_dynamic_submission(): context
    {
        return context_system::instance();
    }

    /**
     * @throws coding_exception
     * @throws moodle_exception
     * @throws dml_exception
     */
    protected function check_access_for_dynamic_submission(): void
    {
        if (!has_capability('mod/certifygen:viewmycontextcertificates', $this->get_context_for_dynamic_submission())) {
            throw new moodle_exception('nopermissions', 'error', '', 'viewcontextcertificates');
        }
    }

    /**
     * @throws coding_exception
     * @throws invalid_persistent_exception
     */
    public function process_dynamic_submission()
    {
        $formdata = (array) $this->get_data();
        $id = $formdata['id'];
        $data = new \stdClass();
        if ($id === 0) {
            $data->status = certifygen_teacherrequests::STATUS_NOT_STARTED;
        }
        $data->lang = $formdata['lang_' . $formdata['modelid']];
        $data->courses = implode(',', $formdata['courses']);
        $data->userid = $formdata['userid'];
        $data->modelid = $formdata['modelid'];

        certifygen_teacherrequests::manage_teacherrequests($id, $data);
    }

    /**
     * @throws coding_exception
     */
    public function set_data_for_dynamic_submission(): void
    {
        $data = [
            'id' => $this->_ajaxformdata['id'],
            'userid' => $this->_ajaxformdata['userid'],
        ];
        if ((int)$data['id'] > 0) {
            $teacherrequest = new certifygen_teacherrequests($data['id']);
            $data['lang'] = $teacherrequest->get('lang');
            $data['courses'] = $teacherrequest->get('courses');
        }
        $this->set_data($data);
    }

    /**
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url
    {
        return new moodle_url('/mod/certifygen/mycertificates.php');
    }
}