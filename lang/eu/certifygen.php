
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
$string['pluginnamesettings'] = 'Unimoodle Certifygen Ziurtagiriaren Ezarpenak';
$string['certifygen:addinstance'] = 'Gehitu Unimoodle Certifygen Ziurtagiriaren instantzia berri bat';
$string['certifygen:view'] = 'Ikusi Unimoodle Certifygen Ziurtagiria';
$string['certifygen:manage'] = 'Kudeatu Unimoodle Certifygen Ziurtagiriak';
$string['certifygen:canmanagecertificates'] = 'Unimoodle Certifygen Ziurtagiriak kudeatu ditzake';
$string['certifygen:viewmycontextcertificates'] = 'Ikusi nire Unimoodle Certifygen ziurtagiriak';
$string['certifygen:viewcontextcertificates'] = 'Ikusi beste irakasleen Unimoodle Certifygen ziurtagiriak';
$string['type'] = 'Mota';
$string['type_help'] = 'Hautatu jaulki nahi duzun ziurtagiri mota. Ikasleak edo irakasleak.';
$string['type_1'] = 'Ikastaro osoa (ikasleentzat)';
$string['type_2'] = 'Ikastaroaren erabilera (irakasleentzat)';
$string['mode'] = 'Modua';
$string['mode_help'] = 'Moduaren laguntza';
$string['mode_1'] = 'Bakarra';
$string['mode_2'] = 'Errepikakorra';
$string['templateid'] = 'Eredua';
$string['templateid_help'] = 'Hautatu ziurtagiriaren eredua';
$string['introduction'] = 'Sarrera';
$string['modulename'] = $string['pluginname'];
$string['modulenameplural'] = 'Unimoodle Certifygen Ziurtagiriak';
$string['name'] = 'Izena';
$string['modelname'] = 'Ereduaren izena';
$string['modelidnumber'] = 'ID zenbakia';
$string['contextcertificatelink'] = 'Unimoodle Certifygen Ziurtagiria - ikastaroa';
$string['chooseamodel'] = 'Aukeratu eredu bat';
$string['model'] = 'Eredua';
$string['modelsmanager'] = 'Ereduen kudeaketa';
$string['associatemodels'] = 'Ereduak testuinguruekin lotu';
$string['download'] = 'Deskargatu';
$string['timeondemmand'] = 'Eskaeren arteko denbora';
$string['timeondemmand_desc'] = 'Ziurtagiria berriro eskatu ahal izateko igaro behar diren egunen kopurua.';
$string['timeondemmand_help'] = 'Ziurtagiria berriro eskatu ahal izateko igaro behar diren egunen kopurua.';
$string['langs'] = 'Hizkuntzak';
$string['chooselang'] = 'Iragazi zerrenda ziurtagiriaren hizkuntzaren arabera.';
$string['validation'] = 'Sorkuntza mota';
$string['validation_desc'] = 'Sorkuntza motaren deskribapena';
$string['validation_help'] = 'Sorkuntza motaren laguntza';
$string['modelmanager'] = 'Ereduen kudeaketa';
$string['create_model'] = 'Sortu Eredua';
$string['edit'] = 'Editatu';
$string['delete'] = 'Ezabatu';
$string['template'] = 'Eredua';
$string['templatereport'] = 'Eredua/Txostena';
$string['lastupdate'] = 'Azken eguneratzea';
$string['actions'] = 'Ekintzak';
$string['code'] = 'Kodea';
$string['status'] = 'Egoera';
$string['mycertificates'] = 'Nire Unimoodle Certifygen Ziurtagiriak';
$string['deletemodeltitle'] = 'Eredua Ezabatzen';
$string['deletemodelbody'] = '"{$a}" izeneko eredua ezabatu nahi duzula ziur zaude?';
$string['cannotdeletemodelcertemited'] = 'Ezin da eredua ezabatu. Jaulkitako ziurtagiriak daude.';
$string['confirm'] = 'Baieztatu';
$string['errortitle'] = 'Errorea';
$string['model'] = 'Eredua';
$string['contexts'] = 'Testuinguruak';
$string['assigncontext'] = 'Testuinguruak esleitu';
$string['editassigncontext'] = 'Esleipenak aldatu';
$string['subplugintype_certifygenvalidation'] = 'Unimoodle Certifygen ziurtagiriaren baliozkotze metodoa';
$string['subplugintype_certifygenvalidation_plural'] = 'Unimoodle Certifygen ziurtagiriaren baliozkotze metodoak';
$string['managecertifygenvalidationplugins'] = 'Kudeatu Unimoodle Certifygen ziurtagiriaren baliozkotze pluginak';
$string['validationplugins'] = 'Baliozkotze pluginak';
$string['certifygenvalidationpluginname'] = $string['validationplugins'];
$string['hideshow'] = 'Ezkutatu/Erakutsi';
$string['settings'] = 'Ezarpenak';
$string['assigncontextto'] = '"{$a}" eredura testuinguruak esleitu';
$string['toomanycategoriestoshow'] = 'Erakusteko kategoria gehiegi';
$string['toomanycoursestoshow'] = 'Erakusteko ikastaro gehiegi';
$string['chooseacontexttype'] = 'Hautatu bilatzeko testuingurua';
$string['writealmost3characters'] = 'Idatzi gutxienez 1 karaktere';
$string['coursecontext'] = 'Ikastaroaren testuingurua';
$string['categorycontext'] = 'Kategoriaren testuingurua';
$string['selectvalidation'] = 'Hautatu ziurtagiriaren baliozkotzea';
$string['selectreport'] = 'Hautatu ziurtagiriaren txosten mota';
$string['nocontextcourse'] = 'Ikastaro honek ez du orri honetara sartzeko baimenik';
$string['hasnocapabilityrequired'] = 'Ez duzu orri honetara sartzeko beharrezko baimenik';
$string['emit'] = 'Jaulki ziurtagiria';
$string['reemit'] = 'Berriro jaulki ziurtagiria';
$string['status_1'] = 'Hasi gabe';
$string['status_2'] = 'Prozesuan';
$string['status_3'] = 'Balioztatuta';
$string['status_4'] = 'Baliozkotze errorea';
$string['status_5'] = 'Gordeta';
$string['status_6'] = 'Biltegiratze errorea';
$string['status_7'] = 'Errorea';
$string['status_8'] = 'Amaituta';
$string['emitcertificate_title'] = 'Ziurtagiria Jaulki';
$string['emitcertificate_body'] = '{$a}-n ziurtagiria jaulki nahi duzula ziur zaude?';
$string['emitcertificate_error'] = 'Ez da posible ziurtagiria jaulkitzea.';
$string['modelassignedsuccessfully'] = 'Eredua behar bezala esleitu da';
$string['cantdowithmodel'] = 'Ezin izan da ezer egin esleitutako ereduarekin';
$string['cantdowithrequest'] = 'Ezin izan da ezer egin esleitutako eskaerarekin';
$string['issuedownloaded'] = 'Deskargatu den kopurua';
$string['reports'] = 'Unimoodle Certifygen Txostenak';
$string['activityreports'] = 'Unimoodle Certifygen Jardueren Txostenak';
$string['activityreports_title'] = 'Ziurtagiri eskariaren txostenak';
$string['courseandcategoryreports'] = 'Unimoodle Certifygen Ikastaro eta Kategoria Txostenak';
$string['activitylink'] = 'Ikastaro/jarduera ziurtagiriak';
$string['contextreports'] = 'Testuinguruaren ziurtagiri txostenak';
$string['reports_total'] = 'Ziurtagiri guztien kopurua';
$string['reports_pending'] = 'Ziurtagiri pendienteak';
$string['reports_finished'] = 'Ziurtagiri amaituak';
$string['reports_errors'] = 'Ziurtagirien erroreak';
$string['searchemit'] = 'Bilaketa/Jaulkipena';
$string['actionemit'] = 'Ekintza';
$string['emitsearch_title'] = 'Ziurtagiri Jaulkipena Bilaketa';
$string['emitsearch_body'] = 'Ziurtagiria bilatu edo jaulki';
$string['certificates'] = 'Ziurtagiriak';
$string['deletecertificate'] = 'Ezabatu ziurtagiria';
$string['deleteteacherrequest'] = 'Ezabatu eskaria';
$string['deleterequesttitle'] = 'Eskaria Ezabatzen';
$string['deleterequestbody'] = 'Ziur zaude "{$a}" eskaera ezabatu nahi duzula?';
$string['seecoursestitle'] = 'Ikus eskariari lotutako ikastaroen zerrenda "{$a}"';
$string['emitrequesttitle'] = 'Ziurtagiria Jaulki';
$string['emitrequestbody'] = '{$a} ziurtagiria jaulki nahi duzula ziur zaude?';
$string['certifygenteacherrequestreport'] = 'Ikusi irakasleen ziurtagiri eskariak';
$string['othercertificates'] = 'Beste "{$a}" eskariak';
$string['mycertificate'] = 'Nire ziurtagiria';
$string['chooseuserfield'] = 'Aukeratu erabiltzaile eremua';
$string['userfield'] = 'Erabiltzaile Eremua';
$string['userfield_desc'] = 'Parametro hau erabiltzailearen identifikazioan erabiltzen da web zerbitzuetan. Ezer hautatzen ez bada, erabiltzailearen IDa erabiliko da.';
$string['report'] = 'Irakaslearen txantiloia';
$string['ok'] = 'Ados';
$string['checkstatustask'] = 'Ziurtagirien egoera egiaztatu';
$string['checkfiletask'] = 'Fitxategiak egiaztatu';
$string['teachercertificates'] = 'Irakasleen ziurtagiriak';
$string['chooseatemplate'] = 'Aukeratu txantiloi bat';
$string['managetemplates'] = 'Kudeatu txantiloiak';
$string['repository'] = 'Biltegia';
$string['repository_help'] = 'Biltegiaren laguntza';
$string['mycertificatesnotaccess'] = 'Ez duzu orri honetara sartzeko baimenik';
$string['teacherrequestreportnomodels'] = 'Oraindik ez da irakasleen ziurtagiriak sortzeko eredurik sortu';
$string['privacy:metadata:certifygen_validations'] = 'Ziurtagiriaren jaulkipenari buruzko informazioa';
$string['privacy:metadata:name'] = 'Ziurtagiriaren izena (irakasleen ziurtagirientzat bakarrik)';
$string['privacy:metadata:courses'] = 'Ziurtagiriarekin lotutako ikastaroen IDak (irakasleen ziurtagirientzat bakarrik)';
$string['privacy:metadata:code'] = 'Ziurtagiriaren kodea (irakasleen ziurtagirientzat bakarrik)';
$string['privacy:metadata:certifygenid'] = 'Jardueren instantziaren IDa (ikasleen ziurtagirientzat bakarrik)';
$string['privacy:metadata:issueid'] = 'Jaulkipenaren IDa (ikasleen ziurtagirientzat bakarrik)';
$string['privacy:metadata:userid'] = 'Ziurtagiria duen erabiltzailearen IDa.';
$string['privacy:metadata:modelid'] = 'Ereduaren IDa';
$string['privacy:metadata:lang'] = 'Ziurtagiriaren hizkuntza';
$string['privacy:metadata:status'] = 'Ziurtagiriaren egoera';
$string['privacy:metadata:usermodified'] = 'Erabiltzailearen IDa';
$string['privacy:metadata:timecreated'] = 'Ziurtagiria noiz jaulki zen';
$string['privacy:metadata:timemodified'] = 'Ziurtagiria noiz aldatu zen';
$string['nopermissiontoemitothercerts'] = 'Ez duzu ziurtagiri hau jaulkitzeko baimenik';
$string['nopermissiontodownloadothercerts'] = 'Ez duzu ziurtagiri hau deskargatzeko baimenik';
$string['nopermissiondeletemodel'] = 'Ez duzu eredu bat ezabatzeko baimenik';
$string['nopermissiondeleteteacherrequest'] = 'Ez duzu eskaera hau ezabatzeko baimenik';
$string['nopermissiontogetcourses'] = 'Ez duzu ikastaroak lortzeko baimenik';
$string['repositorynotvalidwithvalidationplugin'] = '{$a->repository} biltegia ez da bateragarria {$a->validation} baliozkotze pluginarekin';
$string['system'] = 'Sistema';
$string['checkerrortask'] = 'Ziurtagirien igorpen hutsak egiaztatu';
$string['certifygenerrors'] = 'Ikusi certifygen erroreak';
$string['idrequest'] = 'Eskaera id';
$string['validationplugin_not_enabled'] = 'Baliozkotzeko plugina ez dago gaituta';
$string['removefilters'] = 'Kendu iragazkiak';
$string['nopermissiontorevokecerts'] = 'Ez duzu ziurtagiri bat baliogabetzeko baimenik';
$string['certifygen:canemitotherscertificates'] = 'Ziurtagiriak eman ditzake beste erabiltzaile batzuei';
$string['certifygen:reemitcertificates'] = 'Ziurtagiriak berriro eman ditzakezu';
