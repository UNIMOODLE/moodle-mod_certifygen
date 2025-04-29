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
 * En lang strings
 * @package   certifygenvalidation_webservice
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'WEBSERVICE Validation';
$string['enable'] = 'Enable';
$string['enable_help'] = 'If this plugin is enabled, you can use it to validate Unimoodle Certificates via web services';
$string['certifygenvalidation_webservice_settings'] = 'WEBSERVICE settings';
$string['certificate_not_emited'] = 'Certificate not issued';
$string['webservicenotconfigured'] = 'Certifygen WEBSERVICE not configured';
$string['pluginnamesettings'] = 'WEBSERVICE Validation Configuration';
$string['privacy:metadata'] = 'The WEBSERVICE Validation plugin does not store any personal data.';
$string['inprogress_msg'] = 'Access your university\'s administration office to continue with the certificate validation process.';
$string['request_not_found'] = 'Request not found';
$string['request_status_not_accepted'] = 'Status not accepted';
$string['repositoryplugin_not_accepted'] = 'Repository plugin not accepted';
$string['teacherrequest_pdf_error'] = 'There was a problem getting certificate';
$string['validationplugin_not_accepted'] = 'Validation plugin not accepted';
$string['wsoutput'] = 'Web Service Output';
$string['wsoutput_help'] = 'If true, the certifygen activities related with this type of validation will be part of the output of
get_id_instance_certificate_external ws. If true, the teacher requests with models with this type of validation will be part of the
output of get_courses_as_teacher ws.';
