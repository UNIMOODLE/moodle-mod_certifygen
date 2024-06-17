<?php
// This file is part of the tool_certificate plugin for Moodle - http://moodle.org/
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
 * View issued certificate as pdf.

 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_certifygen\persistents\certifygen_validations;

require_once('../../config.php');
global $PAGE;

$issuecode = required_param('code', PARAM_TEXT);
$preview = optional_param('preview', false, PARAM_BOOL);

require_login();
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/mod/certifygen/certificateview.php', ['code' => $issuecode]));
$lang = certifygen_validations::get_lang_by_code($issuecode);
//$lang = explode('_', $issuecode);
if ($preview) {
    $templateid = required_param('templateid', PARAM_INT);
    $template = \mod_certifygen\template::instance($templateid, (object) ['lang' => $lang]);
    if ($template->can_manage()) {
        $template->generate_pdf(true);
    }
} else {
    $issue = \mod_certifygen\template::get_issue_from_code($issuecode);
    $context = \context_course::instance($issue->courseid, IGNORE_MISSING) ?: null;
    $template = $issue ? \mod_certifygen\template::instance($issue->templateid, (object) ['lang' => $lang]) : null;
    if ($template && (\tool_certificate\permission::can_verify() ||
            \tool_certificate\permission::can_view_issue($template, $issue, $context))) {
        $url = $template->get_issue_file_url($issue);

        redirect($url);
    } else {
        throw new moodle_exception('notfound');
    }
}
