
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
$string['pluginadministration'] = 'Mòdul d\'administració del certificat Unimoodle Certifygen';
$string['pluginnamesettings'] = 'Configuració del certificat Unimoodle Certifygen';
$string['certifygen:addinstance'] = 'Afegeix una nova instància del certificat Unimoodle Certifygen';
$string['certifygen:view'] = 'Veure el certificat Unimoodle Certifygen';
$string['certifygen:manage'] = 'Gestionar els certificats Unimoodle Certifygen';
$string['certifygen:canmanagecertificates'] = 'Pot gestionar els certificats Unimoodle Certifygen';
$string['certifygen:viewmycontextcertificates'] = 'Veure els meus certificats Unimoodle Certifygen';
$string['certifygen:viewcontextcertificates'] = 'Veure els certificats Unimoodle Certifygen d\'altres professors';
$string['type'] = 'Tipus';
$string['type_help'] = 'Selecciona el tipus de certificat que vols emetre. Estudiants o professors.';
$string['type_1'] = 'Curs complet (per a estudiants)';
$string['type_2'] = 'Ús del curs (per a professors)';
$string['mode'] = 'Mode';
$string['mode_help'] = 'Ajuda del mode';
$string['mode_1'] = 'Únic';
$string['mode_2'] = 'Recurrent';
$string['templateid'] = 'Plantilla';
$string['templateid_help'] = 'Selecciona la plantilla del certificat';
$string['introduction'] = 'Introducció';
$string['modulename'] = $string['pluginname'];
$string['modulenameplural'] = 'Certificats Unimoodle Certifygen';
$string['name'] = 'Nom';
$string['modelname'] = 'Nom de la plantilla';
$string['modelidnumber'] = 'Número d\'ID';
$string['contextcertificatelink'] = 'Certificat Unimoodle Certifygen - curs';
$string['chooseamodel'] = 'Tria una plantilla';
$string['model'] = 'Plantilla';
$string['modelsmanager'] = 'Gestió de plantilles';
$string['associatemodels'] = 'Associeu plantilles a contextos';
$string['download'] = 'Descarrega';
$string['timeondemmand'] = 'Temps entre sol·licituds';
$string['timeondemmand_desc'] = 'Nombre de dies que han de passar abans de poder sol·licitar de nou un certificat.';
$string['timeondemmand_help'] = 'Nombre de dies que han de passar abans de poder sol·licitar de nou un certificat.';
$string['langs'] = 'Idiomes';
$string['chooselang'] = 'Filtreu la llista segons l\'idioma del certificat.';
$string['validation'] = 'Tipus de creació';
$string['validation_desc'] = 'Descripció del tipus de creació';
$string['validation_help'] = 'Ajuda del tipus de creació';
$string['modelmanager'] = 'Gestió de plantilles';
$string['create_model'] = 'Crear plantilla';
$string['edit'] = 'Edita';
$string['delete'] = 'Suprimeix';
$string['template'] = 'Plantilla';
$string['templatereport'] = 'Plantilla/Informe';
$string['lastupdate'] = 'Darrera actualització';
$string['actions'] = 'Accions';
$string['code'] = 'Codi';
$string['status'] = 'Estat';
$string['mycertificates'] = 'Els meus certificats Unimoodle Certifygen';
$string['deletemodeltitle'] = 'Eliminació de la plantilla';
$string['deletemodelbody'] = 'Esteu segur que voleu suprimir la plantilla anomenada "{$a}"?';
$string['cannotdeletemodelcertemited'] = 'No es pot suprimir la plantilla. Hi ha certificats emesos.';
$string['confirm'] = 'Confirma';
$string['errortitle'] = 'Error';
$string['model'] = 'Plantilla';
$string['contexts'] = 'Contextos';
$string['assigncontext'] = 'Assigna contextos';
$string['editassigncontext'] = 'Edita les assignacions';
$string['subplugintype_certifygenvalidation'] = 'Mètode de validació del certificat Unimoodle Certifygen';
$string['subplugintype_certifygenvalidation_plural'] = 'Mètodes de validació del certificat Unimoodle Certifygen';
$string['managecertifygenvalidationplugins'] = 'Gestionar els connectors de validació del certificat Unimoodle Certifygen';
$string['validationplugins'] = 'Connectors de validació';
$string['certifygenvalidationpluginname'] = $string['validationplugins'];
$string['hideshow'] = 'Amaga/Mostra';
$string['settings'] = 'Configuració';
$string['assigncontextto'] = 'Assigneu contextos a la plantilla "{$a}"';
$string['toomanycategoriestoshow'] = 'Massa categories per mostrar';
$string['toomanycoursestoshow'] = 'Massa cursos per mostrar';
$string['chooseacontexttype'] = 'Seleccioneu un context per buscar';
$string['writealmost3characters'] = 'Escriviu almenys 1 caràcter';
$string['coursecontext'] = 'Context del curs';
$string['categorycontext'] = 'Context de la categoria';
$string['selectvalidation'] = 'Seleccioneu la validació del certificat';
$string['selectreport'] = 'Seleccioneu el tipus d\'informe del certificat';
$string['nocontextcourse'] = 'Aquest curs no té permís per accedir a aquesta pàgina';
$string['hasnocapabilityrequired'] = 'No teniu el permís necessari per accedir a aquesta pàgina';
$string['emit'] = 'Emetre certificat';
$string['reemit'] = 'Reemetre certificat';
$string['status_1'] = 'No iniciat';
$string['status_2'] = 'En procés';
$string['status_3'] = 'Validat';
$string['status_4'] = 'Error de validació';
$string['status_5'] = 'Desat';
$string['status_6'] = 'Error en desar';
$string['status_7'] = 'Error';
$string['status_8'] = 'Completat';
$string['emitcertificate_title'] = 'Emetre certificat';
$string['emitcertificate_body'] = 'Esteu segur que voleu emetre el certificat en {$a}?';
$string['emitcertificate_error'] = 'No es pot emetre el certificat.';
$string['modelassignedsuccessfully'] = 'La plantilla s\'ha assignat correctament';
$string['cantdowithmodel'] = 'No s\'ha pogut fer res amb la plantilla assignada';
$string['cantdowithrequest'] = 'No s\'ha pogut fer res amb la sol·licitud assignada';
$string['issuedownloaded'] = 'Nombre de descàrregues';
$string['reports'] = 'Informes Unimoodle Certifygen';
$string['activityreports'] = 'Informes d\'activitat Unimoodle Certifygen';
$string['activityreports_title'] = 'Informes de sol·licitud de certificat';
$string['courseandcategoryreports'] = 'Informes de cursos i categories Unimoodle Certifygen';
$string['activitylink'] = 'Certificats de curs/activitat';
$string['contextreports'] = 'Informes de certificats de context';
$string['reports_total'] = 'Nombre total de certificats';
$string['reports_pending'] = 'Certificats pendents';
$string['reports_finished'] = 'Certificats completats';
$string['reports_errors'] = 'Errors de certificats';
$string['searchemit'] = 'Cerca/Emissió';
$string['actionemit'] = 'Acció';
$string['emitsearch_title'] = 'Cerca d\'emissió de certificat';
$string['emitsearch_body'] = 'Cerca o emet certificat';
$string['certificates'] = 'Certificats';
$string['deletecertificate'] = 'Suprimeix el certificat';
$string['deleteteacherrequest'] = 'Suprimeix la sol·licitud';
$string['deleterequesttitle'] = 'Eliminant sol·licitud';
$string['deleterequestbody'] = 'Esteu segur que voleu suprimir la sol·licitud "{$a}"?';
$string['seecoursestitle'] = 'Veure la llista de cursos associats a la sol·licitud "{$a}"';
$string['emitrequesttitle'] = 'Emetre certificat';
$string['emitrequestbody'] = 'Esteu segur que voleu emetre el certificat {$a}?';
$string['certifygenteacherrequestreport'] = 'Veure les sol·licituds de certificats dels professors';
$string['othercertificates'] = 'Altres sol·licituds de "{$a}"';
$string['mycertificate'] = 'El meu certificat';
$string['chooseuserfield'] = 'Trieu un camp d\'usuari';
$string['userfield'] = 'Camp d\'usuari';
$string['userfield_desc'] = 'Aquest paràmetre s\'utilitza per identificar l\'usuari en serveis web. Si no es selecciona res, s\'utilitzarà l\'ID de l\'usuari.';
$string['report'] = 'Informe del professor';
$string['ok'] = 'D\'acord';
$string['checkstatustask'] = 'Comprovar l\'estat dels certificats';
$string['checkfiletask'] = 'Comprovar els fitxers';
$string['teachercertificates'] = 'Certificats dels professors';
$string['chooseatemplate'] = 'Trieu una plantilla';
$string['managetemplates'] = 'Gestionar plantilles';
$string['repository'] = 'Repositori';
$string['repository_help'] = 'Ajuda del repositori';
$string['mycertificatesnotaccess'] = 'No teniu permís per accedir a aquesta pàgina';
$string['teacherrequestreportnomodels'] = 'Encara no s\'ha creat cap plantilla per generar certificats dels professors';
$string['privacy:metadata:certifygen_validations'] = 'Informació sobre l\'emissió del certificat';
$string['privacy:metadata:name'] = 'Nom del certificat (només per a certificats de professors)';
$string['privacy:metadata:courses'] = 'IDs dels cursos associats amb el certificat (només per a certificats de professors)';
$string['privacy:metadata:code'] = 'Codi del certificat (només per a certificats de professors)';
$string['privacy:metadata:certifygenid'] = 'ID de la instància de l\'activitat (només per a certificats d\'estudiants)';
$string['privacy:metadata:issueid'] = 'ID de l\'emissió (només per a certificats d\'estudiants)';
$string['privacy:metadata:userid'] = 'ID de l\'usuari que té el certificat.';
$string['privacy:metadata:modelid'] = 'ID de la plantilla';
$string['privacy:metadata:lang'] = 'Idioma del certificat';
$string['privacy:metadata:status'] = 'Estat del certificat';
$string['privacy:metadata:usermodified'] = 'ID de l\'usuari modificat';
$string['privacy:metadata:timecreated'] = 'Quan es va emetre el certificat';
$string['privacy:metadata:timemodified'] = 'Quan es va modificar el certificat';
$string['nopermissiontoemitothercerts'] = 'No teniu permís per emetre aquest certificat';
$string['nopermissiontodownloadothercerts'] = 'No teniu permís per descarregar aquest certificat';
$string['nopermissiondeletemodel'] = 'No teniu permís per suprimir una plantilla';
$string['nopermissiondeleteteacherrequest'] = 'No teniu permís per suprimir aquesta sol·licitud';
$string['nopermissiontogetcourses'] = 'No teniu permís per obtenir els cursos';
$string['repositorynotvalidwithvalidationplugin'] = 'El repositori {$a->repository} no és compatible amb el connector de validació {$a->validation}';
$string['system'] = 'Sistema';
$string['checkerrortask'] = 'Comprovar emissions de certificats fallides';
$string['certifygenerrors'] = 'Veure errors del certifygen';
$string['idrequest'] = 'Id de sol·licitud';
$string['validationplugin_not_enabled'] = 'Connector de validació no habilitat';
$string['removefilters'] = 'Eliminar filtres';
