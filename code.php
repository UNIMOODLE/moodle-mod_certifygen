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
 * Search for certificates view.
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_certifygen\forms\codeform;
use mod_certifygen\interfaces\icertificaterepository;
use mod_certifygen\plugininfo\certifygenrepository;

require_once('../../config.php');
global $PAGE, $OUTPUT;

$code = optional_param('code', '', PARAM_RAW);

require_login();
require_capability('mod/certifygen:manage', context_system::instance());
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/mod/certifygen/code.php');

$PAGE->set_title(get_string('codeview', 'certifygen'));
$codeform = new codeform();
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string(get_string('codeview', 'certifygen')));
$showresults = false;
if ($codeform->is_cancelled()) {
    redirect($PAGE->url); // Empty form.
} else if ($fromform = $codeform->get_data()) {
    $showresults = true;
    $codeform->set_data(['code' => $fromform->code]);
    $url = '';
    // Search for this code.
    /** @var certifygenrepository $plugin */
    foreach (core_plugin_manager::instance()->get_plugins_of_type('certifygenrepository') as $plugin) {
        $validationplugin = $plugin->component;
        $validationpluginclass = $validationplugin . '\\' . $validationplugin;
        /** @var icertificaterepository $subplugin */
        $subplugin = new $validationpluginclass();
        if ($subplugin->is_enabled()) {
            $url = $subplugin->get_file_by_code($fromform->code);
            if (!empty($url)) {
                break;
            }
        }
    }
    $message = get_string('codenotfound', 'mod_certifygen');
    if (!empty($url)) {
        $link = html_writer::link($url, $code);
        $message = get_string('codefound', 'mod_certifygen', $link);
    }
}
echo html_writer::start_div('mt-6');
$codeform->display();
echo html_writer::end_div();
if ($showresults) {
    echo html_writer::tag('h3', get_string('results', 'mod_certifygen'), ['class' => 'mt-6']);
    echo html_writer::div($message, 'm-3');
}
echo $OUTPUT->footer();
