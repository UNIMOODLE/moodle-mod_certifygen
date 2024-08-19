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

namespace certifygenreport_basic;

use stdClass;

class useofthecoursealgorith {

    private const  L1 = 0;
    private const  L2 = 2;
    private const  M1 = 4;
    private const  M2 = 6;
    private const  H1 = 8;
    private const  H2 = 10;
    private const LOW = 'L';
    private const  MEDIUM = 'M';
    private const  HIGH = 'H';
    private int $numstudents;
    private int $courseid;
    private stdClass $course;
    private int $resources;
    private string $resourceslevel;
    private array $resourceviews;
    private string $resourceviewslevel;
    private int $forumnews;
    private string $forumnewslevel;
    private int $forums;
    private string $forumslevel;
    private int $foruminteractions;
    private string $foruminteractionslevel;
    private int $assigns;
    private string $assignslevel;
    private array $assignsubmissions;
    private string $assignsubmissionslevel;
    private int $gradeitems;
    private string $gradeitemslevel;
    private int $gradefeedback;
    private string $gradefeedbacklevel;

    /**
     * @param int $courseid
     */
    public function __construct(int $courseid, int $numstudents)
    {
        $this->courseid = $courseid;
        $this->numstudents = $numstudents;
        $this->course = get_course($courseid);
        if ($this->numstudents == 0) {
            error_log(__CLASS__ . ' courseid: '.var_export($courseid, true));
            error_log(__CLASS__ . ' numstudents: '.var_export($numstudents, true));
            throw new \moodle_exception('cannotusealgorith_nostudents', 'certifygenreport_basic');
        }
    }
    /**
     * @return void
     * @throws \dml_exception
     */
    private function get_course_info() : void {
        $this->get_resources();
        $this->get_resource_views();
        $this->get_forums();
        $this->get_forum_views();
        $this->get_forum_interactions();
        $this->get_assigns();
        $this->get_assigns_submissions();
        $this->get_grade_items();
        $this->get_grade_feedback();
//        print_object('resources');
//        print_object($this->resources);
//        print_object('resourceviews');
//        print_object($this->resourceviews);
//        print_object('forums');
//        print_object($this->forums);
//        print_object('forumnews');
//        print_object($this->forumnews);
//        print_object('foruminteractions');
//        print_object($this->foruminteractions);
//        print_object('assigns');
//        print_object($this->assigns);
//        print_object('assignsubmissions');
//        print_object($this->assignsubmissions);
//        print_object('gradeitems');
//        print_object($this->gradeitems);
    }
    /**
     * @return string
     */
    public function get_course_type() : string {
        $this->get_course_info();

        if (
            ($this->assignsubmissionslevel == self::LOW && ($this->resourceslevel == self::MEDIUM || $this->resourceslevel == self::HIGH) && $this->resourceviewslevel == self::LOW && $this->foruminteractionslevel == self::LOW)
            ||
            ($this->assignsubmissionslevel == self::LOW && $this->resourceslevel == self::LOW && ($this->foruminteractionslevel == self::MEDIUM || $this->foruminteractionslevel == self::HIGH) && $this->forumnewslevel == self::LOW)
            ||
            ($this->assignsubmissionslevel == self::LOW && $this->resourceslevel == self::LOW && $this->foruminteractionslevel == self::LOW)
        ) {
            return 'Inactivo';
        } else if (
            $this->assignsubmissionslevel == self::MEDIUM
            && ($this->assignslevel === self::LOW || $this->assignslevel === self::MEDIUM)
            && ($this->forumnewslevel == self::LOW || $this->forumnewslevel == self::MEDIUM)
        ) {
            return 'Submission';
        } else if (
            $this->assignsubmissionslevel == self::LOW
            && ($this->resourceslevel == self::MEDIUM || $this->resourceslevel == self::HIGH)
            && ($this->resourceviewslevel == self::MEDIUM || $this->resourceviewslevel == self::HIGH)
            && $this->foruminteractionslevel == self::LOW
        ) {
            return 'Repository';
        } else if (
            ($this->assignsubmissionslevel == self::LOW
            && ($this->resourceslevel == self::MEDIUM || $this->resourceslevel == self::HIGH)
            && $this->foruminteractionslevel == self::MEDIUM
            )
            ||
            ($this->assignsubmissionslevel == self::LOW
                && $this->resourceslevel == self::LOW
                && ($this->foruminteractionslevel == self::MEDIUM || $this->foruminteractionslevel == self::HIGH)
                && ($this->forumnewslevel == self::MEDIUM || $this->forumnewslevel == self::HIGH)
            )
        ) {
            return 'Communicative';
        } else if (
            ($this->assignsubmissionslevel == self::HIGH
                &&
                ($this->assignslevel == self::LOW || $this->assignslevel == self::MEDIUM)
            )
            ||
            (
                ($this->assignsubmissionslevel == self::LOW || $this->assignsubmissionslevel == self::MEDIUM)
                && $this->assignslevel == self::HIGH
                && $this->foruminteractionslevel == self::LOW
            )
        ) {
            return 'Evaluative';
        } else {
            return 'Balanced';
        }
    }

