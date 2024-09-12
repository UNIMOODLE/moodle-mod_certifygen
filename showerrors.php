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
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_certifygen\output\views\showerrors_view;

require_once('../../config.php');
require_once('lib.php');
global $PAGE, $OUTPUT;

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
require_capability('mod/certifygen:manage', $context);

$PAGE->set_url('/mod/certifygen/modelmanager.php');
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_title(get_string('certifygenerrors', 'certifygen'));
$view = new showerrors_view();
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string(get_string('certifygenerrors', 'certifygen')));
$output = $PAGE->get_renderer('mod_certifygen');
echo $output->render($view);
echo $OUTPUT->footer();
