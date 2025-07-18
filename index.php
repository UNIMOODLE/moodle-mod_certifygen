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
 * @author     IDEF21 idef21.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\url;

require_once("../../config.php");
global $DB, $PAGE, $OUTPUT, $USER;

// The `id` parameter is the course id.
$id = required_param('id', PARAM_INT);

$PAGE->set_url('/mod/certifygen/index.php', ['id' => $id]);

// Fetch the requested course.
$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);

// Require that the user is logged into the course.
require_course_login($course);
$PAGE->set_pagelayout('incourse');
$modinfo = get_fast_modinfo($course);
$strcertifygens = get_string("modulenameplural", "certifygen");
// Print the header.
$PAGE->navbar->add($strcertifygens);
$PAGE->set_title("$course->shortname: $strcertifygens");
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading($strcertifygens, 2);

\mod_certifygen\event\course_module_instance_list_viewed::create_from_course($course)->trigger();

if (! $certifygens = get_all_instances_in_course("certifygen", $course)) {
    notice(
        get_string('thereareno', 'moodle', $strcertifygens),
        "../../course/view.php?id=$course->id"
    );
    die;
}
$strname  = get_string("name");
$strcourse  = get_string("course");
$table = new html_table();
$table->head  = [$strname, $strcourse];
$table->align = ["left", "center"];

foreach ($certifygens as $instanceid => $certifygen) {
    [$course, $cm] = get_course_and_cm_from_instance($certifygen->id, 'certifygen');
    $context = context_module::instance($cm->id);
    $class = $certifygen->visible ? null : ['class' => 'dimmed']; // Hidden modules are dimmed.
    $link = html_writer::link(new url('view.php', ['id' => $cm->id]), format_string($certifygen->name), $class);
    $courselink = html_writer::link(
        new url('/course/view.php', ['id' => $course->id]),
        format_string($course->fullname),
        $class
    );
    $table->data[] = [$link, $courselink];
}
echo html_writer::table($table);
echo $OUTPUT->footer();
