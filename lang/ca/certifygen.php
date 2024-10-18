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

$string['pluginname'] = 'Certificat Certifygen';
$string['pluginadministration'] = 'Mòdul d’administració del Certificat Unimoodle Certifygen';
$string['pluginnamesettings'] = 'Configuració del Certificat Unimoodle Certifygen';
$string['certifygen:addinstance'] = 'Afegeix una nova instància del Certificat Unimoodle Certifygen';
$string['certifygen:view'] = 'Veure un Certificat Unimoodle Certifygen';
$string['certifygen:manage'] = 'Gestionar certificats Unimoodle Certifygen';
$string['certifygen:canmanagecertificates'] = 'Pot gestionar certificats Unimoodle Certifygen';
$string['certifygen:viewmycontextcertificates'] = 'Veure els meus certificats Unimoodle Certifygen';
$string['certifygen:viewcontextcertificates'] = 'Veure certificats Unimoodle Certifygen d’altres professors';
$string['type'] = 'Tipus';
$string['type_help'] = 'Tria el tipus de certificat que desitges emetre. Alumne o professor.';
$string['type_1'] = 'Curs complet (per a alumnes)';
$string['type_2'] = 'Ús del curs (per a professors)';
$string['mode'] = 'Mode';
$string['mode_help'] = 'Ajuda del mode';
$string['mode_1'] = 'Únic';
$string['mode_2'] = 'Repetitiu';
$string['templateid'] = 'Plantilla';
$string['templateid_help'] = 'Selecciona una plantilla per al certificat';
$string['introduction'] = 'Introducció';
$string['modulename'] = $string['pluginname'];
$string['modulenameplural'] = 'Certificats Unimoodle Certifygen';
$string['name'] = 'Nom';
$string['modelname'] = 'Nom del model';
$string['modelidnumber'] = 'Número d’ID';
$string['contextcertificatelink'] = 'Certificat Unimoodle Certifygen - curs';
$string['chooseamodel'] = 'Tria un model';
$string['model'] = 'Model';
$string['modelsmanager'] = 'Gestió de models';
$string['associatemodels'] = 'Associar models als contextos';
$string['download'] = 'Descarregar';
$string['timeondemmand'] = 'Temps entre sol·licituds';
$string['timeondemmand_desc'] = 'Nombre de dies que han de passar fins que es pugui sol·licitar el certificat novament.';
$string['timeondemmand_help'] = 'Nombre de dies que han de passar fins que es pugui sol·licitar el certificat novament.';
$string['langs'] = 'Idiomes';
$string['chooselang'] = 'Filtra la llista per l’idioma del certificat.';
$string['validation'] = 'Tipus de generació';
$string['validation_desc'] = 'Descripció del tipus de generació';
$string['validation_help'] = 'Ajuda del tipus de generació';
$string['modelmanager'] = 'Gestió de models';
$string['create_model'] = 'Crear model';
$string['edit'] = 'Editar';
$string['delete'] = 'Eliminar';
$string['template'] = 'Plantilla';
$string['templatereport'] = 'Plantilla/Informe';
$string['lastupdate'] = 'Última actualització';
$string['actions'] = 'Accions';
$string['code'] = 'Codi';
$string['status'] = 'Estat';
$string['mycertificates'] = 'Els meus certificats Unimoodle Certifygen';
$string['deletemodeltitle'] = 'Eliminant model';
$string['deletemodelbody'] = 'Estàs segur que vols eliminar el model anomenat "{$a}"?';
$string['cannotdeletemodelcertemited'] = 'No es pot eliminar el model. Hi ha certificats associats emesos.';
$string['confirm'] = 'Confirmar';
$string['errortitle'] = 'Error';
$string['model'] = 'Model';
$string['contexts'] = 'Contextos';
$string['assigncontext'] = 'Assignar contextos';
$string['editassigncontext'] = 'Modificar assignacions';
$string['subplugintype_certifygenvalidation'] = 'Mètode de validació del certificat Unimoodle Certifygen';
$string['subplugintype_certifygenvalidation_plural'] = 'Mètodes de validació del certificat Unimoodle Certifygen';
$string['managecertifygenvalidationplugins'] = 'Gestionar els plugins de validació del certificat Unimoodle Certifygen';
$string['validationplugins'] = 'Plugins de validació';
$string['certifygenvalidationpluginname'] = $string['validationplugins'];
$string['hideshow'] = 'Amagar/Mostrar';
$string['settings'] = 'Configuració';
$string['assigncontextto'] = 'Assignar contextos al model "{$a}"';
$string['toomanycategoriestoshow'] = ' massa categories per mostrar';
$string['toomanycoursestoshow'] = ' massa cursos per mostrar';
$string['chooseacontexttype'] = 'Tria el context on cercar';
$string['writealmost3characters'] = 'Escriu almenys 1 caràcter';
$string['coursecontext'] = 'Context del curs';
$string['categorycontext'] = 'Context de la categoria';
$string['selectvalidation'] = 'Selecciona la validació del certificat';
$string['selectreport'] = 'Selecciona el tipus d’informe del certificat';
$string['nocontextcourse'] = 'Aquest curs no té permís per accedir a aquesta pàgina';
$string['hasnocapabilityrequired'] = 'No tens el permís necessari per accedir a aquesta pàgina';
$string['emit'] = 'Emetre certificat';
$string['reemit'] = 'Re-emetre certificat';
$string['status_1'] = 'No iniciat';
$string['status_2'] = 'En curs';
$string['status_3'] = 'Validat';
$string['status_4'] = 'Error de validació';
$string['status_5'] = 'Emmagatzemat';
$string['status_6'] = 'Error en l’emmagatzematge';
$string['status_7'] = 'Error';
$string['status_8'] = 'Finalitzat';
$string['status_9'] = 'Error general en certificat d\'estudiant';
$string['status_10'] = 'Error general en certificat de professor';
$string['emitcertificate_title'] = 'Emetre certificat';
$string['emitcertificate_body'] = 'Estàs segur que vols emetre el certificat a {$a}?';
$string['emitcertificate_error'] = 'S’ha produït un error en intentar emetre el certificat';
$string['certificatenotfound'] = 'Certificat no trobat';
$string['filter'] = 'Filtrar';
$string['revokecertificate_title'] = 'Revocar certificat';
$string['revokecertificate_body'] = 'Estàs segur que vols revocar el certificat a {$a}?';
$string['revokecertificate_error'] = 'S’ha produït un error en intentar revocar el certificat';
$string['downloadcertificate_title'] = 'Descarregar certificat';
$string['downloadcertificate_body'] = 'Estàs segur que vols descarregar el certificat a {$a}?';
$string['downloadcertificate_error'] = 'S’ha produït un error en intentar descarregar el certificat';
$string['notificationmsgcertificateissued'] = 'notificationmsgcertificateissued';
$string['certificatelist'] = 'Llista de certificats';
$string['selectmycertificateslangdesc'] = 'Pots seleccionar l’idioma del certificat.';
$string['system'] = 'Sistema';
$string['requestid'] = 'Número de sol·licitud';
$string['seecourses'] = 'Veure cursos';
$string['create_request'] = 'Crear sol·licitud';
$string['courseslist'] = 'Llista de cursos per certificar';
$string['deleterequesttitle'] = 'Eliminar sol·licitud';
$string['deleterequestbody'] = 'Estàs segur que vols eliminar la sol·licitud número "{$a}"?';
$string['seecoursestitle'] = 'Llista de cursos associats a la sol·licitud "{$a}"';
$string['emitrequesttitle'] = 'Emetre certificat';
$string['emitrequestbody'] = 'Estàs segur que vols emetre el certificat {$a}?';
$string['certifygenteacherrequestreport'] = 'Veure sol·licituds de certificats dels professors';
$string['othercertificates'] = 'Llistes de sol·licituds de "{$a}"';
$string['mycertificate'] = 'El meu certificat';
$string['chooseuserfield'] = 'Tria un camp d’usuari';
$string['userfield'] = 'Camp d’usuari';
$string['userfield_desc'] = 'Aquest paràmetre s’utilitza en els serveis web per identificar l’usuari. Si no es tria cap, es farà servir l’id de la taula d’usuari.';
$string['report'] = 'Plantilla per a professors';
$string['ok'] = 'Accepto';
$string['checkstatustask'] = 'Comprovar l’estat dels certificats';
$string['checkfiletask'] = 'Comprovar fitxers';
$string['teachercertificates'] = 'Certificats de professors';
$string['chooseatemplate'] = 'Tria una plantilla';
$string['managetemplates'] = 'Gestionar plantilles';
$string['repository'] = 'Repositori';
$string['repository_help'] = 'Ajuda del repositori';
$string['mycertificatesnotaccess'] = 'No tens permís per accedir a aquesta pàgina';
$string['teacherrequestreportnomodels'] = 'Encara no s’ha creat cap model associat a cursos per als certificats de professor';
$string['privacy:metadata:certifygen_validations'] = 'Informació sobre l’emissió del certificat';
$string['privacy:metadata:name'] = 'Nom del certificat (només per als certificats de professor)';
$string['privacy:metadata:courses'] = 'Els IDs de curs associats al certificat (només per als certificats de professor)';
$string['privacy:metadata:code'] = 'Codi del certificat (només per als certificats de professor)';
$string['privacy:metadata:certifygenid'] = 'ID de l’instància d’activitat (només per als certificats d’alumne)';
$string['privacy:metadata:issueid'] = 'ID d’emissió (només per als certificats d’alumne)';
$string['privacy:metadata:userid'] = 'ID de l’usuari al qual pertany el certificat';
$string['privacy:metadata:modelid'] = 'ID del model';
$string['privacy:metadata:lang'] = 'L’idioma del certificat';
$string['privacy:metadata:status'] = 'Estat del certificat';
$string['privacy:metadata:usermodified'] = 'ID d’usuari';
$string['privacy:metadata:timecreated'] = 'Temps en què es va emetre el certificat';
$string['privacy:metadata:timemodified'] = 'Temps en què es va modificar el certificat';
$string['nopermissiontoemitothercerts'] = 'No tens permís per emetre aquest certificat';
$string['nopermissiontodownloadothercerts'] = 'No tens permís per descarregar aquest certificat';
$string['nopermissiondeletemodel'] = 'No tens permís per eliminar un model';
$string['nopermissiondeleteteacherrequest'] = 'No tens permís per eliminar aquesta sol·licitud';
$string['nopermissiontogetcourses'] = 'No tens permís per obtenir cursos';
$string['repositorynotvalidwithvalidationplugin'] = 'El repositori {$a->repository} no és compatible amb el plugin de validació {$a->validation}';
$string['system'] = 'Sistema';
$string['checkerrortask'] = 'Comprovar emissions de certificats fallides';
$string['certifygenerrors'] = 'Veure errors del procés';
$string['idrequest'] = 'ID de sol·licitud';
$string['validationplugin_not_enabled'] = 'El plugin de validació no està habilitat';
$string['removefilters'] = 'Eliminar filtres';
$string['nopermissiontorevokecerts'] = 'No tens permisos per revocar un certificat';
$string['certifygen:canemitotherscertificates'] = 'Pot emetre certificats d’altres usuaris';
$string['certifygen:reemitcertificates'] = 'Pot tornar a emetre certificats';
$string['lang_not_exists'] = 'Aquest idioma no està instal·lat, {$a->lang}';
$string['coursenotexists'] = 'No existeix el curs';
$string['empty_repository_url'] = 'L’enllaç del certificat al repositori està buit';
$string['savefile_returns_error'] = 'Error en desar l’arxiu';
$string['repository_plugin_not_enabled'] = 'El complement del repositori està desactivat';
$string['getfile_missing_file_parameter'] = 'Falta el paràmetre d’arxiu';
$string['validationnotfound'] = 'No existeix el registre a la taula certifygen_validations';
$string['statusnotfinished'] = 'L’estat del certificat no està finalitzat';
$string['cannotreemit'] = 'No es pot reemetre el certificat';
$string['file_not_found'] = 'Arxiu no trobat';
$string['missingreportonmodel'] = 'Falta el paràmetre de l’informe al model';
$string['user_not_found'] = 'Usuari no trobat';
$string['lang_not_found'] = 'L’idioma no està instal·lat a la plataforma';
$string['student_not_enrolled'] = 'L’usuari no està matriculat al curs id={$a} com a estudiant';
$string['teacher_not_enrolled'] = 'L’usuari no està matriculat al curs id={$a} com a professor';
$string['model_type_assigned_to_activity'] = 'El model no està assignat a una activitat';
$string['certificate_not_ready'] = 'El certificat no està llest. L’estat és {$a}';
$string['userfield_and_userid_sent'] = 'Només s’ha d’enviar un paràmetre associat a l’usuari';
$string['userfield_not_valid'] = 'Camp d’usuari no vàlid';
$string['issue_not_found'] = 'Codi d’emissió no trobat';
$string['userfield_not_selected'] = 'No s’ha seleccionat cap camp d’usuari a la plataforma';
$string['user_not_sent'] = 'No s’ha indicat l’usuari';
$string['model_not_found'] = 'No existeix el model';
$string['model_not_valid'] = 'Model no vàlid';
$string['course_not_valid_with_model'] = 'El curs {$a} no és compatible amb el model';
$string['codeview'] = 'Cerca de certificats per codi';
$string['codefound'] = 'Hem trobat un resultat. Descarrega\'t el fitxer clicant al següent link {$a}';
$string['codenotfound'] = 'No hem trobat resultats amb aquest codi.';
$string['certifygensearchfor'] = 'Cercar certificats per codi';
$string['model_must_exists'] = 'El model ha d\'existir';
$string['course_not_valid_for_modelid'] = 'Curs no vàlid per a aquest modelid';
