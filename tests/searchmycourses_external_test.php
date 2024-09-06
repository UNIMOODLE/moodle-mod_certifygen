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

namespace tests;

use advanced_testcase;
use coding_exception;
use dml_exception;
use invalid_parameter_exception;
use mod_certifygen\external\searchmycourses_external;
use mod_certifygen\persistents\certifygen_model;
use moodle_exception;
use restricted_context_exception;

global $CFG;
require_once($CFG->dirroot . '/admin/tool/certificate/tests/generator/lib.php');
require_once($CFG->dirroot . '/lib/externallib.php');

class searchmycourses_external_test extends advanced_testcase
{

    /**
     * Test set up.
     */
    public function setUp(): void
    {
        $this->resetAfterTest();
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    public function test_searchmycourses_notenrolled(): void
    {
        // Create user.
        $user = $this->getDataGenerator()->create_user(
            ['username' => 'test_user_2', 'firstname' => 'test',
                'lastname' => 'user 2', 'email' => 'test_user_2@fake.es']);

        // Login as user.
        $this->setUser($user);

        $cat = self::getDataGenerator()->create_category();
        self::getDataGenerator()->create_course(['fullname' => 'Matemáticas 1', 'category' => $cat->id]);
        self::getDataGenerator()->create_course(['fullname' => 'Matemáticas 2', 'category' => $cat->id]);
        $name = 'Biologia';
        $course1 = self::getDataGenerator()->create_course(['fullname' => $name, 'category' => $cat->id]);
        $name2 = 'biologia 5';
        $course2 = self::getDataGenerator()->create_course(['fullname' => $name2, 'category' => $cat->id]);

        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create model and assign context.
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
            $certificate1->get_id(),
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
        );
        $modgenerator->assign_model_coursecontext($model->get('id'), $course1->id . ',' . $course2->id);
        $result = searchmycourses_external::searchmycourses('bi', $user->id, $model->get('id'));

        // Tests.
        self::assertIsArray($result);
        self::assertArrayHasKey('list', $result);
        self::assertArrayHasKey('maxusersperpage', $result);
        self::assertArrayHasKey('overflow', $result);
        self::assertCount(0, $result['list']);
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    public function test_searchmycourses_enrolled(): void
    {
        // Create user and enrol as teacher.
        $user = $this->getDataGenerator()->create_user(
            ['username' => 'test_user_2', 'firstname' => 'test',
                'lastname' => 'user 2', 'email' => 'test_user_2@fake.es']);

        // Login as user.
        $this->setUser($user);

        $cat = self::getDataGenerator()->create_category();
        self::getDataGenerator()->create_course(['fullname' => 'Matemáticas 1', 'category' => $cat->id]);
        self::getDataGenerator()->create_course(['fullname' => 'Matemáticas 2', 'category' => $cat->id]);
        $name = 'Biologia';
        $course = self::getDataGenerator()->create_course(['fullname' => $name, 'category' => $cat->id]);
        $name2 = 'biologia 5';
        $course2 = self::getDataGenerator()->create_course(['fullname' => $name2, 'category' => $cat->id]);
        self::getDataGenerator()->enrol_user($user->id, $course->id);
        self::getDataGenerator()->enrol_user($user->id, $course2->id);

        // Create template.
        $templategenerator = $this->getDataGenerator()->get_plugin_generator('tool_certificate');
        $certificate1 = $templategenerator->create_template((object)['name' => 'Certificate 1']);

        // Create model and assign context.
        $modgenerator = $this->getDataGenerator()->get_plugin_generator('mod_certifygen');
        $model = $modgenerator->create_model_by_name(
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
            $certificate1->get_id(),
            certifygen_model::TYPE_TEACHER_ALL_COURSES_USED,
        );
        $modgenerator->assign_model_coursecontext($model->get('id'), $course->id . ',' . $course2->id);

        $result = searchmycourses_external::searchmycourses('biol', $user->id, $model->get('id'));
        // Tests.
        self::assertIsArray($result);
        self::assertArrayHasKey('list', $result);
        self::assertArrayHasKey('maxusersperpage', $result);
        self::assertArrayHasKey('overflow', $result);
        self::assertCount(2, $result['list']);
        self::assertArrayHasKey($course->id, $result['list']);
        self::assertArrayHasKey($course2->id, $result['list']);
        self::assertIsObject($result['list'][$course->id]);
        self::assertEquals($name2, $result['list'][$course2->id]->name);
        self::assertEquals($name, $result['list'][$course->id]->name);
    }
}