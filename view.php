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

use mod_certifygen\event\course_module_viewed;
use mod_certifygen\output\views\activity_view;

require_once('../../config.php');
require_once('lib.php');

global $CFG, $PAGE, $DB, $COURSE, $USER;

$id = required_param('id', PARAM_INT);    // Course Module ID.

$cm = get_coursemodule_from_id('certifygen', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$certifygen = $DB->get_record('certifygen', ['id' => $cm->instance], '*', MUST_EXIST);
$cmodel = $DB->get_record('certifygen_cmodels', ['certifygenid' => $cm->instance], '*', MUST_EXIST);
$certifygenmodel = $DB->get_record('certifygen_model', ['id' => $cmodel->modelid], '*', MUST_EXIST);

$PAGE->set_url('/mod/certifygen/view.php', ['id' => $id]);
require_login($course, false, $cm);
$cmcontext = context_module::instance($cm->id);
require_capability('mod/certifygen:view', $cmcontext);

$PAGE->set_heading($course->fullname);
$PAGE->set_title(get_string('modulename', 'certifygen'));

$view = new activity_view($course->id, $certifygenmodel->templateid, $cm);

$event = course_module_viewed::create([
    'objectid' => $cm->instance,
    'context' => $cmcontext,
]);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('certifygen', $certifygen);
$event->trigger();

$output = $PAGE->get_renderer('mod_certifygen');
echo $output->header();
echo $output->render($view);
echo $output->footer();