    /**
     * @param int $item
     * @return int
     */
    private function get_item_level(int $item) : string {
        if ($item <= self::L2) {
            $level = self::LOW;
        } else if ($item <= self::M2) {
            $level = self::MEDIUM;
        } else {
            $level = self::HIGH;
        }
        return $level;
    }
    /**
     * @return void
     * @throws \dml_exception
     */
    private function get_resources() : void {
        global $DB;
        try {
            $sql = "SELECT COUNT(*)  
                    FROM {course_modules} cm
                    LEFT JOIN {modules} ON mdl_modules.id = cm.module
                    WHERE cm.course = :courseid
                    AND mdl_modules.name IN ('resource','url','label','page', 'book', 'folder','glossary','scorm', 'data')
                    AND cm.visible=1";

            $this->resources = $DB->count_records_sql($sql, ['courseid' => $this->courseid]);
            $this->resourceslevel = $this->get_item_level($this->resources);
        } catch (\moodle_exception $e) {
            error_log(__FUNCTION__ . ' error: '. $e->getMessage(), true);
            $this->resources = 0;
        }
    }

    /**
     * @return void
     * @throws \dml_exception
     */
    private function get_resource_views() : void {
        global $DB;
        try {
            $sql = "SELECT logs.userid, count(logs.id) views
            FROM {logstore_standard_log} logs 
            WHERE logs.action='viewed'
            AND logs.component IN ('mod_resource','mod_url','mod_label','mod_page', 'mod_book', 'mod_folder','mod_glossary','mod_scorm','mod_data')
            AND logs.timecreated>:startdate
            AND logs.timecreated<:enddate
            AND logs.courseid = :courseid
            GROUP BY logs.userid,logs.courseid";

            $params = [
                'courseid' => $this->courseid,
                'startdate' => $this->course->startdate,
                'enddate' => $this->course->enddate,
            ];
            $this->resourceviews = $DB->get_records_sql($sql, $params);
            $sum = 0;
            foreach ($this->resourceviews as $resourceview) {
                $sum += $resourceview->views;
            }
            $sum = $sum / $this->numstudents;
            $this->resourceviewslevel = $this->get_item_level($sum);
        } catch (\moodle_exception $e) {
            error_log(__FUNCTION__ . ' error: '. $e->getMessage(), true);
            $this->resourceviews = [];
            $this->resourceviewslevel = self::LOW;
        }
    }


