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
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use mod_certifygen\external\get_courses_as_student_external;
global $CFG;
require_once($CFG->dirroot.'/admin/tool/certificate/tests/generator/lib.php');
require_once($CFG->dirroot.'/lib/externallib.php');

class get_courses_as_student_external_test extends advanced_testcase {

    /**
     * Test set up.
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * @return void
     * @throws invalid_parameter_exception
     */
    public function test_get_courses_as_student(): void {

        // Create user.
        $user1 = $this->getDataGenerator()->create_user(
            ['username' => 'test_user_1', 'firstname' => 'test', 'lastname' => 'user 1', 'email' => 'test_user_1@fake.es']);

        // Create courses.
        $course1 = self::getDataGenerator()->create_course();
        $course2 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as editingteacher
        self::getDataGenerator()->enrol_user($user1->id, $course1->id, 'student');

        // Enrol user in course2 as student
        self::getDataGenerator()->enrol_user($user1->id, $course2->id, 'editingteacher');

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
        $this->assertEquals('', $result['courses'][0]['modellist']);
        $this->assertArrayHasKey('student', $result);
        $this->assertArrayHasKey('id', $result['student']);
        $this->assertArrayHasKey('fullname', $result['student']);
        $this->assertEquals($user1->id, $result['student']['id']);
        $this->assertEquals(fullname($user1), $result['student']['fullname']);
    }
    /**
     * @return void
     * @throws invalid_parameter_exception
     */
    public function test_get_courses_as_student_by_userfield(): void {


        // Create user profile fields.
        $category = self::getDataGenerator()->create_custom_profile_field_category(['name' => 'Category 1']);
        $field = self::getDataGenerator()->create_custom_profile_field(
            ['shortname' => 'DNI',
                'name' => 'DNI',
                'categoryid' => $category->id,
                'required' => 1, 'visible' => 1, 'locked' => 0, 'datatype' => 'text', 'defaultdata' => null]);

        // Configure the platform.
        set_config('userfield', $field->id, 'mod_certifygen');

        // Create user.
        $dni = '123456789P';
        $user1 = $this->getDataGenerator()->create_user(
            ['username' => 'test_user_1', 'firstname' => 'test',
                'lastname' => 'user 1', 'email' => 'test_user_1@fake.es',
                'profile_field_DNI' => $dni]);

        // Create courses.
        $course1 = self::getDataGenerator()->create_course();
        $course2 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as student
        self::getDataGenerator()->enrol_user($user1->id, $course1->id, 'student');

        // Enrol user in course2 as editingteacher
        self::getDataGenerator()->enrol_user($user1->id, $course2->id, 'editingteacher');

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
        $this->assertEquals('', $result['courses'][0]['modellist']);
        $this->assertArrayHasKey('student', $result);
        $this->assertArrayHasKey('id', $result['student']);
        $this->assertArrayHasKey('fullname', $result['student']);
        $this->assertEquals($user1->id, $result['student']['id']);
        $this->assertEquals(fullname($user1), $result['student']['fullname']);
    }
    /**
     * @return void
     * @throws invalid_parameter_exception
     */
    public function test_get_courses_as_student_by_lang(): void {
        global $CFG;

        // Add the multilang filter. Make sure it's enabled globally.
        $CFG->filterall = true;
        $CFG->stringfilters = 'multilang';
        filter_set_global_state('multilang', TEXTFILTER_ON);

        // Create user.
        $user1 = $this->getDataGenerator()->create_user(
            ['username' => 'test_user_1', 'firstname' => 'test',
                'lastname' => 'user 1', 'email' => 'test_user_1@fake.es']);

        // Create courses.
        $spanishname = 'Titulo en castellano';
        $englishname = 'Titulo en ingles';
        $data = [
            'fullname' => '<span lang="es" class="multilang">' . $spanishname
                . '</span><span lang="en" class="multilang">' . $englishname . '</span>'
        ];
        $course1 = self::getDataGenerator()->create_course($data);
        $course2 = self::getDataGenerator()->create_course();

        // Enrol user in course1 as student
        self::getDataGenerator()->enrol_user($user1->id, $course1->id, 'student');

        // Enrol user in course2 as editingteacher
        self::getDataGenerator()->enrol_user($user1->id, $course2->id, 'editingteacher');


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
        $this->assertEquals('', $result['courses'][0]['modellist']);
        $this->assertArrayHasKey('student', $result);
        $this->assertArrayHasKey('id', $result['student']);
        $this->assertArrayHasKey('fullname', $result['student']);
        $this->assertEquals($user1->id, $result['student']['id']);
        $this->assertEquals(fullname($user1), $result['student']['fullname']);
    }
}