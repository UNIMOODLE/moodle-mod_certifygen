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
use mod_certifygen\external\get_courses_as_student_external;
use mod_certifygen\persistents\certifygen_model;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/admin/tool/certificate/tests/generator/lib.php');
require_once($CFG->dirroot.'/lib/externallib.php');
/**
 * Get courses as student test
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_courses_as_student_external_test extends advanced_testcase {

    /**
     * Test set up.
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test 1
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     */
    public function test_get_courses_as_student_nopermission(): void {
        global $DB;

        // Create user.
        $user1 = $this->getDataGenerator()->create_user(
            ['username' => 'test_user_1', 'firstname' => 'test', 'lastname' => 'user 1', 'email' => 'test_user_1@fake.es']);

        // Create courses.
        $course1 = self::getDataGenerator()->create_course();
        $course2 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as editingteacher.
        self::getDataGenerator()->enrol_user($user1->id, $course1->id, 'student');

        // Enrol user in course2 as student.
        self::getDataGenerator()->enrol_user($user1->id, $course2->id, 'editingteacher');

        // Tests.
        $result = get_courses_as_student_external::get_courses_as_student($user1->id, '', '');
        error_log(__FUNCTION__ . ' courses: '.var_export($result, true));
        $this->assertIsArray($result);
        $this->assertArrayNotHasKey('courses', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertIsArray($result['error']);
        $this->assertArrayHasKey('code', $result['error']);
        $this->assertArrayHasKey('message', $result['error']);
        $this->assertEquals('nopermissiontogetcourses', $result['error']['code']);
        $this->assertEquals(get_string('nopermissiontogetcourses', 'mod_certifygen'), $result['error']['message']);
    }

    /**
     * Test 2
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     */
    public function test_get_courses_as_student_with_no_certifygen(): void {
        global $DB;
        // Create user.
        $user1 = $this->getDataGenerator()->create_user(
            ['username' => 'test_user_1', 'firstname' => 'test', 'lastname' => 'user 1', 'email' => 'test_user_1@fake.es']);

        $managerrole = $DB->get_record('role', array('shortname' => 'manager'));
        $this->getDataGenerator()->role_assign($managerrole->id, $user1->id);
        $this->setUser($user1);

        // Create courses.
        $course1 = self::getDataGenerator()->create_course();
        $course2 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as editingteacher.
        self::getDataGenerator()->enrol_user($user1->id, $course1->id, 'student');

        // Enrol user in course2 as student.
        self::getDataGenerator()->enrol_user($user1->id, $course2->id, 'editingteacher');

        // Tests.
        $result = get_courses_as_student_external::get_courses_as_student($user1->id, '', '');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('courses', $result);
        $this->assertIsArray($result['courses']);
        $this->assertCount(0, $result['courses']);
        $this->assertArrayHasKey('student', $result);
        $this->assertArrayHasKey('id', $result['student']);
        $this->assertArrayHasKey('fullname', $result['student']);
        $this->assertEquals($user1->id, $result['student']['id']);
        $this->assertEquals(fullname($user1), $result['student']['fullname']);
    }

    /**
     * Test 3
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     */
    public function test_get_courses_as_student(): void {
        global $DB;
        // Create user.
        $user1 = $this->getDataGenerator()->create_user(
            ['username' => 'test_user_1', 'firstname' => 'test', 'lastname' => 'user 1', 'email' => 'test_user_1@fake.es']);
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', array('shortname' => 'manager'));
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $this->setUser($manager);

        // Create courses.
        $course1 = self::getDataGenerator()->create_course();
        $course2 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as editingteacher.
        self::getDataGenerator()->enrol_user($user1->id, $course1->id, 'student');

        // Enrol user in course2 as student.
        self::getDataGenerator()->enrol_user($user1->id, $course2->id, 'editingteacher');

        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create model.
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            'modelo actividad 1',
            $certificate1->get_id(),
            certifygen_model::TYPE_ACTIVITY
        );

        // Create activity.
        $data = [
            'name' => 'certifygen 1',
            'course' => $course1->id,
            'modelid' => $model->get('id'),
        ];
        $this->getDataGenerator()->create_module('certifygen', $data);

        // Now model is associated to course context.
        $modgenerator->assign_model_coursecontext($model->get('id'), $course1->id);

        // Tests.
        $result = get_courses_as_student_external::get_courses_as_student($user1->id, '', '');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('courses', $result);
        $this->assertIsArray($result['courses']);
        $this->assertCount(1, $result['courses']);
        $this->assertArrayHasKey('id', $result['courses'][0]);
        $this->assertArrayHasKey('shortname', $result['courses'][0]);
        $this->assertArrayHasKey('fullname', $result['courses'][0]);
        $this->assertArrayHasKey('categoryid', $result['courses'][0]);
        $this->assertArrayHasKey('completed', $result['courses'][0]);
        $this->assertArrayHasKey('modellist', $result['courses'][0]);
        $this->assertEquals($course1->id, $result['courses'][0]['id']);
        $this->assertEquals($course1->shortname, $result['courses'][0]['shortname']);
        $this->assertEquals($course1->fullname, $result['courses'][0]['fullname']);
        $this->assertEquals($course1->category, $result['courses'][0]['categoryid']);
        $this->assertEquals(false, $result['courses'][0]['completed']);
        $this->assertEquals($model->get('id'), $result['courses'][0]['modellist']);
        $this->assertArrayHasKey('student', $result);
        $this->assertArrayHasKey('id', $result['student']);
        $this->assertArrayHasKey('fullname', $result['student']);
        $this->assertEquals($user1->id, $result['student']['id']);
        $this->assertEquals(fullname($user1), $result['student']['fullname']);
    }

    /**
     * Test 4
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     */
    public function test_get_courses_as_student_by_userfield(): void {
        global $DB;
        // Create user profile fields.
        $category = self::getDataGenerator()->create_custom_profile_field_category(['name' => 'Category 1']);
        $field = self::getDataGenerator()->create_custom_profile_field(
            ['shortname' => 'DNI',
                'name' => 'DNI',
                'categoryid' => $category->id,
                'required' => 1, 'visible' => 1, 'locked' => 0, 'datatype' => 'text', 'defaultdata' => null]);

        // Configure the platform.
        set_config('userfield', 'profile_' . $field->id, 'mod_certifygen');

        // Create user.
        $dni = '123456789P';
        $user1 = $this->getDataGenerator()->create_user(
            ['username' => 'test_user_1', 'firstname' => 'test',
                'lastname' => 'user 1', 'email' => 'test_user_1@fake.es',
                'profile_field_DNI' => $dni]);
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', array('shortname' => 'manager'));
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $this->setUser($manager);

        // Create courses.
        $course1 = self::getDataGenerator()->create_course();
        $course2 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as student.
        self::getDataGenerator()->enrol_user($user1->id, $course1->id, 'student');

        // Enrol user in course2 as editingteacher.
        self::getDataGenerator()->enrol_user($user1->id, $course2->id, 'editingteacher');

        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create model.
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            'modelo actividad 1',
            $certificate1->get_id(),
            certifygen_model::TYPE_ACTIVITY
        );

        // Create activity.
        $data = [
            'name' => 'certifygen 1',
            'course' => $course1->id,
            'modelid' => $model->get('id'),
        ];
        $this->getDataGenerator()->create_module('certifygen', $data);

        // Now model is associated to course context.
        $modgenerator->assign_model_coursecontext($model->get('id'), $course1->id);

        // Tests.
        $result = get_courses_as_student_external::get_courses_as_student(0, $dni, '');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('courses', $result);
        $this->assertIsArray($result['courses']);
        $this->assertCount(1, $result['courses']);
        $this->assertArrayHasKey('id', $result['courses'][0]);
        $this->assertArrayHasKey('shortname', $result['courses'][0]);
        $this->assertArrayHasKey('fullname', $result['courses'][0]);
        $this->assertArrayHasKey('categoryid', $result['courses'][0]);
        $this->assertArrayHasKey('completed', $result['courses'][0]);
        $this->assertArrayHasKey('modellist', $result['courses'][0]);
        $this->assertEquals($course1->id, $result['courses'][0]['id']);
        $this->assertEquals($course1->shortname, $result['courses'][0]['shortname']);
        $this->assertEquals($course1->fullname, $result['courses'][0]['fullname']);
        $this->assertEquals($course1->category, $result['courses'][0]['categoryid']);
        $this->assertEquals(false, $result['courses'][0]['completed']);
        $this->assertEquals($model->get('id'), $result['courses'][0]['modellist']);
        $this->assertArrayHasKey('student', $result);
        $this->assertArrayHasKey('id', $result['student']);
        $this->assertArrayHasKey('fullname', $result['student']);
        $this->assertEquals($user1->id, $result['student']['id']);
        $this->assertEquals(fullname($user1), $result['student']['fullname']);
    }

    /**
     * Test 5
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     */
    public function test_get_courses_as_student_by_userfield_username(): void {
        global $DB;
        // Configure the platform.
        set_config('userfield', 'username', 'mod_certifygen');

        // Create user.
        $field = 'test_user_1';
        $user1 = $this->getDataGenerator()->create_user(
            ['username' => $field, 'firstname' => 'test',
                'lastname' => 'user 1', 'email' => 'test_user_1@fake.es']);
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', array('shortname' => 'manager'));
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $this->setUser($manager);
        $id = (int) $user1->id;
        // Create courses.
        $course1 = self::getDataGenerator()->create_course();
        $course2 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as student.
        self::getDataGenerator()->enrol_user($user1->id, $course1->id, 'student');

        // Enrol user in course2 as editingteacher.
        self::getDataGenerator()->enrol_user($user1->id, $course2->id, 'editingteacher');

        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create model.
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            'modelo actividad 1',
            $certificate1->get_id(),
            certifygen_model::TYPE_ACTIVITY
        );

        // Create activity.
        $data = [
            'name' => 'certifygen 1',
            'course' => $course1->id,
            'modelid' => $model->get('id'),
        ];
        $this->getDataGenerator()->create_module('certifygen', $data);

        // Now model is associated to course context.
        $modgenerator->assign_model_coursecontext($model->get('id'), $course1->id);

        // Tests.
        $result = get_courses_as_student_external::get_courses_as_student(0, $field, '');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('courses', $result);
        $this->assertIsArray($result['courses']);
        $this->assertCount(1, $result['courses']);
        $this->assertArrayHasKey('id', $result['courses'][0]);
        $this->assertArrayHasKey('shortname', $result['courses'][0]);
        $this->assertArrayHasKey('fullname', $result['courses'][0]);
        $this->assertArrayHasKey('categoryid', $result['courses'][0]);
        $this->assertArrayHasKey('completed', $result['courses'][0]);
        $this->assertArrayHasKey('modellist', $result['courses'][0]);
        $this->assertEquals($course1->id, $result['courses'][0]['id']);
        $this->assertEquals($course1->shortname, $result['courses'][0]['shortname']);
        $this->assertEquals($course1->fullname, $result['courses'][0]['fullname']);
        $this->assertEquals($course1->category, $result['courses'][0]['categoryid']);
        $this->assertEquals(false, $result['courses'][0]['completed']);
        $this->assertEquals($model->get('id'), $result['courses'][0]['modellist']);
        $this->assertArrayHasKey('student', $result);
        $this->assertArrayHasKey('id', $result['student']);
        $this->assertArrayHasKey('fullname', $result['student']);
        $this->assertEquals($user1->id, $result['student']['id']);
        $this->assertEquals(fullname($user1), $result['student']['fullname']);

        // Tests.
        $result = get_courses_as_student_external::get_courses_as_student($id, $field, '');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('courses', $result);
        $this->assertArrayHasKey('student', $result);

        // Tests.
        $result = get_courses_as_student_external::get_courses_as_student($id + 1, $field, '');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertIsArray($result['error']);
        $this->assertArrayHasKey('code', $result['error']);
        $this->assertArrayHasKey('message', $result['error']);
        $this->assertEquals('userfield_and_userid_sent', $result['error']['code']);

        // Tests.
        $result = get_courses_as_student_external::get_courses_as_student(0, 'profile_3', '');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertIsArray($result['error']);
        $this->assertArrayHasKey('code', $result['error']);
        $this->assertArrayHasKey('message', $result['error']);
        $this->assertEquals('user_not_found', $result['error']['code']);
    }

    /**
     * Test 5
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     */
    public function test_get_courses_as_student_by_lang(): void {
        global $CFG, $DB;

        // Add the multilang filter. Make sure it's enabled globally.
        $CFG->filterall = true;
        $CFG->stringfilters = 'multilang';
        filter_set_global_state('multilang', TEXTFILTER_ON);

        // Create user.
        $user1 = $this->getDataGenerator()->create_user(
            ['username' => 'test_user_1', 'firstname' => 'test',
                'lastname' => 'user 1', 'email' => 'test_user_1@fake.es']);
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', array('shortname' => 'manager'));
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $this->setUser($manager);

        // Create courses.
        $spanishname = 'Titulo en castellano';
        $englishname = 'Titulo en ingles';
        $data = [
            'fullname' => '<span lang="es" class="multilang">' . $spanishname
                . '</span><span lang="en" class="multilang">' . $englishname . '</span>',
        ];
        $course1 = self::getDataGenerator()->create_course($data);
        $course2 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as student.
        self::getDataGenerator()->enrol_user($user1->id, $course1->id, 'student');

        // Enrol user in course2 as editingteacher.
        self::getDataGenerator()->enrol_user($user1->id, $course2->id, 'editingteacher');

        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create model.
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            'modelo actividad 1',
            $certificate1->get_id(),
            certifygen_model::TYPE_ACTIVITY
        );

        // Create activity.
        $data = [
            'name' => 'certifygen 1',
            'course' => $course1->id,
            'modelid' => $model->get('id'),
        ];
        $this->getDataGenerator()->create_module('certifygen', $data);

        // Now model is associated to course context.
        $modgenerator->assign_model_coursecontext($model->get('id'), $course1->id);

        // Tests.
        $result = get_courses_as_student_external::get_courses_as_student($user1->id, '', 'en');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('courses', $result);
        $this->assertIsArray($result['courses']);
        $this->assertCount(1, $result['courses']);
        $this->assertArrayHasKey('id', $result['courses'][0]);
        $this->assertArrayHasKey('shortname', $result['courses'][0]);
        $this->assertArrayHasKey('fullname', $result['courses'][0]);
        $this->assertArrayHasKey('categoryid', $result['courses'][0]);
        $this->assertArrayHasKey('completed', $result['courses'][0]);
        $this->assertArrayHasKey('modellist', $result['courses'][0]);
        $this->assertEquals($course1->id, $result['courses'][0]['id']);
        $this->assertEquals($course1->shortname, $result['courses'][0]['shortname']);
        $this->assertEquals($englishname, $result['courses'][0]['fullname']);
        $this->assertEquals($course1->category, $result['courses'][0]['categoryid']);
        $this->assertEquals(false, $result['courses'][0]['completed']);
        $this->assertEquals($model->get('id'), $result['courses'][0]['modellist']);
        $this->assertArrayHasKey('student', $result);
        $this->assertArrayHasKey('id', $result['student']);
        $this->assertArrayHasKey('fullname', $result['student']);
        $this->assertEquals($user1->id, $result['student']['id']);
        $this->assertEquals(fullname($user1), $result['student']['fullname']);
    }
}
