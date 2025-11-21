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
 * Cadenes en català
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

$string['actions'] = 'Accions';
$string['assigncontext'] = 'Assignar contextos';
$string['assigncontextto'] = 'Assignar contextos al model "{$a}"';
$string['associatemodels'] = 'Associar models als contextos';
$string['cannot_begin_upload_session'] = 'No podeu iniciar la sessió de pujada';
$string['cannot_connect_as_current_user'] = 'No es pot connectar com a usuari actual';
$string['cannot_connect_as_system_user'] = 'No es pot connectar com a usuari del sistema';
$string['cannot_create_folder'] = 'No es pot crear la carpeta';
$string['cannot_update_link_sharing_for_document'] = "No es pot actualitzar l'enllaç compartit per al document";
$string['cannotdeletemodelcertemited'] = 'No es pot eliminar el model. Hi ha certificats associats emesos.';
$string['cannotreemit'] = 'No es pot reemetre el certificat';
$string['categorycontext'] = 'Context de la categoria';
$string['certificate_not_ready'] = 'El certificat no està llest. L’estat és {$a}';
$string['certificatelist'] = 'Llista de certificats';
$string['certificatenotfound'] = 'Certificat no trobat';
$string['certifygen:addinstance'] = 'Afegeix una nova instància del Certificat Unimoodle Certifygen';
$string['certifygen:canemitotherscertificates'] = 'Pot emetre certificats d’altres usuaris';
$string['certifygen:canmanagecertificates'] = 'Pot gestionar certificats Unimoodle Certifygen';
$string['certifygen:emitmyactivitycertificate'] = 'Emetre certificats en una activitat';
$string['certifygen:manage'] = 'Gestionar certificats Unimoodle Certifygen';
$string['certifygen:reemitcertificates'] = 'Pot tornar a emetre certificats';
$string['certifygen:view'] = 'Veure un Certificat Unimoodle Certifygen';
$string['certifygen:viewcontextcertificates'] = 'Veure certificats Unimoodle Certifygen d’altres professors';
$string['certifygen:viewmycontextcertificates'] = 'Veure els meus certificats Unimoodle Certifygen';
$string['certifygenerrors'] = 'Veure errors del procés';
$string['certifygensearchfor'] = 'Cercar certificats per codi';
$string['certifygenteacherrequestreport'] = 'Veure sol·licituds de certificats dels professors';
$string['checkerrortask'] = 'Comprovar emissions de certificats fallides';
$string['checkfiletask'] = 'Comprovar fitxers';
$string['checkstatustask'] = 'Comprovar l’estat dels certificats';
$string['chooseacontexttype'] = 'Tria el context on cercar';
$string['chooseamodel'] = 'Tria un model';
$string['chooseatemplate'] = 'Tria una plantilla';
$string['chooselang'] = 'Filtra la llista per l’idioma del certificat.';
$string['chooseuserfield'] = 'Tria un camp d’usuari';
$string['code'] = 'Codi';
$string['codefound'] = 'Hem trobat un resultat. Descarrega\'t el fitxer clicant al següent link {$a}';
$string['codenotfound'] = 'No hem trobat resultats amb aquest codi.';
$string['codeview'] = 'Cerca de certificats per codi';
$string['completiondownload'] = 'Completació per descàrrega de certificat';
$string['completiondownloaddesc'] = 'Els participants han de descarregar un certificat per finalitzar l’activitat.';
$string['configurated_logo'] = 'Log configurat';
$string['confirm'] = 'Confirmar';
$string['contextcertificatelink'] = 'Certificat Unimoodle Certifygen - curs';
$string['contexts'] = 'Contextos';
$string['course_not_valid_for_modelid'] = 'No es pot restaurar l\'activitat {$a->activityname}. El curs ({$a->courseid}) no és vàlid per a aquest model (nom: {$a->name}, idnumber: {$a->idnumber})';
$string['course_not_valid_with_model'] = 'El curs {$a} no és compatible amb el model';
$string['coursecontext'] = 'Context del curs';
$string['coursenotexists'] = 'No existeix el curs';
$string['courseslist'] = 'Llista de cursos per certificar';
$string['create_model'] = 'Crear model';
$string['create_request'] = 'Crear sol·licitud';
$string['delete'] = 'Eliminar';
$string['deletemodelbody'] = 'Estàs segur que vols eliminar el model anomenat "{$a}"?';
$string['deletemodeltitle'] = 'Eliminant model';
$string['deleterequestbody'] = 'Estàs segur que vols eliminar la sol·licitud número "{$a}"?';
$string['deleterequesttitle'] = 'Eliminar sol·licitud';
$string['download'] = 'Descarregar';
$string['downloadcertificate_body'] = 'Estàs segur que vols descarregar el certificat a {$a}?';
$string['downloadcertificate_error'] = 'S’ha produït un error en intentar descarregar el certificat';
$string['downloadcertificate_title'] = 'Descarregar certificat';
$string['edit'] = 'Editar';
$string['editassigncontext'] = 'Modificar assignacions';
$string['emit'] = 'Emetre certificat';
$string['emitcertificate_body'] = 'Estàs segur que vols emetre el certificat a {$a}?';
$string['emitcertificate_error'] = 'S’ha produït un error en intentar emetre el certificat';
$string['emitcertificate_title'] = 'Emetre certificat';
$string['emitrequestbody'] = 'Estàs segur que vols emetre el certificat {$a}?';
$string['emitrequesttitle'] = 'Emetre certificat';
$string['empty_repository_url'] = 'L’enllaç del certificat al repositori està buit';
$string['errortitle'] = 'Error';
$string['file_not_found'] = 'Arxiu no trobat';
$string['filter'] = 'Filtrar';
$string['getfile_missing_file_parameter'] = 'Falta el paràmetre d’arxiu';
$string['hasnocapabilityrequired'] = 'No tens el permís necessari per accedir a aquesta pàgina';
$string['hideshow'] = 'Amagar/Mostrar';
$string['idrequest'] = 'ID de sol·licitud';
$string['introduction'] = 'Introducció';
$string['invalid_language'] = 'Invalid language';
$string['issue_not_found'] = 'Codi d’emissió no trobat';
$string['lang'] = 'Idioma';
$string['lang_not_exists'] = 'Aquest idioma no està instal·lat, {$a->lang}';
$string['lang_not_found'] = 'L’idioma no està instal·lat a la plataforma';
$string['langs'] = 'Idiomes';
$string['lastupdate'] = 'Última actualització';
$string['managecertifygenvalidationplugins'] = 'Gestionar els plugins de validació del certificat Unimoodle Certifygen';
$string['managetemplates'] = 'Gestionar plantilles';
$string['messageprovider:certifygen_notification'] = 'Certifygen notificació';
$string['missingreportonmodel'] = 'Falta el paràmetre de l’informe al model';
$string['mode'] = 'Mode';
$string['mode_1'] = 'Únic';
$string['mode_2'] = 'Repetitiu';
$string['mode_help'] = 'Ajuda del mode';
$string['model'] = 'Model';
$string['model_must_exists'] = 'No es pot restaurar l\'activitat {$a->activityname}. Hi ha d\'haver un model amb idnumber igual a {$a->idnumber}';
$string['model_not_found'] = 'No existeix el model';
$string['model_not_valid'] = 'Model no vàlid';
$string['model_type_assigned_to_activity'] = 'El model està assignat a una activitat';
$string['modelidnumber'] = 'Número d’ID';
$string['modelmanager'] = 'Gestió de models';
$string['modelname'] = 'Nom del model';
$string['modelsmanager'] = 'Gestió de models';
$string['modulename'] = 'Certificat Certifygen';
$string['modulenameplural'] = 'Certificats Unimoodle Certifygen';
$string['mycertificate'] = 'El meu certificat';
$string['mycertificates'] = 'Els meus certificats Unimoodle Certifygen';
$string['mycertificatesnotaccess'] = 'No tens permís per accedir a aquesta pàgina';
$string['name'] = 'Nom';
$string['nocontextassociated'] = 'Aquest model no té cap context associat.';
$string['nocontextcourse'] = 'Aquest curs no té permís per accedir a aquesta pàgina';
$string['nopermissiondeletemodel'] = 'No tens permís per eliminar un model';
$string['nopermissiondeleteteacherrequest'] = 'No tens permís per eliminar aquesta sol·licitud';
$string['nopermissiontodownloadothercerts'] = 'No tens permís per descarregar aquest certificat';
$string['nopermissiontoemitothercerts'] = 'No tens permís per emetre aquest certificat';
$string['nopermissiontogetcourses'] = 'No tens permís per obtenir cursos';
$string['nopermissiontorevokecerts'] = 'No tens permisos per revocar un certificat';
$string['notificationmsgcertificateissued'] = 'notificationmsgcertificateissued';
$string['ok'] = 'Accepto';
$string['othercertificates'] = 'Llistes de sol·licituds de "{$a}"';
$string['pluginadministration'] = 'Mòdul d’administració del Certificat Unimoodle Certifygen';
$string['pluginname'] = 'Certificat Certifygen';
$string['pluginnamesettings'] = 'Configuració del Certificat Unimoodle Certifygen';
$string['privacy:metadata:certifygen_repository'] = 'Informació sobre la ubicació del certificat';
$string['privacy:metadata:certifygen_validations'] = 'Informació sobre l’emissió del certificat';
$string['privacy:metadata:certifygenid'] = 'ID de l’instància d’activitat (només per als certificats d’alumne)';
$string['privacy:metadata:code'] = 'Codi del certificat (només per als certificats de professor)';
$string['privacy:metadata:courses'] = 'Els IDs de curs associats al certificat (només per als certificats de professor)';
$string['privacy:metadata:data'] = 'Dades relacionades amb la ubicació del repositori';
$string['privacy:metadata:issueid'] = 'ID d’emissió (només per als certificats d’alumne)';
$string['privacy:metadata:lang'] = 'L’idioma del certificat';
$string['privacy:metadata:modelid'] = 'ID del model';
$string['privacy:metadata:name'] = 'Nom del certificat (només per als certificats de professor)';
$string['privacy:metadata:status'] = 'Estat del certificat';
$string['privacy:metadata:timecreated'] = 'Temps en què es va emetre el certificat';
$string['privacy:metadata:timemodified'] = 'Temps en què es va modificar el certificat';
$string['privacy:metadata:url'] = 'Enllaç a la ubicació del repositori';
$string['privacy:metadata:userid'] = 'ID de l’usuari al qual pertany el certificat';
$string['privacy:metadata:usermodified'] = 'ID d’usuari';
$string['privacy:metadata:validationid'] = 'L’ID de la instància de validació del certificat';
$string['reemit'] = 'Re-emetre certificat';
$string['removefilters'] = 'Eliminar filtres';
$string['report'] = 'Plantilla per a professors';
$string['repository'] = 'Repositori';
$string['repository_help'] = 'Ajuda del repositori';
$string['repository_plugin_not_enabled'] = 'El complement del repositori està desactivat';
$string['repositorynotvalidwithvalidationplugin'] = 'El repositori {$a->repository} no és compatible amb el plugin de validació {$a->validation}';
$string['requestid'] = 'Número de sol·licitud';
$string['results'] = 'Resultats';
$string['revokecertificate_body'] = 'Estàs segur que vols revocar el certificat a {$a}?';
$string['revokecertificate_error'] = 'S’ha produït un error en intentar revocar el certificat';
$string['revokecertificate_title'] = 'Revocar certificat';
$string['savefile_returns_error'] = 'Error en desar l’arxiu';
$string['seecourses'] = 'Veure cursos';
$string['seecoursestitle'] = 'Llista de cursos associats a la sol·licitud "{$a}"';
$string['selectmycertificateslangdesc'] = 'Pots seleccionar l’idioma del certificat.';
$string['selectreport'] = 'Selecciona el tipus d’informe del certificat';
$string['selectvalidation'] = 'Selecciona la validació del certificat';
$string['settings'] = 'Configuració';
$string['status'] = 'Estat';
$string['status_1'] = 'No iniciat';
$string['status_10'] = 'Error general en certificat de professor';
$string['status_2'] = 'En curs';
$string['status_3'] = 'Validat';
$string['status_4'] = 'Error de validació';
$string['status_5'] = 'Emmagatzemat';
$string['status_6'] = 'Error en l’emmagatzematge';
$string['status_7'] = 'Error';
$string['status_9'] = 'Error general en certificat d\'estudiant';
$string['statusnotfinished'] = 'L’estat del certificat no està finalitzat';
$string['student_not_enrolled'] = 'L’usuari no està matriculat al curs id={$a} com a estudiant';
$string['subplugintype_certifygenvalidation'] = 'Mètode de validació del certificat Unimoodle Certifygen';
$string['subplugintype_certifygenvalidation_plural'] = 'Mètodes de validació del certificat Unimoodle Certifygen';
$string['system'] = 'Sistema';
$string['teacher_not_enrolled'] = 'L’usuari no està matriculat al curs id={$a} com a professor';
$string['teachercertificates'] = 'Certificats de professors';
$string['teacherrequestreportnomodels'] = 'Encara no s’ha creat cap model associat a cursos per als certificats de professor';
$string['template'] = 'Plantilla';
$string['templateid'] = 'Plantilla';
$string['templateid_help'] = 'Selecciona una plantilla per al certificat';
$string['templatenotfound'] = 'Hi ha un problema amb la configuració de l\'activitat. De moment no es pot fer ús d\'aquesta.';
$string['templatereport'] = 'Plantilla/Informe';
$string['timeondemmand'] = 'Temps entre sol·licituds';
$string['timeondemmand_desc'] = 'Nombre de dies que han de passar fins que es pugui sol·licitar el certificat novament.';
$string['timeondemmand_help'] = 'Nombre de dies que han de passar fins que es pugui sol·licitar el certificat novament.';
$string['toomanycategoriestoshow'] = ' massa categories per mostrar';
$string['toomanycoursestoshow'] = ' massa cursos per mostrar';
$string['type'] = 'Tipus';
$string['type_1'] = 'Curs complet (per a alumnes)';
$string['type_2'] = 'Ús del curs (per a professors)';
$string['type_help'] = 'Tria el tipus de certificat que desitges emetre. Alumne o professor.';
$string['user_not_found'] = 'Usuari no trobat';
$string['user_not_sent'] = 'No s’ha indicat l’usuari';
$string['userfield'] = 'Camp d’usuari';
$string['userfield_and_userid_sent'] = 'Només s’ha d’enviar un paràmetre associat a l’usuari';
$string['userfield_desc'] = 'Aquest paràmetre s’utilitza en els serveis web per identificar l’usuari. Si no es tria cap, es farà servir l’id de la taula d’usuari.';
$string['userfield_not_selected'] = 'No s’ha seleccionat cap camp d’usuari a la plataforma';
$string['userfield_not_valid'] = 'Camp d’usuari no vàlid';
$string['validation'] = 'Tipus de generació';
$string['validation_desc'] = 'Descripció del tipus de generació';
$string['validation_help'] = 'Ajuda del tipus de generació';
$string['validationnotfound'] = 'No existeix el registre a la taula certifygen_validations';
$string['validationnotvalidwithrepositoryplugin'] = 'El validació {$a->validation} no és compatible amb el plugin de repositori {$a->repository}';
$string['validationplugin_not_enabled'] = 'El plugin de validació no està habilitat';
$string['validationplugins'] = 'Plugins de validació';
$string['writealmost3characters'] = 'Escriu almenys 1 caràcter';
