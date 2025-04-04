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
 * File lib.php
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\invalid_persistent_exception;
use core_user\output\myprofile\tree;
use mod_certifygen\forms\certificatestablefiltersform;
use mod_certifygen\interfaces\ICertificateReport;
use mod_certifygen\interfaces\ICertificateRepository;
use mod_certifygen\interfaces\ICertificateValidation;
use mod_certifygen\persistents\certifygen;
use mod_certifygen\persistents\certifygen_context;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_repository;
use mod_certifygen\persistents\certifygen_validations;
use tool_certificate\permission;

/**
 * The features this activity supports.
 * @param string $feature
 * @return bool|null
 */
function certifygen_supports(string $feature): ?bool {
    switch ($feature) {
        case FEATURE_BACKUP_MOODLE2:
        case FEATURE_MOD_INTRO:
        case FEATURE_SHOW_DESCRIPTION:
        case FEATURE_COMPLETION_TRACKS_VIEWS:
        case FEATURE_COMPLETION_HAS_RULES:
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
function certifygen_add_instance(stdClass $data, $mform = null): int {
    global $USER, $DB;

    $data->modelname = $data->name;

    // Create a certifygen.
    $certifygendata = [
        'course' => $data->course,
        'name' => $data->name,
        'intro' => $data->intro,
        'introformat' => $data->introformat,
        'completiondownload' => $data->completiondownload,
        'usermodified' => $USER->id,
        'timecreated' => time(),
        'timemodified' => time(),
    ];

    $certifygen = new certifygen(0, (object)$certifygendata);
    $certifygen->create();

    $DB->insert_record('certifygen_cmodels', (object)['modelid' => $data->modelid, 'certifygenid' => $certifygen->get('id')]);

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
function certifygen_update_instance($data, $mform): bool {
    global $USER, $DB;

    // Update a certifygen.
    $certifygen = new certifygen($data->instance);
    $certifygen->set('name', $data->name);
    $certifygen->set('intro', $data->intro);
    $certifygen->set('introformat', $data->introformat);
    $certifygen->set('completiondownload', $data->completiondownload);
    $certifygen->set('usermodified', $USER->id);
    $certifygen->set('timemodified', time());

    if ($cmodel = $DB->get_record('certifygen_cmodels', ['certifygenid' => $certifygen->get('id')])) {
        $cmodel->modelid = $data->modelid;
        $DB->update_record('certifygen_cmodels', $cmodel);
    }
    return $certifygen->update();
}

/**
 * Delete certifygen instance.
 * @param int $id
 * @return bool
 * @throws coding_exception
 */
function certifygen_delete_instance($id): bool {
    global $DB;
    // Delete a certifygen.
    $certifygen = new certifygen($id);

    // Table certifygen_cmodels.
    $DB->delete_records('certifygen_cmodels', ['certifygenid' => $id]);

    // Delete a certifygen_validations and certifygen_repository.
    $validations = certifygen_validations::get_records(['certifygenid' => $id]);
    foreach ($validations as $validation) {
        $repostoryrecords = certifygen_repository::get_records(['validationid' => $validation->get('id')]);
        foreach ($repostoryrecords as $repostoryrecord) {
            $repostoryrecord->delete();
        }
        $validation->delete();
    }
    return $certifygen->delete();
}

/**
 * Get certifygen model modes
 * @return array
 * @throws coding_exception
 */
function mod_certifygen_get_modes(): array {
    return [
        certifygen_model::MODE_UNIQUE => get_string('mode_1', 'mod_certifygen'),
        certifygen_model::MODE_PERIODIC => get_string('mode_2', 'mod_certifygen'),
    ];
}
/**
 * Get certifygen model types
 * @return array
 * @throws coding_exception
 */
function mod_certifygen_get_types(): array {
    return [
        certifygen_model::TYPE_ACTIVITY => get_string(
            'type_' . certifygen_model::TYPE_ACTIVITY,
            'mod_certifygen'
        ),
        certifygen_model::TYPE_TEACHER_ALL_COURSES_USED =>
            get_string('type_' . certifygen_model::TYPE_TEACHER_ALL_COURSES_USED, 'mod_certifygen'),
    ];
}
/**
 * Get certifygen context types
 * @return array
 * @throws coding_exception
 */
function mod_certifygen_get_context_types(): array {
    return [
        certifygen_context::CONTEXT_TYPE_COURSE => get_string('course'),
        certifygen_context::CONTEXT_TYPE_CATEGORY => get_string('category'),
        certifygen_context::CONTEXT_TYPE_SYSTEM => get_string('system', 'mod_certifygen'),
    ];
}
/**
 * mod_certifygen_get_activity_models
 * @param int $courseid
 * @return array
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 */
function mod_certifygen_get_activity_models(int $courseid): array {
    $models = certifygen_model::get_records(['type' => certifygen_model::TYPE_ACTIVITY]);
    $validmodels = certifygen_context::get_course_valid_modelids($courseid);
    $list = [];
    foreach ($models as $model) {
        try {
            // Check if template exists.
            \mod_certifygen\template::instance($model->get('templateid'));
        } catch (moodle_exception $exception) {
            continue;
        }

        if (in_array($model->get('id'), $validmodels)) {
            $list[$model->get('id')] = $model->get('name');
        }
    }
    return $list;
}

/**
 * Get certifygen model validation types
 * @return array
 * @throws coding_exception
 */
function mod_certifygen_get_validation(): array {

    $all = [];
    $enabled = [];
    foreach (core_plugin_manager::instance()->get_plugins_of_type('certifygenvalidation') as $plugin) {
        $validationplugin = $plugin->component;
        $validationpluginclass = $validationplugin . '\\' . $validationplugin;
        /** @var ICertificateValidation $subplugin */
        $subplugin = new $validationpluginclass();
        if ($subplugin->is_enabled()) {
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
 * Get certifygen model report types
 * @return array
 * @throws coding_exception
 */
function mod_certifygen_get_report(): array {

    $enabled = [];
    foreach (core_plugin_manager::instance()->get_plugins_of_type('certifygenreport') as $plugin) {
        $reportplugin = $plugin->component;
        $reportpluginclass = $reportplugin . '\\' . $reportplugin;
        /** @var ICertificateReport $subplugin */
        $subplugin = new $reportpluginclass();
        if ($subplugin->is_enabled()) {
            $enabled[$plugin->component] = get_string('pluginname', $plugin->component);
        }
    }
    return $enabled;
}
/**
 * Get certifygen model repository subplugins
 * @return array
 * @throws coding_exception
 */
function mod_certifygen_get_repositories(): array {

    $enabled = [];
    foreach (core_plugin_manager::instance()->get_plugins_of_type('certifygenrepository') as $plugin) {
        $reportplugin = $plugin->component;
        $reportpluginclass = $reportplugin . '\\' . $reportplugin;
        /** @var ICertificateRepository $subplugin */
        $subplugin = new $reportpluginclass();
        if ($subplugin->is_enabled()) {
            $enabled[$plugin->component] = get_string('pluginname', $plugin->component);
        }
    }

    return $enabled;
}

/**
 * Get certifygen templates available by tool_certificate
 * @param int $courseid
 * @return array
 * @throws dml_exception
 */
function mod_certifygen_get_templates(int $courseid = 0): array {
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
 * mod_certifygen_myprofile_navigation
 *
 * @param tree $tree
 * @param $user
 * @param $iscurrentuser
 * @param $course
 * @return void
 * @throws coding_exception|dml_exception
 */
function mod_certifygen_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course): void {

    global $USER;
    if (
        $USER->id == $user->id
        && certifygen_context::can_i_see_teacherrequestlink($user->id)
    ) {
            $link = get_string('mycertificates', 'mod_certifygen');
            $url = new moodle_url('/mod/certifygen/mycertificates.php');
            $node = new core_user\output\myprofile\node(
                'miscellaneous',
                'modcertifygenmy',
                $link,
                null,
                $url
            );
            $tree->add_node($node);
    }
}

/**
 * mod_certifygen_pluginfile
 * @param $course
 * @param $cm
 * @param $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return false|void
 * @throws coding_exception
 * @throws moodle_exception
 * @throws require_login_exception
 */
function mod_certifygen_pluginfile(
    $course,
    $cm,
    $context,
    string $filearea,
    array $args,
    bool $forcedownload,
    array $options
) {

    if ($context->contextlevel != CONTEXT_COURSE && $context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }

    // Make sure the filearea is one of those used by the plugin.
    if (
        $filearea !== ICertificateValidation::FILE_AREA &&
        $filearea !== ICertificateReport::FILE_AREA &&
        $filearea !== ICertificateRepository::FILE_AREA &&
        $filearea !== ICertificateValidation::FILE_AREA_VALIDATED &&
        $filearea !== 'issues'
    ) {
        return false;
    }

    // Make sure the user is logged in and has access to the module
    // (plugins that are not course modules should leave out the 'cm' part).
    require_login($course);

    // The args is an array containing [itemid, path].
    // Fetch the itemid from the path.
    $itemid = array_shift($args);

    // The itemid can be used to check access to a record, and ensure that the
    // record belongs to the specifeid context. For example.
    if ($filearea === ICertificateValidation::FILE_AREA) {
        $validation = new certifygen_validations($itemid);
        if (!$validation) {
            return false;
        }
    }

    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.
    if (empty($args)) {
        $filepath = '/';
    } else {
        $filepath = '/' . implode('/', $args) . '/';
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file(
        $context->id,
        ICertificateValidation::FILE_COMPONENT,
        $filearea,
        $itemid,
        $filepath,
        $filename
    );
    if (!$file) {
        // The file does not exist.
        return false;
    }
    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    send_stored_file($file, null, 0, $forcedownload, $options);
}

/**
 * mod_certifygen_get_lang_selected
 * @param certifygen_model $model
 * @return string
 * @throws coding_exception
 */
function mod_certifygen_get_lang_selected(certifygen_model $model): string {
    global $USER;
    $modellangs = $model->get_model_languages();
    $lang = '';
    foreach ($modellangs as $modellang) {
        if ($modellang == $USER->lang) {
            $lang = $modellang;
            break;
        }
        $lang = $modellang;
    }

    if (empty($lang)) {
        $a = new stdClass();
        $a->lang = $model->get('langs');
        throw new moodle_exception('lang_not_exists', 'mod_certifygen', '', $a);
    }
    return optional_param('lang', $lang, PARAM_RAW);
}
/**
 * mod_certifygen_get_certificates_table_form
 * @param certifygen_model $model
 * @param moodle_url $url
 * @param string $defaultlang
 * @param string $role
 * @return string
 * @throws coding_exception
 */
function mod_certifygen_get_certificates_table_form(
    certifygen_model $model,
    moodle_url $url,
    string $defaultlang = '',
    string $role = 'student'
): string {

    if (empty($defaultlang)) {
        $defaultlang = mod_certifygen_get_lang_selected($model);
    }
    $data = [
        'langs' => $model->get_model_languages(),
        'defaultlang' => $defaultlang,
        'role' => $role,
    ];
    $form = new certificatestablefiltersform($url->out(), $data);
    return $form->render();
}

/**
 * mod_certifygen_validate_user_parameters_for_ws
 * @param int $userid
 * @param string $userfield
 * @return array
 * @throws dml_exception
 */
function mod_certifygen_validate_user_parameters_for_ws(int $userid, string $userfield): array {
    global $DB;

    if (empty($userid) && empty($userfield)) {
        $results['error']['code'] = 'user_not_sent';
        $results['error']['message'] = get_string('user_not_sent', 'mod_certifygen');
        return $results;
    }
    if (!empty($userfield)) {
        $fieldid = get_config('mod_certifygen', 'userfield');
        if (empty($fieldid)) {
            $results['error']['code'] = 'userfield_not_selected';
            $results['error']['message'] = get_string('userfield_not_selected', 'mod_certifygen');
            return $results;
        }
        if ($fieldid === 'username') {
            $id = $DB->get_field('user', 'id', ['username' => $userfield]);
        } else if ($fieldid === 'email') {
            $id = $DB->get_field('user', 'id', ['email' => $userfield]);
        } else if ($fieldid === 'idnumber') {
            $id = $DB->get_field('user', 'id', ['idnumber' => $userfield]);
        } else if (substr($fieldid, 0, 8) === "profile_") {
            $fieldid = explode('_', $fieldid);
            $fieldid = $fieldid[1];
            $select = 'fieldid = :fieldid';
            $params = ['fieldid' => $fieldid, 'data' => $userfield];
            $comparename = $DB->sql_compare_text('data');
            $comparenameplaceholder = $DB->sql_compare_text(':data');
            $select .= "AND  {$comparename} = {$comparenameplaceholder}";
            $id = $DB->get_field_select('user_info_data', 'userid', $select, $params);
        } else {
            $results['error']['code'] = 'userfield_not_valid';
            $results['error']['message'] = get_string('userfield_not_valid', 'mod_certifygen');
            return $results;
        }
        if (!$id) {
            $results['error']['code'] = 'user_not_found';
            $results['error']['message'] = get_string('user_not_found', 'mod_certifygen');
            return $results;
        } else if (!empty($userid) && !empty($userfield) && $id != $userid) {
            $results['error']['code'] = 'userfield_and_userid_sent';
            $results['error']['message'] = get_string('userfield_and_userid_sent', 'mod_certifygen');
            return $results;
        } else {
            $results['userid'] = $id;
        }
    } else {
        $results['userid'] = $userid;
    }
    return $results;
}

/**
 * mod_certifygen_are_there_any_certificate_emited
 * @param int $modelid
 * @return bool
 * @throws coding_exception
 * @throws dml_exception
 */
function mod_certifygen_are_there_any_certificate_emited(int $modelid): bool {
    global $DB;

    if (!$modelid) {
        return false;
    }

    [$insql, $inparams] = $DB->get_in_or_equal(
        certifygen_validations::STATUS_NOT_STARTED,
        SQL_PARAMS_NAMED,
        'param',
        false
    );
    [$modelsql, $modelparams] = $DB->get_in_or_equal($modelid, SQL_PARAMS_NAMED, 'modelid');
    $params = array_merge($inparams, $modelparams);
    $inparams['modelid'] = $modelid;
    $select = " status  $insql";
    $select .= " AND modelid $modelsql";
    $num = certifygen_validations::count_records_select($select, $params);
    return $num > 0;
}

/**
 * mod_certifygen_are_there_any_certificate_emited_by_instanceid
 * @param int $certifygenid
 * @return bool
 * @throws coding_exception
 * @throws dml_exception
 */
function mod_certifygen_are_there_any_certificate_emited_by_instanceid(int $certifygenid): bool {
    global $DB;

    if (empty($certifygenid)) {
        return false;
    }

    [$insql, $inparams] = $DB->get_in_or_equal(
        certifygen_validations::STATUS_NOT_STARTED,
        SQL_PARAMS_NAMED,
        'param',
        false
    );
    [$modelsql, $modelparams] = $DB->get_in_or_equal($certifygenid, SQL_PARAMS_NAMED, 'certifygenid');
    $params = array_merge($inparams, $modelparams);
    $inparams['certifygenid'] = $certifygenid;
    $select = " status  $insql";
    $select .= " AND certifygenid $modelsql";
    $num = certifygen_validations::count_records_select($select, $params);

    return $num > 0;
}
/**
 * Checks if a language is installed
 * @param string $langcode
 * @return bool
 */
function mod_certifygen_lang_is_installed(string $langcode): bool {

    $installedlangs = get_string_manager()->get_list_of_translations(true);
    if (!array_key_exists($langcode, $installedlangs)) {
        return false;
    }
    return true;
}
/**
 * Add a get_coursemodule_info function in case any forum type wants to add 'extra' information
 * for the course (see resource).
 *
 * Given a course_module object, this function returns any "extra" information that may be needed
 * when printing this activity in a course listing.  See get_array_of_activities() in course/lib.php.
 *
 * @param stdClass $coursemodule The coursemodule object (record).
 * @return cached_cm_info An object on information that the courses
 *                        will know about (most noticeably, an icon).
 */
function certifygen_get_coursemodule_info($coursemodule) {
    $certifygen = certifygen::get_record(['id' => $coursemodule->instance]);
    if (!$certifygen) {
        return false;
    }
    $result = new cached_cm_info();
    $result->name = $certifygen->get('name');
    if ($coursemodule->completion == COMPLETION_TRACKING_AUTOMATIC) {
        $result->customdata['customcompletionrules']['completiondownload'] = $certifygen->get('completiondownload');
    }
    return $result;
}
/**
 * Obtains the automatic completion state for this certifygen based on any conditions
 * in certifygen settings.
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not, $type if conditions not set.
 */
//function certifygen_get_completion_state($course, $cm, $userid, $type) {
//
//    // Get certifygen details
//    $certifygen = certifygen::get_record(['id' => $cm->instance]);
//
//    // If completion option is enabled, evaluate it and return true/false
//    if ($certifygen->get('completiondownload')) {
//        $cvalidations = certifygen_validations::count_records(
//                [
//                    'userid' => $userid,
//                    'certifygenid' => $cm->instance,
//                    'isdownloaded' => 1,
//                ]
//        );
//        return ($cvalidations > 1) ? true : false;
//    } else {
//        // Completion option is not enabled so just return $type
//        return $type;
//    }
//}
/**
 * Callback which returns human-readable strings describing the active completion custom rules for the module instance.
 *
 * @param cm_info|stdClass $cm object with fields ->completion and ->customdata['customcompletionrules']
 * @return array $descriptions the array of descriptions for the custom rules.
 */
//function mod_certifygen_get_completion_active_rule_descriptions($cm) {
//    // Values will be present in cm_info, and we assume these are up to date.
//    if (empty($cm->customdata['customcompletionrules']) || $cm->completion != COMPLETION_TRACKING_AUTOMATIC) {
//        return [];
//    }
//
//    $descriptions = [];
//    foreach ($cm->customdata['customcompletionrules'] as $key => $val) {
//        switch ($key) {
//            case 'completiondownload':
//                if (!empty($val)) {
//                    $descriptions[] = get_string('completiondownloaddesc', 'certifygen');
//                }
//                break;
//            default:
//                break;
//        }
//    }
//    return $descriptions;
//}