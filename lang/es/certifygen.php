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

$string['pluginname'] = 'Certificado Certifygen';
$string['pluginadministration'] = 'Módulo de administración del Certificado de Unimoodle Certifygen';
$string['pluginnamesettings'] = 'Configuración del Certificado Unimoodle Certifygen';
$string['certifygen:addinstance'] = 'Añade una nueva instacia del Certificado de Unimoodle Certifygen';
$string['certifygen:view'] = 'Ver un Certificado de Unimoodle Certifygen';
$string['certifygen:manage'] = 'Gestionar certificados Unimoodle Certifygen';
$string['certifygen:canmanagecertificates'] = 'Puede gestionar certificados Unimoodle Certifygen';
$string['certifygen:viewmycontextcertificates'] = 'Ver mis certificados Unimoodle Certifygen';
$string['certifygen:viewcontextcertificates'] = 'Ver certificados Unimoodle Certifygen de otros profesores';
$string['type'] = 'Tipo';
$string['type_help'] = 'Elige el tipo de certificado que deseas emitir. Alumno o profesor.';
$string['type_1'] = 'Curso completo (para alumnos)';
$string['type_2'] = 'Uso del curso (for teachers)';
$string['mode'] = 'Modo';
$string['mode_help'] = 'Modo help';
$string['mode_1'] = 'Único';
$string['mode_2'] = 'Repetitiva';
$string['templateid'] = 'Plantilla';
$string['templateid_help'] = 'Selecciona una platnilla para el certificado';
$string['introduction'] = 'Introducción';
$string['modulename'] = $string['pluginname'];
$string['modulenameplural'] = 'Certificados de Unimoodle Certifygen';
$string['name'] = 'Nombre';
$string['modelname'] = 'Nombre del modelo';
$string['modelidnumber'] = 'Idnumber';
$string['contextcertificatelink'] = 'Certificado Unimoodle Certifygen - curso';
$string['chooseamodel'] = 'Elige un modelo';
$string['model'] = 'Modelo';
$string['modelsmanager'] = 'Gestión de modelos';
$string['associatemodels'] = 'Asociar modelos a contextos';
$string['download'] = 'Descargar';
$string['timeondemmand'] = 'Tiempo entre peticiones';
$string['timeondemmand_desc'] = 'Número de días que tienen que transcurrir hasta que se pueda volver a pedir el certificado de nuevo.';
$string['timeondemmand_help'] = 'Número de días que tienen que transcurrir hasta que se pueda volver a pedir el certificado de nuevo.';
$string['langs'] = 'Idiomas';
$string['chooselang'] = 'Filtra el listado por el idioma del certificado.';
$string['validation'] = 'Tipo de generación';
$string['validation_desc'] = 'Tipo de generación desc';
$string['validation_help'] = 'Tipo de generación _help';
$string['modelmanager'] = 'Gestión de módelos';
$string['create_model'] = 'Crear Modelo';
$string['edit'] = 'Editar';
$string['delete'] = 'Borrar';
$string['template'] = 'Plantilla';
$string['templatereport'] = 'Plantilla/Informe';
$string['lastupdate'] = 'Última actualización';
$string['actions'] = 'Acciones';
$string['code'] = 'Código';
$string['status'] = 'Estado';
$string['mycertificates'] = 'Mis Certificados de Unimoodle Certifygen';
$string['deletemodeltitle'] = 'Borrando Modelo';
$string['deletemodelbody'] = '¿Está seguro que quieres borrar el modelo llamado "{$a}"?';
$string['cannotdeletemodelcertemited'] = 'El modelo no se puede borrar. Hay certificados asociados emitidos.';
$string['confirm'] = 'Confirmar';
$string['errortitle'] = 'Error';
$string['model'] = 'Modelo';
$string['contexts'] = 'Contextos';
$string['assigncontext'] = 'Asignar contextos';
$string['editassigncontext'] = 'Modificar asignaciones';
$string['subplugintype_certifygenvalidation'] = 'Método de validación del certificado de Unimoodle Certifygen';
$string['subplugintype_certifygenvalidation_plural'] = 'Métodos de validación del certificado de Unimoodle Certifygen';
$string['managecertifygenvalidationplugins'] = 'Gestionar los plugins de validación del certificado Unimoodle Certifygen';
$string['validationplugins'] = 'Validation plugins';
$string['certifygenvalidationpluginname'] = $string['validationplugins'];
$string['hideshow'] = 'Ocultar/Mostrar';
$string['settings'] = 'Configuración';
$string['assigncontextto'] = 'Asignar contextos al model "{$a}"';
$string['toomanycategoriestoshow'] = 'Demasiadas categorias para mostrar';
$string['toomanycoursestoshow'] = 'Too many courses to show';
$string['chooseacontexttype'] = 'Elige el contexto en el que buscar';
$string['writealmost3characters'] = 'Escriba al menos 1 caracter';
$string['coursecontext'] = 'Contexto de curso';
$string['categorycontext'] = 'Contexto de categoría';
$string['selectvalidation'] = 'Seleccionar la validación del certificado';
$string['selectreport'] = 'Seleccionar el tipo de informe del certificado';
$string['nocontextcourse'] = 'Este curso no tiene permiso a esta página';
$string['hasnocapabilityrequired'] = 'No tienes el permiso necesario para acceder a esta página';
$string['emit'] = 'Emitir certificado';
$string['reemit'] = 'Re-emitir certificado';
$string['status_1'] = 'No iniciado';
$string['status_2'] = 'En progreso';
$string['status_3'] = 'Validado';
$string['status_4'] = 'Error de validación';
$string['status_5'] = 'Almacenado';
$string['status_6'] = 'Error en el almacenamiento';
$string['status_7'] = 'Error';
$string['status_8'] = 'Finalizado';
$string['status_9'] = 'Error general en certificado de estudiante';
$string['status_10'] = 'Error general en certificado de profesor';
$string['emitcertificate_title'] = 'Emitit Certificado';
$string['emitcertificate_body'] = '¿Estás seguro de querer emitir el certificado en {$a}?';
$string['emitcertificate_error'] = 'Ha ocurrido un error intentando emitir el certificado';
$string['certificatenotfound'] = 'No se encuentra el certificado';
$string['filter'] = 'Filtrar';
$string['revokecertificate_title'] = 'Eliminar Certificado';
$string['revokecertificate_body'] = '¿Estás seguro de querer eliminar el certificado en {$a}?';
$string['revokecertificate_error'] = 'Ha ocurrido un error intentando eliminar el certificado';
$string['downloadcertificate_title'] = 'Descargar Certificado';
$string['downloadcertificate_body'] = '¿Estás seguro de querer descargar el certificado en {$a}?';
$string['downloadcertificate_error'] = 'Ha ocurrido un error intentando descargar el certificado';
$string['notificationmsgcertificateissued'] = 'notificationmsgcertificateissued';
$string['certificatelist'] = 'Listado de certificados';
$string['selectmycertificateslangdesc'] = 'Puedes seleccionar el idioma del certificado.';
$string['system'] = 'Sistema';
$string['requestid'] = 'Número de petición';
$string['seecourses'] = 'Ver Cursos';
$string['create_request'] = 'Crear Petición';
$string['courseslist'] = 'Listado de cursos para certificar';
$string['deleterequesttitle'] = 'Borrar Petición';
$string['deleterequestbody'] = '¿Estás seguro de querer borrar la petición número "{$a}"?';
$string['seecoursestitle'] = 'Listado de cursos asociados a la petición "{$a}"';
$string['emitrequesttitle'] = 'Emitir certificado';
$string['emitrequestbody'] = '¿Estás seguro de querer emitir el certificado {$a}?';
$string['certifygenteacherrequestreport'] = 'Ver peticiones de certificados de los profesores';
$string['othercertificates'] = 'Lists de peticiones de "{$a}"';
$string['mycertificate'] = 'Mi certificado';
$string['chooseuserfield'] = 'Elige un campo de usuario';
$string['userfield'] = 'Campo de Usuario';
$string['userfield_desc'] = 'Este parametro se utiliza en los servicios web para identificar al usuario. Si no se elige nada se usará el id de la tabla user.';
$string['report'] = 'Plantilla para profesor';
$string['ok'] = 'Acepto';
$string['checkstatustask'] = 'Comprobar estado de los certificados';
$string['checkfiletask'] = 'Comprobar archivos';
$string['teachercertificates'] = 'Certificados  de profesores';
$string['chooseatemplate'] = 'Elige una plantilla';
$string['managetemplates'] = 'Gestionar plantillas';
$string['repository'] = 'Repositorio';
$string['repository_help'] = 'Repositorio help';
$string['mycertificatesnotaccess'] = 'No tienes permiso para acceder a esta pagina';
$string['teacherrequestreportnomodels'] = 'Todavía no se ha creado ningun modelo asociado a cursos para los certificados de profesor';
$string['privacy:metadata:certifygen_validations'] = 'Information about the certificate issuance';
$string['privacy:metadata:name'] = 'Nombre de certificado (solo para certificados de profesor)';
$string['privacy:metadata:courses'] = 'Los ids de curso asociados al certificado (solo para certificados de profesor)';
$string['privacy:metadata:code'] = 'Código de certificadoo (solo para certificados de profesor)';
$string['privacy:metadata:certifygenid'] = 'El id de la instancia de actividad (solo para certificados de alumno)';
$string['privacy:metadata:issueid'] = 'El id de emisión (solo para certificados de alumno)';
$string['privacy:metadata:userid'] = 'Id del usuario al que pertenece el certificado.';
$string['privacy:metadata:modelid'] = 'Id de modelo';
$string['privacy:metadata:lang'] = 'El idioma del certificado';
$string['privacy:metadata:status'] = 'Estado del certificado';
$string['privacy:metadata:usermodified'] = 'Id de usuario';
$string['privacy:metadata:timecreated'] = 'Tiempo en el que se emitió el certificado';
$string['privacy:metadata:timemodified'] = 'Tiempo en el que se modificó el certificado';
$string['nopermissiontoemitothercerts'] = 'No tienes permiso para emitir este certificado';
$string['nopermissiontodownloadothercerts'] = 'No tienes permiso para descargar este certificado';
$string['nopermissiondeletemodel'] = 'No tienes permiso para borrar un modelo';
$string['nopermissiondeleteteacherrequest'] = 'No tienes permiso para borrar esta petición';
$string['nopermissiontogetcourses'] = 'No tienes permiso para obtener cursos';
$string['repositorynotvalidwithvalidationplugin'] = 'El repositorio {$a->repository} no es compatible con el plugin de validación {$a->validation}';
$string['system'] = 'Sistema';
$string['checkerrortask'] = 'Comprobar emisiones de certificados fallidas';
$string['certifygenerrors'] = 'Ver errores del proceso';
$string['idrequest'] = 'Id Petición';
$string['validationplugin_not_enabled'] = 'El plugin de validación no está habilitado';
$string['removefilters'] = 'Eliminar filtros';
$string['nopermissiontorevokecerts'] = 'No tienes permisos para revocar un certificado';
$string['certifygen:canemitotherscertificates'] = 'Puede emitir certificados de otro usuarios';
$string['certifygen:reemitcertificates'] = 'Puede reemitir certificados';
$string['lang_not_exists'] = 'Este idioma no está instalado, {$a->lang}';
$string['coursenotexists'] = 'No existe el curso';
$string['empty_repository_url'] = 'El enlace del certificado en el repositorio está vacío';
$string['savefile_returns_error'] = 'Error al guardar el archivo';
$string['repository_plugin_not_enabled'] = 'El plugin de repositorio está deshabilitado';
$string['getfile_missing_file_parameter'] = 'Falta el parámetro file';
$string['validationnotfound'] = 'No existe el registro en la tabla certifygen_validations';
$string['statusnotfinished'] = 'El estado del certificado no es finalizado';
$string['cannotreemit'] = 'No se puede reemitir el certificado';
$string['file_not_found'] = 'Archivo no encontrado';
$string['missingreportonmodel'] = 'Falta el parámetro report en el modelo';
$string['user_not_found'] = 'Usuario no encontrado';
$string['lang_not_found'] = 'Idioma no instalado en la plataforma';
$string['student_not_enrolled'] = 'El usuario no está matirculado en el curso id={$a} como estudiante';
$string['teacher_not_enrolled'] = 'El usuario no está matirculado en el curso id={$a} como profesor';
$string['model_type_assigned_to_activity'] = 'El modelo no está asociado a una actividad';
$string['certificate_not_ready'] = 'El certifiado no está listo. El estado es {$a}';
$string['userfield_and_userid_sent'] = 'Solo hay que enviar un parámetro asociado al usuario';
$string['userfield_not_valid'] = 'Campo de usuario no válido';
$string['issue_not_found'] = 'Código de emisión no encontrado';
$string['userfield_not_selected'] = 'No se ha seleccionado ningun campo de usuario en la plataforma';
$string['user_not_sent'] = 'Falta indicar el usuario';
$string['model_not_found'] = 'No existe el modelo';
$string['model_not_valid'] = 'Modelo no válido';
$string['course_not_valid_with_model'] = 'El curso, {$a}, no es compatible con el modelo';
$string['codeview'] = 'Búsqueda de certificados por código';
$string['codefound'] = 'Hemos encontrado un resultado. Descargate el fichero pinchando en el siguiente link {$a}';
$string['codenotfound'] = 'No hemos encontrado ningun resultado con este código';
$string['certifygensearchfor'] = 'Búsqueda de certificados por código';
$string['model_must_exists'] = 'No se puede restaurar la actividad {$a->activityname}. Debe existir un modelo con idnumber igual a {$a->idnumber}';
$string['course_not_valid_for_modelid'] = 'No se puede restaurar la actividad {$a->activityname}. El curso ({$a->courseid}) no es válido para este modelo(nombre: {$a->name}, idnumber: {$a->idnumber})';
