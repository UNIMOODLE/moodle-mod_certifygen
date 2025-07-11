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
use certifygenvalidation_webservice\external\change_status_external;
use certifygenvalidation_webservice\external\get_draft_teacher_certificate_external;
use certifygenvalidation_webservice\external\start_teacher_certificate_external;
use core\invalid_persistent_exception;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/admin/tool/certificate/tests/generator/lib.php');
require_once($CFG->dirroot . '/mod/certifygen/tests/generator/lib.php');
require_once($CFG->dirroot . '/lib/externallib.php');

/**
 * change_status_external_test
 * @package   certifygenvalidation_webservice
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class change_status_external_test extends \advanced_testcase {
    /**
     * Test set up.
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test
     * @return void
     * @throws \coding_exception
     * @throws \invalid_parameter_exception
     * @covers \certifygenvalidation_webservice\external\change_status_external::change_status
     */
    public function test_1(): void {

        // Create course.
        $course = self::getDataGenerator()->create_course();

        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create model.
        set_config('enabled', 1, 'certifygenvalidation_webservice');
        set_config('enabled', 1, 'certifygenreport_basic');
        set_config('enabled', 1, 'certifygenrepository_localrepository');
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $data = [
            'type' => certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
            'mode' => certifygen_model::MODE_UNIQUE,
            'templateid' => $certificate1->get_id(),
            'report' => 'certifygenreport_basic',
        ];
        $model = $modgenerator->create_model_for_ws(
            $data['type'],
            $data['mode'],
            $data['templateid'],
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
        $student = $this->getDataGenerator()->create_user([
                'username' => 'test_user_2',
                'firstname' => 'test',
                'lastname' => 'user 2',
                'email' => 'test_user_2@fake.es',
                ]);
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        // Create request.
        $this->setAdminUser();
        $startresult = start_teacher_certificate_external::start_teacher_certificate(
            $teacher->id,
            '',
            'request_1',
            $course->id,
            $model->get('id'),
            'en'
        );
        self::assertIsArray($startresult);
        self::assertArrayHasKey('certificate', $startresult);
        self::assertIsArray($startresult['certificate']);
        self::assertArrayHasKey('validationid', $startresult['certificate']);
        self::assertArrayHasKey('status', $startresult['certificate']);
        self::assertArrayHasKey('statusstr', $startresult['certificate']);
        self::assertArrayHasKey('reporttype', $startresult['certificate']);
        self::assertArrayHasKey('reporttypestr', $startresult['certificate']);
        self::assertEquals(certifygen_validations::STATUS_IN_PROGRESS, $startresult['certificate']['status']);

        // Get draft.
        get_draft_teacher_certificate_external::get_draft_teacher_certificate(
            $startresult['certificate']['validationid'],
            $teacher->id,
            '',
            'Requesti 1',
                "$course->id",
            $model->get('id'),
            'en'
        );
        // Validate.
        $result = change_status_external::change_status(
            $teacher->id,
            '',
            $startresult['certificate']['validationid'],
            'http://www.google.es'
        );

        // Tests.
        self::assertIsArray($result);
        self::assertArrayHasKey('requestid', $result);
        self::assertArrayHasKey('newstatus', $result);
        self::assertArrayHasKey('newstatusdesc', $result);
        self::assertEquals($startresult['certificate']['validationid'], $result['requestid']);
        self::assertEquals(certifygen_validations::STATUS_FINISHED, $result['newstatus']);
        self::assertEquals(
            get_string('status_' . certifygen_validations::STATUS_FINISHED, 'mod_certifygen'),
            $result['newstatusdesc']
        );
    }

    /**
     * Test
     *
     * @return void
     * @throws coding_exception
     * @throws invalid_persistent_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @covers \certifygenvalidation_webservice\external\change_status_external::change_status
     */
    public function test_2(): void {

        // Create course.
        $course = self::getDataGenerator()->create_course();

        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create model.
        set_config('enabled', 1, 'certifygenvalidation_webservice');
        set_config('enabled', 1, 'certifygenreport_basic');
        set_config('enabled', 1, 'certifygenrepository_localrepository');
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $data = [
                'type' => certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
                'mode' => certifygen_model::MODE_UNIQUE,
                'templateid' => $certificate1->get_id(),
                'report' => 'certifygenreport_basic',
        ];
        $model = $modgenerator->create_model_for_ws(
            $data['type'],
            $data['mode'],
            $data['templateid'],
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
        $student = $this->getDataGenerator()->create_user([
                'username' => 'test_user_2',
                'firstname' => 'test',
                'lastname' => 'user 2',
                'email' => 'test_user_2@fake.es',
                ]);
        $this->setUser($teacher);
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        // Emit certificate.
        $this->setAdminUser();
        $result = start_teacher_certificate_external::start_teacher_certificate(
            $teacher->id,
            '',
            'request_1',
            $course->id,
            $model->get('id'),
            'en'
        );
        self::assertIsArray($result);
        self::assertArrayHasKey('certificate', $result);
        self::assertIsArray($result['certificate']);
        self::assertArrayHasKey('validationid', $result['certificate']);
        self::assertArrayHasKey('status', $result['certificate']);
        self::assertArrayHasKey('statusstr', $result['certificate']);
        self::assertArrayHasKey('reporttype', $result['certificate']);
        self::assertArrayHasKey('reporttypestr', $result['certificate']);
        self::assertEquals(certifygen_validations::STATUS_IN_PROGRESS, $result['certificate']['status']);

        // Validate.
        $result = change_status_external::change_status(
            $student->id,
            '',
            $result['certificate']['validationid'],
            'http://www.google.es'
        );

        // Tests.
        self::assertIsArray($result);
        self::assertArrayHasKey('error', $result);
        self::assertIsArray($result['error']);
        self::assertArrayHasKey('code', $result['error']);
        self::assertArrayHasKey('message', $result['error']);
        self::assertEquals('request_user_not_matched', $result['error']['code']);
    }

    /**
     * Test
     *
     * @return void
     * @throws coding_exception
     * @throws invalid_parameter_exception
     * @covers \certifygenvalidation_webservice\external\change_status_external::change_status
     */
    public function test_3(): void {

        // Create course.
        $course = self::getDataGenerator()->create_course();

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
        $this->setAdminUser();
        // Validate.
        $result = change_status_external::change_status(
            $student->id,
            '',
            9999,
            'http://www.gooogle.es'
        );

        // Tests.
        self::assertIsArray($result);
        self::assertArrayHasKey('error', $result);
        self::assertIsArray($result['error']);
        self::assertArrayHasKey('code', $result['error']);
        self::assertArrayHasKey('message', $result['error']);
        self::assertEquals('request_not_found', $result['error']['code']);
    }
    /**
     * Test 4 : userfield OK
     *
     * @return void
     * @throws invalid_persistent_exception
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @covers \certifygenvalidation_webservice\external\change_status_external::change_status
     */
    public function test_4(): void {

        // Create course.
        $course = self::getDataGenerator()->create_course();

        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create model.
        set_config('enabled', 1, 'certifygenvalidation_webservice');
        set_config('enabled', 1, 'certifygenreport_basic');
        set_config('enabled', 1, 'certifygenrepository_localrepository');
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $data = [
                'type' => certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
                'mode' => certifygen_model::MODE_UNIQUE,
                'templateid' => $certificate1->get_id(),
                'report' => 'certifygenreport_basic',
        ];
        $model = $modgenerator->create_model_for_ws(
            $data['type'],
            $data['mode'],
            $data['templateid'],
            $data['report'],
        );
        $modgenerator->assign_model_coursecontext($model->get('id'), $course->id);

        // Create user and enrol as teacher.
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
        $teacher = $this->getDataGenerator()->create_user([
                'username' => 'test_user_1',
                'firstname' => 'test',
                'lastname' => 'user 1',
                'email' => 'test_user_1@fake.es',
                'profile_field_DNI' => $dni,
        ]);
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $student = $this->getDataGenerator()->create_user([
                'username' => 'test_user_2',
                'firstname' => 'test',
                'lastname' => 'user 2',
                'email' => 'test_user_2@fake.es',
        ]);
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        // Emit certificate.
        $this->setAdminUser();
        $startresult = start_teacher_certificate_external::start_teacher_certificate(
            $teacher->id,
            '',
            'request_1',
            $course->id,
            $model->get('id'),
            'en'
        );
        self::assertIsArray($startresult);
        self::assertArrayHasKey('certificate', $startresult);
        self::assertIsArray($startresult['certificate']);
        self::assertArrayHasKey('validationid', $startresult['certificate']);
        self::assertArrayHasKey('status', $startresult['certificate']);
        self::assertArrayHasKey('statusstr', $startresult['certificate']);
        self::assertArrayHasKey('reporttype', $startresult['certificate']);
        self::assertArrayHasKey('reporttypestr', $startresult['certificate']);
        self::assertEquals(certifygen_validations::STATUS_IN_PROGRESS, $startresult['certificate']['status']);

        // Validate.
        $result = change_status_external::change_status(
            $teacher->id,
            '',
            $startresult['certificate']['validationid'],
            'http://www.google.es'
        );

        // Tests.
        self::assertIsArray($result);
        self::assertArrayHasKey('error', $result);
        self::assertArrayHasKey('code', $result['error']);
        self::assertArrayHasKey('message', $result['error']);
        self::assertEquals('request_status_not_accepted', $result['error']['code']);
    }
    /**
     * Test 5 : userfield OK
     *
     * @return void
     * @throws invalid_persistent_exception
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @covers \certifygenvalidation_webservice\external\change_status_external::change_status
     */
    public function test_5(): void {

        // Create course.
        $course = self::getDataGenerator()->create_course();

        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create model.
        set_config('enabled', 1, 'certifygenvalidation_webservice');
        set_config('enabled', 1, 'certifygenreport_basic');
        set_config('enabled', 1, 'certifygenrepository_localrepository');
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $data = [
                'type' => certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
                'mode' => certifygen_model::MODE_UNIQUE,
                'templateid' => $certificate1->get_id(),
                'report' => 'certifygenreport_basic',
        ];
        $model = $modgenerator->create_model_for_ws(
            $data['type'],
            $data['mode'],
            $data['templateid'],
            $data['report'],
        );
        $modgenerator->assign_model_coursecontext($model->get('id'), $course->id);

        // Create user and enrol as teacher.
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
        $teacher = $this->getDataGenerator()->create_user([
                'username' => 'test_user_1',
                'firstname' => 'test',
                'lastname' => 'user 1',
                'email' => 'test_user_1@fake.es',
                'profile_field_DNI' => $dni,
        ]);
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $student = $this->getDataGenerator()->create_user([
                'username' => 'test_user_2',
                'firstname' => 'test',
                'lastname' => 'user 2',
                'email' => 'test_user_2@fake.es',
        ]);
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        // Emit certificate.
        $this->setAdminUser();
        $startresult = start_teacher_certificate_external::start_teacher_certificate(
            $teacher->id,
            '',
            'request_1',
            $course->id,
            $model->get('id'),
            'en'
        );
        self::assertIsArray($startresult);
        self::assertArrayHasKey('certificate', $startresult);
        self::assertIsArray($startresult['certificate']);
        self::assertArrayHasKey('validationid', $startresult['certificate']);
        self::assertArrayHasKey('status', $startresult['certificate']);
        self::assertArrayHasKey('statusstr', $startresult['certificate']);
        self::assertArrayHasKey('reporttype', $startresult['certificate']);
        self::assertArrayHasKey('reporttypestr', $startresult['certificate']);
        self::assertEquals(certifygen_validations::STATUS_IN_PROGRESS, $startresult['certificate']['status']);

        // Get draft.
        get_draft_teacher_certificate_external::get_draft_teacher_certificate(
            $startresult['certificate']['validationid'],
            $teacher->id,
            '',
            'Requesti 1',
            "$course->id",
            $model->get('id'),
            'en'
        );

        // Validate.
        $result = change_status_external::change_status(
            $teacher->id,
            '',
            $startresult['certificate']['validationid'],
            'http://www.google.es'
        );

        // Tests.
        self::assertIsArray($result);
        self::assertArrayHasKey('requestid', $result);
        self::assertArrayHasKey('newstatus', $result);
        self::assertArrayHasKey('newstatusdesc', $result);
        self::assertEquals($startresult['certificate']['validationid'], $result['requestid']);
        self::assertEquals(certifygen_validations::STATUS_FINISHED, $result['newstatus']);
        self::assertEquals(
            get_string('status_' . certifygen_validations::STATUS_FINISHED, 'mod_certifygen'),
            $result['newstatusdesc']
        );
    }

    /**
     * Test 6 : validation plugin not valid
     *
     * @return void
     * @throws invalid_persistent_exception
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @covers \certifygenvalidation_webservice\external\change_status_external::change_status
     */
    public function test_6(): void {
        $this->setAdminUser();
        // Create course.
        $course = self::getDataGenerator()->create_course();

        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create model.
        set_config('enabled', 1, 'certifygenvalidation_webservice');
        set_config('enabled', 1, 'certifygenreport_basic');
        set_config('enabled', 1, 'certifygenrepository_localrepository');
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $data = [
                'type' => certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
                'mode' => certifygen_model::MODE_UNIQUE,
                'templateid' => $certificate1->get_id(),
                'report' => 'certifygenreport_basic',
        ];
        $model = $modgenerator->create_model(
            $data['type'],
            $data['mode'],
            $data['templateid'],
            'certifygenvalidation_local',
            $data['report']
        );
        $modgenerator->assign_model_coursecontext($model->get('id'), $course->id);

        // Create user and enrol as teacher.
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
        $teacher = $this->getDataGenerator()->create_user([
                'username' => 'test_user_1',
                'firstname' => 'test',
                'lastname' => 'user 1',
                'email' => 'test_user_1@fake.es',
                'profile_field_DNI' => $dni,
        ]);
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $student = $this->getDataGenerator()->create_user([
                'username' => 'test_user_2',
                'firstname' => 'test',
                'lastname' => 'user 2',
                'email' => 'test_user_2@fake.es',
        ]);
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        // Create a validation.
        $data = [
            'name' => 'request_29042025_00',
            'courses' => $course->id,
            'code' => 'TRXXXX',
            'userid' => $teacher->id,
            'certifygenid' => 0,
            'lang' => 'en',
            'modelid' => $model->get('id'),
            'status' => certifygen_validations::STATUS_NOT_STARTED,
            'issueid' => null,
            'usermodified' => $teacher->id,
        ];
        $validation = certifygen_validations::manage_validation(0, (object)$data);
        // Validate.
        $result = change_status_external::change_status(
            $teacher->id,
            '',
            $validation->get('id'),
            'http://www.google.es'
        );
        // Tests.
        self::assertIsArray($result);
        self::assertArrayHasKey('error', $result);
        self::assertIsArray($result['error']);
        self::assertArrayHasKey('code', $result['error']);
        self::assertArrayHasKey('message', $result['error']);
        self::assertEquals('validationplugin_not_accepted', $result['error']['code']);
    }
    /**
     * Test 7 : repository plugin not valid
     *
     * @return void
     * @throws invalid_persistent_exception
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @covers \certifygenvalidation_webservice\external\change_status_external::change_status
     */
    public function test_7(): void {
        $this->setAdminUser();
        // Create course.
        $course = self::getDataGenerator()->create_course();

        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create model.
        set_config('enabled', 1, 'certifygenvalidation_webservice');
        set_config('enabled', 1, 'certifygenreport_basic');
        set_config('enabled', 1, 'certifygenrepository_localrepository');
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $data = [
                'type' => certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
                'mode' => certifygen_model::MODE_UNIQUE,
                'templateid' => $certificate1->get_id(),
                'report' => 'certifygenreport_basic',
        ];
        $model = $modgenerator->create_model(
            $data['type'],
            $data['mode'],
            $data['templateid'],
            'certifygenvalidation_webservice',
            $data['report']
        );
        $modgenerator->assign_model_coursecontext($model->get('id'), $course->id);

        // Create user and enrol as teacher.
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
        $teacher = $this->getDataGenerator()->create_user([
                'username' => 'test_user_1',
                'firstname' => 'test',
                'lastname' => 'user 1',
                'email' => 'test_user_1@fake.es',
                'profile_field_DNI' => $dni,
        ]);
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $student = $this->getDataGenerator()->create_user([
                'username' => 'test_user_2',
                'firstname' => 'test',
                'lastname' => 'user 2',
                'email' => 'test_user_2@fake.es',
        ]);
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        // Create a validation.
        $data = [
                'name' => 'request_29042025_00',
                'courses' => $course->id,
                'code' => 'TRXXXX',
                'userid' => $teacher->id,
                'certifygenid' => 0,
                'lang' => 'en',
                'modelid' => $model->get('id'),
                'status' => certifygen_validations::STATUS_NOT_STARTED,
                'issueid' => null,
                'usermodified' => $teacher->id,
        ];
        $validation = certifygen_validations::manage_validation(0, (object)$data);

        // Validate.
        $result = change_status_external::change_status(
            $teacher->id,
            '',
            $validation->get('id'),
            'http://www.google.es'
        );
        // Tests.
        self::assertIsArray($result);
        self::assertArrayHasKey('error', $result);
        self::assertIsArray($result['error']);
        self::assertArrayHasKey('code', $result['error']);
        self::assertArrayHasKey('message', $result['error']);
        self::assertEquals('repositoryplugin_not_accepted', $result['error']['code']);
    }
}
