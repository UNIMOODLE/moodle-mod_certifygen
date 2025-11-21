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
 * Spanish strings
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

$string['actions'] = 'Acciones';
$string['assigncontext'] = 'Asignar contextos';
$string['assigncontextto'] = 'Asignar contextos al model "{$a}"';
$string['associatemodels'] = 'Asociar modelos a contextos';
$string['cannot_begin_upload_session'] = 'No de puede iniciar la sesión de subida';
$string['cannot_connect_as_current_user'] = 'No se puede conectar como usuario actual';
$string['cannot_connect_as_system_user'] = 'No se puede conectar como usuario del sistema';
$string['cannot_create_folder'] = 'No se puede crear la carpeta';
$string['cannot_update_link_sharing_for_document'] = 'Cannot update link sharing for document';
$string['cannotdeletemodelcertemited'] = 'El modelo no se puede borrar. Hay certificados asociados emitidos.';
$string['cannotreemit'] = 'No se puede reemitir el certificado';
$string['categorycontext'] = 'Contexto de categoría';
$string['certificate_not_ready'] = 'El certifiado no está listo. El estado es {$a}';
$string['certificatelist'] = 'Listado de certificados';
$string['certificatenotfound'] = 'No se encuentra el certificado';
$string['certifygen:addinstance'] = 'Añade una nueva instacia del Certificado de Unimoodle Certifygen';
$string['certifygen:canemitotherscertificates'] = 'Puede emitir certificados de otro usuarios';
$string['certifygen:canmanagecertificates'] = 'Puede gestionar certificados Unimoodle Certifygen';
$string['certifygen:emitmyactivitycertificate'] = 'Emitir certificados en una actividad';
$string['certifygen:manage'] = 'Gestionar certificados Unimoodle Certifygen';
$string['certifygen:reemitcertificates'] = 'Puede reemitir certificados';
$string['certifygen:view'] = 'Ver un Certificado de Unimoodle Certifygen';
$string['certifygen:viewcontextcertificates'] = 'Ver certificados Unimoodle Certifygen de otros profesores';
$string['certifygen:viewmycontextcertificates'] = 'Ver mis certificados Unimoodle Certifygen';
$string['certifygenerrors'] = 'Ver errores del proceso';
$string['certifygensearchfor'] = 'Búsqueda de certificados por código';
$string['certifygenteacherrequestreport'] = 'Ver peticiones de certificados de los profesores';
$string['checkerrortask'] = 'Comprobar emisiones de certificados fallidas';
$string['checkfiletask'] = 'Comprobar archivos';
$string['checkstatustask'] = 'Comprobar estado de los certificados';
$string['chooseacontexttype'] = 'Elige el contexto en el que buscar';
$string['chooseamodel'] = 'Elige un modelo';
$string['chooseatemplate'] = 'Elige una plantilla';
$string['chooselang'] = 'Filtra el listado por el idioma del certificado.';
$string['chooseuserfield'] = 'Elige un campo de usuario';
$string['code'] = 'Código';
$string['codefound'] = 'Hemos encontrado un resultado. Descargate el fichero pinchando en el siguiente link {$a}';
$string['codenotfound'] = 'No hemos encontrado ningun resultado con este código';
$string['codeview'] = 'Búsqueda de certificados por código';
$string['completiondownload'] = 'Compleción por descarga de certificado';
$string['completiondownloaddesc'] = 'Los participantes deben descargarse un certificado para finalizar la actividad.';
$string['configurated_logo'] = 'Logo configurado';
$string['confirm'] = 'Confirmar';
$string['contextcertificatelink'] = 'Certificado Unimoodle Certifygen - curso';
$string['contexts'] = 'Contextos';
$string['course_not_valid_for_modelid'] = 'No se puede restaurar la actividad {$a->activityname}. El curso ({$a->courseid}) no es válido para este modelo(nombre: {$a->name}, idnumber: {$a->idnumber})';
$string['course_not_valid_with_model'] = 'El curso, {$a}, no es compatible con el modelo';
$string['coursecontext'] = 'Contexto de curso';
$string['coursenotexists'] = 'No existe el curso';
$string['courseslist'] = 'Listado de cursos para certificar';
$string['create_model'] = 'Crear Modelo';
$string['create_request'] = 'Crear Petición';
$string['delete'] = 'Borrar';
$string['deletemodelbody'] = '¿Está seguro que quieres borrar el modelo llamado "{$a}"?';
$string['deletemodeltitle'] = 'Borrando Modelo';
$string['deleterequestbody'] = '¿Estás seguro de querer borrar la petición número "{$a}"?';
$string['deleterequesttitle'] = 'Borrar Petición';
$string['download'] = 'Descargar';
$string['downloadcertificate_body'] = '¿Estás seguro de querer descargar el certificado en {$a}?';
$string['downloadcertificate_error'] = 'Ha ocurrido un error intentando descargar el certificado';
$string['downloadcertificate_title'] = 'Descargar Certificado';
$string['edit'] = 'Editar';
$string['editassigncontext'] = 'Modificar asignaciones';
$string['emit'] = 'Emitir certificado';
$string['emitcertificate_body'] = '¿Estás seguro de querer emitir el certificado en {$a}?';
$string['emitcertificate_error'] = 'Ha ocurrido un error intentando emitir el certificado';
$string['emitcertificate_title'] = 'Emitit Certificado';
$string['emitrequestbody'] = '¿Estás seguro de querer emitir el certificado {$a}?';
$string['emitrequesttitle'] = 'Emitir certificado';
$string['empty_repository_url'] = 'El enlace del certificado en el repositorio está vacío';
$string['errortitle'] = 'Error';
$string['file_not_found'] = 'Archivo no encontrado';
$string['filter'] = 'Filtrar';
$string['getfile_missing_file_parameter'] = 'Falta el parámetro file';
$string['hasnocapabilityrequired'] = 'No tienes el permiso necesario para acceder a esta página';
$string['hideshow'] = 'Ocultar/Mostrar';
$string['idrequest'] = 'Id Petición';
$string['introduction'] = 'Introducción';
$string['invalid_language'] = 'Idioma no válido';
$string['issue_not_found'] = 'Código de emisión no encontrado';
$string['lang'] = 'Idioma';
$string['lang_not_exists'] = 'Este idioma no está instalado, {$a->lang}';
$string['lang_not_found'] = 'Idioma no instalado en la plataforma';
$string['langs'] = 'Idiomas';
$string['lastupdate'] = 'Última actualización';
$string['managecertifygenvalidationplugins'] = 'Gestionar los plugins de validación del certificado Unimoodle Certifygen';
$string['managetemplates'] = 'Gestionar plantillas';
$string['messageprovider:certifygen_notification'] = 'Notificación Certifygen';
$string['missingreportonmodel'] = 'Falta el parámetro report en el modelo';
$string['mode'] = 'Modo';
$string['mode_1'] = 'Único';
$string['mode_2'] = 'Repetitiva';
$string['mode_help'] = 'Modo help';
$string['model'] = 'Modelo';
$string['model_must_exists'] = 'No se puede restaurar la actividad {$a->activityname}. Debe existir un modelo con idnumber igual a {$a->idnumber}';
$string['model_not_found'] = 'No existe el modelo';
$string['model_not_valid'] = 'Modelo no válido';
$string['model_type_assigned_to_activity'] = 'El modelo está asociado a una actividad';
$string['modelidnumber'] = 'Idnumber';
$string['modelmanager'] = 'Gestión de módelos';
$string['modelname'] = 'Nombre del modelo';
$string['modelsmanager'] = 'Gestión de modelos';
$string['modulename'] = 'Certificado Certifygen';
$string['modulenameplural'] = 'Certificados de Unimoodle Certifygen';
$string['mycertificate'] = 'Mi certificado';
$string['mycertificates'] = 'Mis Certificados de Unimoodle Certifygen';
$string['mycertificatesnotaccess'] = 'No tienes permiso para acceder a esta pagina';
$string['name'] = 'Nombre';
$string['nocontextassociated'] = 'Este modelo no tiene ningun contexto asociado';
$string['nocontextcourse'] = 'Este curso no tiene permiso a esta página';
$string['nopermissiondeletemodel'] = 'No tienes permiso para borrar un modelo';
$string['nopermissiondeleteteacherrequest'] = 'No tienes permiso para borrar esta petición';
$string['nopermissiontodownloadothercerts'] = 'No tienes permiso para descargar este certificado';
$string['nopermissiontoemitothercerts'] = 'No tienes permiso para emitir este certificado';
$string['nopermissiontogetcourses'] = 'No tienes permiso para obtener cursos';
$string['nopermissiontorevokecerts'] = 'No tienes permisos para revocar un certificado';
$string['notificationmsgcertificateissued'] = 'Hola {$a->fullname},<br /><br />Tu certificado ya está disponible! Puedes descargartelo desde:
<a href="{$a->url}">{$a->linkname}</a>';
$string['notificationsubjectcertificateissued'] = 'Tu certificado ya está disponible!';
$string['ok'] = 'Acepto';
$string['othercertificates'] = 'Lists de peticiones de "{$a}"';
$string['pluginadministration'] = 'Módulo de administración del Certificado de Unimoodle Certifygen';
$string['pluginname'] = 'Certificado Certifygen';
$string['pluginnamesettings'] = 'Configuración del Certificado Unimoodle Certifygen';
$string['privacy:metadata:certifygen_repository'] = 'Información sobre la ubicación del certificado';
$string['privacy:metadata:certifygen_validations'] = 'Information about the certificate issuance';
$string['privacy:metadata:certifygenid'] = 'El id de la instancia de actividad (solo para certificados de alumno)';
$string['privacy:metadata:code'] = 'Código de certificadoo (solo para certificados de profesor)';
$string['privacy:metadata:courses'] = 'Los ids de curso asociados al certificado (solo para certificados de profesor)';
$string['privacy:metadata:data'] = 'Datos relacionados con la ubicación del repositorio';
$string['privacy:metadata:issueid'] = 'El id de emisión (solo para certificados de alumno)';
$string['privacy:metadata:lang'] = 'El idioma del certificado';
$string['privacy:metadata:modelid'] = 'Id de modelo';
$string['privacy:metadata:name'] = 'Nombre de certificado (solo para certificados de profesor)';
$string['privacy:metadata:status'] = 'Estado del certificado';
$string['privacy:metadata:timecreated'] = 'Tiempo en el que se emitió el certificado';
$string['privacy:metadata:timemodified'] = 'Tiempo en el que se modificó el certificado';
$string['privacy:metadata:url'] = 'Enlace a la ubicación del repositorio';
$string['privacy:metadata:userid'] = 'Id del usuario al que pertenece el certificado.';
$string['privacy:metadata:usermodified'] = 'Id de usuario';
$string['privacy:metadata:validationid'] = 'El id de la instancia de validación del certificado';
$string['reemit'] = 'Re-emitir certificado';
$string['removefilters'] = 'Eliminar filtros';
$string['report'] = 'Plantilla para profesor';
$string['repository'] = 'Repositorio';
$string['repository_help'] = 'Repositorio help';
$string['repository_plugin_not_enabled'] = 'El plugin de repositorio está deshabilitado';
$string['repositorynotvalidwithvalidationplugin'] = 'El plugin de repositorio {$a->repository} no es compatible con el de validación {$a->validation}.';
$string['requestid'] = 'Número de petición';
$string['results'] = 'Resultados';
$string['revokecertificate_body'] = '¿Estás seguro de querer eliminar el certificado en {$a}?';
$string['revokecertificate_error'] = 'Ha ocurrido un error intentando eliminar el certificado';
$string['revokecertificate_title'] = 'Eliminar Certificado';
$string['savefile_returns_error'] = 'Error al guardar el archivo';
$string['seecourses'] = 'Ver Cursos';
$string['seecoursestitle'] = 'Listado de cursos asociados a la petición "{$a}"';
$string['selectmycertificateslangdesc'] = 'Puedes seleccionar el idioma del certificado.';
$string['selectreport'] = 'Seleccionar el tipo de informe del certificado';
$string['selectvalidation'] = 'Seleccionar la validación del certificado';
$string['settings'] = 'Configuración';
$string['status'] = 'Estado';
$string['status_1'] = 'No iniciado';
$string['status_10'] = 'Error general en certificado de profesor';
$string['status_2'] = 'En progreso';
$string['status_3'] = 'Validado';
$string['status_4'] = 'Error de validación';
$string['status_5'] = 'Almacenado';
$string['status_6'] = 'Error en el almacenamiento';
$string['status_7'] = 'Error';
$string['status_9'] = 'Error general en certificado de estudiante';
$string['statusnotfinished'] = 'El estado del certificado no es finalizado';
$string['student_not_enrolled'] = 'El usuario no está matirculado en el curso id={$a} como estudiante';
$string['subplugintype_certifygenvalidation'] = 'Método de validación del certificado de Unimoodle Certifygen';
$string['subplugintype_certifygenvalidation_plural'] = 'Métodos de validación del certificado de Unimoodle Certifygen';
$string['system'] = 'Sistema';
$string['teacher_not_enrolled'] = 'El usuario no está matirculado en el curso id={$a} como profesor';
$string['teachercertificates'] = 'Certificados  de profesores';
$string['teacherrequestreportnomodels'] = 'Todavía no se ha creado ningun modelo asociado a cursos para los certificados de profesor';
$string['template'] = 'Plantilla';
$string['templateid'] = 'Plantilla';
$string['templateid_help'] = 'Selecciona una platnilla para el certificado';
$string['templatenotfound'] = 'Hay un problema con la configuración de la plantilla del certificado. De momento no se puede hacer uso de la actividad.';
$string['templatereport'] = 'Plantilla/Informe';
$string['timeondemmand'] = 'Tiempo entre peticiones';
$string['timeondemmand_desc'] = 'Número de días que tienen que transcurrir hasta que se pueda volver a pedir el certificado de nuevo.';
$string['timeondemmand_help'] = 'Número de días que tienen que transcurrir hasta que se pueda volver a pedir el certificado de nuevo.';
$string['toomanycategoriestoshow'] = 'Demasiadas categorias para mostrar';
$string['toomanycoursestoshow'] = 'Demasiados cursos para mostrar';
$string['type'] = 'Tipo';
$string['type_1'] = 'Curso completo (para alumnos)';
$string['type_2'] = 'Uso del curso (for teachers)';
$string['type_help'] = 'Elige el tipo de certificado que deseas emitir. Alumno o profesor.';
$string['user_not_found'] = 'Usuario no encontrado';
$string['user_not_sent'] = 'Falta indicar el usuario';
$string['userfield'] = 'Campo de Usuario';
$string['userfield_and_userid_sent'] = 'Solo hay que enviar un parámetro asociado al usuario';
$string['userfield_desc'] = 'Este parametro se utiliza en los servicios web para identificar al usuario. Si no se elige nada se usará el id de la tabla user.';
$string['userfield_not_selected'] = 'No se ha seleccionado ningun campo de usuario en la plataforma';
$string['userfield_not_valid'] = 'Campo de usuario no válido';
$string['validation'] = 'Tipo de generación';
$string['validation_desc'] = 'Tipo de generación desc';
$string['validation_help'] = 'Tipo de generación _help';
$string['validationnotfound'] = 'No existe el registro en la tabla certifygen_validations';
$string['validationnotvalidwithrepositoryplugin'] = 'El plugin de validación {$a->validation} no es compatible con el de repositorio {$a->repository}.';
$string['validationplugin_not_enabled'] = 'El plugin de validación no está habilitado';
$string['validationplugins'] = 'Subplugins de validación';
$string['writealmost3characters'] = 'Escriba al menos 1 caracter';
