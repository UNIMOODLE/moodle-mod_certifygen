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

use core\context\course;
use core\context\system;
use mod_certifygen\interfaces\icertificaterepository;
use mod_certifygen\interfaces\icertificatevalidation;
use mod_certifygen\template;

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
    /** @var int Course id the activity had in the backup. */
    protected $oldcourseid;
    /** @var string Name of the restored activity. */
    protected $activityname;
    /** @var int Id of the restored certifygen instance. */
    protected $newcertifygenid;
    /** @var int Id of the last validation processed, in the backup. */
    protected $oldvalidationid;
    /** @var int Id of the last validation processed, once restored. */
    protected $newvalidationid;
    /** @var int|null Certificate issue id the last validation processed had in the backup. */
    protected $oldissueid;
    /** @var int[] Ids of the restored validations whose certificate has been restored too. */
    protected $validationswithissue = [];

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
                (new url('/'))->out(),
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
                (new url('/'))->out(),
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
        $this->oldissueid = empty($data['issueid']) ? null : (int) $data['issueid'];

        // The files are copied from the original course, so they only exist when restoring on the same site.
        if (!$this->task->is_samesite()) {
            return;
        }

        // Files.
        $fs = get_file_storage();
        // Files from mod_certifygen.
        $validationsfiles = $fs->get_area_files(
            course::instance($this->oldcourseid)->id,
            'mod_certifygen',
            'certifygenvalidationvalidated',
            $oldid
        );
        foreach ($validationsfiles as $file) {
            if ($file->get_filename() == '.') {
                continue;
            }
            $filerecord = [
                    'contextid' => course::instance($this->get_courseid())->id,
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
            course::instance($this->oldcourseid)->id,
            'mod_certifygen',
            'certifygenrepository',
            $oldid
        );
        foreach ($repositoryfiles as $file) {
            if ($file->get_filename() == '.') {
                continue;
            }
            $filerecord = [
                    'contextid' => course::instance($this->get_courseid())->id,
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
     * Restores a tool_certificate issue as a copy that belongs to the restored activity.
     *
     * Issues are only restored on the same site: certificate templates are not part of the backup.
     * The issue code is unique site-wide, so when the original issue still exists the copy gets a new
     * code, and its PDF is regenerated so that the printed code and QR match it. That is only possible
     * when the model neither signs nor validates the PDF nor stores it outside Moodle.
     * Validations whose certificate is not restored are discarded in after_execute().
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
        // Every validation in the backup carries all the issues of the activity: keep only its own.
        if (empty($this->oldissueid) || (int) $data['id'] !== $this->oldissueid) {
            return;
        }
        $data = (object) $data;
        if (!$this->task->is_samesite() || !$DB->record_exists('tool_certificate_templates', ['id' => $data->templateid])) {
            return;
        }

        $oldid = $data->id;
        unset($data->id);
        $data->courseid = $this->get_courseid();
        $data->userid = $this->get_mappingid('user', $data->userid);
        $codetaken = $DB->record_exists('tool_certificate_issues', ['code' => $data->code]);
        // With the code free, the original PDFs are reused if they still exist (they don't once revoked).
        $regenerate = $codetaken || !$this->original_files_exist((int) $oldid);
        if ($regenerate && !$this->can_regenerate_certificate()) {
            return;
        }
        if ($codetaken) {
            $data->code = \tool_certificate\certificate::generate_code($data->userid);
        }
        $newitemid = $DB->insert_record('tool_certificate_issues', $data);
        $this->set_mapping('tool_certificate_issue', $oldid, $newitemid, true, $this->task->get_old_system_contextid());
        $DB->set_field('certifygen_validations', 'issueid', $newitemid, ['id' => $this->newvalidationid]);
        $this->validationswithissue[] = $this->newvalidationid;

        $fs = get_file_storage();
        if ($regenerate) {
            $issue = $DB->get_record('tool_certificate_issues', ['id' => $newitemid], '*', MUST_EXIST);
            $issuefile = template::instance($issue->templateid)->create_issue_file($issue, true);
            // The archived copy restored from the original course carries the original code: replace it.
            $coursecontextid = course::instance($this->get_courseid())->id;
            $fs->delete_area_files(
                $coursecontextid,
                icertificaterepository::FILE_COMPONENT,
                icertificaterepository::FILE_AREA,
                $this->newvalidationid
            );
            $fs->create_file_from_storedfile([
                'contextid' => $coursecontextid,
                'component' => icertificaterepository::FILE_COMPONENT,
                'filearea' => icertificaterepository::FILE_AREA,
                'itemid' => $this->newvalidationid,
                'filepath' => icertificaterepository::FILE_PATH,
                'filename' => $issue->code . '.pdf',
            ], $issuefile);
            return;
        }

        // Same code: the original PDF is still valid for the copy.
        $issuesfiles = $fs->get_area_files(system::instance()->id, 'mod_certifygen', 'issues', $oldid);
        foreach ($issuesfiles as $file) {
            if ($file->get_filename() == '.') {
                continue;
            }
            $fs->create_file_from_storedfile([
                'contextid' => system::instance()->id,
                'component' => $file->get_component(),
                'filearea' => $file->get_filearea(),
                'itemid' => $newitemid,
                'filepath' => $file->get_filepath(),
                'filename' => $file->get_filename(),
            ], $file);
        }
    }

    /**
     * Whether the PDFs of the original certificate are still there to be copied.
     *
     * @param int $oldissueid
     * @return bool
     */
    protected function original_files_exist(int $oldissueid): bool {
        $fs = get_file_storage();
        $issuefiles = $fs->get_area_files(system::instance()->id, 'mod_certifygen', 'issues', $oldissueid, 'id', false);
        $repositoryfiles = $fs->get_area_files(
            course::instance($this->get_courseid())->id,
            icertificaterepository::FILE_COMPONENT,
            icertificaterepository::FILE_AREA,
            $this->newvalidationid,
            'id',
            false
        );
        return !empty($issuefiles) && !empty($repositoryfiles);
    }

    /**
     * Whether the certificate of the validation being restored can be regenerated.
     *
     * A regenerated PDF is a plain one: it would lose the signature or seal added by any validation
     * other than "none", and it can only be archived again in the local repository.
     *
     * @return bool
     * @throws dml_exception
     */
    protected function can_regenerate_certificate(): bool {
        global $DB;
        $modelid = $DB->get_field('certifygen_validations', 'modelid', ['id' => $this->newvalidationid]);
        $model = $DB->get_record('certifygen_model', ['id' => $modelid], 'validation, repository');
        return $model
            && $model->validation === 'certifygenvalidation_none'
            && $model->repository === 'certifygenrepository_localrepository';
    }

    /**
     * Deletes a restored validation whose certificate could not be restored, with its records and files.
     *
     * Kept, it would point to the certificate of the original course, or to none on another site.
     *
     * @param int $validationid
     * @throws dml_exception
     */
    protected function discard_validation(int $validationid): void {
        global $DB;
        $fs = get_file_storage();
        $coursecontextid = course::instance($this->get_courseid())->id;
        $fileareas = [
            icertificatevalidation::FILE_AREA,
            icertificatevalidation::FILE_AREA_VALIDATED,
            icertificaterepository::FILE_AREA,
        ];
        foreach ($fileareas as $filearea) {
            $fs->delete_area_files($coursecontextid, 'mod_certifygen', $filearea, $validationid);
        }
        $DB->delete_records('certifygen_repository', ['validationid' => $validationid]);
        $DB->delete_records('certifygen_error', ['validationid' => $validationid]);
        $DB->delete_records('certifygen_validationcsv', ['validationid' => $validationid]);
        $DB->delete_records('certifygen_validations', ['id' => $validationid]);
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
        global $DB;

        $this->add_related_files('mod_certifygen', 'intro', null);

        // A validation that had a certificate keeps it only if the certificate was restored for this activity.
        $validations = $DB->get_records_select(
            'certifygen_validations',
            'certifygenid = :certifygenid AND issueid IS NOT NULL AND issueid <> 0',
            ['certifygenid' => $this->newcertifygenid],
            '',
            'id'
        );
        foreach (array_keys($validations) as $validationid) {
            if (!in_array($validationid, $this->validationswithissue)) {
                $this->discard_validation($validationid);
            }
        }
    }
}