    /**
     * @return void
     * @throws \dml_exception
     */
    private function get_forums() : void {
        global $DB;
        try {
            $sql = "SELECT COUNT(*)
                        FROM {course_modules} cm
                        LEFT JOIN {modules} m ON m.id = cm.module
                        WHERE cm.course = :courseid
                        AND m.name IN ('forum') AND cm.visible=1";
            $this->forums = $DB->count_records_sql($sql, ['courseid' => $this->courseid]);
            $this->forumslevel = $this->get_item_level($this->forums);
        } catch (\moodle_exception $e) {
            error_log(__FUNCTION__ . ' error: '. $e->getMessage(), true);
            $this->forums = 0;
            $this->forumslevel = self::LOW;
        }
    }
    /**
     * @return void
     * @throws \dml_exception
     */
    private function get_forum_views() : void {
        global $DB;
        try {
            $sql = "SELECT COUNT(*)
                        FROM {course_modules} cm
                        LEFT JOIN {modules} m ON m.id = cm.module
                        WHERE cm.course = :courseid
                        AND m.name IN ('forum') AND cm.visible=1
                        AND cm.instance IN (SELECT f.id 
                                FROM {forum} f WHERE f.type = 'news' AND f.course = :courseid2)";
            $this->forumnews = $DB->count_records_sql($sql, ['courseid' => $this->courseid, 'courseid2' => $this->courseid]);
            $this->forumnewslevel = $this->get_item_level($this->forumnews);
        } catch (\moodle_exception $e) {
            error_log(__FUNCTION__ . ' error: '. $e->getMessage(), true);
            $this->forumnews = 0;
            $this->forumnewslevel = self::LOW;
        }
    }
    /**
     * @return void
     * @throws \dml_exception
     */
    private function get_forum_interactions() : void {
        global $DB;
        try {
            $sql = "SELECT count(slogs.id) total
                FROM  {logstore_standard_log} as slogs
                WHERE slogs.component ='mod_forum' 
                AND slogs.action='viewed' 
                AND slogs.target='discussion'
                AND slogs.timecreated > :startdate
                AND slogs.timecreated < :enddate
                AND slogs.courseid = :courseid
                GROUP BY slogs.courseid";
            $params = [
                'courseid' => $this->courseid,
                'startdate' => $this->course->startdate,
                'enddate' => $this->course->enddate,
            ];
            $foruminteractions = $DB->get_records_sql($sql, $params);
            $this->foruminteractions = 0;
            if (array_key_exists('total', $foruminteractions)) {
                $this->foruminteractions = $foruminteractions->total;
            }
            $this->foruminteractionslevel = $this->get_item_level($this->foruminteractions);
        } catch (\moodle_exception $e) {
            error_log(__FUNCTION__ . ' error: '. $e->getMessage(), true);
            $this->foruminteractions = [];
            $this->foruminteractionslevel = self::LOW;
        }
    }
    /**
     * @return void
     * @throws \dml_exception
     */
    private function get_assigns() : void {
        global $DB;
        try {
            $sql = "SELECT count(*) media 
                        FROM {course_modules} cm 
                        INNER JOIN {modules} m ON m.id = cm.module
                        WHERE m.name = 'assign'
                        AND cm.visible=1 
                        AND cm.course = :courseid
                        GROUP BY cm.course";
            $params = [
                'courseid' => $this->courseid,
            ];
            $assigns = $DB->get_records_sql($sql, $params);
            $this->assigns = 0;
            if (array_key_exists('media', $assigns)) {
                $this->assigns = $assigns->media;
            }
            $this->assignslevel = $this->get_item_level($this->assigns);
        } catch (\moodle_exception $e) {
            error_log(__FUNCTION__ . ' error: '. $e->getMessage(), true);
            $this->assigns = [];
            $this->assignslevel = self::LOW;
        }
    }
    /**
     * @return void
     * @throws \dml_exception
     */
    private function get_assigns_submissions() : void {
        global $DB;
        try {
            $sql = "SELECT assign_subm.userid userid, COUNT(assign_subm.id) total
                        FROM {assign_submission} assign_subm
                        LEFT JOIN {assign} assign  ON (assign.id = assign_subm.assignment)
                        WHERE assign_subm.timecreated > :startdate
                        AND assign.course = :courseid
                        GROUP BY assign.course, assign_subm.userid";
            $params = [
                'courseid' => $this->courseid,
                'startdate' => $this->course->startdate,
            ];
            $this->assignsubmissions = $DB->get_records_sql($sql, $params);
            $sum = 0;
            foreach ($this->assignsubmissions as $assignsubmission) {
                $sum += $assignsubmission->total;
            }
            $sum = $sum / $this->numstudents;
            $this->assignsubmissionslevel = $this->get_item_level($sum);
        } catch (\moodle_exception $e) {
            error_log(__FUNCTION__ . ' error: '. $e->getMessage(), true);
            $this->assignsubmissions = [];
            $this->assignsubmissionslevel = self::LOW;
        }
    }
    /**
     * @return void
     * @throws \dml_exception
     */
    private function get_grade_items() : void {
        global $DB;
        try {
            $sql = "SELECT count(gradeitems.id) total
                        FROM {grade_items} gradeitems
                        WHERE gradeitems.hidden=0 
                        AND gradeitems.itemtype in ('manual', 'mod')
                        AND gradeitems.courseid = :courseid
                        GROUP BY gradeitems.courseid";
            $params = [
                'courseid' => $this->courseid,
            ];
            $gradeitems = $DB->get_records_sql($sql, $params);
            $this->gradeitems = 0;
            if (array_key_exists('total', $gradeitems)) {
                $this->gradeitems = $gradeitems->total;
            }
            $this->gradeitemslevel = $this->get_item_level($this->gradeitems);
        } catch (\moodle_exception $e) {
            error_log(__FUNCTION__ . ' error: '. $e->getMessage(), true);
            $this->gradeitems = [];
            $this->gradeitemslevel = self::LOW;
        }
    }
    /**
     * @return void
     * @throws \dml_exception
     */
    private function get_grade_feedback() : void {
        global $DB;
        try {
            $sql = "SELECT count(distinct(items.id))
                        FROM {grade_grades} grades
                        RIGHT JOIN {grade_items} items on (items.id = grades.itemid)
                        WHERE grades.hidden=0 
                        AND itemtype in ('manual', 'mod')
                        AND feedback is not NULL
                        AND items.courseid = :courseid
                        GROUP BY courseid";
            $params = [
                'courseid' => $this->courseid,
            ];
            $gradefeedback = $DB->get_records_sql($sql, $params);
            $this->gradefeedback = 0;
            if (array_key_exists('total', $gradefeedback)) {
                $this->gradefeedback = $gradefeedback->total;
            }
            $this->gradefeedbacklevel = $this->get_item_level($this->gradefeedback);
        } catch (\moodle_exception $e) {
            error_log(__FUNCTION__ . ' error: '. $e->getMessage(), true);
            $this->gradefeedback = 0;
            $this->gradefeedbacklevel = self::LOW;
        }
    }
}