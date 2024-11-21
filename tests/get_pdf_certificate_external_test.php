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
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_certifygen;
use certifygenvalidation_webservice\external\change_status_external;
use core\invalid_persistent_exception;
use mod_certifygen\external\emitcertificate_external;
use mod_certifygen\external\get_pdf_certificate_external;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;
use mod_certifygen\task\checkfile;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/admin/tool/certificate/tests/generator/lib.php');
require_once($CFG->dirroot . '/lib/externallib.php');
/**
 * Get pdf certificate test
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_pdf_certificate_external_test extends \advanced_testcase {
    /**
     * Test set up.
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test 1
     *
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \required_capability_exception
     * @covers \mod_certifygen\external\get_pdf_certificate_external::get_pdf_certificate
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
            certifygen_model::TYPE_ACTIVITY
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
        $result = get_pdf_certificate_external::get_pdf_certificate(
            $student->id,
            '',
            $modcertifygen->id,
            $lang,
            '',
        );
        $validation = certifygen_validations::get_record($data);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('certificate', $result);
        $this->assertArrayHasKey('validationid', $result['certificate']);
        $this->assertArrayHasKey('status', $result['certificate']);
        $this->assertArrayHasKey('statusstr', $result['certificate']);
        $this->assertArrayHasKey('file', $result['certificate']);
        $this->assertArrayHasKey('reporttype', $result['certificate']);
        $this->assertArrayHasKey('reporttypestr', $result['certificate']);

        $this->assertEquals($validation->get('id'), $result['certificate']['validationid']);
        $this->assertEquals($validation->get('status'), $result['certificate']['status']);
        $this->assertEquals(certifygen_model::TYPE_ACTIVITY, $result['certificate']['reporttype']);
    }

    /**
     * Test 2
     *
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     * @throws \required_capability_exception
     * @throws invalid_persistent_exception
     * @covers \mod_certifygen\external\get_pdf_certificate_external::get_pdf_certificate
     */
    public function test_2(): void {
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
            certifygen_model::TYPE_ACTIVITY
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

        // Emit certificate.
        $this->setUser($student);
        emitcertificate_external::emitcertificate(
            0,
            $modcertifygen->id,
            $model->get('id'),
            $lang,
            $student->id,
            $course1->id
        );

        // Obtenemos el pdf.
        $this->setUser($manager);
        $result = get_pdf_certificate_external::get_pdf_certificate(
            $student->id,
            '',
            $modcertifygen->id,
            $lang,
            '',
        );
        $validation = certifygen_validations::get_record($data);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('certificate', $result);
        $this->assertArrayHasKey('validationid', $result['certificate']);
        $this->assertArrayHasKey('status', $result['certificate']);
        $this->assertArrayHasKey('statusstr', $result['certificate']);
        $this->assertArrayHasKey('file', $result['certificate']);
        $this->assertArrayHasKey('reporttype', $result['certificate']);
        $this->assertArrayHasKey('reporttypestr', $result['certificate']);

        $this->assertEquals($validation->get('id'), $result['certificate']['validationid']);
        $this->assertEquals($validation->get('status'), $result['certificate']['status']);
        $this->assertEquals(certifygen_model::TYPE_ACTIVITY, $result['certificate']['reporttype']);
    }

    /**
     * Test 3
     *
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \required_capability_exception
     * @covers \mod_certifygen\external\get_pdf_certificate_external::get_pdf_certificate
     */
    public function test_3(): void {
        global $DB;

        // Create user profile fields.
        $category = self::getDataGenerator()->create_custom_profile_field_category(['name' => 'Category 1']);
        $field = self::getDataGenerator()->create_custom_profile_field(
            ['shortname' => 'DNI',
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

        // Create user.
        $dni = '123456789P';
        $student = $this->getDataGenerator()->create_user(
            ['username' => 'test_student_1', 'firstname' => 'test',
            'lastname' => 'student 1', 'email' => 'test_student_1@fake.es',
                    'profile_field_DNI' => $dni]
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
            certifygen_model::TYPE_ACTIVITY
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
        $result = get_pdf_certificate_external::get_pdf_certificate(
            0,
            $dni,
            $modcertifygen->id,
            $lang,
            '',
        );
        $validation = certifygen_validations::get_record($data);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('certificate', $result);
        $this->assertArrayHasKey('validationid', $result['certificate']);
        $this->assertArrayHasKey('status', $result['certificate']);
        $this->assertArrayHasKey('statusstr', $result['certificate']);
        $this->assertArrayHasKey('file', $result['certificate']);
        $this->assertArrayHasKey('reporttype', $result['certificate']);
        $this->assertArrayHasKey('reporttypestr', $result['certificate']);

        $this->assertEquals($validation->get('id'), $result['certificate']['validationid']);
        $this->assertEquals($validation->get('status'), $result['certificate']['status']);
        $this->assertEquals(certifygen_model::TYPE_ACTIVITY, $result['certificate']['reporttype']);
    }

    /**
     * Test 4
     *
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \required_capability_exception
     * @covers \mod_certifygen\external\get_pdf_certificate_external::get_pdf_certificate
     */
    public function test_4(): void {
        global $DB;

        // Configure the platform.
        set_config('userfield', 'username', 'mod_certifygen');

        // Create user.
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $this->setUser($manager);

        // Create user.
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
            certifygen_model::TYPE_ACTIVITY
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
        $result = get_pdf_certificate_external::get_pdf_certificate(
            0,
            $student->username,
            $modcertifygen->id,
            $lang,
            '',
        );
        $validation = certifygen_validations::get_record($data);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('certificate', $result);
        $this->assertArrayHasKey('validationid', $result['certificate']);
        $this->assertArrayHasKey('status', $result['certificate']);
        $this->assertArrayHasKey('statusstr', $result['certificate']);
        $this->assertArrayHasKey('file', $result['certificate']);
        $this->assertArrayHasKey('reporttype', $result['certificate']);
        $this->assertArrayHasKey('reporttypestr', $result['certificate']);

        $this->assertEquals($validation->get('id'), $result['certificate']['validationid']);
        $this->assertEquals($validation->get('status'), $result['certificate']['status']);
        $this->assertEquals(certifygen_model::TYPE_ACTIVITY, $result['certificate']['reporttype']);
    }

    /**
     * Test 5
     *
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \required_capability_exception
     * @covers \mod_certifygen\external\get_pdf_certificate_external::get_pdf_certificate
     */
    public function test_5(): void {
        global $DB;

        // Configure the platform.
        set_config('userfield', 'idnumber', 'mod_certifygen');

        // Create user.
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $this->setUser($manager);

        // Create user.
        $student = $this->getDataGenerator()->create_user(
            ['username' => 'test_student_1', 'firstname' => 'test',
                    'lastname' => 'student 1', 'email' => 'test_student_1@fake.es',
                    'idnumber' => 'test_student_1']
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
            certifygen_model::TYPE_ACTIVITY
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
        $result = get_pdf_certificate_external::get_pdf_certificate(
            0,
            $student->idnumber,
            $modcertifygen->id,
            $lang,
            '',
        );
        $validation = certifygen_validations::get_record($data);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('certificate', $result);
        $this->assertArrayHasKey('validationid', $result['certificate']);
        $this->assertArrayHasKey('status', $result['certificate']);
        $this->assertArrayHasKey('statusstr', $result['certificate']);
        $this->assertArrayHasKey('file', $result['certificate']);
        $this->assertArrayHasKey('reporttype', $result['certificate']);
        $this->assertArrayHasKey('reporttypestr', $result['certificate']);

        $this->assertEquals($validation->get('id'), $result['certificate']['validationid']);
        $this->assertEquals($validation->get('status'), $result['certificate']['status']);
        $this->assertEquals(certifygen_model::TYPE_ACTIVITY, $result['certificate']['reporttype']);
    }

    /**
     * Test 6: ws error
     *
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     * @throws \required_capability_exception
     * @throws invalid_persistent_exception
     * @covers \mod_certifygen\external\get_pdf_certificate_external::get_pdf_certificate
     */
    public function test_6(): void {
        global $DB;
        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);
        $templategenerator->create_page($certificate1)->get_id();

        // Create model.
        set_config('enabled', 1, 'certifygenvalidation_webservice');
        set_config('enabled', 1, 'certifygenrepository_localrepository');
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model(
            certifygen_model::TYPE_ACTIVITY,
            certifygen_model::MODE_UNIQUE,
            $certificate1->get_id(),
            'certifygenvalidation_webservice',
            '',
        );
        $langs = $model->get('langs');
        $langs = explode(',', $langs);
        $lang = $langs[0];

        // Create course.
        $course = self::getDataGenerator()->create_course();

        // Create mod_certifygen module.
        $datamodule = [
                'name' => 'Test 1,',
                'course' => $course->id,
                'modelid' => $model->get('id'),
        ];
        $modcertifygen = self::getDataGenerator()->create_module('certifygen', $datamodule);
        $cm = get_coursemodule_from_instance('certifygen', $modcertifygen->id, $course->id, false, MUST_EXIST);

        // Create users.
        $student = $this->getDataGenerator()->create_user([
                'username' => 'test_user_1',
                'firstname' => 'test',
                'lastname' => 'user 1',
                'email' => 'test_user_1@fake.es',
        ]);

        // Enrol into the course as student.
        self::getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        // Login as student.
        $this->setUser($student);

        $data = [
                'userid' => $student->id,
                'certifygenid' => $modcertifygen->id,
        ];
        $validation = certifygen_validations::get_record($data);
        self::assertFalse($validation);
        emitcertificate_external::emitcertificate(0, $cm->instance, $model->get('id'), $lang, $student->id, $course->id);

        // Obtenemos el pdf.
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $this->setUser($manager);
        $result = get_pdf_certificate_external::get_pdf_certificate(
            $student->id,
            '',
            $modcertifygen->id,
            $lang,
            '',
        );
        // Tests.
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertArrayHasKey('message', $result['error']);
        $this->assertArrayHasKey('code', $result['error']);
        $this->assertEquals(get_string('statusnotfinished', 'mod_certifygen'), $result['error']['message']);
        $this->assertEquals('status_not_finished', $result['error']['code']);
    }

    /**
     * Test 7: ws ok
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     * @throws \required_capability_exception
     * @throws invalid_persistent_exception
     * @covers \mod_certifygen\external\get_pdf_certificate_external::get_pdf_certificate
     */
    public function test_7(): void {
        global $DB;
        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);
        $templategenerator->create_page($certificate1)->get_id();

        // Create model.
        set_config('enabled', 1, 'certifygenvalidation_webservice');
        set_config('enabled', 1, 'certifygenrepository_localrepository');
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model(
            certifygen_model::TYPE_ACTIVITY,
            certifygen_model::MODE_UNIQUE,
            $certificate1->get_id(),
            'certifygenvalidation_webservice',
            '',
        );
        $langs = $model->get('langs');
        $langs = explode(',', $langs);
        $lang = $langs[0];

        // Create course.
        $course = self::getDataGenerator()->create_course();

        // Create mod_certifygen module.
        $datamodule = [
                'name' => 'Test 1,',
                'course' => $course->id,
                'modelid' => $model->get('id'),
        ];
        $modcertifygen = self::getDataGenerator()->create_module('certifygen', $datamodule);
        $cm = get_coursemodule_from_instance('certifygen', $modcertifygen->id, $course->id, false, MUST_EXIST);

        // Create users.
        $student = $this->getDataGenerator()->create_user([
                'username' => 'test_user_1',
                'firstname' => 'test',
                'lastname' => 'user 1',
                'email' => 'test_user_1@fake.es',
        ]);

        // Enrol into the course as student.
        self::getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        // Login as student.
        $this->setUser($student);

        $data = [
                'userid' => $student->id,
                'certifygenid' => $modcertifygen->id,
        ];
        $validation = certifygen_validations::get_record($data);
        self::assertFalse($validation);
        emitcertificate_external::emitcertificate(0, $cm->instance, $model->get('id'), $lang, $student->id, $course->id);

        // Change status.
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $this->setUser($manager);
        $validation = certifygen_validations::get_record($data);
        change_status_external::change_status(
            $student->id,
            '',
            $validation->get('id')
        );
        $validation = new certifygen_validations($validation->get('id'));
        self::assertEquals(certifygen_validations::STATUS_VALIDATION_OK, (int)$validation->get('status'));

        // Execute task.
        $removaltask = new checkfile();
        $removaltask->execute();

        // Now validation record exists.
        $validation = new certifygen_validations($validation->get('id'));
        self::assertEquals(certifygen_validations::STATUS_FINISHED, (int)$validation->get('status'));

        // Obtenemos el pdf.
        $result = get_pdf_certificate_external::get_pdf_certificate(
            $student->id,
            '',
            $modcertifygen->id,
            $lang,
            '',
        );

        $this->assertIsArray($result);

        // Tests.
        self::assertIsArray($result);
        $this->assertArrayHasKey('certificate', $result);
        $this->assertArrayHasKey('validationid', $result['certificate']);
        $this->assertArrayHasKey('status', $result['certificate']);
        $this->assertArrayHasKey('statusstr', $result['certificate']);
        $this->assertArrayHasKey('file', $result['certificate']);
        $this->assertArrayHasKey('reporttype', $result['certificate']);
        $this->assertArrayHasKey('reporttypestr', $result['certificate']);

        $this->assertEquals($validation->get('id'), $result['certificate']['validationid']);
        $this->assertEquals($validation->get('status'), $result['certificate']['status']);
        $this->assertEquals(certifygen_model::TYPE_ACTIVITY, $result['certificate']['reporttype']);
    }
}
