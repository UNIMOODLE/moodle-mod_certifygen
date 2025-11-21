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

$string['actions'] = 'Actions';
$string['assigncontext'] = 'Assign contexts';
$string['assigncontextto'] = 'Assigning context to model "{$a}"';
$string['associatemodels'] = 'Associate models to contexts';
$string['cannot_begin_upload_session'] = 'Cannot begin upload session';
$string['cannot_connect_as_current_user'] = 'Cannot connect as current user';
$string['cannot_connect_as_system_user'] = 'Cannot connect as system user';
$string['cannot_create_folder'] = 'Cannot create folder';
$string['cannot_update_link_sharing_for_document'] = 'Cannot update link sharing for document';
$string['cannotdeletemodelcertemited'] = 'Model cannot be deleted. Certificates have already been issued.';
$string['cannotreemit'] = 'The certificate cannot be reissued';
$string['categorycontext'] = 'Category context';
$string['certificate_not_ready'] = 'The certificate is not ready. The status is {$a}';
$string['certificatelist'] = 'Certificate list';
$string['certificatenotfound'] = 'Certificate not found';
$string['certifygen:addinstance'] = 'Add a new unimoodle certifygen instance';
$string['certifygen:canemitotherscertificates'] = 'You can issue certificates to other users';
$string['certifygen:canmanagecertificates'] = 'Can manage certificates for a Unimoodle Certifygen activity';
$string['certifygen:emitmyactivitycertificate'] = 'Issuing certificates in an activity';
$string['certifygen:manage'] = 'Manage an unimoodle certifygen certificate';
$string['certifygen:reemitcertificates'] = 'You can reissue certificates';
$string['certifygen:view'] = 'View an unimoodle certifygen certificate';
$string['certifygen:viewcontextcertificates'] = 'View Context Certificates an unimoodle certifygen certificate';
$string['certifygen:viewmycontextcertificates'] = 'View My Context Certificates an unimoodle certifygen certificate';
$string['certifygenerrors'] = 'View certifygen errors';
$string['certifygensearchfor'] = 'Search for certificates by code';
$string['certifygenteacherrequestreport'] = 'View teacher requests certificates';
$string['checkerrortask'] = 'Check failed certificate issuances';
$string['checkfiletask'] = 'Check file';
$string['checkstatustask'] = 'Check status';
$string['chooseacontexttype'] = 'Choose the context in which it will search';
$string['chooseamodel'] = 'Choose a model';
$string['chooseatemplate'] = 'Choose a template';
$string['chooselang'] = 'You can filter the certificate list by its language';
$string['chooseuserfield'] = 'Choose user field';
$string['code'] = 'Code';
$string['codefound'] = 'We have found a result. Download the file by clicking on the following link {$a}';
$string['codenotfound'] = 'We have not found any results with this code';
$string['codeview'] = 'Search for certificates by code';
$string['completiondownload'] = 'Completion by certificate download';
$string['completiondownloaddesc'] = 'Participants must download a certificate to complete the activity.';
$string['configurated_logo'] = 'Configurated logo';
$string['confirm'] = 'Confirm';
$string['contextcertificatelink'] = 'Unimoodle Certifygen Course Certificate';
$string['contexts'] = 'Contexts';
$string['course_not_valid_for_modelid'] = 'Cannot restore activity {$a->activityname}. The course ({$a->courseid}) is not valid for this model(name: {$a->name}, idnumber: {$a->idnumber})';
$string['course_not_valid_with_model'] = 'The course, {$a}, is not compatible with the model';
$string['coursecontext'] = 'Course context';
$string['coursenotexists'] = 'Course not exists';
$string['courseslist'] = 'Course list for certificates';
$string['create_model'] = 'Create Model';
$string['create_request'] = 'Create Request';
$string['delete'] = 'delete';
$string['deletemodelbody'] = 'Are you sure, you want to delete model called "{$a}"?';
$string['deletemodeltitle'] = 'Deleting Model';
$string['deleterequestbody'] = 'Are you sure, you want to delete request with id "{$a}"?';
$string['deleterequesttitle'] = 'Delete Request';
$string['download'] = 'Download';
$string['downloadcertificate_body'] = 'Are you sure, you want to download the certificate in {$a}?';
$string['downloadcertificate_error'] = 'There was an error trying to download the certificate';
$string['downloadcertificate_title'] = 'Download Certificate';
$string['edit'] = 'Edit';
$string['editassigncontext'] = 'Modify contexts assignment';
$string['emit'] = 'Emit certificate';
$string['emitcertificate_body'] = 'Are you sure, you want to emit the certificate in {$a}?';
$string['emitcertificate_error'] = 'There was an error trying to emit the certificate';
$string['emitcertificate_title'] = 'Emit Certificate';
$string['emitrequestbody'] = 'Are you sure, you want to emit request with id "{$a}"?';
$string['emitrequesttitle'] = 'Emit certificate';
$string['empty_repository_url'] = 'The certificate link in the repository is empty';
$string['errortitle'] = 'Error';
$string['file_not_found'] = 'File not found';
$string['filter'] = 'Filter';
$string['getfile_missing_file_parameter'] = 'The file parameter is missing';
$string['hasnocapabilityrequired'] = 'You do not have the required permission';
$string['hideshow'] = 'Hide/Show';
$string['idrequest'] = 'Request id';
$string['introduction'] = 'Introduction';
$string['invalid_language'] = 'Invalid language';
$string['issue_not_found'] = 'Issue code not found';
$string['lang'] = 'Language';
$string['lang_not_exists'] = 'Language not installed: {$a->lang}';
$string['lang_not_found'] = 'Language not installed on the platform';
$string['langs'] = 'Languages';
$string['lastupdate'] = 'Last Update';
$string['managecertifygenvalidationplugins'] = 'Manage unimoodle certifygen validation plugins';
$string['managetemplates'] = 'Manage templates';
$string['messageprovider:certifygen_notification'] = 'Certifygen notifications';
$string['missingreportonmodel'] = 'The report parameter is missing in the model';
$string['mode'] = 'Mode';
$string['mode_1'] = 'Unique';
$string['mode_2'] = 'Recurrent';
$string['mode_help'] = 'Mode help';
$string['model'] = 'Model';
$string['model_must_exists'] = 'Cannot restore activity {$a->activityname}. A model with idnumber equal to {$a->idnumber} must exist.';
$string['model_not_found'] = 'Model not found';
$string['model_not_valid'] = 'Invalid model';
$string['model_type_assigned_to_activity'] = 'The model is not assigned to an activity';
$string['modelidnumber'] = 'Idnumber';
$string['modelmanager'] = 'Model Manager';
$string['modelname'] = 'Model name';
$string['modelsmanager'] = 'Models manager';
$string['modulename'] = 'Unimoodle Certifygen';
$string['modulenameplural'] = 'Unimoodle Certifygens';
$string['mycertificate'] = 'My certificate';
$string['mycertificates'] = 'My Unimoodle Certifygen certificates';
$string['mycertificatesnotaccess'] = 'You can not get access to this page';
$string['name'] = 'Name';
$string['nocontextassociated'] = 'This model has no associated context.';
$string['nocontextcourse'] = 'This course has no access to this page';
$string['nopermissiondeletemodel'] = 'You have no permission to delete a model';
$string['nopermissiondeleteteacherrequest'] = 'You have no permission to delete this request';
$string['nopermissiontodownloadothercerts'] = 'You have no permission to download this certificate';
$string['nopermissiontoemitothercerts'] = 'You have no permission to issue this certificate';
$string['nopermissiontogetcourses'] = 'You have no permission to get courses';
$string['nopermissiontorevokecerts'] = 'You do not have permissions to revoke a certificate';
$string['notificationmsgcertificateissued'] = 'Hi {$a->fullname},<br /><br />Your certificate is available! You will find it here:
<a href="{$a->url}">{$a->linkname}</a>';
$string['notificationsubjectcertificateissued'] = 'Your certifygen certificate is ready';
$string['ok'] = 'OK';
$string['othercertificates'] = 'List of "{$a}" \'s requests';
$string['pluginadministration'] = 'Unimoodle certifygen module administration';
$string['pluginname'] = 'Unimoodle certifygen';
$string['pluginnamesettings'] = 'Unimoodle certifygen Settings';
$string['privacy:metadata:certifygen_repository'] = 'Information about the certificate location';
$string['privacy:metadata:certifygen_validations'] = 'Information about the certificate issuance';
$string['privacy:metadata:certifygenid'] = 'The activity instance id (only for a student certificate)';
$string['privacy:metadata:code'] = 'The certificate code (only for a teacher certificate)';
$string['privacy:metadata:courses'] = 'The courses ids associated to the certificate (only for a teacher certificate)';
$string['privacy:metadata:data'] = 'Data related to the repository location';
$string['privacy:metadata:issueid'] = 'The issue id (only for a student certificate)';
$string['privacy:metadata:lang'] = 'The language  of the certificate';
$string['privacy:metadata:modelid'] = 'The model id.';
$string['privacy:metadata:name'] = 'The certificate name (only for a teacher certificate)';
$string['privacy:metadata:status'] = 'The certificate status';
$string['privacy:metadata:timecreated'] = 'The time when the certificate is issued';
$string['privacy:metadata:timemodified'] = 'The time when the certificate issuance was modified';
$string['privacy:metadata:url'] = 'Link to the repository location';
$string['privacy:metadata:userid'] = 'The ID of the user who owns the certificate.';
$string['privacy:metadata:usermodified'] = 'The user id who issue the certificate';
$string['privacy:metadata:validationid'] = 'The ID of the certificate validation instance';
$string['reemit'] = 'Re-emit certificate';
$string['removefilters'] = 'Remove filters';
$string['report'] = 'Teacher Template';
$string['repository'] = 'Repository';
$string['repository_help'] = 'Repository help';
$string['repository_plugin_not_enabled'] = 'The repository plugin is disabled';
$string['repositorynotvalidwithvalidationplugin'] = '{$a->repository} repository plugin is not compatible with {$a->validation} validation plugin.';
$string['requestid'] = 'Request Number';
$string['results'] = 'Results';
$string['revokecertificate_body'] = 'Are you sure, you want to revoke the certificate in {$a}?';
$string['revokecertificate_error'] = 'There was an error trying to revoke the certificate';
$string['revokecertificate_title'] = 'Revoke Certificate';
$string['savefile_returns_error'] = 'Error saving the file';
$string['seecourses'] = 'See Courses';
$string['seecoursestitle'] = 'Courses list from request "{$a}"';
$string['selectmycertificateslangdesc'] = 'You can select the language of your certificate';
$string['selectreport'] = 'Select report type';
$string['selectvalidation'] = 'Select generation type';
$string['settings'] = 'Settings';
$string['status'] = 'Status';
$string['status_1'] = 'Not started';
$string['status_10'] = 'General error in teacher certificate';
$string['status_2'] = 'In progress';
$string['status_3'] = 'Validated';
$string['status_4'] = 'Validation Error';
$string['status_5'] = 'Storaged OK';
$string['status_6'] = 'Storaged Error';
$string['status_7'] = 'Error';
$string['status_8'] = 'Id: {$a->code}';
$string['status_9'] = 'General error in student certificate';
$string['statusnotfinished'] = 'The certificate status is not finished';
$string['student_not_enrolled'] = 'The user is not enrolled in the course id={$a} as a student';
$string['subplugintype_certifygenreport_plural'] = 'Unimoodle certifygen teacher\'s usage report methods';
$string['subplugintype_certifygenrepository_plural'] = 'Unimoodle certifygen repositories methods';
$string['subplugintype_certifygenvalidation'] = 'Unimoodle certifygen validation method';
$string['subplugintype_certifygenvalidation_plural'] = 'Unimoodle certifygen validations methods';
$string['system'] = 'System';
$string['teacher_not_enrolled'] = 'The user is not enrolled in the course id={$a} as a teacher';
$string['teachercertificates'] = 'Teacher\'s certificates';
$string['teacherrequestreportnomodels'] = 'There are not any model associated to teacher requests';
$string['template'] = 'Template';
$string['templateid'] = 'Template';
$string['templateid_help'] = 'Select the template you want to use.';
$string['templatenotfound'] = 'There is a problem with the activity configuration. The activity cannot be used at the moment.';
$string['templatereport'] = 'Template/Report';
$string['timeondemmand'] = 'Time between requests';
$string['timeondemmand_desc'] = 'Number of days that must elapse before the certificate can be requested again.';
$string['timeondemmand_help'] = 'Number of days that must elapse before the certificate can be requested again.';
$string['toomanycategoriestoshow'] = 'Too many categories to show';
$string['toomanycoursestoshow'] = 'Too many courses to show';
$string['type'] = 'Type';
$string['type_1'] = 'Course completion (for students)';
$string['type_2'] = 'Course used (for teachers)';
$string['type_help'] = 'Choose the type of certificate you want to issue, for students or teachers.';
$string['user_not_found'] = 'User not found';
$string['user_not_sent'] = 'User not specified';
$string['userfield'] = 'User field';
$string['userfield_and_userid_sent'] = 'Only one parameter associated with the user should be sent';
$string['userfield_desc'] = 'This field is only used on web services to identify the user. If nothing is selected, it will be used id from user table.';
$string['userfield_not_selected'] = 'No user field has been selected on the platform';
$string['userfield_not_valid'] = 'Invalid user field';
$string['validation'] = 'Validation';
$string['validation_desc'] = 'Validation description';
$string['validation_help'] = 'Validation help';
$string['validationnotfound'] = 'The record does not exist in the certifygen_validations table';
$string['validationnotvalidwithrepositoryplugin'] = 'The validation plugin {$a->validation} is not compatible with the repository plugin {$a->repository}.';
$string['validationplugin_not_enabled'] = 'Validation plugin not enabled';
$string['validationplugins'] = 'Validation plugins';
$string['writealmost3characters'] = 'Enter at least one character';
