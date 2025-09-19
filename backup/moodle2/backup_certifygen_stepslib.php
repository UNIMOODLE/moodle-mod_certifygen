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
 * Define the complete structure for backup, with file and id annotations.
 *
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * THe class defines the complete structure for backup, with file and id annotations.
 *
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_certifygen_activity_structure_step extends backup_activity_structure_step {
    /**
     * Defines the structure of the resulting xml file.
     *
     * @return backup_nested_element The structure wrapped by the common 'activity' element.
     * @throws base_element_struct_exception
     * @throws base_step_exception
     * @throws ddl_exception
     * @throws dml_exception
     */
    protected function define_structure() {
        global $DB;

        if (!$DB->get_manager()->table_exists('tool_certificate_issues')) {
            throw new dml_exception('tool_certificate_issues table does not exists');
        }

        // Certifygen model.
        $cmodels = new backup_nested_element('models');
        $cmodel = new backup_nested_element(
            'model',
            ['id'],
            [
                    'name',
                    'idnumber',
                    'type',
                    'mode',
                    'templateid',
                    'timeondemmand',
                    'langs',
                    'validation',
                    'report',
                    'repository',
                    'usermodified',
                    'timecreated',
                    'timemodified',
            ]
        );
        // Certifygen repository.
        $crepositorys = new backup_nested_element('crepositorys');
        $crepository = new backup_nested_element(
            'crepository',
            ['id'],
            [
                    'validationid',
                    'userid',
                    'url',
                    'data',
                    'usermodified',
                    'timecreated',
                    'timemodified',
            ]
        );
        // Activity certifygen.
        $certifygen = new backup_nested_element(
            'certifygen',
            ['id'],
            [
                    'course',
                    'name',
                    'intro',
                    'introformat',
                    'completiondownload',
                    'usermodified',
                    'timecreated',
                    'timemodified',
            ]
        );
        // Activity certifygen_cmodels.
        $ccmodels = new backup_nested_element('ccmodels');
        $ccmodel = new backup_nested_element(
            'ccmodel',
            ['id'],
            [
                'modelid',
                'certifygenid',
            ]
        );
        // Certifygen errors.
        $cerrors = new backup_nested_element('cerrors');
        $cerror = new backup_nested_element(
            'cerror',
            ['id'],
            [
                    'validationid',
                    'status',
                    'code',
                    'message',
                    'usermodified',
                    'timecreated',
                    'timemodified',
            ]
        );
        // Certifygen_validations.
        $cvalidations = new backup_nested_element('cvalidations');
        $cvalidation = new backup_nested_element(
            'cvalidation',
            ['id'],
            [
                    'name',
                    'courses',
                    'code',
                    'certifygenid',
                    'issueid',
                    'userid',
                    'modelid',
                    'lang',
                    'status',
                    'usermodified',
                    'timecreated',
                    'timemodified',
            ]
        );
        // Certifygen_validationcsv.
        $cvalidationcsvs = new backup_nested_element('cvalidationcsvs');
        $cvalidationcsv = new backup_nested_element(
            'cvalidationcsv',
            ['id'],
            [
                    'validationid',
                    'applicationid',
                    'token',
                    'usermodified',
                    'timecreated',
                    'timemodified',
            ]
        );
        // Certifygen contexts.
        $ccontexts = new backup_nested_element('ccontexts');
        $ccontext = new backup_nested_element(
            'ccontext',
            ['id'],
            [
                    'modelid',
                    'contextids',
                    'type',
                    'usermodified',
                    'timecreated',
                    'timemodified',
            ],
        );
        // Issues.
        $issues = new backup_nested_element('issues');
        $issue = new backup_nested_element(
            'issue',
            ['id'],
            [
                    'userid',
                    'templateid',
                    'code',
                    'emailed',
                    'timecreated',
                    'expires',
                    'data',
                    'component',
                    'courseid',
            ],
        );

        // Build the tree.
        $certifygen->add_child($ccmodels);
        $ccmodels->add_child($ccmodel);
        $ccmodel->add_child($cmodels);
        $cmodels->add_child($cmodel);
        $cmodel->add_child($ccontexts);
        $ccontexts->add_child($ccontext);
        $certifygen->add_child($cvalidations);
        $cvalidations->add_child($cvalidation);
        $cvalidation->add_child($cvalidationcsvs);
        $cvalidation->add_child($crepositorys);
        $cvalidation->add_child($cerrors);
        $cvalidation->add_child($issues);
        $issues->add_child($issue);
        $crepositorys->add_child($crepository);
        $cerrors->add_child($cerror);
        $cvalidationcsvs->add_child($cvalidationcsv);

        // Define the source tables for the elements.
        $certifygen->set_source_table(
            'certifygen',
            ['id' => backup::VAR_ACTIVITYID]
        );
        $ccmodel->set_source_table(
            'certifygen_cmodels',
            ['certifygenid' => backup::VAR_ACTIVITYID]
        );
        $params = ['certifygenid' => backup::VAR_ACTIVITYID];
        $cmodel->set_source_sql('
            SELECT cm.*
            FROM {certifygen_model} cm
            JOIN {certifygen_cmodels} ccm ON cm.id = ccm.modelid
                WHERE ccm.certifygenid = :certifygenid', $params);
        $ccontext->set_source_sql('
            SELECT cc.*
            FROM {certifygen_context} cc
            JOIN {certifygen_cmodels} cm ON cm.id = cc.modelid
                WHERE cm.certifygenid = :certifygenid', $params);
        // If we are including user info then save the issues.
        if ($this->get_setting_value('userinfo')) {
            $cvalidation->set_source_table(
                'certifygen_validations',
                [
                    'certifygenid' => backup::VAR_ACTIVITYID,
                ]
            );
            $issue->set_source_sql('
                SELECT tc.*
                FROM {tool_certificate_issues} tc
                JOIN {certifygen_validations} cv ON tc.id = cv.issueid AND cv.userid = tc.userid
                    WHERE cv.certifygenid = :certifygenid', $params);
            $cerror->set_source_sql('
                SELECT ce.*
                FROM {certifygen_error} ce
                JOIN {certifygen_validations} cv ON cv.id = ce.validationid
                    WHERE cv.certifygenid = :certifygenid', $params);
            $crepository->set_source_sql('
                SELECT cr.*
                FROM {certifygen_repository} cr
                JOIN {certifygen_validations} cv ON (cv.id = cr.validationid AND cr.userid = cv.userid)
                    WHERE cv.certifygenid = :certifygenid', $params);
            $cvalidationcsv->set_source_sql('
                SELECT cr.*
                FROM {certifygen_validationcsv} cr
                JOIN {certifygen_validations} cv ON cv.id = cr.validationid
                    WHERE cv.certifygenid = :certifygenid', $params);
        }

        // Define id annotations.
        $issue->annotate_ids('user', 'userid');

        // Define file annotations.
        $certifygen->annotate_files('mod_certifygen', 'intro', null); // This file area hasn't itemid.
        if ($this->get_setting_value('userinfo')) {
            $issue->annotate_files('tool_certificate', 'issues', 'id', system::instance()->id);
            $certifygen->annotate_files(
                'mod_certifygen',
                'certifygenrepository',
                'id',
                course::instance($this->get_courseid())->id
            );
            $certifygen->annotate_files(
                'mod_certifygen',
                'certifygenvalidationvalidated',
                'id',
                course::instance($this->get_courseid())->id
            );
        }
        return $this->prepare_activity_structure($certifygen);
    }
}
