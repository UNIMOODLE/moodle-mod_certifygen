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

use mod_certifygen\certifygen_file;
/**
 * icertificatevalidation
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface icertificatevalidation {
    /** @var string FILE_COMPONENT */
    const FILE_COMPONENT = 'mod_certifygen';
    /** @var string FILE_AREA */
    const FILE_AREA = 'certifygenvalidation';
    /** @var string FILE_AREA_VALIDATED */
    const FILE_AREA_VALIDATED = 'certifygenvalidationvalidated';
    /** @var string FILE_PATH */
    const FILE_PATH = '/';

    /**
     *  is enabled
     * @return bool
     */
    public function is_enabled(): bool;

    /**
     * Send file
     * @param certifygen_file $file
     * @return array
     */
    public function send_file(certifygen_file $file): array;

    /**
     * Get file
     * return $result = ['error' => [], 'file' => $file,];
     *
     * @param int $courseid
     * @param int $validationid
     * @return array
     */
    public function get_file(int $courseid, int $validationid): array;

    /**
     * canRevoke
     * @param int $courseid
     * @return bool
     */
    public function can_revoke(int $courseid): bool;

    /**
     * This method is called by checkstatus task to check if the certificate status has changed.
     * Return true if validation plugin does not return the certificate validated inmediately on sendFile function.
     * @return bool
     */
    public function check_status(): bool;
    /**
     * This method is called by checkfIle task to check if the certificate file is already on external app.
     * Return true if validation plugin must be called to obtain the certificate file.
     * @return bool
     */
    public function checkfile(): bool;

    /**
     * Get status
     * @param int $validationid
     * @param string $code
     * @return int
     */
    public function get_status(int $validationid, string $code): int;

    /**
     * Returns an array of strings associated to certifiacte status to be shown on
     * activityteacher_table and profile_my_certificates_table
     */
    public function get_status_messages(): array;

    /**
     * If true, the certifygen activities related with this type of validation will be part
     * of the output of get_id_instance_certificate_external ws.
     * If true, the teacher requests with models with this type of validation will be part
     *  of the output of get_courses_as_teacher ws.
     *
     * @return bool
     */
    public function is_visible_in_ws(): bool;

    /**
     * If true, students and teachers can emit from the platfomr the certificate
     * @return bool
     */
    public function show_emit_button(): bool;
    /**
     * Return the list of repository plugin names this validation plugin is valid with.
     * Empty means all of them are valid.
     * @return array
     */
    public function get_consistent_repository_plugins(): array;
}
