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
 * Basque strings
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

$string['actions'] = 'Ekintzak';
$string['assigncontext'] = 'Testuinguruak esleitu';
$string['assigncontextto'] = 'Testuinguruak esleitu "{$a}" eredura';
$string['associatemodels'] = 'Ereduak testuinguruetara esleitu';
$string['cannot_begin_upload_session'] = 'Ezin duzu igoera-saioa hasi';
$string['cannot_connect_as_current_user'] = 'Ezin da egungo erabiltzaile gisa konektatu';
$string['cannot_connect_as_system_user'] = 'Ezin da sistemaren erabiltzaile gisa konektatu';
$string['cannot_create_folder'] = 'Ezin da karpeta sortu';
$string['cannot_update_link_sharing_for_document'] = 'Ezin da eguneratu dokumenturako esteka partekatua';
$string['cannotdeletemodelcertemited'] = 'Eredua ezin da ezabatu. Ziurtagiriak jaulki dira.';
$string['cannotreemit'] = 'Ziurtagiria ezin da berriro eman';
$string['categorycontext'] = 'Kategoriaren testuingurua';
$string['certificate_not_ready'] = 'Ziurtagiria ez dago prest. Egoera da {$a}';
$string['certificatelist'] = 'Ziurtagirien zerrenda';
$string['certificatenotfound'] = 'Ziurtagiria ez da aurkitu';
$string['certifygen:addinstance'] = 'Gehitu Unimoodle Certifygen Ziurtagiriaren instantzia berri bat';
$string['certifygen:canemitotherscertificates'] = 'Beste erabiltzaileen ziurtagiriak jaulki ditzake';
$string['certifygen:canmanagecertificates'] = 'Kudeatu ditzake Unimoodle Certifygen ziurtagiriak';
$string['certifygen:emitmyactivitycertificate'] = 'Jarduera batean ziurtagiriak ematea';
$string['certifygen:manage'] = 'Kudeatu Unimoodle Certifygen ziurtagiriak';
$string['certifygen:reemitcertificates'] = 'Ziurtagiriak berriz jaulki ditzake';
$string['certifygen:view'] = 'Ikusi Unimoodle Certifygen Ziurtagiria';
$string['certifygen:viewcontextcertificates'] = 'Ikusi beste irakasleen Unimoodle Certifygen ziurtagiriak';
$string['certifygen:viewmycontextcertificates'] = 'Ikusi nire Unimoodle Certifygen ziurtagiriak';
$string['certifygenerrors'] = 'Ikusi prozesuko akatsak';
$string['certifygensearchfor'] = 'Bilatu ziurtagiriak kodearen arabera';
$string['certifygenteacherrequestreport'] = 'Ikusi irakasleen ziurtagiri eskaerak';
$string['checkerrortask'] = 'Ziurtagiriak jaulkitzeko akatsak egiaztatu';
$string['checkfiletask'] = 'Fitxategiak egiaztatu';
$string['checkstatustask'] = 'Ziurtagirien egoera egiaztatu';
$string['chooseacontexttype'] = 'Aukeratu bilatu nahi duzun testuingurua';
$string['chooseamodel'] = 'Aukeratu eredu bat';
$string['chooseatemplate'] = 'Aukeratu txantiloia';
$string['chooselang'] = 'Filtratu ziurtagiriaren hizkuntzaren arabera.';
$string['chooseuserfield'] = 'Aukeratu erabiltzaile eremu bat';
$string['code'] = 'Kodea';
$string['codefound'] = 'Emaitza aurkitu dugu. Deskargatu fitxategia hurrengo estekan klik eginez {$a}';
$string['codenotfound'] = 'Kode honekin ez dugu emaitzarik aurkitu';
$string['codeview'] = 'Bilatu ziurtagiriak kodearen arabera';
$string['completiondownload'] = 'Ziurtagiriaren deskargaren bidezko osatzea';
$string['completiondownloaddesc'] = 'Parte-hartzaileek ziurtagiria deskargatu behar dute jarduera amaitzeko.';
$string['configurated_logo'] = 'Logoa konfiguratuta';
$string['confirm'] = 'Berretsi';
$string['contextcertificatelink'] = 'Unimoodle Certifygen Ziurtagiria - ikastaroa';
$string['contexts'] = 'Testuinguruak';
$string['course_not_valid_for_modelid'] = 'Ezin da jarduera berrezarri {$a->activityname}. Ikastaroak ({$a->courseid}) ez du balio eredu honetarako (izena: {$a->name}, idzenbakia: {$a->idnumber})';
$string['course_not_valid_with_model'] = 'Ikastaroa, {$a}, ez da ereduarekin bateragarria';
$string['coursecontext'] = 'Ikastaroaren testuingurua';
$string['coursenotexists'] = 'Ikastaroa ez da existitzen';
$string['courseslist'] = 'Ziurtatzeko ikastaroen zerrenda';
$string['create_model'] = 'Eredua sortu';
$string['create_request'] = 'Eskaera sortu';
$string['delete'] = 'Ezabatu';
$string['deletemodelbody'] = 'Ziur zaude "{$a}" izeneko eredua ezabatu nahi duzula?';
$string['deletemodeltitle'] = 'Eredua ezabatzen';
$string['deleterequestbody'] = 'Ziur zaude "{$a}" eskaera ezabatu nahi duzula?';
$string['deleterequesttitle'] = 'Eskaera ezabatu';
$string['download'] = 'Deskargatu';
$string['downloadcertificate_body'] = 'Ziur zaude {$a} ziurtagiria deskargatu nahi duzula?';
$string['downloadcertificate_error'] = 'Errore bat gertatu da ziurtagiria deskargatzen saiatzean';
$string['downloadcertificate_title'] = 'Ziurtagiria deskargatu';
$string['edit'] = 'Editatu';
$string['editassigncontext'] = 'Esleipenak editatu';
$string['emit'] = 'Ziurtagiria jaulki';
$string['emitcertificate_body'] = 'Ziur zaude {$a} ziurtagiria jaulki nahi duzula?';
$string['emitcertificate_error'] = 'Errore bat gertatu da ziurtagiria jaulkitzen saiatzean';
$string['emitcertificate_title'] = 'Ziurtagiria jaulki';
$string['emitrequestbody'] = 'Ziur zaude {$a} ziurtagiria jaulki nahi duzula?';
$string['emitrequesttitle'] = 'Ziurtagiria jaulki';
$string['empty_repository_url'] = 'Ziurtagiriaren esteka biltegian hutsik dago';
$string['errortitle'] = 'Errorea';
$string['file_not_found'] = 'Fitxategia ez da aurkitu';
$string['filter'] = 'Iragazi';
$string['getfile_missing_file_parameter'] = 'Fitxategi parametroa falta da';
$string['hasnocapabilityrequired'] = 'Ez duzu orrialde honetara sartzeko beharrezko baimenik';
$string['hideshow'] = 'Ezkutatu/Erakutsi';
$string['idrequest'] = 'Eskaera IDa';
$string['introduction'] = 'Sarrera';
$string['invalid_language'] = 'Invalid language';
$string['issue_not_found'] = 'Emisio kodea ez da aurkitu';
$string['lang'] = 'Hizkuntza';
$string['lang_not_exists'] = 'Hizkuntza hau ez dago instalatuta, {$a->lang}';
$string['lang_not_found'] = 'Hizkuntza ez dago plataforman instalatuta';
$string['langs'] = 'Hizkuntzak';
$string['lastupdate'] = 'Azken eguneraketa';
$string['managecertifygenvalidationplugins'] = 'Kudeatu Unimoodle Certifygen ziurtagiriaren baliozkotze pluginak';
$string['managetemplates'] = 'Txantiloiak kudeatu';
$string['messageprovider:certifygen_notification'] = 'Certifygen abisua';
$string['missingreportonmodel'] = 'Txosten parametroa falta da ereduan';
$string['mode'] = 'Modua';
$string['mode_1'] = 'Bakarra';
$string['mode_2'] = 'Errepikakorra';
$string['mode_help'] = 'Moduaren laguntza';
$string['model'] = 'Eredua';
$string['model_must_exists'] = 'Ezin da {$a->activityname} jarduera leheneratu. {$a->idnumber}-ren ID zenbakia duen eredu bat egon behar da';
$string['model_not_found'] = 'Eredua ez da existitzen';
$string['model_not_valid'] = 'Eredu baliogabea';
$string['model_type_assigned_to_activity'] = 'Eredua dago jarduerari esleituta';
$string['modelidnumber'] = 'Id zenbakia';
$string['modelmanager'] = 'Ereduen kudeaketa';
$string['modelname'] = 'Ereduaren izena';
$string['modelsmanager'] = 'Ereduen kudeaketa';
$string['modulename'] = 'Certifygen Ziurtagiria';
$string['modulenameplural'] = 'Unimoodle Certifygen Ziurtagiriak';
$string['mycertificate'] = 'Nire ziurtagiria';
$string['mycertificates'] = 'Nire Unimoodle Certifygen Ziurtagiriak';
$string['mycertificatesnotaccess'] = 'Ez duzu orrialde honetara sartzeko baimenik';
$string['name'] = 'Izena';
$string['nocontextassociated'] = 'Eredu honek ez du inolako testuingururik lotuta.';
$string['nocontextcourse'] = 'Ikastaro honek ez du orrialde honetara sartzeko baimenik';
$string['nopermissiondeletemodel'] = 'Ez duzu eredu bat ezabatzeko baimenik';
$string['nopermissiondeleteteacherrequest'] = 'Ez duzu eskaera hau ezabatzeko baimenik';
$string['nopermissiontodownloadothercerts'] = 'Ez duzu beste erabiltzaileen ziurtagiriak deskargatzeko baimenik';
$string['nopermissiontoemitothercerts'] = 'Ez duzu beste erabiltzaileen ziurtagiriak jaulkitzeko baimenik';
$string['nopermissiontogetcourses'] = 'Ez duzu ikastaroak lortzeko baimenik';
$string['nopermissiontorevokecerts'] = 'Ez duzu ziurtagiria baliogabetzeko baimenik';
$string['notificationmsgcertificateissued'] = 'notificationmsgcertificateissued';
$string['ok'] = 'Ados';
$string['othercertificates'] = '"{$a}" eskaeren zerrendak';
$string['pluginadministration'] = 'Unimoodle Certifygen Ziurtagiriaren Administrazio Moduloa';
$string['pluginname'] = 'Certifygen Ziurtagiria';
$string['pluginnamesettings'] = 'Unimoodle Certifygen Ziurtagiriaren Konfigurazioa';
$string['privacy:metadata:certifygen_repository'] = 'Ziurtagiriaren kokapenari buruzko informazioa';
$string['privacy:metadata:certifygen_validations'] = 'Ziurtagiriaren igorpenari buruzko informazioa';
$string['privacy:metadata:certifygenid'] = 'Jardueraren instantzia IDa (ikasleen ziurtagirientzat soilik)';
$string['privacy:metadata:code'] = 'Ziurtagiriaren kodea (irakasleen ziurtagirientzat soilik)';
$string['privacy:metadata:courses'] = 'Ziurtagiriarekin lotutako ikastaroen IDak (irakasleen ziurtagirientzat soilik)';
$string['privacy:metadata:data'] = 'Biltegiaren kokapenarekin lotutako datuak';
$string['privacy:metadata:issueid'] = 'Igorpen IDa (ikasleen ziurtagirientzat soilik)';
$string['privacy:metadata:lang'] = 'Ziurtagiriaren hizkuntza';
$string['privacy:metadata:modelid'] = 'Eredu IDa';
$string['privacy:metadata:name'] = 'Ziurtagiriaren izena (irakasleen ziurtagirientzat soilik)';
$string['privacy:metadata:status'] = 'Ziurtagiriaren egoera';
$string['privacy:metadata:timecreated'] = 'Ziurtagiria jaulki zen denbora';
$string['privacy:metadata:timemodified'] = 'Ziurtagiria aldatu zen denbora';
$string['privacy:metadata:url'] = 'Biltegiaren kokapenerako esteka';
$string['privacy:metadata:userid'] = 'Ziurtagiria duen erabiltzailearen IDa';
$string['privacy:metadata:usermodified'] = 'Erabiltzaile IDa';
$string['privacy:metadata:validationid'] = 'Ziurtagiriaren balioztapen instantziaren IDa';
$string['reemit'] = 'Ziurtagiria berriz jaulki';
$string['removefilters'] = 'Ezabatu iragazkiak';
$string['report'] = 'Irakaslearen txantiloia';
$string['repository'] = 'Errepositorioa';
$string['repository_help'] = 'Errepositorioaren laguntza';
$string['repository_plugin_not_enabled'] = 'Biltegiko plugin-a desgaituta dago';
$string['repositorynotvalidwithvalidationplugin'] = 'Errepositorioa {$a->repository} ez da bateragarria baliozkotze pluginarekin {$a->validation}';
$string['requestid'] = 'Eskaera zenbakia';
$string['results'] = 'Emaitzak';
$string['revokecertificate_body'] = 'Ziur zaude {$a} ziurtagiria ezabatu nahi duzula?';
$string['revokecertificate_error'] = 'Errore bat gertatu da ziurtagiria ezabatzen saiatzean';
$string['revokecertificate_title'] = 'Ziurtagiria ezabatu';
$string['savefile_returns_error'] = 'Errorea fitxategia gordetzean';
$string['seecourses'] = 'Ikusi ikastaroak';
$string['seecoursestitle'] = 'Eskaerari "{$a}" lotutako ikastaroen zerrenda';
$string['selectmycertificateslangdesc'] = 'Ziurtagiriaren hizkuntza hauta dezakezu.';
$string['selectreport'] = 'Aukeratu ziurtagiriaren txosten mota';
$string['selectvalidation'] = 'Aukeratu ziurtagiriaren baliozkotzea';
$string['settings'] = 'Ezarpenak';
$string['status'] = 'Egoera';
$string['status_1'] = 'Hasi gabe';
$string['status_10'] = 'Akats orokorra irakaslearen agirian';
$string['status_2'] = 'Prozesuan';
$string['status_3'] = 'Balioztatua';
$string['status_4'] = 'Baliozkotze errorea';
$string['status_5'] = 'Biltegiratua';
$string['status_6'] = 'Biltegiratze errorea';
$string['status_7'] = 'Errorea';
$string['status_9'] = 'Akats orokorra ikaslearen ziurtagirian';
$string['statusnotfinished'] = 'Ziurtagiriaren egoera ez dago amaituta';
$string['student_not_enrolled'] = 'Erabiltzailea ez dago matrikulatuta {$a} ikastaroan ikasle bezala';
$string['subplugintype_certifygenvalidation'] = 'Unimoodle Certifygen Ziurtagiriaren baliozkotze metodoa';
$string['subplugintype_certifygenvalidation_plural'] = 'Unimoodle Certifygen Ziurtagiriaren baliozkotze metodoak';
$string['system'] = 'Sistema';
$string['teacher_not_enrolled'] = 'Erabiltzailea ez dago matrikulatuta {$a} ikastaroan irakasle bezala';
$string['teachercertificates'] = 'Irakasleen ziurtagiriak';
$string['teacherrequestreportnomodels'] = 'Oraindik ez da irakasleen ziurtagirientzat ikastaroekin lotutako eredurik sortu';
$string['template'] = 'Txantiloia';
$string['templateid'] = 'Txantiloia';
$string['templateid_help'] = 'Hautatu ziurtagiriaren txantiloia';
$string['templatenotfound'] = 'Jardueraren konfigurazioan arazo bat dago. Une honetan ezin da hori erabili.';
$string['templatereport'] = 'Txantiloia/Txostena';
$string['timeondemmand'] = 'Eskaeren arteko denbora';
$string['timeondemmand_desc'] = 'Egun kopurua, berriz ziurtagiria eskatu ahal izateko igaro behar direnak.';
$string['timeondemmand_help'] = 'Egun kopurua, berriz ziurtagiria eskatu ahal izateko igaro behar direnak.';
$string['toomanycategoriestoshow'] = 'Erakusteko kategoria gehiegi';
$string['toomanycoursestoshow'] = 'Erakusteko ikastaro gehiegi';
$string['type'] = 'Mota';
$string['type_1'] = 'Ikastaro osoa (ikasleentzat)';
$string['type_2'] = 'Ikastaroaren erabilera (irakasleentzat)';
$string['type_help'] = 'Aukeratu eman nahi duzun ziurtagiri mota. Ikasle edo irakasle.';
$string['user_not_found'] = 'Erabiltzailea ez da aurkitu';
$string['user_not_sent'] = 'Ez da erabiltzailea adierazi';
$string['userfield'] = 'Erabiltzaile eremua';
$string['userfield_and_userid_sent'] = 'Erabiltzaileari lotutako parametro bat bakarrik bidali behar da';
$string['userfield_desc'] = 'Parametro hau web zerbitzuetan erabiltzen da erabiltzailea identifikatzeko. Ezer aukeratzen ez bada, erabiltzaile taulako IDa erabiliko da.';
$string['userfield_not_selected'] = 'Ez da hautatu erabiltzaile eremurik plataforman';
$string['userfield_not_valid'] = 'Erabiltzaile eremua baliogabea da';
$string['validation'] = 'Sortze mota';
$string['validation_desc'] = 'Sortze motaren deskribapena';
$string['validation_help'] = 'Sortze mota - laguntza';
$string['validationnotfound'] = 'Ez dago erregistroa certifygen_validations taulan';
$string['validationnotvalidwithrepositoryplugin'] = 'Baliozkotze plugins {$a->validation} ez da bateragarria  pluginarekin {$a->repository}';
$string['validationplugin_not_enabled'] = 'Baliozkotze plugin-a ez dago gaituta';
$string['validationplugins'] = 'Baliozkotze pluginak';
$string['writealmost3characters'] = 'Idatzi gutxienez 1 karaktere';
