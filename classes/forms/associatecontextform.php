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

/**
 *
 * @package     XXXX
 * @author      202X Elena Barrios Galán <elena@tresipunt.com>
 * @copyright   3iPunt <https://www.tresipunt.com/>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace mod_certifygen\forms;


use context;
use context_system;
use moodle_exception;
use moodle_url;

class associatecontextform extends \core_form\dynamic_form {

    /**
     * @inheritDoc
     */
    protected function definition()
    {

        $mform =& $this->_form;

        // Modelid.
        $mform->addElement('hidden', 'modelid', 0);
        $mform->setType('modelid', PARAM_INT);

        // Context type: course or category.
        $mform->addElement('select', 'ctype', get_string('chooseacontexttype', 'mod_certifygen'),
            [
                'category' => get_string('category'),
                'course' => get_string('course')
            ]);
        $mform->setType('ctype', PARAM_RAW);

        // Select for categories.
        $options = [
            'ajax' => 'mod_certifygen/form_category_selector',
            'multiple' => true,
            'valuehtmlcallback' => function($categoryid) : string {
                $category = \core_course_category::get($categoryid);
                return $category->name;
            }
        ];
        $mform->addElement('autocomplete', 'categorycontext', get_string('user'), [], $options)->setHiddenLabel(true);
        $mform->hideIf('categorycontext', 'ctype', 'eq', 'course');

        // otor
//        $displaylist = \core_course_category::make_categories_list('moodle/course:create');
//        $mform->addElement('autocomplete', 'category', get_string('coursecategory'), $displaylist);


        // Select for courses.
        $options = [
            'ajax' => 'core_user/form_user_selector',
            'multiple' => true,
            'valuehtmlcallback' => function($userid) : string {
                $user = \core_user::get_user($userid);
                return $user->firstname;
            }
        ];
        $mform->addElement('autocomplete', 'coursecontext', get_string('user'), [], $options)->setHiddenLabel(true);
        $mform->hideIf('coursecontext', 'ctype', 'eq', 'category');
    }

    protected function get_context_for_dynamic_submission(): context
    {
        return context_system::instance();
    }

    protected function check_access_for_dynamic_submission(): void
    {
        if (!has_capability('mod/certifygen:manage', $this->get_context_for_dynamic_submission())) {
            throw new moodle_exception('nopermissions', 'error', '', 'manage models');
        }
    }

    public function process_dynamic_submission()
    {
        // TODO: Implement process_dynamic_submission() method.
        error_log(__FUNCTION__);
    }

    public function set_data_for_dynamic_submission(): void
    {
        // TODO: Implement set_data_for_dynamic_submission() method.
        if (!empty($this->_ajaxformdata['id'])) {
            $this->set_data([
                    'modelid' => $this->_ajaxformdata['id'],
                ]
            );
        }
    }

    protected function get_page_url_for_dynamic_submission(): moodle_url
    {
        return new moodle_url('/mod/certifygen/modelmanager.php');
    }
}