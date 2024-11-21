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
use mod_certifygen\external\emitteacherrequest_external;
use mod_certifygen\external\get_pdf_teacher_certificate_external;
use mod_certifygen\persistents\certifygen_model;
use certifygenvalidation_webservice\external\change_status_external;
use core\invalid_persistent_exception;
use mod_certifygen\persistents\certifygen_validations;
use mod_certifygen\task\checkfile;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/lib/externallib.php');
/**
 * Get pdf teacher certificate test
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_pdf_teacher_certificate_external_test extends \advanced_testcase {
    /**
     * Test set up.
     */
    public function setUp(): void {
        $this->resetAfterTest();
        $controller = new \tool_langimport\controller();
        $controller->install_languagepacks('es');
    }

    /**
     * Test 1:calls get_pdf_teacher_certificate without emiting.
     *
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @covers \mod_certifygen\external\get_pdf_teacher_certificate_external::get_pdf_teacher_certificate
     */
    public function test_1(): void {
        global $DB;

        // Create user.
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $this->setUser($manager);

        // Create users.
        $student = $this->getDataGenerator()->create_user([
                'username' => 'test_student_1', 'firstname' => 'test',
                        'lastname' => 'student 1', 'email' => 'test_student_1@fake.es',
                ]);
        $teacher = $this->getDataGenerator()->create_user([
                'username' => 'test_teacher_1', 'firstname' => 'test',
                        'lastname' => 'teacher 1', 'email' => 'test_teacher_1@fake.es',
                ]);
        // Create courses.
        $course1 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as editingteacher.
        self::getDataGenerator()->enrol_user($student->id, $course1->id, 'student');
        self::getDataGenerator()->enrol_user($teacher->id, $course1->id, 'editingteacher');

        // Create mod_certifygen.
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
            0,
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED
        );
        $modgenerator->assign_model_coursecontext($model->get('id'), $course1->id);
        $langs = $model->get('langs');
        $langs = explode(',', $langs);
        $lang = $langs[0];
        $data = [
                'userid' => $teacher->id,
                'modelid' => $model->get('id'),
        ];
        $validation = certifygen_validations::get_record($data);
        self::assertFalse($validation);
        $result = get_pdf_teacher_certificate_external::get_pdf_teacher_certificate(
            $teacher->id,
            '',
            'request_1',
            (string)$course1->id,
            $model->get('id'),
            $lang
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
        $this->assertEquals(certifygen_model::TYPE_TEACHER_ALL_COURSES_USED, $result['certificate']['reporttype']);
    }

    /**
     * Test 2: before calling get_pdf_teacher_certificate ws, called eitteacherrequest ws..
     *
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws invalid_persistent_exception
     * @covers \mod_certifygen\external\get_pdf_teacher_certificate_external::get_pdf_teacher_certificate
     */
    public function test_2(): void {
        global $DB;

        // Create user.
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $this->setUser($manager);

        // Create users.
        $student = $this->getDataGenerator()->create_user([
                'username' => 'test_student_1', 'firstname' => 'test',
                'lastname' => 'student 1', 'email' => 'test_student_1@fake.es',
        ]);
        $teacher = $this->getDataGenerator()->create_user([
                'username' => 'test_teacher_1', 'firstname' => 'test',
                'lastname' => 'teacher 1', 'email' => 'test_teacher_1@fake.es',
        ]);
        // Create courses.
        $course1 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as editingteacher.
        self::getDataGenerator()->enrol_user($student->id, $course1->id, 'student');
        self::getDataGenerator()->enrol_user($teacher->id, $course1->id, 'editingteacher');

        // Create mod_certifygen.
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
            0,
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED
        );
        $modgenerator->assign_model_coursecontext($model->get('id'), $course1->id);
        $langs = $model->get('langs');
        $langs = explode(',', $langs);
        $lang = $langs[1];
        $data = [
                'userid' => $teacher->id,
                'modelid' => $model->get('id'),
        ];
        $validation = certifygen_validations::get_record($data);
        self::assertFalse($validation);

        // Create teacherrequest.
        $this->setUser($teacher);
        $teacherrequest = $modgenerator->create_teacher_request($model->get('id'), $course1->id, $teacher->id);
        self::assertEquals(certifygen_validations::STATUS_NOT_STARTED, $teacherrequest->get('status'));

        // Emit ws.
        $re = emitteacherrequest_external::emitteacherrequest($teacherrequest->get('id'));
        $teacherrequest = new certifygen_validations($teacherrequest->get('id'));
        self::assertEquals(certifygen_validations::STATUS_FINISHED, $teacherrequest->get('status'));

        // Obtenemos el pdf.
        $this->setUser($manager);
        $result = get_pdf_teacher_certificate_external::get_pdf_teacher_certificate(
            $teacher->id,
            '',
            $teacherrequest->get('name'),
            (string)$course1->id,
            $model->get('id'),
            $lang
        );
        $teacherrequest = new certifygen_validations($teacherrequest->get('id'));

        // Tests.
        $this->assertIsArray($result);
        $this->assertArrayHasKey('certificate', $result);
        $this->assertArrayHasKey('validationid', $result['certificate']);
        $this->assertArrayHasKey('status', $result['certificate']);
        $this->assertArrayHasKey('statusstr', $result['certificate']);
        $this->assertArrayHasKey('file', $result['certificate']);
        $this->assertArrayHasKey('reporttype', $result['certificate']);
        $this->assertArrayHasKey('reporttypestr', $result['certificate']);

        $this->assertEquals($teacherrequest->get('id'), $result['certificate']['validationid']);
        self::assertEquals(certifygen_validations::STATUS_FINISHED, $result['certificate']['status']);
        $this->assertEquals(certifygen_model::TYPE_TEACHER_ALL_COURSES_USED, $result['certificate']['reporttype']);
    }

    /**
     * Test 3: userfields
     *
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws invalid_persistent_exception
     * @covers \mod_certifygen\external\get_pdf_teacher_certificate_external::get_pdf_teacher_certificate
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

        // Create users.
        $student = $this->getDataGenerator()->create_user([
                'username' => 'test_student_1', 'firstname' => 'test',
                'lastname' => 'student 1', 'email' => 'test_student_1@fake.es',
        ]);
        $dni = '123456789P';
        $teacher = $this->getDataGenerator()->create_user([
                'username' => 'test_teacher_1', 'firstname' => 'test',
                'lastname' => 'teacher 1', 'email' => 'test_teacher_1@fake.es',
                'profile_field_DNI' => $dni,
        ]);
        // Create courses.
        $course1 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as editingteacher.
        self::getDataGenerator()->enrol_user($student->id, $course1->id, 'student');
        self::getDataGenerator()->enrol_user($teacher->id, $course1->id, 'editingteacher');

        // Create mod_certifygen.
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
            0,
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED
        );
        $modgenerator->assign_model_coursecontext($model->get('id'), $course1->id);
        $langs = $model->get('langs');
        $langs = explode(',', $langs);
        $lang = $langs[1];
        $data = [
                'userid' => $teacher->id,
                'modelid' => $model->get('id'),
        ];
        $validation = certifygen_validations::get_record($data);
        self::assertFalse($validation);

        // Create teacherrequest.
        $this->setUser($teacher);
        $teacherrequest = $modgenerator->create_teacher_request($model->get('id'), $course1->id, $teacher->id);
        self::assertEquals(certifygen_validations::STATUS_NOT_STARTED, $teacherrequest->get('status'));

        // Emit ws.
        $re = emitteacherrequest_external::emitteacherrequest($teacherrequest->get('id'));
        $teacherrequest = new certifygen_validations($teacherrequest->get('id'));
        self::assertEquals(certifygen_validations::STATUS_FINISHED, $teacherrequest->get('status'));

        // Obtenemos el pdf.
        $this->setUser($manager);
        $result = get_pdf_teacher_certificate_external::get_pdf_teacher_certificate(
            0,
            $dni,
            $teacherrequest->get('name'),
            (string)$course1->id,
            $model->get('id'),
            $lang
        );

        $teacherrequest = new certifygen_validations($teacherrequest->get('id'));

        // Tests.
        $this->assertIsArray($result);
        $this->assertArrayHasKey('certificate', $result);
        $this->assertArrayHasKey('validationid', $result['certificate']);
        $this->assertArrayHasKey('status', $result['certificate']);
        $this->assertArrayHasKey('statusstr', $result['certificate']);
        $this->assertArrayHasKey('file', $result['certificate']);
        $this->assertArrayHasKey('reporttype', $result['certificate']);
        $this->assertArrayHasKey('reporttypestr', $result['certificate']);

        $this->assertEquals($teacherrequest->get('id'), $result['certificate']['validationid']);
        self::assertEquals(certifygen_validations::STATUS_FINISHED, $result['certificate']['status']);
        $this->assertEquals(certifygen_model::TYPE_TEACHER_ALL_COURSES_USED, $result['certificate']['reporttype']);
    }

    /**
     * Test 4: find user by usrename
     *
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws invalid_persistent_exception
     * @covers \mod_certifygen\external\get_pdf_teacher_certificate_external::get_pdf_teacher_certificate
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

        // Create users.
        $student = $this->getDataGenerator()->create_user([
                'username' => 'test_student_1', 'firstname' => 'test',
                'lastname' => 'student 1', 'email' => 'test_student_1@fake.es',
        ]);
        $teacher = $this->getDataGenerator()->create_user([
                'username' => 'test_teacher_1', 'firstname' => 'test',
                'lastname' => 'teacher 1', 'email' => 'test_teacher_1@fake.es',
        ]);
        // Create courses.
        $course1 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as editingteacher.
        self::getDataGenerator()->enrol_user($student->id, $course1->id, 'student');
        self::getDataGenerator()->enrol_user($teacher->id, $course1->id, 'editingteacher');

        // Create mod_certifygen.
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
            0,
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED
        );
        $modgenerator->assign_model_coursecontext($model->get('id'), $course1->id);
        $langs = $model->get('langs');
        $langs = explode(',', $langs);
        $lang = $langs[1];
        $data = [
                'userid' => $teacher->id,
                'modelid' => $model->get('id'),
        ];
        $validation = certifygen_validations::get_record($data);
        self::assertFalse($validation);

        // Create teacherrequest.
        $this->setUser($teacher);
        $teacherrequest = $modgenerator->create_teacher_request($model->get('id'), $course1->id, $teacher->id);
        self::assertEquals(certifygen_validations::STATUS_NOT_STARTED, $teacherrequest->get('status'));

        // Emit ws.
        emitteacherrequest_external::emitteacherrequest($teacherrequest->get('id'));
        $teacherrequest = new certifygen_validations($teacherrequest->get('id'));
        self::assertEquals(certifygen_validations::STATUS_FINISHED, $teacherrequest->get('status'));

        // Obtenemos el pdf.
        $this->setUser($manager);
        $result = get_pdf_teacher_certificate_external::get_pdf_teacher_certificate(
            0,
            $teacher->username,
            $teacherrequest->get('name'),
            (string)$course1->id,
            $model->get('id'),
            $lang
        );

        $teacherrequest = new certifygen_validations($teacherrequest->get('id'));

        // Tests.
        $this->assertIsArray($result);
        $this->assertArrayHasKey('certificate', $result);
        $this->assertArrayHasKey('validationid', $result['certificate']);
        $this->assertArrayHasKey('status', $result['certificate']);
        $this->assertArrayHasKey('statusstr', $result['certificate']);
        $this->assertArrayHasKey('file', $result['certificate']);
        $this->assertArrayHasKey('reporttype', $result['certificate']);
        $this->assertArrayHasKey('reporttypestr', $result['certificate']);

        $this->assertEquals($teacherrequest->get('id'), $result['certificate']['validationid']);
        self::assertEquals(certifygen_validations::STATUS_FINISHED, $result['certificate']['status']);
        $this->assertEquals(certifygen_model::TYPE_TEACHER_ALL_COURSES_USED, $result['certificate']['reporttype']);
    }

    /**
     * Test 5: find user by idnumber
     *
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws invalid_persistent_exception
     * @covers \mod_certifygen\external\get_pdf_teacher_certificate_external::get_pdf_teacher_certificate
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

        // Create users.
        $student = $this->getDataGenerator()->create_user([
                'username' => 'test_student_1', 'firstname' => 'test',
                'lastname' => 'student 1', 'email' => 'test_student_1@fake.es',
        ]);
        $teacher = $this->getDataGenerator()->create_user([
                'username' => 'test_teacher_1', 'firstname' => 'test',
                'lastname' => 'teacher 1', 'email' => 'test_teacher_1@fake.es',
                'idnumber' => 'test_teacher_1',
        ]);
        // Create courses.
        $course1 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as editingteacher.
        self::getDataGenerator()->enrol_user($student->id, $course1->id, 'student');
        self::getDataGenerator()->enrol_user($teacher->id, $course1->id, 'editingteacher');

        // Create mod_certifygen.
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
            0,
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED
        );
        $modgenerator->assign_model_coursecontext($model->get('id'), $course1->id);
        $langs = $model->get('langs');
        $langs = explode(',', $langs);
        $lang = $langs[1];
        $data = [
                'userid' => $teacher->id,
                'modelid' => $model->get('id'),
        ];
        $validation = certifygen_validations::get_record($data);
        self::assertFalse($validation);

        // Create teacherrequest.
        $this->setUser($teacher);
        $teacherrequest = $modgenerator->create_teacher_request($model->get('id'), $course1->id, $teacher->id);
        self::assertEquals(certifygen_validations::STATUS_NOT_STARTED, $teacherrequest->get('status'));

        // Emit ws.
        emitteacherrequest_external::emitteacherrequest($teacherrequest->get('id'));
        $teacherrequest = new certifygen_validations($teacherrequest->get('id'));
        self::assertEquals(certifygen_validations::STATUS_FINISHED, $teacherrequest->get('status'));

        // Obtenemos el pdf.
        $this->setUser($manager);
        $result = get_pdf_teacher_certificate_external::get_pdf_teacher_certificate(
            0,
            $teacher->idnumber,
            $teacherrequest->get('name'),
            (string)$course1->id,
            $model->get('id'),
            $lang
        );

        $teacherrequest = new certifygen_validations($teacherrequest->get('id'));

        // Tests.
        $this->assertIsArray($result);
        $this->assertArrayHasKey('certificate', $result);
        $this->assertArrayHasKey('validationid', $result['certificate']);
        $this->assertArrayHasKey('status', $result['certificate']);
        $this->assertArrayHasKey('statusstr', $result['certificate']);
        $this->assertArrayHasKey('file', $result['certificate']);
        $this->assertArrayHasKey('reporttype', $result['certificate']);
        $this->assertArrayHasKey('reporttypestr', $result['certificate']);

        $this->assertEquals($teacherrequest->get('id'), $result['certificate']['validationid']);
        self::assertEquals(certifygen_validations::STATUS_FINISHED, $result['certificate']['status']);
        $this->assertEquals(certifygen_model::TYPE_TEACHER_ALL_COURSES_USED, $result['certificate']['reporttype']);
    }

    /**
     * Test 6: validation ws: error
     *
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws invalid_persistent_exception
     * @covers \mod_certifygen\external\get_pdf_teacher_certificate_external::get_pdf_teacher_certificate
     */
    public function test_6(): void {
        global $DB;

        // Create user.
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $this->setUser($manager);

        // Create users.
        $student = $this->getDataGenerator()->create_user([
                'username' => 'test_student_1', 'firstname' => 'test',
                'lastname' => 'student 1', 'email' => 'test_student_1@fake.es',
        ]);
        $teacher = $this->getDataGenerator()->create_user([
                'username' => 'test_teacher_1', 'firstname' => 'test',
                'lastname' => 'teacher 1', 'email' => 'test_teacher_1@fake.es',
                'idnumber' => 'test_teacher_1',
        ]);
        // Create courses.
        $course1 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as editingteacher.
        self::getDataGenerator()->enrol_user($student->id, $course1->id, 'student');
        self::getDataGenerator()->enrol_user($teacher->id, $course1->id, 'editingteacher');

        // Create model.
        set_config('enabled', 1, 'certifygenvalidation_webservice');
        set_config('enabled', 1, 'certifygenrepository_localrepository');
        set_config('enabled', 1, 'certifygenreport_basic');
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model(
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
            certifygen_model::MODE_UNIQUE,
            0,
            'certifygenvalidation_webservice',
            'certifygenreport_basic',
        );
        $modgenerator->assign_model_coursecontext($model->get('id'), $course1->id);
        $langs = $model->get('langs');
        $langs = explode(',', $langs);
        $lang = $langs[1];
        $data = [
                'userid' => $teacher->id,
                'modelid' => $model->get('id'),
        ];
        $validation = certifygen_validations::get_record($data);
        self::assertFalse($validation);

        // Create teacherrequest.
        $this->setUser($teacher);
        $teacherrequest = $modgenerator->create_teacher_request($model->get('id'), $course1->id, $teacher->id);
        self::assertEquals(certifygen_validations::STATUS_NOT_STARTED, $teacherrequest->get('status'));

        // Emit ws.
        emitteacherrequest_external::emitteacherrequest($teacherrequest->get('id'));
        $teacherrequest = new certifygen_validations($teacherrequest->get('id'));
        self::assertEquals(certifygen_validations::STATUS_IN_PROGRESS, $teacherrequest->get('status'));

        // Obtenemos el pdf.
        $this->setUser($manager);
        $result = get_pdf_teacher_certificate_external::get_pdf_teacher_certificate(
            $teacher->id,
            '',
            $teacherrequest->get('name'),
            (string)$course1->id,
            $model->get('id'),
            $lang
        );
        $teacherrequest = new certifygen_validations($teacherrequest->get('id'));

        // Tests.
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertArrayHasKey('message', $result['error']);
        $this->assertArrayHasKey('code', $result['error']);
        $this->assertEquals(
            get_string('certificate_not_ready', 'mod_certifygen', $teacherrequest->get('status')),
            $result['error']['message']
        );
        $this->assertEquals('certificate_not_ready', $result['error']['code']);
    }

    /**
     * Test 7: validation ws: ok
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws invalid_persistent_exception
     * @covers \mod_certifygen\external\get_pdf_teacher_certificate_external::get_pdf_teacher_certificate
     */
    public function test_7(): void {
        global $DB;

        // Create user.
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $this->setUser($manager);

        // Create users.
        $student = $this->getDataGenerator()->create_user([
                'username' => 'test_student_1', 'firstname' => 'test',
                'lastname' => 'student 1', 'email' => 'test_student_1@fake.es',
        ]);
        $teacher = $this->getDataGenerator()->create_user([
                'username' => 'test_teacher_1', 'firstname' => 'test',
                'lastname' => 'teacher 1', 'email' => 'test_teacher_1@fake.es',
                'idnumber' => 'test_teacher_1',
        ]);
        // Create courses.
        $course1 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as editingteacher.
        self::getDataGenerator()->enrol_user($student->id, $course1->id, 'student');
        self::getDataGenerator()->enrol_user($teacher->id, $course1->id, 'editingteacher');

        // Create model.
        set_config('enabled', 1, 'certifygenvalidation_webservice');
        set_config('enabled', 1, 'certifygenrepository_localrepository');
        set_config('enabled', 1, 'certifygenreport_basic');
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model(
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
            certifygen_model::MODE_UNIQUE,
            0,
            'certifygenvalidation_webservice',
            'certifygenreport_basic',
        );
        $modgenerator->assign_model_coursecontext($model->get('id'), $course1->id);
        $langs = $model->get('langs');
        $langs = explode(',', $langs);
        $lang = $langs[1];
        $data = [
                'userid' => $teacher->id,
                'modelid' => $model->get('id'),
        ];
        $teacherrequest = certifygen_validations::get_record($data);
        self::assertFalse($teacherrequest);

        // Create teacherrequest.
        $this->setUser($teacher);
        $teacherrequest = $modgenerator->create_teacher_request($model->get('id'), $course1->id, $teacher->id);
        self::assertEquals(certifygen_validations::STATUS_NOT_STARTED, $teacherrequest->get('status'));

        // Emit ws.
        emitteacherrequest_external::emitteacherrequest($teacherrequest->get('id'));
        $teacherrequest = new certifygen_validations($teacherrequest->get('id'));
        self::assertEquals(certifygen_validations::STATUS_IN_PROGRESS, $teacherrequest->get('status'));

        // Obtenemos el pdf.
        $this->setUser($manager);

        $teacherrequest = new certifygen_validations($teacherrequest->get('id'));
        change_status_external::change_status(
            $teacher->id,
            '',
            $teacherrequest->get('id')
        );
        $teacherrequest = new certifygen_validations($teacherrequest->get('id'));
        self::assertEquals(certifygen_validations::STATUS_VALIDATION_OK, (int)$teacherrequest->get('status'));

        // Execute task.
        $removaltask = new checkfile();
        $removaltask->execute();
        $result = get_pdf_teacher_certificate_external::get_pdf_teacher_certificate(
            $teacher->id,
            '',
            $teacherrequest->get('name'),
            (string)$course1->id,
            $model->get('id'),
            $lang
        );

        // Tests.
        $this->assertIsArray($result);
        $this->assertArrayHasKey('certificate', $result);
        $this->assertArrayHasKey('validationid', $result['certificate']);
        $this->assertArrayHasKey('status', $result['certificate']);
        $this->assertArrayHasKey('statusstr', $result['certificate']);
        $this->assertArrayHasKey('file', $result['certificate']);
        $this->assertArrayHasKey('reporttype', $result['certificate']);
        $this->assertArrayHasKey('reporttypestr', $result['certificate']);

        $this->assertEquals($teacherrequest->get('id'), $result['certificate']['validationid']);
        self::assertEquals(certifygen_validations::STATUS_FINISHED, $result['certificate']['status']);
        $this->assertEquals(certifygen_model::TYPE_TEACHER_ALL_COURSES_USED, $result['certificate']['reporttype']);
    }
}
