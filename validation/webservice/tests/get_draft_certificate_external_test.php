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
 *
 * @package   certifygenvalidation_webservice
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace certifygenvalidation_webservice;

use advanced_testcase;
use certifygenvalidation_webservice\external\get_draft_certificate_external;
use certifygenvalidation_webservice\external\start_student_certificate_external;
use coding_exception;
use dml_exception;
use invalid_parameter_exception;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;
use required_capability_exception;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/admin/tool/certificate/tests/generator/lib.php');

/**
 * Get pdf certificate test
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_draft_certificate_external_test extends advanced_testcase {
    /**
     * Test set up.
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test 1 - userfield
     *
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     * @covers \certifygenvalidation_webservice\external\get_draft_certificate_external
     */
    public function test_1(): void {
        global $DB;

        // Create user.
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $this->setUser($manager);

        // Create users.
        $student = $this->getDataGenerator()->create_user(
            ['username' => 'test_student_1', 'firstname' => 'test',
                    'lastname' => 'student 1', 'email' => 'test_student_1@fake.es']
        );
        // Create courses.
        $course1 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as editingteacher.
        self::getDataGenerator()->enrol_user($student->id, $course1->id, 'student');

        // Create mod_certifygen.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);
        $templategenerator->create_page($certificate1)->get_id();
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            certifygen_model::TYPE_ACTIVITY,
            $certificate1->get_id(),
            certifygen_model::TYPE_ACTIVITY,
        );
        $langs = $model->get('langs');
        $langs = explode(',', $langs);
        $lang = $langs[0];
        $datamodule = [
                'name' => 'Test 1,',
                'course' => $course1->id,
                'modelid' => $model->get('id'),
                'instance' => 0,
        ];
        $modcertifygen = self::getDataGenerator()->create_module('certifygen', $datamodule);
        $data = [
                'userid' => $student->id,
                'certifygenid' => $modcertifygen->id,
        ];
        $validation = certifygen_validations::get_record($data);
        self::assertFalse($validation);
        $result = get_draft_certificate_external::get_draft_certificate(
            0,
            'asd',
            $modcertifygen->id,
            $lang,
            '',
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertIsArray($result['error']);
        $this->assertArrayHasKey('code', $result['error']);
        $this->assertArrayHasKey('message', $result['error']);
        $this->assertEquals('userfield_not_selected', $result['error']['code']);
    }
    /**
     * Test 2 - userfield
     *
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     * @covers \certifygenvalidation_webservice\external\get_draft_certificate_external
     */
    public function test_2(): void {
        global $DB;

        // Create user profile fields.
        $category = self::getDataGenerator()->create_custom_profile_field_category(['name' => 'Category 1']);
        $field = self::getDataGenerator()->create_custom_profile_field(
            [
                'shortname' => 'DNI',
                'name' => 'DNI',
                'categoryid' => $category->id,
                'required' => 1, 'visible' => 1,
                'locked' => 0,
                'datatype' => 'text',
                'defaultdata' => null,
            ]
        );

        // Configure the platform.
        //set_config('userfield', 'profile_' . $field->id, 'mod_certifygen');
        set_config('userfield', 'city', 'mod_certifygen');

        // Create user.
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $this->setUser($manager);

        // Create users.
        $dni = '123456789P';
        $student = $this->getDataGenerator()->create_user([
            'username' => 'test_student_1', 'firstname' => 'test',
            'lastname' => 'student 1', 'email' => 'test_student_1@fake.es',
            'profile_field_DNI' => $dni,
        ]);
        // Create courses.
        $course1 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as editingteacher.
        self::getDataGenerator()->enrol_user($student->id, $course1->id, 'student');

        // Create mod_certifygen.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);
        $templategenerator->create_page($certificate1)->get_id();
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            certifygen_model::TYPE_ACTIVITY,
            $certificate1->get_id(),
            certifygen_model::TYPE_ACTIVITY,
        );
        $langs = $model->get('langs');
        $langs = explode(',', $langs);
        $lang = $langs[0];
        $datamodule = [
                'name' => 'Test 1,',
                'course' => $course1->id,
                'modelid' => $model->get('id'),
                'instance' => 0,
        ];
        $modcertifygen = self::getDataGenerator()->create_module('certifygen', $datamodule);
        $data = [
                'userid' => $student->id,
                'certifygenid' => $modcertifygen->id,
        ];
        $validation = certifygen_validations::get_record($data);
        self::assertFalse($validation);
        $result = get_draft_certificate_external::get_draft_certificate(
            0,
            'asd',
            $modcertifygen->id,
            $lang,
            '',
        );
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertIsArray($result['error']);
        $this->assertArrayHasKey('code', $result['error']);
        $this->assertArrayHasKey('message', $result['error']);
        $this->assertEquals('userfield_not_valid', $result['error']['code']);
    }
    /**
     * Test 3 - userfield
     *
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     * @covers \certifygenvalidation_webservice\external\get_draft_certificate_external
     */
    public function test_3(): void {
        global $DB;

        // Create user profile fields.
        $category = self::getDataGenerator()->create_custom_profile_field_category(['name' => 'Category 1']);
        $field = self::getDataGenerator()->create_custom_profile_field(
            [
                'shortname' => 'DNI',
                'name' => 'DNI',
                'categoryid' => $category->id,
                'required' => 1, 'visible' => 1,
                'locked' => 0,
                'datatype' => 'text',
                'defaultdata' => null,
            ]
        );

        // Configure the platform.
        set_config('userfield', 'profile_' . $field->id, 'mod_certifygen');

        // Create user.
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $this->setUser($manager);

        // Create users.
        $dni = '123456789P';
        $student = $this->getDataGenerator()->create_user([
            'username' => 'test_student_1', 'firstname' => 'test',
            'lastname' => 'student 1', 'email' => 'test_student_1@fake.es',
            'profile_field_DNI' => $dni,
        ]);
        // Create courses.
        $course1 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as editingteacher.
        self::getDataGenerator()->enrol_user($student->id, $course1->id, 'student');

        // Create mod_certifygen.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);
        $templategenerator->create_page($certificate1)->get_id();
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_for_ws(
            certifygen_model::TYPE_ACTIVITY,
            certifygen_model::MODE_UNIQUE,
            $certificate1->get_id()
        );
        $langs = $model->get('langs');
        $langs = explode(',', $langs);
        $lang = $langs[0];
        $datamodule = [
                'name' => 'Test 1,',
                'course' => $course1->id,
                'modelid' => $model->get('id'),
                'instance' => 0,
        ];
        $modcertifygen = self::getDataGenerator()->create_module('certifygen', $datamodule);
        $data = [
                'userid' => $student->id,
                'certifygenid' => $modcertifygen->id,
        ];
        $validation = certifygen_validations::get_record($data);
        self::assertFalse($validation);
        $result = get_draft_certificate_external::get_draft_certificate(
            0,
            $dni,
            $modcertifygen->id,
            $lang,
            '',
        );
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertIsArray($result['error']);
        $this->assertArrayHasKey('code', $result['error']);
        $this->assertArrayHasKey('message', $result['error']);
        $this->assertEquals('certificate_not_emited', $result['error']['code']);
    }
    /**
     * Test 4
     *
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     * @covers \certifygenvalidation_webservice\external\get_draft_certificate_external
     */
    public function test_4(): void {
        global $DB;

        // Create user.
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $this->setUser($manager);

        // Create users.
        $student = $this->getDataGenerator()->create_user(
            ['username' => 'test_student_1', 'firstname' => 'test',
                    'lastname' => 'student 1', 'email' => 'test_student_1@fake.es']
        );
        // Create courses.
        $course1 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as editingteacher.
        self::getDataGenerator()->enrol_user($student->id, $course1->id, 'student');

        // Create mod_certifygen.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);
        $templategenerator->create_page($certificate1)->get_id();
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_for_ws(
                certifygen_model::TYPE_ACTIVITY,
                certifygen_model::MODE_UNIQUE,
                $certificate1->get_id()
        );
        $langs = $model->get('langs');
        $langs = explode(',', $langs);
        $lang = $langs[0];
        $datamodule = [
                'name' => 'Test 1,',
                'course' => $course1->id,
                'modelid' => $model->get('id'),
                'instance' => 0,
        ];
        $modcertifygen = self::getDataGenerator()->create_module('certifygen', $datamodule);
        $data = [
                'userid' => $student->id,
                'certifygenid' => $modcertifygen->id,
        ];
        $validation = certifygen_validations::get_record($data);
        self::assertFalse($validation);
        $result = get_draft_certificate_external::get_draft_certificate(
            $student->id,
            '',
            $modcertifygen->id,
            $lang,
            '',
        );
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertIsArray($result['error']);
        $this->assertArrayHasKey('code', $result['error']);
        $this->assertArrayHasKey('message', $result['error']);
        $this->assertEquals('certificate_not_emited', $result['error']['code']);
    }
    /**
     * Test 5
     *
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     * @covers \certifygenvalidation_webservice\external\get_draft_certificate_external
     */
    public function test_5(): void {
        global $DB;

        // Create user.
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $this->setUser($manager);

        // Create users.
        $student = $this->getDataGenerator()->create_user(
            ['username' => 'test_student_1', 'firstname' => 'test',
                    'lastname' => 'student 1', 'email' => 'test_student_1@fake.es']
        );
        // Create courses.
        $course1 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as editingteacher.
        self::getDataGenerator()->enrol_user($student->id, $course1->id, 'student');

        // Create mod_certifygen.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);
        $templategenerator->create_page($certificate1)->get_id();
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            certifygen_model::TYPE_ACTIVITY,
            $certificate1->get_id(),
            certifygen_model::TYPE_ACTIVITY,
        );
        $langs = $model->get('langs');
        $langs = explode(',', $langs);
        $lang = $langs[0];
        $datamodule = [
                'name' => 'Test 1,',
                'course' => $course1->id,
                'modelid' => $model->get('id'),
                'instance' => 0,
        ];
        $modcertifygen = self::getDataGenerator()->create_module('certifygen', $datamodule);
        $data = [
                'userid' => $student->id,
                'certifygenid' => $modcertifygen->id,
        ];
        $validation = certifygen_validations::get_record($data);
        self::assertFalse($validation);
        $result = get_draft_certificate_external::get_draft_certificate(
            999999,
            '',
            $modcertifygen->id,
            $lang,
            '',
        );
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertIsArray($result['error']);
        $this->assertArrayHasKey('code', $result['error']);
        $this->assertArrayHasKey('message', $result['error']);
        $this->assertEquals('user_not_found', $result['error']['code']);
    }
    /**
     * Test 6
     *
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     * @covers \certifygenvalidation_webservice\external\get_draft_certificate_external
     */
    public function test_6(): void {
        global $DB;

        // Create user.
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $this->setUser($manager);

        // Create users.
        $student = $this->getDataGenerator()->create_user(
            ['username' => 'test_student_1', 'firstname' => 'test',
                    'lastname' => 'student 1', 'email' => 'test_student_1@fake.es']
        );
        // Create courses.
        $course1 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as editingteacher.
        //self::getDataGenerator()->enrol_user($student->id, $course1->id, 'student');

        // Create mod_certifygen.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);
        $templategenerator->create_page($certificate1)->get_id();
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            certifygen_model::TYPE_ACTIVITY,
            $certificate1->get_id(),
            certifygen_model::TYPE_ACTIVITY,
        );
        $langs = $model->get('langs');
        $langs = explode(',', $langs);
        $lang = $langs[0];
        $datamodule = [
                'name' => 'Test 1,',
                'course' => $course1->id,
                'modelid' => $model->get('id'),
                'instance' => 0,
        ];
        $modcertifygen = self::getDataGenerator()->create_module('certifygen', $datamodule);
        $data = [
                'userid' => $student->id,
                'certifygenid' => $modcertifygen->id,
        ];
        $validation = certifygen_validations::get_record($data);
        self::assertFalse($validation);
        $result = get_draft_certificate_external::get_draft_certificate(
            $student->id,
            '',
            $modcertifygen->id,
            $lang,
            '',
        );
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertIsArray($result['error']);
        $this->assertArrayHasKey('code', $result['error']);
        $this->assertArrayHasKey('message', $result['error']);
        $this->assertEquals('user_not_enrolled_on_idinstance_course_as_student', $result['error']['code']);
    }
    /**
     * Test 7
     *
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     * @covers \certifygenvalidation_webservice\external\get_draft_certificate_external
     */
    public function test_7(): void {
        global $DB;

        // Create user.
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $this->setUser($manager);

        // Create users.
        $student = $this->getDataGenerator()->create_user(
            ['username' => 'test_student_1', 'firstname' => 'test',
                    'lastname' => 'student 1', 'email' => 'test_student_1@fake.es']
        );
        // Create courses.
        $course1 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as editingteacher.
        self::getDataGenerator()->enrol_user($student->id, $course1->id, 'student');

        // Create mod_certifygen.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);
        $templategenerator->create_page($certificate1)->get_id();
        $this->getDataGenerator()->get_plugin_generator('mod_certifygen');

        // Create model.
        set_config('enabled', 1, 'certifygenvalidation_webservice');
        set_config('enabled', 1, 'certifygenreport_basic');
        set_config('enabled', 1, 'certifygenrepository_urlrepository');
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $data = [
                'type' => certifygen_model::TYPE_ACTIVITY,
                'mode' => certifygen_model::MODE_UNIQUE,
                'templateid' => $certificate1->get_id(),
        ];
        $model = $modgenerator->create_model_for_ws(
            $data['type'],
            $data['mode'],
            $data['templateid'],
        );
        $modgenerator->assign_model_coursecontext($model->get('id'), $course1->id);
        $langs = $model->get('langs');
        $langs = explode(',', $langs);
        $lang = $langs[0];
        $datamodule = [
                'name' => 'Test 1,',
                'course' => $course1->id,
                'modelid' => $model->get('id'),
                'instance' => 0,
        ];
        $modcertifygen = self::getDataGenerator()->create_module('certifygen', $datamodule);
        $data = [
                'userid' => $student->id,
                'certifygenid' => $modcertifygen->id,
        ];
        $validation = certifygen_validations::get_record($data);
        self::assertFalse($validation);

        // Starts process.
        $this->setAdminUser();
        $startresult = start_student_certificate_external::start_student_certificate(
            0,
            $modcertifygen->id,
            'en',
            $student->id,
            ''
        );

        self::assertIsArray($startresult);
        self::assertArrayHasKey('result', $startresult);
        $this->assertTrue($startresult['result']);

        $result = get_draft_certificate_external::get_draft_certificate(
            $student->id,
            '',
            $modcertifygen->id,
            $lang
        );
        $this->assertIsArray($result);
        $this->assertArrayHasKey('certificate', $result);
        $this->assertIsArray($result['certificate']);
        $this->assertArrayHasKey('validationid', $result['certificate']);
        $this->assertArrayHasKey('status', $result['certificate']);
        $this->assertArrayHasKey('file', $result['certificate']);
        $this->assertEquals(certifygen_validations::STATUS_VALIDATION_OK, $result['certificate']['status']);
    }
}
