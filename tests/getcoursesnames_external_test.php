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
use mod_certifygen\external\deletemodel_external;
use mod_certifygen\external\get_courses_as_teacher_external;
use mod_certifygen\external\getcoursesnames_external;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/admin/tool/certificate/tests/generator/lib.php');
require_once($CFG->dirroot.'/lib/externallib.php');
/**
 * Get courses names test
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class getcoursesnames_external_test extends advanced_testcase {

    /**
     * Test set up.
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test
     * @return void
     * @throws invalid_parameter_exception
     */
    public function test_getcoursesnames(): void {
        $course1 = self::getDataGenerator()->create_course();
        $course2 = self::getDataGenerator()->create_course();
        $course3 = self::getDataGenerator()->create_course();
        $courseids = [$course1->id, $course2->id, $course3->id];
        $courseids = implode(',', $courseids);
        $result = getcoursesnames_external::getcoursesnames($courseids);
        self::assertIsArray($result);
        self::assertArrayHasKey('list', $result);
        self::assertCount(3, $result['list']);
        self::assertArrayHasKey('id', $result['list'][0]);
        self::assertArrayHasKey('shortname', $result['list'][0]);
        self::assertArrayHasKey('fullname', $result['list'][0]);
        self::assertArrayHasKey('link', $result['list'][0]);
        self::assertEquals($course1->id, $result['list'][0]['id']);
        self::assertEquals($course1->shortname, $result['list'][0]['shortname']);
        self::assertEquals($course1->fullname, $result['list'][0]['fullname']);
        self::assertEquals($course2->id, $result['list'][1]['id']);
        self::assertEquals($course2->shortname, $result['list'][1]['shortname']);
        self::assertEquals($course2->fullname, $result['list'][1]['fullname']);
        self::assertEquals($course3->id, $result['list'][2]['id']);
        self::assertEquals($course3->shortname, $result['list'][2]['shortname']);
        self::assertEquals($course3->fullname, $result['list'][2]['fullname']);
    }
}
