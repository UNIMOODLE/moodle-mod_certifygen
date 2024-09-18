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

use certifygenvalidation_webservice\external\change_status_external;
use core\invalid_persistent_exception;
use mod_certifygen\external\emitteacherrequest_external;
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
class change_status_external_test extends advanced_testcase {
    /**
     * Test set up.
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test
     * @return void
     * @throws invalid_persistent_exception
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
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

        // Login as $teacher.
        $this->setUser($teacher);

        // Create request.
        $teacherrequest = $modgenerator->create_teacher_request($model->get('id'), $course->id, $teacher->id);
        self::assertEquals(certifygen_validations::STATUS_NOT_STARTED, $teacherrequest->get('status'));

        // Emit certificate.
        emitteacherrequest_external::emitteacherrequest($teacherrequest->get('id'));

        // Test status.
        $teacherrequest = new certifygen_validations($teacherrequest->get('id'));
        self::assertEquals(certifygen_validations::STATUS_IN_PROGRESS, $teacherrequest->get('status'));

        // Validate.
        $this->setAdminUser();
        $result = change_status_external::change_status(
            $teacher->id,
            '',
            $teacherrequest->get('id')
        );

        // Tests.
        self::assertIsArray($result);
        self::assertArrayHasKey('requestid', $result);
        self::assertArrayHasKey('newstatus', $result);
        self::assertArrayHasKey('newstatusdesc', $result);
        self::assertEquals($teacherrequest->get('id'), $result['requestid']);
        self::assertEquals(certifygen_validations::STATUS_VALIDATION_OK, $result['newstatus']);
        self::assertEquals(
            get_string('status_' . certifygen_validations::STATUS_VALIDATION_OK, 'mod_certifygen'),
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
        $this->setUser($teacher);
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        // Create request.
        $teacherrequest = $modgenerator->create_teacher_request($model->get('id'), $course->id, $teacher->id);
        self::assertEquals(certifygen_validations::STATUS_NOT_STARTED, $teacherrequest->get('status'));

        // Emit certificate.
        emitteacherrequest_external::emitteacherrequest($teacherrequest->get('id'));

        $teacherrequest = new certifygen_validations($teacherrequest->get('id'));
        self::assertEquals(certifygen_validations::STATUS_IN_PROGRESS, $teacherrequest->get('status'));

        // Validate.
        $this->setAdminUser();
        $result = change_status_external::change_status(
            $student->id,
            '',
            $teacherrequest->get('id')
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
            9999
        );

        // Tests.
        self::assertIsArray($result);
        self::assertArrayHasKey('error', $result);
        self::assertIsArray($result['error']);
        self::assertArrayHasKey('code', $result['error']);
        self::assertArrayHasKey('message', $result['error']);
        self::assertEquals('request_not_found', $result['error']['code']);
    }
}
