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
use mod_certifygen\external\deletemodel_external;
use mod_certifygen\external\emitcertificate_external;
use mod_certifygen\external\revokecertificate_external;
use mod_certifygen\external\searchcategory_external;
use mod_certifygen\persistents\certifygen_model;
use mod_certifygen\persistents\certifygen_validations;

global $CFG;
require_once($CFG->dirroot.'/admin/tool/certificate/tests/generator/lib.php');
require_once($CFG->dirroot.'/lib/externallib.php');

class searchcategory_external_test extends advanced_testcase {

    /**
     * Test set up.
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * @return void
     */
    public function test_searchcategory(): void {
        // Create user and enrol as teacher.
        $user = $this->getDataGenerator()->create_user(
            ['username' => 'test_user_2', 'firstname' => 'test',
                'lastname' => 'user 2', 'email' => 'test_user_2@fake.es']);

        // Login as user.
        $this->setUser($user);

        $name1 = 'Primaria 1';
        $category1 = self::getDataGenerator()->create_category(['name' => $name1]);
        $name2 = 'primaria 2';
        $category2 = self::getDataGenerator()->create_category(['name' => $name2]);
        self::getDataGenerator()->create_category(['name' => 'ESO 1']);
        $result = searchcategory_external::searchcategory('Prim');

        // Tests.
        self::assertIsArray($result);
        self::assertArrayHasKey('list', $result);
        self::assertArrayHasKey('maxusersperpage', $result);
        self::assertArrayHasKey('overflow', $result);
        self::assertCount(2, $result['list']);

        self::assertEquals(' / ' . $name1, $result['list'][$category1->id]->name);
        self::assertEquals(' / ' . $name2, $result['list'][$category2->id]->name);
    }
}