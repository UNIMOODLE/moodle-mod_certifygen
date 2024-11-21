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
 *
 * @package   certifygenreport_basic
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Basic Report';
$string['pluginnamesettings'] = 'Basic Report Settings';
$string['enable'] = 'Enable';
$string['enable_help'] = 'If this plugin is enabled, you can use it to report Unimoodle Teacher Certificates';
$string['path'] = 'Path';
$string['path_help'] = 'External service command path HELP';
$string['certifygenreport_basic_settings'] = 'Basic Report Settings';
$string['logo'] = 'Logo';
$string['logo_desc'] = 'Logo that appears on teacher certificates';
$string['footer'] = 'Footer';
$string['footer_desc'] = 'Footer that appears on teacher certificates';
$string['and'] = 'and';
$string['reporttext'] = 'Certificate of use of the Virtual Campus of the University of XXXXX issued for the teacher {$a->teacher}  >> according to the automatic classification method of course usage referred to at the end of this document1';
$string['courseinfo'] = 'The course/subject {$a->coursename} {$a->coursedetails}, taught by the teacher {$a->teachers}, is of type {$a->type}.';
$string['courseinfopl'] = 'The course/subject {$a->coursename} {$a->coursedetails}, taught by the teachers {$a->teachers}, is of type {$a->type}.';
$string['coursetypedesc'] = 'If TYPE=Inactive >>“Low usage of the Virtual Campus by teachers and/or students is detected. It is recommended to increase the use of the Virtual Campus, by incorporating additional resources for students to consult and/or activities in which they participate more actively.”<br>
If TYPE=With submissions >>“The Virtual Campus is mainly used to channel the submission of assignments and as a repository. It is recommended to make better use of feedback mechanisms and the virtual campus grade book, to improve communication with students oriented towards formative assessment, as well as to consider a greater incorporation of participatory activities.”<br>
If TYPE= Repository >>“The Virtual Campus is mainly used as a repository. It is recommended to better utilize the activity modules of the virtual campus, to channel assignment submissions and improve communication mechanisms with students.”';
$string['cdetail_1'] = 'belonging to the category {$a->name}';
$string['cdetail_2'] = 'with a start date in the Virtual Campus {$a->date}';
$string['cdetail_3'] = 'with an end date in the Virtual Campus {$a->date}';
$string['cannotusealgorith_nostudents'] = 'There are no students in the course';
$string['privacy:metadata'] = 'The Basic Report plugin does not store personal data.';
