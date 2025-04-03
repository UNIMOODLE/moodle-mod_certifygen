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
 * All the steps to restore mod_certifygen are defined here.
 *
 * @package    mod_certifygen
 * @category    backup
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * CLass defines the structure step to restore one mod_certifygen activity.
 *
 * @package    mod_certifygen
 * @category    backup
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_certifygen_activity_structure_step extends restore_activity_structure_step {
    /**
     * Defines the structure to be restored.
     *
     * @return restore_path_element[].
     * @throws base_step_exception
     */
    protected function define_structure(): array {
        $paths = [];
        $paths[] = new restore_path_element('certifygen', '/activity/certifygen');
        $paths[] = new restore_path_element('ccmodel', '/activity/certifygen/ccmodels/ccmodel');
        $paths[] = new restore_path_element('model', '/activity/certifygen/ccmodels/ccmodel/models/model');

        // Check if we want the issues as well.
        if ($this->get_setting_value('userinfo')) {
            $paths[] = new restore_path_element(
                'cvalidation',
                '/activity/certifygen/cvalidations/cvalidation'
            );
            $paths[] = new restore_path_element(
                'cvalidationcsv',
                '/activity/certifygen/cvalidations/cvalidation/cvalidationcsvs/cvalidationcsv'
            );
            $paths[] = new restore_path_element(
                'crepository',
                '/activity/certifygen/cvalidations/cvalidation/crepositorys/crepository'
            );
            $paths[] = new restore_path_element(
                'cerror',
                '/activity/certifygen/cvalidations/cvalidation/cerrors/cerror'
            );
            $paths[] = new restore_path_element(
                'tool_certificate_issue',
                '/activity/certifygen/cvalidations/cvalidation/issues/issue'
            );
        }

        return $this->prepare_activity_structure($paths);
    }

    /**
     * Processes the element restore data.
     *
     * @param array $data Parsed element data.
     * @throws base_step_exception
     * @throws dml_exception
     */
    protected function process_certifygen(array $data): void {
        global $DB;
        $data = (object) $data;
        $this->oldcourseid = $data->course;
        $data->course = $this->get_courseid();
        $this->activityname = $data->name;
        if (!isset($data->completiondownload)) {
            $data->completiondownload = 0;
        }
        // Insert the record.
        $newitemid = $DB->insert_record('certifygen', $data);
        $this->newcertifygenid = $newitemid;
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Processes the element restore data.
     *
     * @param array $data Parsed element data.
     * @throws dml_exception
     */
    protected function process_ccmodel(array $data): void {
        global $DB;
        $data = (object) $data;
        $data->certifygenid = $this->get_new_parentid('certifygen');
        // Insert the record.
        $DB->insert_record('certifygen_cmodels', $data);
    }

    /**
     * Processes the element restore data.
     *
     * @param array $data Parsed element data.
     * @throws base_step_exception
     * @throws dml_exception
     * @throws moodle_exception
     * @throws restore_step_exception
     */
    protected function process_model(array $data): void {
        global $DB;

        $updatemodelid = false;
        // Validate if the restore can be done.
        // Model idnumber must exists.
        $params = [
            'idnumber' => $data['idnumber'],
        ];
        $comparename = $DB->sql_compare_text('cm.idnumber');
        $comparenameplaceholder = $DB->sql_compare_text(':idnumber');
        $comparenamecondition = "{$comparename} = {$comparenameplaceholder}";

        $sql = "SELECT *
                  FROM {certifygen_model} cm
                 WHERE {$comparenamecondition}";
        $existingmodel = $DB->get_record_sql($sql, $params);
        $newmodelid = $data['id'];
        if (!$existingmodel) {
            $DB->delete_records('certifygen_cmodels', ['certifygenid' => $this->get_new_parentid('certifygen')]);
            $DB->delete_records('certifygen', ['id' => $this->get_new_parentid('certifygen')]);
            $params = ['activityname' => $this->activityname, 'idnumber' => $data['idnumber']];
            throw new moodle_exception(
                'model_must_exists',
                'mod_certifygen',
                (new moodle_url('/'))->out(),
                (object) $params
            );
        } else if ($existingmodel && $existingmodel->id != $data['id']) {
            // Hay que modificar el modelid en certifygen y certifygen_cmodels.
            $updatemodelid = true;
            $newmodelid = $existingmodel->id;
        }

        // Validate if the restore can be done.
        // New course id must be on model context.
        $modelids = \mod_certifygen\persistents\certifygen_context::get_course_valid_modelids($this->get_courseid());
        if (!in_array($newmodelid, $modelids)) {
            // Remove db inserts.
            $DB->delete_records('certifygen_cmodels', ['certifygenid' => $this->get_new_parentid('certifygen')]);
            $DB->delete_records('certifygen', ['id' => $this->get_new_parentid('certifygen')]);
            $params = ['courseid' => $this->get_courseid(), 'activityname' => $this->activityname,
                    'name' => $data['name'], 'idnumber' => $data['idnumber']];
            throw new moodle_exception(
                'course_not_valid_for_modelid',
                'mod_certifygen',
                (new moodle_url('/'))->out(),
                (object) $params
            );
        }
        if ($updatemodelid) {
            $certifygen = $DB->get_record('certifygen', ['id' => $this->newcertifygenid]);
            $certifygen->modelid = $newmodelid;
            $DB->update_record('certifygen', $certifygen);
            $ccmodel = $DB->get_record('certifygen_cmodels', ['certifygenid' => $this->newcertifygenid]);
            $ccmodel->modelid = $newmodelid;
            $DB->update_record('certifygen_cmodels', $ccmodel);
        }

        $this->set_mapping('modelid', $data['id'], $newmodelid);
    }

    /**
     * Processes the element restore data.
     *
     * @param array $data Parsed element data.
     * @throws base_step_exception
     * @throws coding_exception
     * @throws dml_exception
     * @throws file_exception
     * @throws restore_step_exception
     * @throws stored_file_creation_exception
     */
    protected function process_cvalidation(array $data): void {
        global $DB, $USER;

        $data['certifygenid'] = $this->get_new_parentid('certifygen');
        $data['modelid'] = $this->get_mappingid('modelid', $data['modelid']);
        $oldid = $data['id'];
        unset($data['id']);
        $data['usermodified'] = $USER->id;
        $data['timecreated'] = time();
        $data['timemodified'] = time();
        $newvalidationid = $DB->insert_record('certifygen_validations', (object)$data);
        $this->set_mapping('validationid', $oldid, $newvalidationid, true, $this->task->get_old_contextid());
        $this->oldvalidationid = $oldid;
        $this->newvalidationid = $newvalidationid;

        // Files.
        $fs = get_file_storage();
        // Files from mod_certifygen.
        $validationsfiles = $fs->get_area_files(
            context_course::instance($this->oldcourseid)->id,
            'mod_certifygen',
            'certifygenvalidationvalidated',
            $oldid
        );
        foreach ($validationsfiles as $file) {
            if ($file->get_filename() == '.') {
                continue;
            }
            $filerecord = [
                    'contextid' => context_course::instance($this->get_courseid())->id,
                    'component' => $file->get_component(),
                    'filearea' => $file->get_filearea(),
                    'itemid' => $newvalidationid,
                    'filepath' => $file->get_filepath(),
                    'filename' => $file->get_filename(),
            ];
            $fs->create_file_from_storedfile($filerecord, $file);
        }
        // Files from mod_certifygen.
        $repositoryfiles = $fs->get_area_files(
            context_course::instance($this->oldcourseid)->id,
            'mod_certifygen',
            'certifygenrepository',
            $oldid
        );
        foreach ($repositoryfiles as $file) {
            if ($file->get_filename() == '.') {
                continue;
            }
            $filerecord = [
                    'contextid' => context_course::instance($this->get_courseid())->id,
                    'component' => $file->get_component(),
                    'filearea' => $file->get_filearea(),
                    'itemid' => $newvalidationid,
                    'filepath' => $file->get_filepath(),
                    'filename' => $file->get_filename(),
            ];
            $fs->create_file_from_storedfile($filerecord, $file);
        }
    }

    /**
     * Handles restoring a tool_certificate issue.
     *
     * @param array $data Parsed element data.
     * @throws base_step_exception
     * @throws coding_exception
     * @throws ddl_exception
     * @throws dml_exception
     * @throws file_exception
     * @throws restore_step_exception
     * @throws stored_file_creation_exception
     */
    protected function process_tool_certificate_issue(array $data) {
        global $DB;

        if (!$DB->get_manager()->table_exists('tool_certificate_issues')) {
            throw new \dml_exception('tool_certificate_issues table does not exists');
        }
        if (!$DB->get_manager()->table_exists('tool_certificate_templates')) {
            throw new \dml_exception('tool_certificate_templates table does not exists');
        }
        // Check issueid - oldvalidationid.
        $userid = $DB->get_field(
            'certifygen_validations',
            'userid',
            [
                'id' => $this->newvalidationid,
            ]
        );
        if ($userid != $data['userid']) {
            return;
        }
        $data = (object) $data;

        $codefound = $DB->record_exists('tool_certificate_issues', ['code' => $data->code]);
        $templatefound = $DB->record_exists('tool_certificate_templates', ['id' => $data->templateid]);

        // For now, we only restore issues if is same site, template exists and same issue code does not exist.
        if ($this->task->is_samesite() && $templatefound && !$codefound) {
            $oldid = $data->id;
            $data->courseid = $this->get_courseid();
            $data->userid = $this->get_mappingid('user', $data->userid);
            $newitemid = $DB->insert_record('tool_certificate_issues', $data);
            $this->set_mapping('tool_certificate_issue', $oldid, $newitemid, true, $this->task->get_old_system_contextid());

            // Update issueid in certifygen_validations table.
            $validation = $DB->get_record(
                'certifygen_validations',
                [
                    'certifygenid' => $this->get_new_parentid('certifygen'),
                    'issueid' => $oldid,
                    'userid' => $data->userid,
                ]
            );
            $validation->issueid = $newitemid;
            $DB->insert_record('certifygen_validations', (object)$validation);

            // Files from tool_certificate.
            $fs = get_file_storage();
            $issuesfiles = $fs->get_area_files(
                $this->task->get_old_system_contextid(),
                'mod_certifygen',
                'issues',
                $oldid
            );
            foreach ($issuesfiles as $file) {
                if ($file->get_filename() == '.') {
                    continue;
                }
                $filerecord = [
                        'contextid' => context_system::instance()->id,
                        'component' => $file->get_component(),
                        'filearea' => $file->get_filearea(),
                        'itemid' => $newitemid,
                        'filepath' => $file->get_filepath(),
                        'filename' => $file->get_filename(),
                ];
                $fs->create_file_from_storedfile($filerecord, $file);
            }
        }
    }

    /**
     * Handles restoring a tool_certificate issue.
     *
     * @param array $data Parsed element data.
     * @throws dml_exception
     */
    protected function process_cerror(array $data) {
        global $DB;
        if ($data['validationid'] != $this->oldvalidationid) {
            return;
        }
        $data['validationid'] = $this->newvalidationid;
        unset($data['id']);

        $DB->insert_record('certifygen_error', (object)$data);
    }

    /**
     * Handles restoring a tool_certificate issue.
     *
     * @param array $data Parsed element data.
     * @throws dml_exception
     */
    protected function process_crepository(array $data) {
        global $DB;
        if ($data['validationid'] != $this->oldvalidationid) {
            return;
        }
        $data['validationid'] = $this->newvalidationid;
        unset($data['id']);
        $DB->insert_record('certifygen_repository', (object)$data);
    }

    /**
     * Handles restoring a tool_certificate issue.
     *
     * @param array $data Parsed element data.
     * @throws dml_exception
     */
    protected function process_cvalidationcsv(array $data) {
        global $DB;
        if ($data['validationid'] != $this->oldvalidationid) {
            return;
        }
        $data['validationid'] = $this->newvalidationid;
        unset($data['id']);
        $DB->insert_record('certifygen_validationcsv', (object)$data);
    }

    /**
     * Defines post-execution actions.
     */
    protected function after_execute(): void {

        $this->add_related_files('mod_certifygen', 'intro', null);
    }
}
