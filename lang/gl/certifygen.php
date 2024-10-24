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
 * Spaish strings
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Certificado Certifygen';
$string['pluginadministration'] = 'Módulo de administración do Certificado de Unimoodle Certifygen';
$string['pluginnamesettings'] = 'Configuración do Certificado Unimoodle Certifygen';
$string['certifygen:addinstance'] = 'Engade unha nova instancia do Certificado de Unimoodle Certifygen';
$string['certifygen:view'] = 'Ver un Certificado de Unimoodle Certifygen';
$string['certifygen:manage'] = 'Xestionar certificados Unimoodle Certifygen';
$string['certifygen:canmanagecertificates'] = 'Pode xestionar certificados Unimoodle Certifygen';
$string['certifygen:viewmycontextcertificates'] = 'Ver os meus certificados Unimoodle Certifygen';
$string['certifygen:viewcontextcertificates'] = 'Ver certificados Unimoodle Certifygen doutros profesores';
$string['type'] = 'Tipo';
$string['type_help'] = 'Escolle o tipo de certificado que desexas emitir. Alumnado ou profesorado.';
$string['type_1'] = 'Curso completo (para alumnado)';
$string['type_2'] = 'Uso do curso (para profesorado)';
$string['mode'] = 'Modo';
$string['mode_help'] = 'Axuda do modo';
$string['mode_1'] = 'Único';
$string['mode_2'] = 'Repetitivo';
$string['templateid'] = 'Modelo';
$string['templateid_help'] = 'Selecciona un modelo para o certificado';
$string['introduction'] = 'Introdución';
$string['modulename'] = $string['pluginname'];
$string['modulenameplural'] = 'Certificados de Unimoodle Certifygen';
$string['name'] = 'Nome';
$string['modelname'] = 'Nome do modelo';
$string['modelidnumber'] = 'Número de Id';
$string['contextcertificatelink'] = 'Certificado Unimoodle Certifygen - curso';
$string['chooseamodel'] = 'Escolle un modelo';
$string['model'] = 'Modelo';
$string['modelsmanager'] = 'Xestión de modelos';
$string['associatemodels'] = 'Asociar modelos a contextos';
$string['download'] = 'Descargar';
$string['timeondemmand'] = 'Tempo entre peticións';
$string['timeondemmand_desc'] = 'Número de días que deben transcorrer ata que se poida volver a pedir o certificado de novo.';
$string['timeondemmand_help'] = 'Número de días que deben transcorrer ata que se poida volver a pedir o certificado de novo.';
$string['langs'] = 'Idiomas';
$string['chooselang'] = 'Filtra a lista polo idioma do certificado.';
$string['validation'] = 'Tipo de xeración';
$string['validation_desc'] = 'Descrición do tipo de xeración';
$string['validation_help'] = 'Axuda do tipo de xeración';
$string['modelmanager'] = 'Xestión de modelos';
$string['create_model'] = 'Crear Modelo';
$string['edit'] = 'Editar';
$string['delete'] = 'Borrar';
$string['template'] = 'Modelo';
$string['templatereport'] = 'Modelo/Informe';
$string['lastupdate'] = 'Última actualización';
$string['actions'] = 'Accións';
$string['code'] = 'Código';
$string['status'] = 'Estado';
$string['mycertificates'] = 'Os meus Certificados de Unimoodle Certifygen';
$string['deletemodeltitle'] = 'Borrando Modelo';
$string['deletemodelbody'] = 'Estás seguro de querer borrar o modelo chamado "{$a}"?';
$string['cannotdeletemodelcertemited'] = 'Non se pode borrar o modelo. Hai certificados asociados emitidos.';
$string['confirm'] = 'Aceptar';
$string['errortitle'] = 'Erro';
$string['model'] = 'Modelo';
$string['contexts'] = 'Contextos';
$string['assigncontext'] = 'Asignar contextos';
$string['editassigncontext'] = 'Modificar asignacións';
$string['subplugintype_certifygenvalidation'] = 'Método de validación do certificado de Unimoodle Certifygen';
$string['subplugintype_certifygenvalidation_plural'] = 'Métodos de validación do certificado de Unimoodle Certifygen';
$string['managecertifygenvalidationplugins'] = 'Xestionar os plugins de validación do certificado Unimoodle Certifygen';
$string['validationplugins'] = 'Plugins de validación';
$string['certifygenvalidationpluginname'] = $string['validationplugins'];
$string['hideshow'] = 'Agochar/Mostrar';
$string['settings'] = 'Configuración';
$string['assigncontextto'] = 'Asignar contextos ao modelo "{$a}"';
$string['toomanycategoriestoshow'] = 'Demasiadas categorías para mostrar';
$string['toomanycoursestoshow'] = 'Demasiados cursos para mostrar';
$string['chooseacontexttype'] = 'Escolle o contexto no que buscar';
$string['writealmost3characters'] = 'Escriba polo menos 1 carácter';
$string['coursecontext'] = 'Contexto do curso';
$string['categorycontext'] = 'Contexto da categoría';
$string['selectvalidation'] = 'Seleccionar a validación do certificado';
$string['selectreport'] = 'Seleccionar o tipo de informe do certificado';
$string['nocontextcourse'] = 'Este curso non ten permiso para acceder a esta páxina';
$string['hasnocapabilityrequired'] = 'Non tes o permiso necesario para acceder a esta páxina';
$string['emit'] = 'Emitir certificado';
$string['reemit'] = 'Reemitir certificado';
$string['status_1'] = 'Non iniciado';
$string['status_2'] = 'En progreso';
$string['status_3'] = 'Validado';
$string['status_4'] = 'Erro de validación';
$string['status_5'] = 'Almacenado';
$string['status_6'] = 'Erro no almacenamento';
$string['status_7'] = 'Erro';
$string['status_8'] = 'Finalizado';
$string['status_9'] = 'Erro xeral no certificado de estudante';
$string['status_10'] = 'Erro xeral no certificado de profesor';
$string['emitcertificate_title'] = 'Emitir Certificado';
$string['emitcertificate_body'] = 'Estás seguro de querer emitir o certificado en {$a}?';
$string['emitcertificate_error'] = 'Houbo un erro intentando emitir o certificado';
$string['certificatenotfound'] = 'Non se atopa o certificado';
$string['filter'] = 'Filtrar';
$string['revokecertificate_title'] = 'Eliminar Certificado';
$string['revokecertificate_body'] = 'Estás seguro de querer eliminar o certificado en {$a}?';
$string['revokecertificate_error'] = 'Houbo un erro intentando eliminar o certificado';
$string['downloadcertificate_title'] = 'Descargar Certificado';
$string['downloadcertificate_body'] = 'Estás seguro de querer descargar o certificado en {$a}?';
$string['downloadcertificate_error'] = 'Houbo un erro intentando descargar o certificado';
$string['notificationmsgcertificateissued'] = 'Notificación de certificado emitido';
$string['certificatelist'] = 'Lista de certificados';
$string['selectmycertificateslangdesc'] = 'Podes seleccionar o idioma do certificado.';
$string['system'] = 'Sistema';
$string['requestid'] = 'Número de petición';
$string['seecourses'] = 'Ver Cursos';
$string['create_request'] = 'Crear Petición';
$string['courseslist'] = 'Lista de cursos para certificar';
$string['deleterequesttitle'] = 'Borrar Petición';
$string['deleterequestbody'] = 'Estás seguro de querer borrar a petición número "{$a}"?';
$string['seecoursestitle'] = 'Lista de cursos asociados á petición "{$a}"';
$string['emitrequesttitle'] = 'Emitir certificado';
$string['emitrequestbody'] = 'Estás seguro de querer emitir o certificado {$a}?';
$string['certifygenteacherrequestreport'] = 'Ver peticións de certificados dos profesores';
$string['othercertificates'] = 'Listas de peticións de "{$a}"';
$string['mycertificate'] = 'O meu certificado';
$string['chooseuserfield'] = 'Escolle un campo de usuario';
$string['userfield'] = 'Campo de Usuario';
$string['userfield_desc'] = 'Este parámetro úsase nos servizos web para identificar ao usuario. Se non se escolle nada, usarase o id da táboa de usuario.';
$string['report'] = 'Modelo para profesor';
$string['ok'] = 'Acepto';
$string['checkstatustask'] = 'Comprobar o estado dos certificados';
$string['checkfiletask'] = 'Comprobar arquivos';
$string['teachercertificates'] = 'Certificados de profesores';
$string['chooseatemplate'] = 'Escolle un modelo';
$string['managetemplates'] = 'Xestionar modelos';
$string['repository'] = 'Repositorio';
$string['repository_help'] = 'Axuda do repositorio';
$string['mycertificatesnotaccess'] = 'Non tes permiso para acceder a esta páxina';
$string['teacherrequestreportnomodels'] = 'Aínda non se creou ningún modelo asociado a cursos para os certificados de profesor';
$string['privacy:metadata:certifygen_validations'] = 'Información sobre a emisión do certificado';
$string['privacy:metadata:name'] = 'Nome do certificado (só para certificados de profesor)';
$string['privacy:metadata:courses'] = 'Os ids de curso asociados ao certificado (só para certificados de profesor)';
$string['privacy:metadata:code'] = 'Código de certificado (só para certificados de profesor)';
$string['privacy:metadata:certifygenid'] = 'Id da instancia de actividade (só para certificados de alumnado)';
$string['privacy:metadata:issueid'] = 'Id de emisión (só para certificados de alumnado)';
$string['privacy:metadata:userid'] = 'Id do usuario ao que pertence o certificado.';
$string['privacy:metadata:modelid'] = 'Id do modelo';
$string['privacy:metadata:lang'] = 'O idioma do certificado';
$string['privacy:metadata:status'] = 'Estado do certificado';
$string['privacy:metadata:usermodified'] = 'Id de usuario';
$string['privacy:metadata:timecreated'] = 'Hora na que se emitiu o certificado';
$string['privacy:metadata:timemodified'] = 'Hora na que se modificou o certificado';
$string['nopermissiontoemitothercerts'] = 'Non tes permiso para emitir este certificado';
$string['nopermissiontodownloadothercerts'] = 'Non tes permiso para descargar este certificado';
$string['nopermissiondeletemodel'] = 'Non tes permiso para borrar un modelo';
$string['nopermissiondeleteteacherrequest'] = 'Non tes permiso para borrar esta petición';
$string['nopermissiontogetcourses'] = 'Non tes permiso para obter cursos';
$string['repositorynotvalidwithvalidationplugin'] = 'O repositorio {$a->repository} non é compatible co plugin de validación {$a->validation}';
$string['system'] = 'Sistema';
$string['checkerrortask'] = 'Comprobar emisións de certificados fallidas';
$string['certifygenerrors'] = 'Consulta os erros de certifygen';
$string['idrequest'] = 'Id de solicitude';
$string['validationplugin_not_enabled'] = 'O complemento de validación non está activado';
$string['removefilters'] = 'Eliminar filtros';
$string['nopermissiontorevokecerts'] = 'Non tes permisos para revogar un certificado';
$string['certifygen:canemitotherscertificates'] = 'Pode emitir certificados a outros usuarios';
$string['certifygen:reemitcertificates'] = 'Podes volver a emitir certificados';
$string['lang_not_exists'] = 'Este idioma non está instalado, {$a->lang}';
$string['coursenotexists'] = 'O curso non existe';
$string['empty_repository_url'] = 'A ligazón do certificado no repositorio está baleira';
$string['savefile_returns_error'] = 'Erro ao gardar o ficheiro';
$string['repository_plugin_not_enabled'] = 'O plugin do repositorio está desactivado';
$string['getfile_missing_file_parameter'] = 'Falta o parámetro ficheiro';
$string['validationnotfound'] = 'Non existe o rexistro na táboa certifygen_validations';
$string['statusnotfinished'] = 'O estado do certificado non está rematado';
$string['cannotreemit'] = 'Non se pode reemitir o certificado';
$string['file_not_found'] = 'Ficheiro non atopado';
$string['missingreportonmodel'] = 'Falta o parámetro informe no modelo';
$string['user_not_found'] = 'Usuario non atopado';
$string['lang_not_found'] = 'Idioma non instalado na plataforma';
$string['student_not_enrolled'] = 'O usuario non está matriculado no curso id={$a} como estudante';
$string['teacher_not_enrolled'] = 'O usuario non está matriculado no curso id={$a} como profesor';
$string['model_type_assigned_to_activity'] = 'O modelo non está asignado a unha actividade';
$string['certificate_not_ready'] = 'O certificado non está listo. O estado é {$a}';
$string['userfield_and_userid_sent'] = 'Só se debe enviar un parámetro asociado ao usuario';
$string['userfield_not_valid'] = 'Campo de usuario non válido';
$string['issue_not_found'] = 'Código de emisión non atopado';
$string['userfield_not_selected'] = 'Non se seleccionou ningún campo de usuario na plataforma';
$string['user_not_sent'] = 'Non se indicou o usuario';
$string['model_not_found'] = 'O modelo non existe';
$string['model_not_valid'] = 'Modelo non válido';
$string['course_not_valid_with_model'] = 'O curso, {$a}, non é compatible co modelo';
$string['codeview'] = 'Busca certificados por código';
$string['codefound'] = 'Atopamos un resultado. Descarga o ficheiro facendo clic na seguinte ligazón {$a}';
$string['codenotfound'] = 'Non atopamos ningún resultado con este código';
$string['certifygensearchfor'] = 'Buscar certificados por código';
$string['model_must_exists'] = 'O modelo debe existir';
$string['course_not_valid_for_modelid'] = 'Non se pode restaurar a actividade {$a->activityname}. O curso ({$a->courseid}) non é válido para este modelo (nome: {$a->name}, número de identificación: {$a->idnumber})';
