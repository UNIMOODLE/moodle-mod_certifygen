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
$string['certifygen:canmanagecertificates'] = 'Can Manage Certificates an unimoodle certifygen certificate';
$string['certifygen:viewmycontextcertificates'] = 'View My Context Certificates an unimoodle certifygen certificate';
$string['certifygen:viewcontextcertificates'] = 'View Context Certificates an unimoodle certifygen certificate';
$string['certifygen:emitmyactivitycertificate'] = 'Issuing certificates in an activity';
$string['type'] = 'Type';
$string['type_help'] = 'Choose the type of certificate you want to issue, for students or teachers.';
$string['type_1'] = 'Course completion (for students)';
$string['type_2'] = 'Course used (for teachers)';
$string['mode'] = 'Mode';
$string['mode_help'] = 'Mode help';
$string['mode_1'] = 'Unique';
$string['mode_2'] = 'Recurrent';
$string['templateid'] = 'Template';
$string['templateid_help'] = 'Select the template you want to use.';
$string['introduction'] = 'Introduction';
$string['modulename'] = 'Unimoodle Certifygen';
$string['modulenameplural'] = 'Unimoodle Certifygens';
$string['name'] = 'Name';
$string['modelname'] = 'Model name';
$string['modelidnumber'] = 'Idnumber';
$string['contextcertificatelink'] = 'Unimoodle Certifygen Course Certificate';
$string['chooseamodel'] = 'Choose a model';
$string['model'] = 'Model';
$string['modelsmanager'] = 'Models manager';
$string['associatemodels'] = 'Associate models to Contexts';
$string['download'] = 'Download';
$string['timeondemmand'] = 'Time betwwen demmands';
$string['timeondemmand_desc'] = 'Number of days that must elapse until the certificate can be requested again.';
$string['timeondemmand_help'] = 'Number of days that must elapse until the certificate can be requested again.';
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
$string['cannotdeletemodelcertemited'] = 'Model can not be deleted. There already are certifies emitted';
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
$string['hideshow'] = 'Hide/Show';
$string['settings'] = 'Settings';
$string['assigncontextto'] = 'Assigning context to model "{$a}"';
$string['toomanycategoriestoshow'] = 'Too many categories to show';
$string['toomanycoursestoshow'] = 'Too many courses to show';
$string['chooseacontexttype'] = 'Choose the context in which it till search';
$string['writealmost3characters'] = 'Write almost 1 character';
$string['coursecontext'] = 'Course context';
$string['categorycontext'] = 'Category context';
$string['selectvalidation'] = 'Select generation type';
$string['selectreport'] = 'Select report type';
$string['nocontextcourse'] = 'This course has no access to this page';
$string['hasnocapabilityrequired'] = 'You do not have permission required';
$string['emit'] = 'Emit certificate';
$string['reemit'] = 'Re-emit certificate';
$string['status_1'] = 'Not started';
$string['status_2'] = 'In progress';
$string['status_3'] = 'Validated';
$string['status_4'] = 'Validation Error';
$string['status_5'] = 'Storaged OK';
$string['status_6'] = 'Storaged Error';
$string['status_7'] = 'Error';
$string['status_8'] = 'Finished';
$string['status_9'] = 'General error in student certificate';
$string['status_10'] = 'General error in teacher certificate';
$string['emitcertificate_title'] = 'Emit Certificate';
$string['emitcertificate_body'] = 'Are you sure, you want to emit the certificate in {$a}?';
$string['emitcertificate_error'] = 'There was an error trying to emit the certificate';
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
$string['seecoursestitle'] = 'Courses list from request "{$a}"';
$string['emitrequesttitle'] = 'Emiti certificate';
$string['emitrequestbody'] = 'Are you sure, you want to emit request with id "{$a}"?';
$string['certifygenteacherrequestreport'] = 'View teacher requests certificates';
$string['othercertificates'] = 'List of "{$a}" \'s requests';
$string['mycertificate'] = 'My certificate';
$string['chooseuserfield'] = 'Choose user field';
$string['userfield'] = 'User field';
$string['userfield_desc'] = 'This field is only used on web services to identify the user. If nothing is selected, it will be used id from user table.';
$string['report'] = 'Teacher Template';
$string['ok'] = 'OK';
$string['checkstatustask'] = 'Check status';
$string['checkfiletask'] = 'Check file';
$string['teachercertificates'] = 'Teacher\'s certificates';
$string['chooseatemplate'] = 'Choose a template';
$string['managetemplates'] = 'Manage templates';
$string['repository'] = 'Repository';
$string['repository_help'] = 'Repository help';
$string['mycertificatesnotaccess'] = 'You can not get access to this page';
$string['teacherrequestreportnomodels'] = 'There are not any model associated to teacher requests';
$string['privacy:metadata:certifygen_validations'] = 'Information about the certificate issuance';
$string['privacy:metadata:name'] = 'The certificate name (only for a teacher certificate)';
$string['privacy:metadata:courses'] = 'The courses ids associated to the certificate (only for a teacher certificate)';
$string['privacy:metadata:code'] = 'The certificate code (only for a teacher certificate)';
$string['privacy:metadata:certifygenid'] = 'The activity instance id (only for a student certificate)';
$string['privacy:metadata:issueid'] = 'The issue id (only for a student certificate)';
$string['privacy:metadata:userid'] = 'The ID of the user who owns the certificate.';
$string['privacy:metadata:modelid'] = 'The model id.';
$string['privacy:metadata:lang'] = 'The language  of the certificate';
$string['privacy:metadata:status'] = 'The certificate status';
$string['privacy:metadata:usermodified'] = 'The user id who issue the certificate';
$string['privacy:metadata:timecreated'] = 'The time when the certificate is issued';
$string['privacy:metadata:timemodified'] = 'The time when the certificate issuance was modified';
$string['nopermissiontoemitothercerts'] = 'You have no permission to issue this certificate';
$string['nopermissiontodownloadothercerts'] = 'You have no permission to download this certificate';
$string['nopermissiondeletemodel'] = 'You have no permission to delete a model';
$string['nopermissiondeleteteacherrequest'] = 'You have no permission to delete this request';
$string['nopermissiontogetcourses'] = 'You have no permission to get courses';
$string['repositorynotvalidwithvalidationplugin'] = '{$a->repository} repository plugin is not compatible with {$a->validation} validation plugin.';
$string['system'] = 'System';
$string['checkerrortask'] = 'Check failed certificate issuances';
$string['certifygenerrors'] = 'View certifygen errors';
$string['idrequest'] = 'Request id';
$string['validationplugin_not_enabled'] = 'Validation plugin not enabled';
$string['removefilters'] = 'Remove filters';
$string['nopermissiontorevokecerts'] = 'You do not have permissions to revoke a certificate';
$string['certifygen:canemitotherscertificates'] = 'You can issue certificates to other users';
$string['certifygen:reemitcertificates'] = 'You can reissue certificates';
$string['lang_not_exists'] = 'Language not installed {$a->lang}';
$string['coursenotexists'] = 'Course not exists';
$string['empty_repository_url'] = 'The certificate link in the repository is empty';
$string['savefile_returns_error'] = 'Error saving the file';
$string['repository_plugin_not_enabled'] = 'The repository plugin is disabled';
$string['getfile_missing_file_parameter'] = 'The file parameter is missing';
$string['validationnotfound'] = 'The record does not exist in the certifygen_validations table';
$string['statusnotfinished'] = 'The certificate status is not finished';
$string['cannotreemit'] = 'The certificate cannot be reissued';
$string['file_not_found'] = 'File not found';
$string['missingreportonmodel'] = 'The report parameter is missing in the model';
$string['user_not_found'] = 'User not found';
$string['lang_not_found'] = 'Language not installed on the platform';
$string['student_not_enrolled'] = 'The user is not enrolled in the course id={$a} as a student';
$string['teacher_not_enrolled'] = 'The user is not enrolled in the course id={$a} as a teacher';
$string['model_type_assigned_to_activity'] = 'The model is not assigned to an activity';
$string['certificate_not_ready'] = 'The certificate is not ready. The status is {$a}';
$string['userfield_and_userid_sent'] = 'Only one parameter associated with the user should be sent';
$string['userfield_not_valid'] = 'Invalid user field';
$string['issue_not_found'] = 'Issue code not found';
$string['userfield_not_selected'] = 'No user field has been selected on the platform';
$string['user_not_sent'] = 'User not specified';
$string['model_not_found'] = 'Model not found';
$string['model_not_valid'] = 'Invalid model';
$string['course_not_valid_with_model'] = 'The course, {$a}, is not compatible with the model';
$string['codeview'] = 'Search for certificates by code';
$string['codefound'] = 'We have found a result. Download the file by clicking on the following link {$a}';
$string['codenotfound'] = 'We have not found any results with this code';
$string['certifygensearchfor'] = 'Search for certificates by code';
$string['model_must_exists'] = 'Cannot restore activity {$a->activityname}. A model with idnumber equal to {$a->idnumber} must exist.';
$string['course_not_valid_for_modelid'] = 'Cannot restore activity {$a->activityname}. The course ({$a->courseid}) is not valid for this model(name: {$a->name}, idnumber: {$a->idnumber})';
$string['templatenotfound'] = 'There is a problem with the activity configuration. The activity cannot be used at the moment.';
