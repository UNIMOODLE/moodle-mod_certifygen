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
namespace mod_certifygen\forms;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once("$CFG->dirroot/mod/certifygen/lib.php");

use coding_exception;
use context;
use context_system;
use core\invalid_persistent_exception;
use core_form\dynamic_form;
use dml_exception;
use mod_certifygen\persistents\certifygen_context;
use mod_certifygen\persistents\certifygen_validations;
use moodle_exception;
use moodle_url;
use stdClass;

/**
 * Teacher request form
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class teacherrequestform extends dynamic_form {
    /**
     * definition
     * @throws coding_exception|dml_exception
     */
    protected function definition() {
        $mform =& $this->_form;

        $mform->addElement('text', 'name', get_string('name', 'mod_certifygen'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);

        // Model list.
        [$modelids, $langs] = certifygen_context::get_system_context_modelids_and_langs();
        $mform->addElement(
            'select',
            'modelid',
            get_string('model', 'mod_certifygen'),
            $modelids
        );
        $mform->setType('modelid', PARAM_INT);
        $mform->addRule('modelid', get_string('required'), 'required');

        // Language.
        foreach ($langs as $modelid => $lang) {
            // Language: from model list languages.
            $mform->addElement('select', 'lang_' . $modelid, get_string('language'), $lang);
            $mform->setType('lang_' . $modelid, PARAM_RAW);
            $mform->hideIf('lang_' . $modelid, 'modelid', 'noteq', $modelid);
        }

        // Course list.
        $options = [
            'ajax' => 'mod_certifygen/form_mycourses_selector',
            'multiple' => true,
            'userid' => (int)$this->_ajaxformdata['userid'],
            'valuehtmlcallback' => function ($courseid): string {
                $course = get_course($courseid);
                $formated = format_text($course->fullname);
                $formated = strip_tags($formated);
                return $formated;
            },
        ];
        $mform->addElement(
            'autocomplete',
            'courses',
            get_string('courseslist', 'mod_certifygen'),
            [],
            $options
        );
        $mform->addRule('courses', get_string('required'), 'required');

        // Hidden elements.
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'userid', 0);
        $mform->setType('userid', PARAM_INT);
    }

    /**
     * get_context_for_dynamic_submission
     * @throws dml_exception
     */
    protected function get_context_for_dynamic_submission(): context {
        return context_system::instance();
    }

    /**
     * check_access_for_dynamic_submission
     * @throws coding_exception
     * @throws moodle_exception
     * @throws dml_exception
     */
    protected function check_access_for_dynamic_submission(): void {
        if (!has_capability('mod/certifygen:viewmycontextcertificates', $this->get_context_for_dynamic_submission())) {
            throw new moodle_exception('nopermissions', 'error', '', 'viewcontextcertificates');
        }
    }

    /**
     * process_dynamic_submission
     *
     * @throws coding_exception
     * @throws invalid_persistent_exception|dml_exception
     */
    public function process_dynamic_submission() {
        $formdata = (array) $this->get_data();
        $id = $formdata['id'];
        $data = new stdClass();
        if ($id === 0) {
            $data->status = certifygen_validations::STATUS_NOT_STARTED;
        }
        $data->lang = $formdata['lang_' . $formdata['modelid']];
        $data->courses = implode(',', $formdata['courses']);
        $data->userid = $formdata['userid'];
        $data->modelid = $formdata['modelid'];
        $data->name = $formdata['name'];
        $data->certifygenid = 0;
        certifygen_validations::manage_validation($id, $data);
    }

    /**
     * set_data_for_dynamic_submission
     * @throws coding_exception
     */
    public function set_data_for_dynamic_submission(): void {
        $data = [
            'id' => $this->_ajaxformdata['id'],
            'userid' => $this->_ajaxformdata['userid'],
        ];
        if ((int)$data['id'] > 0) {
            $teacherrequest = new certifygen_validations($data['id']);
            $data['lang'] = $teacherrequest->get('lang');
            $data['courses'] = $teacherrequest->get('courses');
            $data['name'] = $teacherrequest->get('name');
        }
        $this->set_data($data);
    }

    /**
     * get_page_url_for_dynamic_submission
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        return new moodle_url('/mod/certifygen/mycertificates.php');
    }

    /**
     * Validation
     * @param $data
     * @param $files
     * @return array
     * @throws coding_exception
     */
    public function validation($data, $files) {
        $errors = [];

        if (
            !array_key_exists('courses', $data)
            || (array_key_exists('courses', $data) && empty($data['courses']))
        ) {
            $errors['courses'] = get_string('required');
        }

        return $errors;
    }
}
