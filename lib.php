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

// This line protects the file from being accessed by a URL directly.
use core\invalid_persistent_exception;
use core_user\output\myprofile\tree;
use mod_certifygen\persistents\certifygen;
use mod_certifygen\persistents\certifygen_context;
use mod_certifygen\persistents\certifygen_model;
use tool_certificate\permission;

defined('MOODLE_INTERNAL') || die();


/**
 * The features this activity supports.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return true|null True if module supports feature, null if doesn't know
 *@uses FEATURE_GROUPMEMBERSONLY
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 */
function certifygen_supports(string $feature): ?bool
{
    switch ($feature) {
        case FEATURE_GROUPINGS:
        case FEATURE_MOD_INTRO:
        case FEATURE_SHOW_DESCRIPTION:
        case FEATURE_COMPLETION_TRACKS_VIEWS:
        case FEATURE_BACKUP_MOODLE2:
        case FEATURE_GROUPS:
            return true;
        default:
            return null;
    }
}

/**
 * Add certifygen instance.
 * @param stdClass $data
 * @param mod_certifygen_mod_form $mform
 * @return int new certifygen instance id
 * @throws coding_exception
 * @throws invalid_persistent_exception
 */
function certifygen_add_instance(stdClass $data, mod_certifygen_mod_form $mform): int
{
    global $USER;

    $data->modelname = $data->name;
    // Create a model.
    $model = certifygen_model::save_model_object($data);

    // Create a certifygen.
    $certifygendata = [
        'course' => $data->course,
        'modelid' => $model->get('id'),
        'name' => $data->name,
        'intro' => $data->intro,
        'introformat' => $data->introformat,
        'usermodified' => $USER->id,
        'timecreated' => time(),
        'timemodified' => time(),
    ];

    $certifygen = new certifygen(0, (object)$certifygendata);
    $certifygen->create();

    return $certifygen->get('id');
}

/**
 * Update certifygen instance.
 *
 * @param $data
 * @param $mform
 * @return bool
 * @throws invalid_persistent_exception
 * @throws coding_exception
 */
function certifygen_update_instance($data, $mform): bool
{
    global $USER;

    // Update a model.
    $data->modelname = $data->name;
    certifygen_model::save_model_object($data);

    // Update a certifygen.
    $certifygen = new certifygen($data->instance);
    $certifygen->set('name', $data->name);
    $certifygen->set('intro', $data->intro);
    $certifygen->set('introformat', $data->introformat);
    $certifygen->set('usermodified', $USER->id);
    $certifygen->set('timemodified', time());
    return $certifygen->update();
}

/**
 * Delete certifygen instance.
 *
 * @param stdClass $data
 * @param mod_certifygen_mod_form $mform
 * @return bool Success/Fail
 * @throws coding_exception
 */
function certifygen_delete_instance(stdClass $data, mod_certifygen_mod_form $mform): bool
{

    // Delete a model.
    $model = new certifygen_model($data->modelid);
    $model->delete();

    // Delete a certifygen.
    $certifygen = new certifygen($data->certifygenid);

    return $certifygen->delete();
}

/**
 * Get certifygen model modes
 * @return array
 * @throws coding_exception
 */
function mod_certifygen_get_modes() : array {
    return [
        certifygen_model::MODE_UNIQUE => get_string('mode_1', 'mod_certifygen'),
        certifygen_model::MODE_PERIODIC => get_string('mode_2', 'mod_certifygen'),
    ];
}

/**
 * Get certifygen model validation types
 * @return array
 * @throws coding_exception
 * @throws dml_exception
 */
function mod_certifygen_get_validation() : array {

    $all[0] = get_string('selectvalidation', 'mod_certifygen');
    $enabled = [];
    foreach (core_plugin_manager::instance()->get_plugins_of_type('certifygenvalidation') as $plugin) {
        $enable = (int) get_config($plugin->component, 'enable');
        if ($enable) {
            $enabled[$plugin->component] = get_string('pluginname', $plugin->component);
            $all[$plugin->component] = get_string('pluginname', $plugin->component);
        }
    }
    if (empty($enabled)) {
        return [];
    }

    return $all;
}

/**
 * Get certifygen templates available by tool_certificate
 * @param int $courseid
 * @return array
 * @throws dml_exception
 */
function mod_certifygen_get_templates(int $courseid = 0) : array {
    $context = context_system::instance();
    if ($courseid > 0) {
        $context = context_course::instance($courseid);
    }

    $templates = [];
    if (!empty($records = permission::get_visible_templates($context))) {
        foreach ($records as $record) {
            $templates[$record->id] = format_string($record->name);
        }
    }
    return $templates;
}

/**
 * This function extends the course navigation with MYUA Configuration.
 *
 * @param navigation_node $navigation
 * @param stdClass $course
 * @param context_course $context
 * @throws coding_exception|moodle_exception
 */
function mod_certifygen_extend_navigation_course(navigation_node $navigation, stdClass $course, context_course $context) {

    global $USER;
    // Only for teachers (capability managegroups).
    $enrolledids = get_enrolled_users($context, 'moodle/course:managegroups', 0, 'u.id');
    if (!empty($enrolledids)) {
        $enrolledids = array_keys($enrolledids);
    }
    if (!in_array($USER->id, $enrolledids)) {
        return;
    }
    if (certifygen_context::has_course_context($course->id)) {
        $label = get_string('contextcertificatelink', 'mod_certifygen');
        $url = new moodle_url('/mod/certifygen/courselink.php', array('id' => $course->id));
        $icon = new pix_icon('t/edit', $label);
        $navigation->add($label, $url, navigation_node::TYPE_COURSE, null, null, $icon);
    }
}

/**
 * @param tree $tree
 * @param $user
 * @param $iscurrentuser
 * @param $course
 * @return void
 * @throws coding_exception
 */
function mod_certifygen_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {

    global $USER;
    if (permission::can_view_list($user->id)) {
        if ($USER->id == $user->id) {

            $coursedetailscategory = new core_user\output\myprofile\category('mycertifygens',
                get_string('pluginname', 'mod_certifygen'), 'coursedetails');
            $tree->add_category($coursedetailscategory);

            $link = get_string('mycertificates', 'mod_certifygen');
            $url = new moodle_url('/mod/certifygen/mycertificates.php');
            $node = new core_user\output\myprofile\node('mycertifygens', 'modcertifygenmy', $link, null, $url);
            $tree->add_node($node);
        }

    }
}