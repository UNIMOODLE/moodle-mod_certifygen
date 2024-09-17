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
use mod_certifygen\external\emitteacherrequest_external;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/admin/tool/certificate/tests/generator/lib.php');
require_once($CFG->dirroot . '/lib/externallib.php');
/**
 * Issue certificate test
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class emitteacherrequest_external_test extends advanced_testcase {

    /**
     * Test set up.
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * test
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    public function test_emitteacherrequest(): void {

        // Create course.
        $course = self::getDataGenerator()->create_course();

        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create model.
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
            $certificate1->get_id(),
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
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

        $this->setUser($teacher);

        // Create teacherrequest.
        $teacherrequest = $modgenerator->create_teacher_request($model->get('id'), $course->id, $teacher->id);
        self::assertEquals(certifygen_validations::STATUS_NOT_STARTED, $teacherrequest->get('status'));
        $result = emitteacherrequest_external::emitteacherrequest($teacherrequest->get('id'));
        $teacherrequest = new certifygen_validations($teacherrequest->get('id'));

        // Tests.
        self::assertIsArray($result);
        self::assertArrayHasKey('result', $result);
        self::assertArrayHasKey('message', $result);
        self::assertTrue($result['result']);
        self::assertEquals(get_string('ok', 'mod_certifygen'), $result['message']);
        self::assertEquals(certifygen_validations::STATUS_FINISHED, $teacherrequest->get('status'));
    }

    /**
     * test
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    public function test_emitteacherrequest2(): void {

        // Create course.
        $course = self::getDataGenerator()->create_course();

        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create model.
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
            $certificate1->get_id(),
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
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
        $teacher2 = $this->getDataGenerator()->create_user([
                'username' => 'test_user_3',
                'firstname' => 'test',
                'lastname' => 'user 3',
                'email' => 'test_user_3@fake.es',
        ]);
        $this->getDataGenerator()->enrol_user($teacher2->id, $course->id, 'editingteacher');
        $student = $this->getDataGenerator()->create_user([
                'username' => 'test_user_2',
                'firstname' => 'test',
                'lastname' => 'user 2',
                'email' => 'test_user_2@fake.es',
                ]);
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        $this->setUser($teacher2);

        // Create teacherrequest.
        $teacherrequest = $modgenerator->create_teacher_request($model->get('id'), $course->id, $teacher->id);
        self::assertEquals(certifygen_validations::STATUS_NOT_STARTED, $teacherrequest->get('status'));
        $result = emitteacherrequest_external::emitteacherrequest($teacherrequest->get('id'));

        // Tests.
        self::assertIsArray($result);
        self::assertArrayHasKey('result', $result);
        self::assertArrayHasKey('message', $result);
        self::assertEquals(get_string('nopermissiontoemitothercerts', 'mod_certifygen'), $result['message']);
        self::assertFalse($result['result']);
    }
    /**
     * Test: validation ws + localrepository.
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    public function test_emitteacherrequest_3(): void {

        // Create course.
        $course = self::getDataGenerator()->create_course();

        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create model.
        set_config('enabled', 1, 'certifygenvalidation_webservice');
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model(
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
            certifygen_model::MODE_UNIQUE,
            $certificate1->get_id(),
            'certifygenvalidation_webservice',
            'certifygenreport_basic',
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

        $this->setUser($teacher);

        // Create teacherrequest.
        $teacherrequest = $modgenerator->create_teacher_request($model->get('id'), $course->id, $teacher->id);
        self::assertEquals(certifygen_validations::STATUS_NOT_STARTED, $teacherrequest->get('status'));
        $result = emitteacherrequest_external::emitteacherrequest($teacherrequest->get('id'));
        $teacherrequest = new certifygen_validations($teacherrequest->get('id'));

        // Tests.
        self::assertIsArray($result);
        self::assertArrayHasKey('result', $result);
        self::assertArrayHasKey('message', $result);
        self::assertTrue($result['result']);
        self::assertEquals(get_string('ok', 'mod_certifygen'), $result['message']);
        self::assertEquals(certifygen_validations::STATUS_IN_PROGRESS, $teacherrequest->get('status'));
    }
}
