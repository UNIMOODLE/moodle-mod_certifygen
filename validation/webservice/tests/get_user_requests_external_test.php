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

use certifygenvalidation_webservice\external\get_user_requests_external;
use core\invalid_persistent_exception;
use mod_certifygen\external\emitcertificate_external;
use mod_certifygen\external\emitteacherrequest_external;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/admin/tool/certificate/tests/generator/lib.php');
require_once($CFG->dirroot . '/mod/certifygen/tests/generator/lib.php');
/**
 * get_user_requests_external_test
 * @package   certifygenvalidation_webservice
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_user_requests_external_test extends \advanced_testcase {
    /**
     * Test set up.
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test 1
     * @covers \certifygenvalidation_webservice\external\get_user_requests_external::get_user_requests
     * @return void
     * @throws \coding_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_persistent_exception
     */
    public function test_1(): void {
        // Create course.
        $course = self::getDataGenerator()->create_course();

        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create model.
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $data = [
            'name' => 'model1',
            'idnumber' => '',
            'type' => certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
            'mode' => certifygen_model::MODE_UNIQUE,
            'templateid' => $certificate1->get_id(),
            'timeondemmand' => 0,
            'langs' => 'en',
            'validation' => 'certifygenvalidation_webservice',
            'report' => 'certifygenreport_basic',
            'repository' => 'certifygenrepository_localrepository',
        ];
        $model = new certifygen_model(0, (object)$data);
        $model = $model->create();
        $modgenerator->assign_model_coursecontext($model->get('id'), $course->id);

        // Create user and enrol as teacher.
        $teacher = $this->getDataGenerator()->create_user([
                'username' => 'test_user_1',
                'firstname' => 'test',
                'lastname' => 'user 1',
                'email' => 'test_user_1@fake.es',
                ]);
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $student = $this->getDataGenerator()->create_user([
                'username' => 'test_user_2',
                'firstname' => 'test',
                'lastname' => 'user 2',
                'email' => 'test_user_2@fake.es',
                ]);
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        // Create request.
        $teacherrequest = $modgenerator->create_teacher_request($model->get('id'), $course->id, $teacher->id);
        self::assertEquals(certifygen_validations::STATUS_NOT_STARTED, $teacherrequest->get('status'));

        // Validate.
        $this->setAdminUser();
        $result = get_user_requests_external::get_user_requests(
            $teacher->id,
            '',
            'en'
        );

        // Tests.
        self::assertIsArray($result);
        self::assertArrayHasKey('error', $result);
        self::assertIsArray($result['error']);
        self::assertArrayHasKey('code', $result['error']);
        self::assertArrayHasKey('message', $result['error']);
        self::assertEquals('pluginnotenabled', $result['error']['code']);
    }

    /**
     * Test 2
     *
     * @covers \certifygenvalidation_webservice\external\get_user_requests_external::get_user_requests
     * @return void
     * @throws \coding_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_persistent_exception
     */
    public function test_2(): void {
        // Create course.
        $course = self::getDataGenerator()->create_course();

        // Configure the platform.
        set_config('enabled', 1, 'certifygenvalidation_webservice');
        set_config('enabled', 1, 'certifygenreport_basic');
        set_config('enabled', 1, 'certifygenrepository_localrepository');
        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create model.
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $data = [
            'name' => 'model1',
            'idnumber' => '',
            'type' => certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
            'mode' => certifygen_model::MODE_UNIQUE,
            'templateid' => $certificate1->get_id(),
            'timeondemmand' => 0,
            'langs' => 'en',
            'validation' => 'certifygenvalidation_webservice',
            'report' => 'certifygenreport_basic',
            'repository' => 'certifygenrepository_localrepository',
        ];
        $model = new certifygen_model(0, (object)$data);
        $model = $model->create();
        $modgenerator->assign_model_coursecontext($model->get('id'), $course->id);

        // Create user and enrol as teacher.
        $teacher = $this->getDataGenerator()->create_user([
                'username' => 'test_user_1',
                'firstname' => 'test',
                'lastname' => 'user 1',
                'email' => 'test_user_1@fake.es',
                ]);
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $student = $this->getDataGenerator()->create_user([
                'username' => 'test_user_2',
                'firstname' => 'test',
                'lastname' => 'user 2',
                'email' => 'test_user_2@fake.es',
                ]);
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');
        $this->setUser($teacher);

        // Create request.
        $teacherrequest = $modgenerator->create_teacher_request($model->get('id'), $course->id, $teacher->id);
        self::assertEquals(certifygen_validations::STATUS_NOT_STARTED, $teacherrequest->get('status'));

        // Validate.
        $this->setAdminUser();
        $result = get_user_requests_external::get_user_requests(
            $teacher->id,
            '',
            'en'
        );

        // Tests.
        self::assertIsArray($result);
        self::assertArrayHasKey('requests', $result);
        self::assertIsArray($result['requests']);
        self::assertEmpty($result['requests']);
    }

    /**
     * Test 3
     *
     * @covers \certifygenvalidation_webservice\external\get_user_requests_external::get_user_requests
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_persistent_exception
     */
    public function test_3(): void {
        // Create course.
        $course = self::getDataGenerator()->create_course();

        // Configure the platform.
        set_config('enabled', 1, 'certifygenvalidation_webservice');
        set_config('enabled', 1, 'certifygenreport_basic');
        set_config('enabled', 1, 'certifygenrepository_localrepository');

        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create model.
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $data = [
            'type' => certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
            'mode' => certifygen_model::MODE_UNIQUE,
            'templateid' => $certificate1->get_id(),
            'validation' => 'certifygenvalidation_webservice',
            'report' => 'certifygenreport_basic',
        ];
        $model = $modgenerator->create_model(
            $data['type'],
            $data['mode'],
            $data['templateid'],
            $data['validation'],
            $data['report'],
        );
        $modgenerator->assign_model_coursecontext($model->get('id'), $course->id);

        // Create user and enrol as teacher.
        $teacher = $this->getDataGenerator()->create_user([
                'username' => 'test_user_1',
                'firstname' => 'test',
                'lastname' => 'user 1',
                'email' => 'test_user_1@fake.es',
            ]);
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $student = $this->getDataGenerator()->create_user(
            ['username' => 'test_user_2', 'firstname' => 'test',
            'lastname' => 'user 2', 'email' => 'test_user_2@fake.es']
        );
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        // Create request.
        $this->setUser($teacher);
        $teacherrequest = $modgenerator->create_teacher_request($model->get('id'), $course->id, $teacher->id);
        self::assertEquals(certifygen_validations::STATUS_NOT_STARTED, $teacherrequest->get('status'));

        // Emit certificate.
        emitteacherrequest_external::emitteacherrequest($teacherrequest->get('id'));
        $teacherrequest = new certifygen_validations($teacherrequest->get('id'));
        self::assertEquals(certifygen_validations::STATUS_IN_PROGRESS, $teacherrequest->get('status'));

        // Validate.
        $this->setAdminUser();
        $result = get_user_requests_external::get_user_requests($teacher->id, '', 'en');
        self::assertIsArray($result);
        self::assertArrayHasKey('requests', $result);
        self::assertIsArray($result['requests']);

        self::assertArrayHasKey('id', $result['requests'][0]);
        self::assertArrayHasKey('name', $result['requests'][0]);
        self::assertArrayHasKey('code', $result['requests'][0]);
        self::assertArrayHasKey('lang', $result['requests'][0]);
        self::assertArrayHasKey('status', $result['requests'][0]);
        self::assertArrayHasKey('statusdesc', $result['requests'][0]);
        self::assertArrayHasKey('courses', $result['requests'][0]);
        self::assertIsArray($result['requests'][0]['courses']);
        self::assertArrayHasKey('id', $result['requests'][0]['courses'][0]);
        self::assertArrayHasKey('fullname', $result['requests'][0]['courses'][0]);
        self::assertArrayHasKey('shortname', $result['requests'][0]['courses'][0]);
        self::assertArrayHasKey('model', $result['requests'][0]);
        self::assertIsArray($result['requests'][0]['model']);
        self::assertArrayHasKey('id', $result['requests'][0]['model']);
        self::assertArrayHasKey('name', $result['requests'][0]['model']);
        self::assertArrayHasKey('idnumber', $result['requests'][0]['model']);
        self::assertArrayHasKey('type', $result['requests'][0]['model']);
        self::assertArrayHasKey('typedesc', $result['requests'][0]['model']);
        self::assertArrayHasKey('mode', $result['requests'][0]['model']);
        self::assertArrayHasKey('templateid', $result['requests'][0]['model']);
        self::assertArrayHasKey('timeondemmand', $result['requests'][0]['model']);
        self::assertArrayHasKey('langs', $result['requests'][0]['model']);
        self::assertArrayHasKey('validation', $result['requests'][0]['model']);
        self::assertArrayHasKey('report', $result['requests'][0]['model']);
        self::assertArrayHasKey('repository', $result['requests'][0]['model']);
    }

    /**
     * Test 4
     *
     * @covers \certifygenvalidation_webservice\external\get_user_requests_external::get_user_requests
     * @return void
     * @throws \coding_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     * @throws \invalid_persistent_exception
     */
    public function test_4(): void {

        // Configure the platform.
        set_config('enabled', 1, 'certifygenvalidation_webservice');
        set_config('enabled', 1, 'certifygenrepository_localrepository');

        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create course.
        $course = self::getDataGenerator()->create_course();

        // Create model.
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $data = [
            'name' => 'model1',
            'idnumber' => '',
            'type' => certifygen_model::TYPE_ACTIVITY,
            'mode' => certifygen_model::MODE_UNIQUE,
            'templateid' => $certificate1->get_id(),
            'timeondemmand' => 0,
            'langs' => 'en',
            'validation' => 'certifygenvalidation_webservice',
            'report' => '',
            'repository' => 'certifygenrepository_localrepository',
        ];
        $model = new certifygen_model(0, (object)$data);
        $model = $model->create();
        $modgenerator->assign_model_coursecontext($model->get('id'), $course->id);

        $langs = $model->get('langs');
        $langs = explode(',', $langs);
        $lang = $langs[0];

        // Create mod_certifygen module.
        $datamodule = [
            'name' => 'Test 1,',
            'course' => $course->id,
            'modelid' => $model->get('id'),
        ];
        $modcertifygen = self::getDataGenerator()->create_module('certifygen', $datamodule);
        $cm = get_coursemodule_from_instance('certifygen', $modcertifygen->id, $course->id, false, MUST_EXIST);

        // Create users.
        $student = $this->getDataGenerator()->create_user(
            ['username' => 'test_user_1', 'firstname' => 'test',
            'lastname' => 'user 1', 'email' => 'test_user_1@fake.es']
        );

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

        // Validate.
        $this->setAdminUser();
        $result = get_user_requests_external::get_user_requests($student->id, '', 'en');
        self::assertIsArray($result);
        self::assertArrayHasKey('requests', $result);
        self::assertIsArray($result['requests']);
        self::assertArrayHasKey('id', $result['requests'][0]);
        self::assertArrayNotHasKey('name', $result['requests'][0]);
        self::assertArrayHasKey('code', $result['requests'][0]);
        self::assertArrayHasKey('lang', $result['requests'][0]);
        self::assertArrayHasKey('status', $result['requests'][0]);
        self::assertArrayHasKey('statusdesc', $result['requests'][0]);
        self::assertArrayNotHasKey('courses', $result['requests'][0]);
        self::assertArrayHasKey('model', $result['requests'][0]);
        self::assertIsArray($result['requests'][0]['model']);
        self::assertArrayHasKey('id', $result['requests'][0]['model']);
        self::assertArrayHasKey('name', $result['requests'][0]['model']);
        self::assertArrayHasKey('idnumber', $result['requests'][0]['model']);
        self::assertArrayHasKey('type', $result['requests'][0]['model']);
        self::assertArrayHasKey('typedesc', $result['requests'][0]['model']);
        self::assertArrayHasKey('mode', $result['requests'][0]['model']);
        self::assertArrayHasKey('templateid', $result['requests'][0]['model']);
        self::assertArrayHasKey('timeondemmand', $result['requests'][0]['model']);
        self::assertArrayHasKey('langs', $result['requests'][0]['model']);
        self::assertArrayHasKey('validation', $result['requests'][0]['model']);
        self::assertArrayHasKey('report', $result['requests'][0]['model']);
        self::assertArrayHasKey('repository', $result['requests'][0]['model']);
    }

    /**
     * Test 5: userfield ok
     *
     * @covers \certifygenvalidation_webservice\external\get_user_requests_external::get_user_requests
     * @return void
     * @throws \coding_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     * @throws \invalid_persistent_exception
     */
    public function test_5(): void {

        // Configure the platform.
        set_config('enabled', 1, 'certifygenvalidation_webservice');
        set_config('enabled', 1, 'certifygenrepository_localrepository');

        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create course.
        $course = self::getDataGenerator()->create_course();

        // Create model.
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $data = [
            'name' => 'model1',
            'idnumber' => '',
            'type' => certifygen_model::TYPE_ACTIVITY,
            'mode' => certifygen_model::MODE_UNIQUE,
            'templateid' => $certificate1->get_id(),
            'timeondemmand' => 0,
            'langs' => 'en',
            'validation' => 'certifygenvalidation_webservice',
            'report' => '',
            'repository' => 'certifygenrepository_localrepository',
        ];
        $model = new certifygen_model(0, (object)$data);
        $model = $model->create();
        $modgenerator->assign_model_coursecontext($model->get('id'), $course->id);

        $langs = $model->get('langs');
        $langs = explode(',', $langs);
        $lang = $langs[0];

        // Create mod_certifygen module.
        $datamodule = [
            'name' => 'Test 1,',
            'course' => $course->id,
            'modelid' => $model->get('id'),
        ];
        $modcertifygen = self::getDataGenerator()->create_module('certifygen', $datamodule);
        $cm = get_coursemodule_from_instance('certifygen', $modcertifygen->id, $course->id, false, MUST_EXIST);

        // Create user profile fields.
        $category = self::getDataGenerator()->create_custom_profile_field_category(['name' => 'Category 1']);
        $field = self::getDataGenerator()->create_custom_profile_field([
                'shortname' => 'DNI',
                'name' => 'DNI',
                'categoryid' => $category->id,
                'required' => 1,
                'visible' => 1,
                'locked' => 0,
                'datatype' => 'text',
                'defaultdata' => null,
        ]);

        // Configure the platform.
        set_config('userfield', 'profile_' . $field->id, 'mod_certifygen');
        $dni = '123456789P';

        // Create users.
        $student = $this->getDataGenerator()->create_user(
            ['username' => 'test_user_1', 'firstname' => 'test',
            'lastname' => 'user 1', 'email' => 'test_user_1@fake.es',
            'profile_field_DNI' => $dni,
            ]
        );

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

        // Validate.
        $this->setAdminUser();
        $result = get_user_requests_external::get_user_requests(0, $dni, 'en');
        self::assertIsArray($result);
        self::assertArrayHasKey('requests', $result);
        self::assertIsArray($result['requests']);
        self::assertArrayHasKey('id', $result['requests'][0]);
        self::assertArrayNotHasKey('name', $result['requests'][0]);
        self::assertArrayHasKey('code', $result['requests'][0]);
        self::assertArrayHasKey('lang', $result['requests'][0]);
        self::assertArrayHasKey('status', $result['requests'][0]);
        self::assertArrayHasKey('statusdesc', $result['requests'][0]);
        self::assertArrayNotHasKey('courses', $result['requests'][0]);
        self::assertArrayHasKey('model', $result['requests'][0]);
        self::assertIsArray($result['requests'][0]['model']);
        self::assertArrayHasKey('id', $result['requests'][0]['model']);
        self::assertArrayHasKey('name', $result['requests'][0]['model']);
        self::assertArrayHasKey('idnumber', $result['requests'][0]['model']);
        self::assertArrayHasKey('type', $result['requests'][0]['model']);
        self::assertArrayHasKey('typedesc', $result['requests'][0]['model']);
        self::assertArrayHasKey('mode', $result['requests'][0]['model']);
        self::assertArrayHasKey('templateid', $result['requests'][0]['model']);
        self::assertArrayHasKey('timeondemmand', $result['requests'][0]['model']);
        self::assertArrayHasKey('langs', $result['requests'][0]['model']);
        self::assertArrayHasKey('validation', $result['requests'][0]['model']);
        self::assertArrayHasKey('report', $result['requests'][0]['model']);
        self::assertArrayHasKey('repository', $result['requests'][0]['model']);
    }

    /**
     * Test 6: userfield KO
     *
     * @covers \certifygenvalidation_webservice\external\get_user_requests_external::get_user_requests
     * @return void
     * @throws \coding_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     * @throws \invalid_persistent_exception
     */
    public function test_6(): void {

        // Configure the platform.
        set_config('enabled', 1, 'certifygenvalidation_webservice');
        set_config('enabled', 1, 'certifygenrepository_localrepository');

        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create course.
        $course = self::getDataGenerator()->create_course();

        // Create model.
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $data = [
                'name' => 'model1',
                'idnumber' => '',
                'type' => certifygen_model::TYPE_ACTIVITY,
                'mode' => certifygen_model::MODE_UNIQUE,
                'templateid' => $certificate1->get_id(),
                'timeondemmand' => 0,
                'langs' => 'en',
                'validation' => 'certifygenvalidation_webservice',
                'report' => '',
                'repository' => 'certifygenrepository_localrepository',
        ];
        $model = new certifygen_model(0, (object)$data);
        $model = $model->create();
        $modgenerator->assign_model_coursecontext($model->get('id'), $course->id);

        $langs = $model->get('langs');
        $langs = explode(',', $langs);
        $lang = $langs[0];

        // Create mod_certifygen module.
        $datamodule = [
                'name' => 'Test 1,',
                'course' => $course->id,
                'modelid' => $model->get('id'),
        ];
        $modcertifygen = self::getDataGenerator()->create_module('certifygen', $datamodule);
        $cm = get_coursemodule_from_instance('certifygen', $modcertifygen->id, $course->id, false, MUST_EXIST);

        // Create user profile fields.
        $category = self::getDataGenerator()->create_custom_profile_field_category(['name' => 'Category 1']);
        $field = self::getDataGenerator()->create_custom_profile_field([
                'shortname' => 'DNI',
                'name' => 'DNI',
                'categoryid' => $category->id,
                'required' => 1,
                'visible' => 1,
                'locked' => 0,
                'datatype' => 'text',
                'defaultdata' => null,
        ]);

        // Configure the platform.
        set_config('userfield', 'profile_' . $field->id, 'mod_certifygen');
        $dni = '123456789P';

        // Create users.
        $student = $this->getDataGenerator()->create_user(
            [
            'username' => 'test_user_1', 'firstname' => 'test',
            'lastname' => 'user 1', 'email' => 'test_user_1@fake.es',
            'profile_field_DNI' => $dni,
            ]
        );

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

        // Validate.
        $this->setAdminUser();
        $result = get_user_requests_external::get_user_requests($student->id - 1, $dni, 'en');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertIsArray($result['error']);
        $this->assertArrayHasKey('code', $result['error']);
        $this->assertEquals('userfield_and_userid_sent', $result['error']['code']);
    }
}
