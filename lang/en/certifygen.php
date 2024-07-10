<?php
// This file is part of Moodle - http://moodle.org/
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
// Project implemented by the "Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * English strings
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Unimoodle certifygen';
$string['pluginadministration'] = 'Unimoodle certifygen module administration';
$string['pluginnamesettings'] = 'Unimoodle certifygen Settings';
$string['certifygen:addinstance'] = 'Add a new unimoodle certifygen instance';
$string['certifygen:view'] = 'View an unimoodle certifygen certificate';
$string['certifygen:manage'] = 'Manage an unimoodle certifygen certificate';
$string['certifygen:viewcontextcertificates'] = 'View Context Certificates an unimoodle certifygen certificate';
$string['type'] = 'Type';
$string['type_help'] = 'Types help';
$string['type_1'] = 'Course completion (for students)';
$string['type_2'] = 'Course used (for teachers)';
$string['type_3'] = 'All courses used (for teachers)';
$string['mode'] = 'Mode';
$string['mode_help'] = 'Mode help';
$string['mode_1'] = 'Unique';
$string['mode_2'] = 'Periodic';
$string['templateid'] = 'Template';
$string['templateid_help'] = 'Select the template you want to use.';
$string['introduction'] = 'Introduction';
$string['modulename'] = 'Unimoodle Certifygen';
$string['modulenameplural'] = 'Unimoodle Certifygens';
$string['name'] = 'Name';
$string['modelname'] = 'Model name';
$string['contextcertificatelink'] = 'Unimoodle Certifygen Course Certificate';
$string['chooseamodel'] = 'Choose a model';
$string['model'] = 'Model';
$string['modelsmanager'] = 'Models manager';
$string['associatemodels'] = 'Associate models to Contexts';
$string['download'] = 'Download';
$string['timeondemmand'] = 'Time betwwen demmands';
$string['timeondemmand_desc'] = 'Time betwwen demmands  desc';
$string['timeondemmand_help'] = 'Time betwwen demmands help';
$string['langs'] = 'Languages';
$string['chooselang'] = 'You can filter the certificate list by its language';
$string['validation'] = 'validation';
$string['validation_desc'] = 'validation desc';
$string['validation_help'] = 'validation _help';
$string['modelmanager'] = 'Model Manager';
$string['create_model'] = 'Create Model';
$string['edit'] = 'Edit';
$string['delete'] = 'delete';
$string['template'] = 'Template';
$string['templatereport'] = 'Template/Report';
$string['lastupdate'] = 'Last Update';
$string['actions'] = 'Actions';
$string['code'] = 'Code';
$string['status'] = 'Status';
$string['mycertificates'] = 'My Unimoodle Certifygen Certificates';
$string['deletemodeltitle'] = 'Deleting Model';
$string['deletemodelbody'] = 'Are you sure, you want to delete model called "{$a}"?';
$string['confirm'] = 'Confirm';
$string['errortitle'] = 'Error';
$string['model'] = 'Model';
$string['contexts'] = 'Contexts';
$string['assigncontext'] = 'Assign contexts';
$string['editassigncontext'] = 'Modify contexts assignment';
$string['subplugintype_certifygenvalidation'] = 'Unimoodle certifygen validation method';
$string['subplugintype_certifygenvalidation_plural'] = 'Unimoodle certifygen validations methods';
$string['managecertifygenvalidationplugins'] = 'Manage unimoodle certifygen validation plugins';
$string['validationplugins'] = 'Validation plugins';
$string['certifygenvalidationpluginname'] = $string['validationplugins'];
$string['hideshow'] = 'Hide/Show';
$string['settings'] = 'Settings';
$string['assigncontextto'] = 'Assigning context to model "{$a}"';
$string['toomanycategoriestoshow'] = 'Too many categories to show';
$string['toomanycoursestoshow'] = 'Too many courses to show';
$string['chooseacontexttype'] = 'Choose the context in which it till search';
$string['writealmost3characters'] = 'Write almost 3 characters';
$string['coursecontext'] = 'Course context';
$string['categorycontext'] = 'Category context';
$string['selectvalidation'] = 'Select generation type';
$string['selectreport'] = 'Select report type';
$string['nocontextcourse'] = 'This course has no access to this page';
$string['hasnocapabilityrequired'] = 'You do not have permission required';
$string['emit'] = 'Emit certificate';
$string['status_1'] = 'Not started';
$string['status_2'] = 'In progress';
$string['status_3'] = 'Finished';
$string['status_4'] = 'Error';
$string['emitcertificate_title'] = 'Emit Certificate';
$string['emitcertificate_body'] = 'Are you sure, you want to emit the certificate in {$a}?';
$string['emitcertificate_error'] = 'There was an error trying to emit the certificate';
$string['confirm'] = 'Confirm';
$string['certificatenotfound'] = 'Certificate not found';
$string['filter'] = 'Filter';
$string['revokecertificate_title'] = 'Revoke Certificate';
$string['revokecertificate_body'] = 'Are you sure, you want to revoke the certificate in {$a}?';
$string['revokecertificate_error'] = 'There was an error trying to revoke the certificate';
$string['downloadcertificate_title'] = 'Download Certificate';
$string['downloadcertificate_body'] = 'Are you sure, you want to download the certificate in {$a}?';
$string['downloadcertificate_error'] = 'There was an error trying to download the certificate';
$string['notificationmsgcertificateissued'] = 'notificationmsgcertificateissued';
$string['certificatelist'] = 'Certificate List';
$string['selectmycertificateslangdesc'] = 'You can select the language of your certificate';
$string['system'] = 'System';
$string['requestid'] = 'Request Number';
$string['seecourses'] = 'See Courses';
$string['create_request'] = 'Create Request';
$string['courseslist'] = 'Courses list to certificate';
$string['deleterequesttitle'] = 'Delete Request';
$string['deleterequestbody'] = 'Are you sure, you want to delete request with id "{$a}"?';
$string['seecoursestitle'] = 'Courses list from request: "{$a}"?';
$string['emitrequesttitle'] = 'Emiti certificate';
$string['emitrequestbody'] = 'Are you sure, you want to emit request with id "{$a}"?';
$string['certifygenteacherrequestreport'] = 'View teacher requests certificates';
$string['othercertificates'] = 'List of "{$a}" \'s requests';
$string['mycertificate'] = 'My certificate';
$string['chooseuserprofilefield'] = 'Choose user profile field';
$string['userfield'] = 'User field';
$string['userfield_desc'] = 'This field is only used on web services to identify the user. If nothing is selected, it will be used id from user table.';
$string['report'] = 'Report';
