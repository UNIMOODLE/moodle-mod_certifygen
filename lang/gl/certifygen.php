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

$string['actions'] = 'Accións';
$string['assigncontext'] = 'Asignar contextos';
$string['assigncontextto'] = 'Asignar contextos ao modelo "{$a}"';
$string['associatemodels'] = 'Asociar modelos a contextos';
$string['cannot_begin_upload_session'] = 'Non de pode iniciar a sesión de subida';
$string['cannot_connect_as_current_user'] = 'Non se pode conectar como usuario actual';
$string['cannot_connect_as_system_user'] = 'Non se pode conectar como usuario do sistema';
$string['cannot_create_folder'] = 'Non se pode crear o cartafol';
$string['cannot_update_link_sharing_for_document'] = 'Non se pode actualizar a ligazón compartida para o documento';
$string['cannotdeletemodelcertemited'] = 'Non se pode borrar o modelo. Hai certificados asociados emitidos.';
$string['cannotreemit'] = 'Non se pode reemitir o certificado';
$string['categorycontext'] = 'Contexto da categoría';
$string['certificate_not_ready'] = 'O certificado non está listo. O estado é {$a}';
$string['certificatelist'] = 'Lista de certificados';
$string['certificatenotfound'] = 'Non se atopa o certificado';
$string['certifygen:addinstance'] = 'Engade unha nova instancia do Certificado de Unimoodle Certifygen';
$string['certifygen:canemitotherscertificates'] = 'Pode emitir certificados a outros usuarios';
$string['certifygen:canmanagecertificates'] = 'Pode xestionar certificados Unimoodle Certifygen';
$string['certifygen:emitmyactivitycertificate'] = 'Expedir certificados nunha actividade';
$string['certifygen:manage'] = 'Xestionar certificados Unimoodle Certifygen';
$string['certifygen:reemitcertificates'] = 'Podes volver a emitir certificados';
$string['certifygen:view'] = 'Ver un Certificado de Unimoodle Certifygen';
$string['certifygen:viewcontextcertificates'] = 'Ver certificados Unimoodle Certifygen doutros profesores';
$string['certifygen:viewmycontextcertificates'] = 'Ver os meus certificados Unimoodle Certifygen';
$string['certifygenerrors'] = 'Consulta os erros de certifygen';
$string['certifygensearchfor'] = 'Buscar certificados por código';
$string['certifygenteacherrequestreport'] = 'Ver peticións de certificados dos profesores';
$string['checkerrortask'] = 'Comprobar emisións de certificados fallidas';
$string['checkfiletask'] = 'Comprobar arquivos';
$string['checkstatustask'] = 'Comprobar o estado dos certificados';
$string['chooseacontexttype'] = 'Escolle o contexto no que buscar';
$string['chooseamodel'] = 'Escolle un modelo';
$string['chooseatemplate'] = 'Escolle un modelo';
$string['chooselang'] = 'Filtra a lista polo idioma do certificado.';
$string['chooseuserfield'] = 'Escolle un campo de usuario';
$string['code'] = 'Código';
$string['codefound'] = 'Atopamos un resultado. Descarga o ficheiro facendo clic na seguinte ligazón {$a}';
$string['codenotfound'] = 'Non atopamos ningún resultado con este código';
$string['codeview'] = 'Busca certificados por código';
$string['completiondownload'] = 'Completación por descarga de certificado';
$string['completiondownloaddesc'] = 'Os participantes deben descargar un certificado para finalizar a actividade.';
$string['configurated_logo'] = 'Logo configurado';
$string['confirm'] = 'Aceptar';
$string['contextcertificatelink'] = 'Certificado Unimoodle Certifygen - curso';
$string['contexts'] = 'Contextos';
$string['course_not_valid_for_modelid'] = 'Non se pode restaurar a actividade {$a->activityname}. O curso ({$a->courseid}) non é válido para este modelo (nome: {$a->name}, número de identificación: {$a->idnumber})';
$string['course_not_valid_with_model'] = 'O curso, {$a}, non é compatible co modelo';
$string['coursecontext'] = 'Contexto do curso';
$string['coursenotexists'] = 'O curso non existe';
$string['courseslist'] = 'Lista de cursos para certificar';
$string['create_model'] = 'Crear Modelo';
$string['create_request'] = 'Crear Petición';
$string['delete'] = 'Borrar';
$string['deletemodelbody'] = 'Estás seguro de querer borrar o modelo chamado "{$a}"?';
$string['deletemodeltitle'] = 'Borrando Modelo';
$string['deleterequestbody'] = 'Estás seguro de querer borrar a petición número "{$a}"?';
$string['deleterequesttitle'] = 'Borrar Petición';
$string['download'] = 'Descargar';
$string['downloadcertificate_body'] = 'Estás seguro de querer descargar o certificado en {$a}?';
$string['downloadcertificate_error'] = 'Houbo un erro intentando descargar o certificado';
$string['downloadcertificate_title'] = 'Descargar Certificado';
$string['edit'] = 'Editar';
$string['editassigncontext'] = 'Modificar asignacións';
$string['emit'] = 'Emitir certificado';
$string['emitcertificate_body'] = 'Estás seguro de querer emitir o certificado en {$a}?';
$string['emitcertificate_error'] = 'Houbo un erro intentando emitir o certificado';
$string['emitcertificate_title'] = 'Emitir Certificado';
$string['emitrequestbody'] = 'Estás seguro de querer emitir o certificado {$a}?';
$string['emitrequesttitle'] = 'Emitir certificado';
$string['empty_repository_url'] = 'A ligazón do certificado no repositorio está baleira';
$string['errortitle'] = 'Erro';
$string['file_not_found'] = 'Ficheiro non atopado';
$string['filter'] = 'Filtrar';
$string['getfile_missing_file_parameter'] = 'Falta o parámetro ficheiro';
$string['hasnocapabilityrequired'] = 'Non tes o permiso necesario para acceder a esta páxina';
$string['hideshow'] = 'Agochar/Mostrar';
$string['idrequest'] = 'Id de solicitude';
$string['introduction'] = 'Introdución';
$string['invalid_language'] = 'Idioma non válido';
$string['issue_not_found'] = 'Código de emisión non atopado';
$string['lang'] = 'idioma';
$string['lang_not_exists'] = 'Este idioma non está instalado, {$a->lang}';
$string['lang_not_found'] = 'Idioma non instalado na plataforma';
$string['langs'] = 'Idiomas';
$string['lastupdate'] = 'Última actualización';
$string['managecertifygenvalidationplugins'] = 'Xestionar os plugins de validación do certificado Unimoodle Certifygen';
$string['managetemplates'] = 'Xestionar modelos';
$string['messageprovider:certifygen_notification'] = 'Notificación Certifygen';
$string['missingreportonmodel'] = 'Falta o parámetro informe no modelo';
$string['mode'] = 'Modo';
$string['mode_1'] = 'Único';
$string['mode_2'] = 'Repetitivo';
$string['mode_help'] = 'Axuda do modo';
$string['model'] = 'Modelo';
$string['model_must_exists'] = 'Non se pode restaurar a actividade {$a->activityname}. Debe haber un modelo cun idnumber igual a {$a->idnumber}';
$string['model_not_found'] = 'O modelo non existe';
$string['model_not_valid'] = 'Modelo non válido';
$string['model_type_assigned_to_activity'] = 'O modelo está asignado a unha actividade';
$string['modelidnumber'] = 'Número de Id';
$string['modelmanager'] = 'Xestión de modelos';
$string['modelname'] = 'Nome do modelo';
$string['modelsmanager'] = 'Xestión de modelos';
$string['modulename'] = 'Certificado Certifygen';
$string['modulenameplural'] = 'Certificados de Unimoodle Certifygen';
$string['mycertificate'] = 'O meu certificado';
$string['mycertificates'] = 'Os meus Certificados de Unimoodle Certifygen';
$string['mycertificatesnotaccess'] = 'Non tes permiso para acceder a esta páxina';
$string['name'] = 'Nome';
$string['nocontextassociated'] = 'Este modelo non ten ningún contexto asociado.';
$string['nocontextcourse'] = 'Este curso non ten permiso para acceder a esta páxina';
$string['nopermissiondeletemodel'] = 'Non tes permiso para borrar un modelo';
$string['nopermissiondeleteteacherrequest'] = 'Non tes permiso para borrar esta petición';
$string['nopermissiontodownloadothercerts'] = 'Non tes permiso para descargar este certificado';
$string['nopermissiontoemitothercerts'] = 'Non tes permiso para emitir este certificado';
$string['nopermissiontogetcourses'] = 'Non tes permiso para obter cursos';
$string['nopermissiontorevokecerts'] = 'Non tes permisos para revogar un certificado';
$string['notificationmsgcertificateissued'] = 'Notificación de certificado emitido';
$string['ok'] = 'Acepto';
$string['othercertificates'] = 'Listas de peticións de "{$a}"';
$string['pluginadministration'] = 'Módulo de administración do Certificado de Unimoodle Certifygen';
$string['pluginname'] = 'Certificado Certifygen';
$string['pluginnamesettings'] = 'Configuración do Certificado Unimoodle Certifygen';
$string['privacy:metadata:certifygen_repository'] = 'Información sobre a localización do certificado';
$string['privacy:metadata:certifygen_validations'] = 'Información sobre a emisión do certificado';
$string['privacy:metadata:certifygenid'] = 'Id da instancia de actividade (só para certificados de alumnado)';
$string['privacy:metadata:code'] = 'Código de certificado (só para certificados de profesor)';
$string['privacy:metadata:courses'] = 'Os ids de curso asociados ao certificado (só para certificados de profesor)';
$string['privacy:metadata:issueid'] = 'Id de emisión (só para certificados de alumnado)';
$string['privacy:metadata:lang'] = 'O idioma do certificado';
$string['privacy:metadata:modelid'] = 'Id do modelo';
$string['privacy:metadata:name'] = 'Nome do certificado (só para certificados de profesor)';
$string['privacy:metadata:status'] = 'Estado do certificado';
$string['privacy:metadata:timecreated'] = 'Hora na que se emitiu o certificado';
$string['privacy:metadata:timemodified'] = 'Hora na que se modificou o certificado';
$string['privacy:metadata:userid'] = 'Id do usuario ao que pertence o certificado.';
$string['privacy:metadata:usermodified'] = 'Id de usuario';
$string['reemit'] = 'Reemitir certificado';
$string['removefilters'] = 'Eliminar filtros';
$string['report'] = 'Modelo para profesor';
$string['repository'] = 'Repositorio';
$string['repository_help'] = 'Axuda do repositorio';
$string['repository_plugin_not_enabled'] = 'O plugin do repositorio está desactivado';
$string['repositorynotvalidwithvalidationplugin'] = 'O repositorio {$a->repository} non é compatible co plugin de validación {$a->validation}';
$string['requestid'] = 'Número de petición';
$string['results'] = 'Resultados';
$string['revokecertificate_body'] = 'Estás seguro de querer eliminar o certificado en {$a}?';
$string['revokecertificate_error'] = 'Houbo un erro intentando eliminar o certificado';
$string['revokecertificate_title'] = 'Eliminar Certificado';
$string['savefile_returns_error'] = 'Erro ao gardar o ficheiro';
$string['seecourses'] = 'Ver Cursos';
$string['seecoursestitle'] = 'Lista de cursos asociados á petición "{$a}"';
$string['selectmycertificateslangdesc'] = 'Podes seleccionar o idioma do certificado.';
$string['selectreport'] = 'Seleccionar o tipo de informe do certificado';
$string['selectvalidation'] = 'Seleccionar a validación do certificado';
$string['settings'] = 'Configuración';
$string['status'] = 'Estado';
$string['status_1'] = 'Non iniciado';
$string['status_10'] = 'Erro xeral no certificado de profesor';
$string['status_2'] = 'En progreso';
$string['status_3'] = 'Validado';
$string['status_4'] = 'Erro de validación';
$string['status_5'] = 'Almacenado';
$string['status_6'] = 'Erro no almacenamento';
$string['status_7'] = 'Erro';
$string['status_9'] = 'Erro xeral no certificado de estudante';
$string['statusnotfinished'] = 'O estado do certificado non está rematado';
$string['student_not_enrolled'] = 'O usuario non está matriculado no curso id={$a} como estudante';
$string['subplugintype_certifygenvalidation'] = 'Método de validación do certificado de Unimoodle Certifygen';
$string['subplugintype_certifygenvalidation_plural'] = 'Métodos de validación do certificado de Unimoodle Certifygen';
$string['system'] = 'Sistema';
$string['teacher_not_enrolled'] = 'O usuario non está matriculado no curso id={$a} como profesor';
$string['teachercertificates'] = 'Certificados de profesores';
$string['teacherrequestreportnomodels'] = 'Aínda non se creou ningún modelo asociado a cursos para os certificados de profesor';
$string['template'] = 'Modelo';
$string['templateid'] = 'Modelo';
$string['templateid_help'] = 'Selecciona un modelo para o certificado';
$string['templatenotfound'] = 'Hai un problema coa configuración da actividade. Polo de agora non se pode facer uso dela.';
$string['templatereport'] = 'Modelo/Informe';
$string['timeondemmand'] = 'Tempo entre peticións';
$string['timeondemmand_desc'] = 'Número de días que deben transcorrer ata que se poida volver a pedir o certificado de novo.';
$string['timeondemmand_help'] = 'Número de días que deben transcorrer ata que se poida volver a pedir o certificado de novo.';
$string['toomanycategoriestoshow'] = 'Demasiadas categorías para mostrar';
$string['toomanycoursestoshow'] = 'Demasiados cursos para mostrar';
$string['type'] = 'Tipo';
$string['type_1'] = 'Curso completo (para alumnado)';
$string['type_2'] = 'Uso do curso (para profesorado)';
$string['type_help'] = 'Escolle o tipo de certificado que desexas emitir. Alumnado ou profesorado.';
$string['user_not_found'] = 'Usuario non atopado';
$string['user_not_sent'] = 'Non se indicou o usuario';
$string['userfield'] = 'Campo de Usuario';
$string['userfield_and_userid_sent'] = 'Só se debe enviar un parámetro asociado ao usuario';
$string['userfield_desc'] = 'Este parámetro úsase nos servizos web para identificar ao usuario. Se non se escolle nada, usarase o id da táboa de usuario.';
$string['userfield_not_selected'] = 'Non se seleccionou ningún campo de usuario na plataforma';
$string['userfield_not_valid'] = 'Campo de usuario non válido';
$string['validation'] = 'Tipo de xeración';
$string['validation_desc'] = 'Descrición do tipo de xeración';
$string['validation_help'] = 'Axuda do tipo de xeración';
$string['validationnotfound'] = 'Non existe o rexistro na táboa certifygen_validations';
$string['validationnotvalidwithrepositoryplugin'] = 'O plugin de validación {$a->validation} non é compatible co do repositorio {$a->repository}';
$string['validationplugin_not_enabled'] = 'O complemento de validación non está activado';
$string['validationplugins'] = 'Plugins de validación';
$string['writealmost3characters'] = 'Escriba polo menos 1 carácter';

