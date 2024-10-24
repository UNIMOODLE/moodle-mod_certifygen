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
namespace mod_certifygen\interfaces;

use mod_certifygen\persistents\certifygen_validations;
/**
 * ICertificateReport
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface ICertificateReport {
    /** @var string FILE_COMPONENT*/
    const FILE_COMPONENT = 'mod_certifygen';
    /** @var string FILE_AREA*/
    const FILE_AREA = 'certifygenreport';
    /** @var string FILE_PATH */
    const FILE_PATH = '/';

    /**
     * is_enabled
     * @return bool
     */
    public function is_enabled(): bool;

    /**
     * createFile
     * @param certifygen_validations $trequest
     * @return array
     */
    public function create_file(certifygen_validations $trequest): array;

    /**
     * Get the certificate content.
     * @param certifygen_validations $trequest
     * @return array
     */
    public function get_certificate_elements(certifygen_validations $trequest): array;
}