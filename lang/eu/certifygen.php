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

$string['pluginname'] = 'Certifygen Ziurtagiria';
$string['pluginadministration'] = 'Unimoodle Certifygen Ziurtagiriaren Administrazio Moduloa';
$string['pluginnamesettings'] = 'Unimoodle Certifygen Ziurtagiriaren Konfigurazioa';
$string['certifygen:addinstance'] = 'Gehitu Unimoodle Certifygen Ziurtagiriaren instantzia berri bat';
$string['certifygen:view'] = 'Ikusi Unimoodle Certifygen Ziurtagiria';
$string['certifygen:manage'] = 'Kudeatu Unimoodle Certifygen ziurtagiriak';
$string['certifygen:canmanagecertificates'] = 'Kudeatu ditzake Unimoodle Certifygen ziurtagiriak';
$string['certifygen:viewmycontextcertificates'] = 'Ikusi nire Unimoodle Certifygen ziurtagiriak';
$string['certifygen:viewcontextcertificates'] = 'Ikusi beste irakasleen Unimoodle Certifygen ziurtagiriak';
$string['certifygen:emitmyactivitycertificate'] = 'Jarduera batean ziurtagiriak ematea';
$string['type'] = 'Mota';
$string['type_help'] = 'Aukeratu eman nahi duzun ziurtagiri mota. Ikasle edo irakasle.';
$string['type_1'] = 'Ikastaro osoa (ikasleentzat)';
$string['type_2'] = 'Ikastaroaren erabilera (irakasleentzat)';
$string['mode'] = 'Modua';
$string['mode_help'] = 'Moduaren laguntza';
$string['mode_1'] = 'Bakarra';
$string['mode_2'] = 'Errepikakorra';
$string['templateid'] = 'Txantiloia';
$string['templateid_help'] = 'Hautatu ziurtagiriaren txantiloia';
$string['introduction'] = 'Sarrera';
$string['modulename'] = $string['pluginname'];
$string['modulenameplural'] = 'Unimoodle Certifygen Ziurtagiriak';
$string['name'] = 'Izena';
$string['modelname'] = 'Ereduaren izena';
$string['modelidnumber'] = 'Id zenbakia';
$string['contextcertificatelink'] = 'Unimoodle Certifygen Ziurtagiria - ikastaroa';
$string['chooseamodel'] = 'Aukeratu eredu bat';
$string['model'] = 'Eredua';
$string['modelsmanager'] = 'Ereduen kudeaketa';
$string['associatemodels'] = 'Ereduak testuinguruetara esleitu';
$string['download'] = 'Deskargatu';
$string['timeondemmand'] = 'Eskaeren arteko denbora';
$string['timeondemmand_desc'] = 'Egun kopurua, berriz ziurtagiria eskatu ahal izateko igaro behar direnak.';
$string['timeondemmand_help'] = 'Egun kopurua, berriz ziurtagiria eskatu ahal izateko igaro behar direnak.';
$string['langs'] = 'Hizkuntzak';
$string['chooselang'] = 'Filtratu ziurtagiriaren hizkuntzaren arabera.';
$string['validation'] = 'Sortze mota';
$string['validation_desc'] = 'Sortze motaren deskribapena';
$string['validation_help'] = 'Sortze mota - laguntza';
$string['modelmanager'] = 'Ereduen kudeaketa';
$string['create_model'] = 'Eredua sortu';
$string['edit'] = 'Editatu';
$string['delete'] = 'Ezabatu';
$string['template'] = 'Txantiloia';
$string['templatereport'] = 'Txantiloia/Txostena';
$string['lastupdate'] = 'Azken eguneraketa';
$string['actions'] = 'Ekintzak';
$string['code'] = 'Kodea';
$string['status'] = 'Egoera';
$string['mycertificates'] = 'Nire Unimoodle Certifygen Ziurtagiriak';
$string['deletemodeltitle'] = 'Eredua ezabatzen';
$string['deletemodelbody'] = 'Ziur zaude "{$a}" izeneko eredua ezabatu nahi duzula?';
$string['cannotdeletemodelcertemited'] = 'Eredua ezin da ezabatu. Ziurtagiriak jaulki dira.';
$string['confirm'] = 'Berretsi';
$string['errortitle'] = 'Errorea';
$string['model'] = 'Eredua';
$string['contexts'] = 'Testuinguruak';
$string['assigncontext'] = 'Testuinguruak esleitu';
$string['editassigncontext'] = 'Esleipenak editatu';
$string['subplugintype_certifygenvalidation'] = 'Unimoodle Certifygen Ziurtagiriaren baliozkotze metodoa';
$string['subplugintype_certifygenvalidation_plural'] = 'Unimoodle Certifygen Ziurtagiriaren baliozkotze metodoak';
$string['managecertifygenvalidationplugins'] = 'Kudeatu Unimoodle Certifygen ziurtagiriaren baliozkotze pluginak';
$string['validationplugins'] = 'Baliozkotze pluginak';
$string['hideshow'] = 'Ezkutatu/Erakutsi';
$string['settings'] = 'Ezarpenak';
$string['assigncontextto'] = 'Testuinguruak esleitu "{$a}" eredura';
$string['toomanycategoriestoshow'] = 'Erakusteko kategoria gehiegi';
$string['toomanycoursestoshow'] = 'Erakusteko ikastaro gehiegi';
$string['chooseacontexttype'] = 'Aukeratu bilatu nahi duzun testuingurua';
$string['writealmost3characters'] = 'Idatzi gutxienez 1 karaktere';
$string['coursecontext'] = 'Ikastaroaren testuingurua';
$string['categorycontext'] = 'Kategoriaren testuingurua';
$string['selectvalidation'] = 'Aukeratu ziurtagiriaren baliozkotzea';
$string['selectreport'] = 'Aukeratu ziurtagiriaren txosten mota';
$string['nocontextcourse'] = 'Ikastaro honek ez du orrialde honetara sartzeko baimenik';
$string['hasnocapabilityrequired'] = 'Ez duzu orrialde honetara sartzeko beharrezko baimenik';
$string['emit'] = 'Ziurtagiria jaulki';
$string['reemit'] = 'Ziurtagiria berriz jaulki';
$string['status_1'] = 'Hasi gabe';
$string['status_2'] = 'Prozesuan';
$string['status_3'] = 'Balioztatua';
$string['status_4'] = 'Baliozkotze errorea';
$string['status_5'] = 'Biltegiratua';
$string['status_6'] = 'Biltegiratze errorea';
$string['status_7'] = 'Errorea';
$string['status_8'] = 'Amaituta';
$string['status_9'] = 'Akats orokorra ikaslearen ziurtagirian';
$string['status_10'] = 'Akats orokorra irakaslearen agirian';
$string['emitcertificate_title'] = 'Ziurtagiria jaulki';
$string['emitcertificate_body'] = 'Ziur zaude {$a} ziurtagiria jaulki nahi duzula?';
$string['emitcertificate_error'] = 'Errore bat gertatu da ziurtagiria jaulkitzen saiatzean';
$string['certificatenotfound'] = 'Ziurtagiria ez da aurkitu';
$string['filter'] = 'Iragazi';
$string['revokecertificate_title'] = 'Ziurtagiria ezabatu';
$string['revokecertificate_body'] = 'Ziur zaude {$a} ziurtagiria ezabatu nahi duzula?';
$string['revokecertificate_error'] = 'Errore bat gertatu da ziurtagiria ezabatzen saiatzean';
$string['downloadcertificate_title'] = 'Ziurtagiria deskargatu';
$string['downloadcertificate_body'] = 'Ziur zaude {$a} ziurtagiria deskargatu nahi duzula?';
$string['downloadcertificate_error'] = 'Errore bat gertatu da ziurtagiria deskargatzen saiatzean';
$string['notificationmsgcertificateissued'] = 'notificationmsgcertificateissued';
$string['certificatelist'] = 'Ziurtagirien zerrenda';
$string['selectmycertificateslangdesc'] = 'Ziurtagiriaren hizkuntza hauta dezakezu.';
$string['system'] = 'Sistema';
$string['requestid'] = 'Eskaera zenbakia';
$string['seecourses'] = 'Ikusi ikastaroak';
$string['create_request'] = 'Eskaera sortu';
$string['courseslist'] = 'Ziurtatzeko ikastaroen zerrenda';
$string['deleterequesttitle'] = 'Eskaera ezabatu';
$string['deleterequestbody'] = 'Ziur zaude "{$a}" eskaera ezabatu nahi duzula?';
$string['seecoursestitle'] = 'Eskaerari "{$a}" lotutako ikastaroen zerrenda';
$string['emitrequesttitle'] = 'Ziurtagiria jaulki';
$string['emitrequestbody'] = 'Ziur zaude {$a} ziurtagiria jaulki nahi duzula?';
$string['certifygenteacherrequestreport'] = 'Ikusi irakasleen ziurtagiri eskaerak';
$string['othercertificates'] = '"{$a}" eskaeren zerrendak';
$string['mycertificate'] = 'Nire ziurtagiria';
$string['chooseuserfield'] = 'Aukeratu erabiltzaile eremu bat';
$string['userfield'] = 'Erabiltzaile eremua';
$string['userfield_desc'] = 'Parametro hau web zerbitzuetan erabiltzen da erabiltzailea identifikatzeko. Ezer aukeratzen ez bada, erabiltzaile taulako IDa erabiliko da.';
$string['report'] = 'Irakaslearen txantiloia';
$string['ok'] = 'Ados';
$string['checkstatustask'] = 'Ziurtagirien egoera egiaztatu';
$string['checkfiletask'] = 'Fitxategiak egiaztatu';
$string['teachercertificates'] = 'Irakasleen ziurtagiriak';
$string['chooseatemplate'] = 'Aukeratu txantiloia';
$string['managetemplates'] = 'Txantiloiak kudeatu';
$string['repository'] = 'Errepositorioa';
$string['repository_help'] = 'Errepositorioaren laguntza';
$string['mycertificatesnotaccess'] = 'Ez duzu orrialde honetara sartzeko baimenik';
$string['teacherrequestreportnomodels'] = 'Oraindik ez da irakasleen ziurtagirientzat ikastaroekin lotutako eredurik sortu';
$string['privacy:metadata:certifygen_validations'] = 'Ziurtagiriaren igorpenari buruzko informazioa';
$string['privacy:metadata:name'] = 'Ziurtagiriaren izena (irakasleen ziurtagirientzat soilik)';
$string['privacy:metadata:courses'] = 'Ziurtagiriarekin lotutako ikastaroen IDak (irakasleen ziurtagirientzat soilik)';
$string['privacy:metadata:code'] = 'Ziurtagiriaren kodea (irakasleen ziurtagirientzat soilik)';
$string['privacy:metadata:certifygenid'] = 'Jardueraren instantzia IDa (ikasleen ziurtagirientzat soilik)';
$string['privacy:metadata:issueid'] = 'Igorpen IDa (ikasleen ziurtagirientzat soilik)';
$string['privacy:metadata:userid'] = 'Ziurtagiria duen erabiltzailearen IDa';
$string['privacy:metadata:modelid'] = 'Eredu IDa';
$string['privacy:metadata:lang'] = 'Ziurtagiriaren hizkuntza';
$string['privacy:metadata:status'] = 'Ziurtagiriaren egoera';
$string['privacy:metadata:usermodified'] = 'Erabiltzaile IDa';
$string['privacy:metadata:timecreated'] = 'Ziurtagiria jaulki zen denbora';
$string['privacy:metadata:timemodified'] = 'Ziurtagiria aldatu zen denbora';
$string['nopermissiontoemitothercerts'] = 'Ez duzu beste erabiltzaileen ziurtagiriak jaulkitzeko baimenik';
$string['nopermissiontodownloadothercerts'] = 'Ez duzu beste erabiltzaileen ziurtagiriak deskargatzeko baimenik';
$string['nopermissiondeletemodel'] = 'Ez duzu eredu bat ezabatzeko baimenik';
$string['nopermissiondeleteteacherrequest'] = 'Ez duzu eskaera hau ezabatzeko baimenik';
$string['nopermissiontogetcourses'] = 'Ez duzu ikastaroak lortzeko baimenik';
$string['repositorynotvalidwithvalidationplugin'] = 'Errepositorioa {$a->repository} ez da bateragarria baliozkotze pluginarekin {$a->validation}';
$string['system'] = 'Sistema';
$string['checkerrortask'] = 'Ziurtagiriak jaulkitzeko akatsak egiaztatu';
$string['certifygenerrors'] = 'Ikusi prozesuko akatsak';
$string['idrequest'] = 'Eskaera IDa';
$string['validationplugin_not_enabled'] = 'Baliozkotze plugin-a ez dago gaituta';
$string['removefilters'] = 'Ezabatu iragazkiak';
$string['nopermissiontorevokecerts'] = 'Ez duzu ziurtagiria baliogabetzeko baimenik';
$string['certifygen:canemitotherscertificates'] = 'Beste erabiltzaileen ziurtagiriak jaulki ditzake';
$string['certifygen:reemitcertificates'] = 'Ziurtagiriak berriz jaulki ditzake';
$string['lang_not_exists'] = 'Hizkuntza hau ez dago instalatuta, {$a->lang}';
$string['coursenotexists'] = 'Ikastaroa ez da existitzen';
$string['empty_repository_url'] = 'Ziurtagiriaren esteka biltegian hutsik dago';
$string['savefile_returns_error'] = 'Errorea fitxategia gordetzean';
$string['repository_plugin_not_enabled'] = 'Biltegiko plugin-a desgaituta dago';
$string['getfile_missing_file_parameter'] = 'Fitxategi parametroa falta da';
$string['validationnotfound'] = 'Ez dago erregistroa certifygen_validations taulan';
$string['statusnotfinished'] = 'Ziurtagiriaren egoera ez dago amaituta';
$string['cannotreemit'] = 'Ziurtagiria ezin da berriro eman';
$string['file_not_found'] = 'Fitxategia ez da aurkitu';
$string['missingreportonmodel'] = 'Txosten parametroa falta da ereduan';
$string['user_not_found'] = 'Erabiltzailea ez da aurkitu';
$string['lang_not_found'] = 'Hizkuntza ez dago plataforman instalatuta';
$string['student_not_enrolled'] = 'Erabiltzailea ez dago matrikulatuta {$a} ikastaroan ikasle bezala';
$string['teacher_not_enrolled'] = 'Erabiltzailea ez dago matrikulatuta {$a} ikastaroan irakasle bezala';
$string['model_type_assigned_to_activity'] = 'Eredua ez dago jarduerari esleituta';
$string['certificate_not_ready'] = 'Ziurtagiria ez dago prest. Egoera da {$a}';
$string['userfield_and_userid_sent'] = 'Erabiltzaileari lotutako parametro bat bakarrik bidali behar da';
$string['userfield_not_valid'] = 'Erabiltzaile eremua baliogabea da';
$string['issue_not_found'] = 'Emisio kodea ez da aurkitu';
$string['userfield_not_selected'] = 'Ez da hautatu erabiltzaile eremurik plataforman';
$string['user_not_sent'] = 'Ez da erabiltzailea adierazi';
$string['model_not_found'] = 'Eredua ez da existitzen';
$string['model_not_valid'] = 'Eredu baliogabea';
$string['course_not_valid_with_model'] = 'Ikastaroa, {$a}, ez da ereduarekin bateragarria';
$string['codeview'] = 'Bilatu ziurtagiriak kodearen arabera';
$string['codefound'] = 'Emaitza aurkitu dugu. Deskargatu fitxategia hurrengo estekan klik eginez {$a}';
$string['codenotfound'] = 'Kode honekin ez dugu emaitzarik aurkitu';
$string['certifygensearchfor'] = 'Bilatu ziurtagiriak kodearen arabera';
$string['model_must_exists'] = 'Ezin da {$a->activityname} jarduera leheneratu. {$a->idnumber}-ren ID zenbakia duen eredu bat egon behar da';
$string['course_not_valid_for_modelid'] = 'Ezin da jarduera berrezarri {$a->activityname}. Ikastaroak ({$a->courseid}) ez du balio eredu honetarako (izena: {$a->name}, idzenbakia: {$a->idnumber})';
$string['templatenotfound'] = 'Jardueraren konfigurazioan arazo bat dago. Une honetan ezin da hori erabili.';
