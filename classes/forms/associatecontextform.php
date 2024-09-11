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
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos..
/**
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace mod_certifygen\forms;
global $CFG;
require_once($CFG->dirroot . '/mod/certifygen/lib.php');
use coding_exception;
use context;
use context_system;
use core\invalid_persistent_exception;
use core_course_category;
use core_form\dynamic_form;
use dml_exception;
use mod_certifygen\persistents\certifygen_context;
use moodle_exception;
use moodle_url;

class associatecontextform extends dynamic_form {

    /**
     * @inheritDoc
     * @throws coding_exception
     */
    protected function definition()
    {

        $mform =& $this->_form;

        // Modelid.
        $mform->addElement('hidden', 'modelid', 0);
        $mform->setType('modelid', PARAM_INT);

        // Id.
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

        // Context type: course or category.
        $mform->addElement('select', 'ctype', get_string('chooseacontexttype', 'mod_certifygen'),
            mod_certifygen_get_context_types());
        $mform->setType('ctype', PARAM_INT);

        // Select for categories.
        $options = [
            'ajax' => 'mod_certifygen/form_category_selector',
            'multiple' => true,
            'valuehtmlcallback' => function($categoryid) : string {
                $category = core_course_category::get($categoryid);
                return $category->name;
            }
        ];
        $mform->addElement('autocomplete', 'categorycontext', get_string('categorycontext', 'mod_certifygen'), [], $options)->setHiddenLabel(true);
        $mform->hideIf('categorycontext', 'ctype', 'in',
            [certifygen_context::CONTEXT_TYPE_COURSE, certifygen_context::CONTEXT_TYPE_SYSTEM]);
        // Select for courses.
        $options = [
            'ajax' => 'mod_certifygen/form_course_selector',
            'multiple' => true,
            'valuehtmlcallback' => function($courseid) : string {
                $course = get_course($courseid);
                return $course->fullname;
            }
        ];
        $mform->addElement('autocomplete', 'coursecontext', get_string('coursecontext', 'mod_certifygen'), [], $options)->setHiddenLabel(true);
        $mform->hideIf('coursecontext', 'ctype', 'in',
            [certifygen_context::CONTEXT_TYPE_CATEGORY, certifygen_context::CONTEXT_TYPE_SYSTEM]);
    }

    /**
     * @throws dml_exception
     */
    protected function get_context_for_dynamic_submission(): context
    {
        return context_system::instance();
    }

    /**
     * @throws moodle_exception
     * @throws coding_exception
     * @throws dml_exception
     */
    protected function check_access_for_dynamic_submission(): void
    {
        if (!has_capability('mod/certifygen:manage', $this->get_context_for_dynamic_submission())) {
            throw new moodle_exception('nopermissions', 'error', '', 'manage models');
        }
    }

    /**
     * @throws coding_exception
     * @throws invalid_persistent_exception
     */
    public function process_dynamic_submission()
    {

        $formdata = $this->get_data();

        $type = (int)$formdata->ctype;
        $contextids = [];
        if ($formdata->ctype == certifygen_context::CONTEXT_TYPE_COURSE) {
            $contextids = array_values($formdata->coursecontext);
        } else if ($formdata->ctype == certifygen_context::CONTEXT_TYPE_CATEGORY) {
            $type = certifygen_context::CONTEXT_TYPE_CATEGORY;
            $contextids = array_values($formdata->categorycontext);
        }
        $data = [
            'id' => $formdata->id ?? 0,
            'modelid' => $formdata->modelid,
            'type' => $type,
            'contextids' => implode(',', $contextids),

        ];
        if ($type != certifygen_context::CONTEXT_TYPE_SYSTEM && empty($data['contextids'])) {
            $modelcontext = new certifygen_context($formdata->id);
            $modelcontext->delete();
            return 0;
        } else {
            return certifygen_context::save_model_object((object) $data)->get('id');
        }
    }

    /**
     * @throws coding_exception
     */
    public function set_data_for_dynamic_submission(): void
    {
        $modelid = $this->_ajaxformdata['modelid'];
        $data = [
            'modelid' => $modelid,
        ];
        if (!empty($this->_ajaxformdata['id'])) {
            $modelcontext = new certifygen_context($this->_ajaxformdata['id']);
            $data['id'] = $this->_ajaxformdata['id'];
            $data['ctype'] = $modelcontext->get('type');
            if ($modelcontext->get('type') == certifygen_context::CONTEXT_TYPE_CATEGORY) {
                $data['categorycontext'] = explode(',', $modelcontext->get('contextids'));
            } else if ($modelcontext->get('type') == certifygen_context::CONTEXT_TYPE_COURSE) {
                $data['coursecontext'] = explode(',', $modelcontext->get('contextids'));
            } else {
                $data['coursecontext'] = [];
            }
        }
        $this->set_data($data);
    }

    protected function get_page_url_for_dynamic_submission(): moodle_url
    {
        return new moodle_url('/mod/certifygen/modelmanager.php');
    }
}