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
 * @package    certifygenreport_basic
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace certifygenreport_basic\output;
global $CFG;
require_once($CFG->dirroot.'/user/lib.php');

use certifygenreport_basic\useofthecoursealgorithm;
use coding_exception;
use context_course;
use context_system;
use dml_exception;
use moodle_exception;
use moodle_url;
use renderable;
use stdClass;
use templatable;

class report_view implements renderable, templatable
{
    private int $userid;
    private bool $showtext;
    private bool $showendtext;
    const REPORT_COMPONENT = 'certifygenreport_basic';
    const REPORT_FILEAREA = 'logo';
    const MAX_NUMBER_COURSES = 7;

    /**
     * @param int $userid
     * @param array $courses
     * @param bool $showtext
     * @param bool $showendtext
     */
    public function __construct(int $userid, array $courses, bool $showtext = true, bool $showendtext = true)
    {
        $this->userid = $userid;
        $this->courses = $courses;
        $this->showtext = $showtext;
        $this->showendtext = $showendtext;
    }
    /**
     * @param \renderer_base $output
     * @return stdClass
     * @throws coding_exception
     * @throws dml_exception
     */
    public function export_for_template(\renderer_base $output)
    {
        $user = user_get_users_by_id([$this->userid]);
        $user = reset($user);
        $name = fullname($user);
        $data = new stdClass();
        $url = $this->get_logo_url();
        $data->logosrc = $url;
        if ($this->showtext) {
            $data->hastext = true;
            $data->text = get_string('reporttext', 'certifygenreport_basic', (object)['teacher' => $name] );
        }
        if ($this->showendtext) {
            $data->hasendtext = true;
            $data->endtext = get_string('coursetypedesc', 'certifygenreport_basic');
        }
        $data->list = $this->get_courses_list();
        return $data;
    }

    /**
     * @return array
     */
    private function get_courses_list() : array {
        $courses = [];
        foreach ($this->courses as $course) {
            $courses[] = $this->get_user_evaluation_in_course((int)$course['courseid']);
        }
        return $courses;
    }

    /**
     * @param stdClass $course
     * @return string[]
     */
    private function get_user_evaluation_in_course(int $courseid) : array {
        $course = get_course($courseid);
        $teachers = $this->get_course_teachers($courseid);
        $info = new stdClass();
        $info->coursename = strip_tags(format_text($course->fullname));
        $info->coursedetails = $this->get_course_details_string($course);
        $info->teachers = $this->get_course_teachersstring($teachers);
        $info->type = $this->get_course_type_by_algorithm($courseid);
        $courseinfokey = 'courseinfo';
        if (count($teachers) > 1) {
            $courseinfokey = 'courseinfopl';
        }
        $courseinfo = get_string($courseinfokey, 'certifygenreport_basic', $info);
        return ['course' => $courseinfo];
    }

    /**
     * @param stdClass $course
     * @return string
     * @throws coding_exception
     * @throws moodle_exception
     */
    private function get_course_details_string(stdClass $course) : string {
        $detail = '';
        $category = \core_course_category::get($course->category);
        $data = ['name' => strip_tags(format_text($category->name))];
        $detail .= get_string('cdetail_1', 'certifygenreport_basic', (object)$data);
        if ($course->startdate > 0) {
            $data = ['date' => userdate($course->startdate)];
            $detail .= ' ' . get_string('cdetail_2', 'certifygenreport_basic', (object)$data);
        }
        if ($course->enddate > 0) {
            $data = ['date' => userdate($course->enddate)];
            $detail .= ' ' . get_string('cdetail_3', 'certifygenreport_basic', (object)$data);
        }
        return $detail;
    }
    /**
     * @param int $courseid
     * @return array
     */
    private function get_course_teachers(int $courseid) : array {
        $context = context_course::instance($courseid);
        $teachers = get_enrolled_users($context, 'moodle/course:managegroups');
        return $teachers;
    }

    /**
     * @param int $courseid
     * @return int
     * @throws coding_exception
     */
    private function get_course_students_number(int $courseid) : int {
        $students = 0;
        $context = context_course::instance($courseid);
        $participants = get_enrolled_users($context);
        foreach ($participants as $participant) {
            if (has_capability('moodle/course:managegroups', $context, $participant)) {
                continue;
            }
            $students++;
        }
        return $students;
    }
    /**
     * @param array $teachers
     * @return string
     * @throws coding_exception
     */
    private function get_course_teachersstring(array $teachers) : string {
        $teachersstring = '';
        $total = count($teachers);
        $cont = 0;
        foreach ($teachers as $teacher) {
            $cont++;
            if (!empty($teachersstring) && $cont != $total){
                $teachersstring .= ', ';
            } else if (!empty($teachersstring) && $cont == $total) {
                $teachersstring .= ' ' . get_string('and', 'certifygenreport_basic') . ' ';
            }
            $teachersstring .= fullname($teacher);
        }
        return $teachersstring;
    }

    /**
     * @param int $courseid
     * @return string
     * @throws coding_exception
     * @throws moodle_exception
     */
    private function get_course_type_by_algorithm(int $courseid) : string {
        /**
         * Los tipos de curso caracterizados son los siguientes: Inactivo, Con
         * entregas, Repositorio, Comunicativo, Evaluativo y Equilibrado.)
         * */
        $numstudents = $this->get_course_students_number($courseid);

        $algorith = new useofthecoursealgorithm($courseid, $numstudents);
        return $algorith->get_course_type();
    }
    /**
     * @return moodle_url
     * @throws dml_exception
     */
    public function get_logo_url() : string {
        global $OUTPUT, $CFG;
        $fs = get_file_storage();
        $context = context_system::instance();
        $filename = get_config('certifygenreport_basic', 'logo');
        $logo = $fs->get_file($context->id, self::REPORT_COMPONENT, self::REPORT_FILEAREA, 0,
            '/', $filename);
        if (!$logo) {
            return '';
        }
        $img_base64_encoded =  'data:image/png;base64, ' . base64_encode($logo->get_content());
        return  '@' . preg_replace('#^data:image/[^;]+;base64,#', '', $img_base64_encoded) . '">';
    }
}