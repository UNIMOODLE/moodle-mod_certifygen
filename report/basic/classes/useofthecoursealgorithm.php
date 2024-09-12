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
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 *
 * @package    certifygenreport_basic
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace certifygenreport_basic;

use dml_exception;
use stdClass;
/**
 * useofthecoursealgorithm
 * @package    certifygenreport_basic
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class useofthecoursealgorithm {

    /** @var int L1 */
    private const  L1 = 0;
    /** @var int L2 */
    private const  L2 = 2;
    /** @var int M1 */
    private const  M1 = 4;
    /** @var int M2 */
    private const  M2 = 6;
    /** @var int H1 */
    private const  H1 = 8;
    /** @var int H2 */
    private const  H2 = 10;
    /** @var int LOW */
    private const LOW = 'L';
    /** @var int MEDIUM */
    private const  MEDIUM = 'M';
    /** @var int HIGH */
    private const  HIGH = 'H';
    /** @var int $numstudents */
    private int $numstudents;
    /** @var int $courseid */
    private int $courseid;
    /** @var int $course */
    private stdClass $course;
    /** @var int $resources */
    private int $resources;
    /** @var int $resourceslevel */
    private string $resourceslevel;
    /** @var int $resourceviews */
    private array $resourceviews;
    /** @var int $resourceviewslevel */
    private string $resourceviewslevel;
    /** @var int $forumnews */
    private int $forumnews;
    /** @var int $forumnewslevel */
    private string $forumnewslevel;
    /** @var int $forums */
    private int $forums;
    /** @var int $forumslevel */
    private string $forumslevel;
    /** @var int $foruminteractions */
    private int $foruminteractions;
    /** @var int $foruminteractionslevel */
    private string $foruminteractionslevel;
    /** @var int $assigns */
    private int $assigns;
    /** @var int $assignslevel */
    private string $assignslevel;
    /** @var int $assignsubmissions */
    private array $assignsubmissions;
    /** @var int $assignsubmissionslevel */
    private string $assignsubmissionslevel;
    /** @var int $gradeitems */
    private int $gradeitems;
    /** @var int $gradeitemslevel */
    private string $gradeitemslevel;
    /** @var int $gradefeedback */
    private int $gradefeedback;
    /** @var int $gradefeedbacklevel */
    private string $gradefeedbacklevel;

    /**
     * Construct
     * @param int $courseid
     * @param int $numstudents
     * @throws dml_exception
     * @throws \moodle_exception
     */
    public function __construct(int $courseid, int $numstudents) {
        $this->courseid = $courseid;
        $this->numstudents = $numstudents;
        $this->course = get_course($courseid);
        if ($this->numstudents == 0) {
            debugging(__CLASS__ . '  courseid '  . $courseid);
            debugging(__CLASS__ . '  numstudents '  . $numstudents);
            throw new \moodle_exception('cannotusealgorith_nostudents', 'certifygenreport_basic');
        }
    }
    /**
     * get_course_info
     * @return void
     * @throws dml_exception
     */
    private function get_course_info(): void {
        $this->get_resources();
        $this->get_resource_views();
        $this->get_forums();
        $this->get_forum_views();
        $this->get_forum_interactions();
        $this->get_assigns();
        $this->get_assigns_submissions();
        $this->get_grade_items();
        $this->get_grade_feedback();
    }
    /**
     * get_course_type
     * @return string
     * @throws dml_exception
     */
    public function get_course_type(): string {
        $this->get_course_info();

        if (
            ($this->assignsubmissionslevel == self::LOW && ($this->resourceslevel == self::MEDIUM
                    || $this->resourceslevel == self::HIGH) && $this->resourceviewslevel == self::LOW
                && $this->foruminteractionslevel == self::LOW)
            ||
            ($this->assignsubmissionslevel == self::LOW && $this->resourceslevel == self::LOW
                && ($this->foruminteractionslevel == self::MEDIUM || $this->foruminteractionslevel == self::HIGH)
                && $this->forumnewslevel == self::LOW)
            ||
            ($this->assignsubmissionslevel == self::LOW && $this->resourceslevel == self::LOW
                && $this->foruminteractionslevel == self::LOW)
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
     * get_item_level
     * @param int $item
     * @return string
     */
    private function get_item_level(int $item): string {
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
     * get_resources
     * @return void
     */
    private function get_resources(): void {
        global $DB;
        try {
            $sql = "SELECT COUNT(*)";
            $sql .= " FROM {course_modules} cm";
            $sql .= " LEFT JOIN {modules} m ON m.id = cm.module";
            $sql .= " WHERE cm.course = :courseid";
            $sql .= " AND m.name IN ('resource','url','label','page', 'book', 'folder','glossary','scorm', 'data')";
            $sql .= " AND cm.visible=1";
            $this->resources = $DB->count_records_sql($sql, ['courseid' => $this->courseid]);
            $this->resourceslevel = $this->get_item_level($this->resources);
        } catch (\moodle_exception $e) {
            debugging(__FUNCTION__ . ' e: ' . $e->getMessage());
            $this->resources = 0;
            $this->resourceslevel = self::LOW;
        }
    }

    /**
     * get_resource_views
     * @return void
     */
    private function get_resource_views(): void {
        global $DB;
        try {
            $sql = "SELECT logs.userid, count(logs.id) views";
            $sql .= " FROM {logstore_standard_log} logs ";
            $sql .= " WHERE logs.action='viewed'";
            $sql .= " AND logs.component IN ('mod_resource','mod_url','mod_label','mod_page', 'mod_book', 'mod_folder',";
            $sql .= " 'mod_glossary','mod_scorm','mod_data')";
            $sql .= " AND logs.timecreated > :startdate";
            $sql .= " AND logs.timecreated < :enddate";
            $sql .= " AND logs.courseid = :courseid";
            $sql .= " GROUP BY logs.userid,logs.courseid";
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
            debugging(__FUNCTION__ . ' e: ' . $e->getMessage());
            $this->resourceviews = [];
            $this->resourceviewslevel = self::LOW;
        }
    }


    /**
     * get_forums
     * @return void
     */
    private function get_forums(): void {
        global $DB;
        try {
            $sql = "SELECT COUNT(*)";
            $sql .= " FROM {course_modules} cm";
            $sql .= " LEFT JOIN {modules} m ON m.id = cm.module";
            $sql .= " WHERE cm.course = :courseid";
            $sql .= " AND m.name IN ('forum') AND cm.visible=1";
            $this->forums = $DB->count_records_sql($sql, ['courseid' => $this->courseid]);
            $this->forumslevel = $this->get_item_level($this->forums);
        } catch (\moodle_exception $e) {
            debugging(__FUNCTION__ . ' e: ' . $e->getMessage());
            $this->forums = 0;
            $this->forumslevel = self::LOW;
        }
    }
    /**
     * get_forum_views
     * @return void
     */
    private function get_forum_views(): void {
        global $DB;
        try {
            $sql = "SELECT COUNT(*)";
            $sql .= " FROM {course_modules} cm";
            $sql .= " LEFT JOIN {modules} m ON m.id = cm.module";
            $sql .= " WHERE cm.course = :courseid";
            $sql .= " AND m.name IN ('forum') AND cm.visible=1";
            $sql .= " AND cm.instance IN (SELECT f.id";
            $sql .= " FROM {forum} f WHERE f.type = 'news' AND f.course = :courseid2)";
            $this->forumnews = $DB->count_records_sql($sql, ['courseid' => $this->courseid,
                'courseid2' => $this->courseid]);
            $this->forumnewslevel = $this->get_item_level($this->forumnews);
        } catch (\moodle_exception $e) {
            debugging(__FUNCTION__ . ' e: ' . $e->getMessage());
            $this->forumnews = 0;
            $this->forumnewslevel = self::LOW;
        }
    }
    /**
     * get_forum_interactions
     * @return void
     */
    private function get_forum_interactions(): void {
        global $DB;
        try {
            $sql = "SELECT count(slogs.id) total";
            $sql .= " FROM  {logstore_standard_log} as slogs";
            $sql .= " WHERE slogs.component ='mod_forum' ";
            $sql .= " AND slogs.action='viewed' ";
            $sql .= " AND slogs.target='discussion'";
            $sql .= " AND slogs.timecreated > :startdate";
            $sql .= " AND slogs.timecreated < :enddate";
            $sql .= " AND slogs.courseid = :courseid";
            $sql .= " GROUP BY slogs.courseid";
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
            debugging(__FUNCTION__ . ' e: ' . $e->getMessage());
            $this->foruminteractions = [];
            $this->foruminteractionslevel = self::LOW;
        }
    }
    /**
     * get_assigns
     * @return void
     */
    private function get_assigns(): void {
        global $DB;
        try {
            $sql = "SELECT count(*) media ";
            $sql .= " FROM {course_modules} cm ";
            $sql .= " INNER JOIN {modules} m ON m.id = cm.module";
            $sql .= " WHERE m.name = 'assign'";
            $sql .= " AND cm.visible=1 ";
            $sql .= " AND cm.course = :courseid";
            $sql .= " GROUP BY cm.course";
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
            debugging(__FUNCTION__ . ' e: ' . $e->getMessage());
            $this->assigns = [];
            $this->assignslevel = self::LOW;
        }
    }
    /**
     * get_assigns_submissions
     * @return void
     */
    private function get_assigns_submissions(): void {
        global $DB;
        try {
            $sql = "SELECT assign_subm.userid userid, COUNT(assign_subm.id) total";
            $sql .= " FROM {assign_submission} assign_subm";
            $sql .= " LEFT JOIN {assign} assign  ON (assign.id = assign_subm.assignment)";
            $sql .= " WHERE assign_subm.timecreated > :startdate";
            $sql .= " AND assign.course = :courseid";
            $sql .= " GROUP BY assign.course, assign_subm.userid";
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
            debugging(__FUNCTION__ . ' e: ' . $e->getMessage());
            $this->assignsubmissions = [];
            $this->assignsubmissionslevel = self::LOW;
        }
    }
    /**
     * get_grade_items
     * @return void
     */
    private function get_grade_items(): void {
        global $DB;
        try {
            $sql = "SELECT count(gradeitems.id) total";
            $sql .= " FROM {grade_items} gradeitems";
            $sql .= " WHERE gradeitems.hidden=0 ";
            $sql .= " AND gradeitems.itemtype in ('manual', 'mod')";
            $sql .= " AND gradeitems.courseid = :courseid";
            $sql .= " GROUP BY gradeitems.courseid";
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
            debugging(__FUNCTION__ . ' e: ' . $e->getMessage());
            $this->gradeitems = [];
            $this->gradeitemslevel = self::LOW;
        }
    }
    /**
     * get_grade_feedback
     * @return void
     */
    private function get_grade_feedback(): void {
        global $DB;
        try {
            $sql = "SELECT count(distinct(items.id))";
            $sql .= " FROM {grade_grades} grades";
            $sql .= " RIGHT JOIN {grade_items} items on (items.id = grades.itemid)";
            $sql .= " WHERE grades.hidden=0";
            $sql .= " AND itemtype in ('manual', 'mod')";
            $sql .= " AND feedback is not NULL";
            $sql .= " AND items.courseid = :courseid";
            $sql .= " GROUP BY courseid";
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
            debugging(__FUNCTION__ . ' e: ' . $e->getMessage());
            $this->gradefeedback = 0;
            $this->gradefeedbacklevel = self::LOW;
        }
    }
}
