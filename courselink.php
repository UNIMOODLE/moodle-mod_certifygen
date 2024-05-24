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


use tool_certificate\template;

require_once('../../config.php');
require_once('lib.php');

global $CFG, $PAGE, $DB, $COURSE, $USER;

$courseid = required_param('id', PARAM_INT);    // Course ID.
$course = $DB->get_record('course', ['id' => $courseid]);
print_object("el curso es: ");
print_object($courseid);
print_object("El contexto de curso es: ");
print_object(context_course::instance($courseid)->id);
print_object("la categoria es: ");
print_object($course->category);
print_object("El contexto de categoria: ");
print_object(context_coursecat::instance($course->category)->id);

// Crear registros en bbdd.
//$datamodel = [
//    'type' => \mod_certifygen\persistents\certifygen_model::TYPE_COURSE_USED,
//    'mode' => \mod_certifygen\persistents\certifygen_model::MODE_UNIQUE,
//    'availability' => '{"op":"&","c":[],"showc":[]}',
//    'templateid' => 3,
//
//];
//$um = new \mod_certifygen\persistents\certifygen_model(0, (object) $datamodel);
//$um->create();
//
//$data = [
//    'contextid' => context_course::instance($courseid)->id,
//    'modelid' => $um->get('id'),
//    'type' => \mod_certifygen\persistents\certifygen_context::CONTEXT_TYPE_COURSE,
//    'usermodified' => $USER->id,
//
//];
//$uc = new \mod_certifygen\persistents\certifygen_context(0, (object) $data);
//$uc->create();

//$um = new \mod_certifygen\persistents\certifygen_model(2);
//// Crear certificado.
//$template = template::instance($um->get('templateid'));
//$issuedata = \mod_coursecertificate\helper::get_issue_data($course, $USER);
//$certificateid = $template->issue_certificate($USER->id, null, $issuedata, 'mod_certifygen', $courseid, null);
//print_object("certificateid: ");
//print_object($certificateid);

// Ver certificado por pantalla.
redirect(\tool_certificate\template::view_url('0075193541AU')->out(false));