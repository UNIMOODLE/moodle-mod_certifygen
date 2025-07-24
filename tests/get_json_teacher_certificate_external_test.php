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
use mod_certifygen\external\get_json_certificate_external;
use mod_certifygen\external\get_json_teacher_certificate_external;
use mod_certifygen\persistents\certifygen_model;

defined('MOODLE_INTERNAL') || die();

global $CFG;

/**
 * Get json teacher certificate test
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_json_teacher_certificate_external_test extends \advanced_testcase {
    /**
     * Test set up.
     */
    public function setUp(): void {
        $this->resetAfterTest();
        $controller = new \tool_langimport\controller();
        $controller->install_languagepacks('es');
    }

    /**
     * Test 1: User not found
     *
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @covers \mod_certifygen\external\get_json_teacher_certificate_external::get_json_teacher_certificate
     */
    public function test_1(): void {
        global $DB;

        // Create user.
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $this->setUser($manager);
        $result = get_json_teacher_certificate_external::get_json_teacher_certificate(
            9999,
            '',
            '456,6789',
            'gl',
            34
        );
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertArrayHasKey('code', $result['error']);
        $this->assertArrayHasKey('message', $result['error']);
        $this->assertEquals('user_not_found', $result['error']['code']);
        $this->assertEquals(get_string('user_not_found', 'mod_certifygen'), $result['error']['message']);
    }

    /**
     * Test 2: Language not found
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     * @covers \mod_certifygen\external\get_json_teacher_certificate_external::get_json_teacher_certificate
     */
    public function test_2(): void {
        global $DB;

        // Create manager.
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $this->setUser($manager);

        // Create courses.
        $course1 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as editingteacher.
        $teacher = $this->getDataGenerator()->create_user();
        self::getDataGenerator()->enrol_user($teacher->id, $course1->id, 'editingteacher');

        $result = get_json_teacher_certificate_external::get_json_teacher_certificate(
            $teacher->id,
            '',
            '456,6789',
            'gl',
            34
        );
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertArrayHasKey('code', $result['error']);
        $this->assertArrayHasKey('message', $result['error']);
        $this->assertEquals('lang_not_found', $result['error']['code']);
        $this->assertEquals(get_string('lang_not_found', 'mod_certifygen'), $result['error']['message']);
    }

    /**
     * Test 3: Model not found
     *
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @covers \mod_certifygen\external\get_json_teacher_certificate_external::get_json_teacher_certificate
     */
    public function test_3(): void {
        global $DB;

        // Create manager.
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $this->setUser($manager);

        // Create courses.
        $course1 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as editingteacher.
        $teacher = $this->getDataGenerator()->create_user();
        self::getDataGenerator()->enrol_user($teacher->id, $course1->id, 'editingteacher');

        $result = get_json_teacher_certificate_external::get_json_teacher_certificate(
            $teacher->id,
            '',
            '456,6789',
            'en',
            34
        );
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
    }

    /**
     * Test 4: course_not_valid_with_model
     *
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @covers \mod_certifygen\external\get_json_teacher_certificate_external::get_json_teacher_certificate
     */
    public function test_4(): void {
        global $DB;

        // Create manager.
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $this->setUser($manager);

        // Create courses.
        $course1 = self::getDataGenerator()->create_course();
        $course2 = self::getDataGenerator()->create_course();
        $course3 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as editingteacher.
        $teacher = $this->getDataGenerator()->create_user();
        self::getDataGenerator()->enrol_user($teacher->id, $course1->id, 'editingteacher');

        // Create mod_certifygen.
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
            0,
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED
        );
        $modgenerator->assign_model_coursecontext($model->get('id'), $course1->id);

        $result = get_json_teacher_certificate_external::get_json_teacher_certificate(
            $teacher->id,
            '',
            $course2->id . ',' . $course3->id,
            'en',
            $model->get('id')
        );
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertArrayHasKey('code', $result['error']);
        $this->assertArrayHasKey('message', $result['error']);
        $this->assertEquals('course_not_valid_with_model', $result['error']['code']);
        $this->assertEquals(get_string('course_not_valid_with_model', 'mod_certifygen', $course2->id), $result['error']['message']);
    }

    /**
     * Test 5: course_not_valid_with_model
     *
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @covers \mod_certifygen\external\get_json_teacher_certificate_external::get_json_teacher_certificate
     */
    public function test_5(): void {
        global $DB;

        // Create manager.
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $this->setUser($manager);

        // Create courses.
        $course1 = self::getDataGenerator()->create_course();
        $course2 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as editingteacher.
        $teacher = $this->getDataGenerator()->create_user();
        self::getDataGenerator()->enrol_user($teacher->id, $course1->id, 'editingteacher');
        self::getDataGenerator()->enrol_user($teacher->id, $course2->id, 'editingteacher');

        // Create mod_certifygen.
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
            0,
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED
        );
        $modgenerator->assign_model_coursecontext($model->get('id'), "$course1->id,$course2->id");

        $result = get_json_teacher_certificate_external::get_json_teacher_certificate(
            $teacher->id,
            '',
            "$course1->id,$course2->id",
            'en',
            $model->get('id')
        );
        // Tests.
        $this->assertIsArray($result);
        $this->assertArrayHasKey('json', $result);
        $courses = json_decode($result['json']);
        $this->assertEquals(count($courses), 2);
    }
}
