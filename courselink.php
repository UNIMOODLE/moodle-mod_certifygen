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

use mod_certifygen\certifygen;
use mod_certifygen\output\views\context_certificate_view;
use mod_certifygen\persistents\certifygen_context;
use mod_certifygen\persistents\certifygen_model;
use tool_certificate\template;

require_once('../../config.php');
require_once('lib.php');

global $CFG, $PAGE, $DB, $COURSE, $USER;

$courseid = required_param('id', PARAM_INT);    // Course ID.
$course = get_course($courseid);

// PASOS:
/**
 * 1- comprobar que existecontexto para este curso
 * 2 - comprobar q el usuario es profesor de este curso.
 * 3- comprobar que el modelo de este curso tenga validador chequeado
 *      2-1 si no lo tiene, descargar certificado del tool_certificate
 *      2-2 si lo tiene, mostrar tabla de estado
 */
require_login();
$hascertifycontext = certifygen_context::has_course_context($courseid);
if (!$hascertifycontext) {
    throw new moodle_exception('nocontextcourse', 'mod_certifygen');
}
$coursecontext = context_course::instance($courseid);
if (!has_capability('mod/certifygen:viewcontextcertificates', $coursecontext)) {
    throw new moodle_exception('hasnocapabilityrequired', 'mod_certifygen');
}
$PAGE->set_context($coursecontext);
$url = new moodle_url('/mod/certifygen/courselink.php', ['id' => $courseid]);
$PAGE->set_url($url);
$modelid = certifygen_context::get_course_context_modelid($courseid);
$certifygenmodel = new certifygen_model($modelid);

$view = new \mod_certifygen\output\views\mycertificates_view($certifygenmodel, $courseid, $url);
$output = $PAGE->get_renderer('mod_certifygen');

echo $output->header();
echo $output->heading(format_string($certifygenmodel->get('name')));
echo $output->render($view);
echo $output->footer();

