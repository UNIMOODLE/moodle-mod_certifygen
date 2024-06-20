/**
 * @module    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
"use strict";

import jQuery from 'jquery';
import {get_string as getString, get_strings as getStrings} from 'core/str';
import Ajax from 'core/ajax';
import Templates from 'core/templates';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Notification from 'core/notification';
import ModalForm from 'core_form/modalform';

let REGION = {
    ROOT: '[data-region="profile-my-certificates"]',
};
let TEMPLATES = {
    LOADING: 'core/overlay_loading',
    TEACHER_REQUEST: 'mod_certifygen/profile_my_certificates',
    COURSE_LIST_REQUEST: 'mod_certifygen/course_list_request',
    // STUDENTS_TABLE: 'mod_certifygen/my_certificates_validator',
};
let ACTION = {
    CREATE_REQUEST: '[data-action="create-request"]',
    DELETE_REQUEST: '[data-action="delete-request"]',
    SEE_COURSES: '[data-action="see-courses"]',
    EMIT: '[data-action="emit"]',
    // EMIT_CERTIFICATE: '[data-action="emit-certificate"]',
    // REVOKE_CERTIFICATE: '[data-action="revoke-certificate"]',
};
let SERVICES = {
    GET_TEACHER_REQUEST_VIEW_DATA: 'mod_certifygen_getteacherrequestviewdata',
    DELETE_TEACHER_REQUEST: 'mod_certifygen_deleteteacherrequest',
    GET_COURSES_NAMES: 'mod_certifygen_getcoursesnames',
    EMIT_TEACHER_REQUEST: 'mod_certifygen_emitteacherrequest',
    // EMIT_CERTIFICATE: 'mod_certifygen_emitcertificate',
    // MYSTUDENTS_TABLE_DATA: 'mod_certifygen_get_students_certificates_table',
    // GET_MYCERTIFICATE_TABLE_DATA: 'mod_certifygen_getmycertificatedata',
    // REVOKE_CERTIFICATE: 'mod_certifygen_revokecertificate',
};
const requestManagement = () => {
    jQuery(ACTION.CREATE_REQUEST).on('click', createRequest);
    jQuery(ACTION.DELETE_REQUEST).on('click', deleteRequest);
    jQuery(ACTION.SEE_COURSES).on('click', seeCourses);
    jQuery(ACTION.EMIT).on('click', emitCertificate);
    // jQuery(ACTION.REVOKE_CERTIFICATE).on('click', revokeCertificate);
};
const emitCertificate = async (event) => {
    let id = event.currentTarget.getAttribute('data-id');
    id = parseInt(id);
    let userid = event.currentTarget.getAttribute('data-userid');
    const stringkeys = [
        {key: 'emitrequesttitle', component: 'mod_certifygen'},
        {key: 'emitrequestbody', component: 'mod_certifygen', param: id},
        {key: 'confirm', component: 'mod_certifygen'},
        {key: 'errortitle', component: 'mod_certifygen'},
    ];
    getStrings(stringkeys).then((langStrings) => {
        return ModalFactory.create({
            title: langStrings[0],
            body: langStrings[1],
            type: ModalFactory.types.SAVE_CANCEL
        }).then(modal => {
            modal.getRoot().on(ModalEvents.hidden, () => {
                modal.destroy();
            });
            modal.getRoot().on(ModalEvents.save, () => {
                let request = {
                    methodname: SERVICES.EMIT_TEACHER_REQUEST,
                    args: {id}
                };
                Ajax.call([request])[0].done(function(response) {
                    if (response.result == 1) {
                        // Recargar tabla.
                        reloadTeacherRequestTable(userid);
                    } else {
                        // Mostrar mensaje error.
                        return ModalFactory.create({
                            title: langStrings[0],
                            body: response.message,
                            type: ModalFactory.types.CANCEL
                        }).then(modal => {
                            modal.getRoot().on(ModalEvents.hidden, () => {
                                modal.destroy();
                            });
                        });
                    }
                }).fail(Notification.exception);
            });
            return modal;
        });
    }).done(function(modal) {
        modal.show();
    }).fail(Notification.exception);
};
const seeCourses = async (event) => {
    let id = event.currentTarget.getAttribute('data-id');
    let coursesids = event.currentTarget.getAttribute('data-courses');
    const stringkeys = [
        {key: 'seecoursestitle', component: 'mod_certifygen', param: id},
    ];
    let langStrings = await getStrings(stringkeys);
    let request = {
        methodname: SERVICES.GET_COURSES_NAMES,
        args: {coursesids}
    };
    let coursesdata = await Ajax.call([request])[0];
    let courseshtml = await Templates.render(TEMPLATES.COURSE_LIST_REQUEST, coursesdata).done(function(html) {
        return html;
    });
    await ModalFactory.create({
            title: langStrings[0],
            body: courseshtml,
            type: ModalFactory.types.DEFAULT,
        }).then(modal => {
            modal.show();
            modal.getRoot().on(ModalEvents.hidden, () => {
                modal.destroy();
            });
        });
};
const deleteRequest = (event) => {
    let id = event.currentTarget.getAttribute('data-id');
    const stringkeys = [
        {key: 'deleterequesttitle', component: 'mod_certifygen'},
        {key: 'deleterequestbody', component: 'mod_certifygen', param: id},
        {key: 'confirm', component: 'mod_certifygen'},
        {key: 'errortitle', component: 'mod_certifygen'},
    ];
    getStrings(stringkeys).then((langStrings) => {
        return ModalFactory.create({
            title: langStrings[0],
            body: langStrings[1],
            type: ModalFactory.types.SAVE_CANCEL
        }).then(modal => {
            modal.getRoot().on(ModalEvents.hidden, () => {
                modal.destroy();
            });
            modal.getRoot().on(ModalEvents.save, () => {
                let request = {
                    methodname: SERVICES.DELETE_TEACHER_REQUEST,
                    args: { id}
                };
                Ajax.call([request])[0].done(function(response) {
                    if (response.result == 1) {
                        // Remove tr.
                        jQuery(event.currentTarget).parent().parent().remove();
                    } else {
                        // Mostrar mensaje error.
                        return ModalFactory.create({
                            title: langStrings[0],
                            body: response.message,
                            type: ModalFactory.types.CANCEL
                        }).then(modal => {
                            modal.getRoot().on(ModalEvents.hidden, () => {
                                modal.destroy();
                            });
                        });
                    }
                }).fail(Notification.exception);
            });
            return modal;
        });
    }).done(function(modal) {
        modal.show();
    }).fail(Notification.exception);
};
const createRequest = (e) => {
    e.preventDefault();
    const element = e.target;
    let id = event.currentTarget.getAttribute('data-id');
    let userid = event.currentTarget.getAttribute('data-userid');

    const modalForm = new ModalForm({
        // Name of the class where form is defined (must extend \core_form\dynamic_form):
        formClass: "mod_certifygen\\forms\\teacherrequestform",
        // Add as many arguments as you need, they will be passed to the form:
        args: {id, userid},
        // Pass any configuration settings to the modal dialogue, for example, the title:
        modalConfig: {title: getString('create_request', 'mod_certifygen')},
        // DOM element that should get the focus after the modal dialogue is closed:
        returnFocus: element,
    });
    // Listen to events if you want to execute something on form submit. Event detail will contain everything the process()
    // function returned:
    modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, () => {
        // Recargar la tabla.
        setTimeout(reloadTeacherRequestTable(userid), 1000);

    });
    // Show the form.
    modalForm.show();
};
const reloadTeacherRequestTable = (userid) => {
    Templates.render(TEMPLATES.LOADING, {visible: true}).done(function(html) {
        let identifier = jQuery(REGION.ROOT);
        identifier.append(html);
        let request = {
            methodname: SERVICES.GET_TEACHER_REQUEST_VIEW_DATA,
            args: {userid}
        };
        Ajax.call([request])[0].done(function(data) {
            Templates.render(TEMPLATES.TEACHER_REQUEST, data).then((html, js) => {
                identifier.html(html);
                Templates.runTemplateJS(js);
            }).fail(Notification.exception);
        }).fail(Notification.exception);
    });
};

// const revokeCertificate = async(event) => {
//     let issueid = event.currentTarget.getAttribute('data-issueid');
//     let cmid = event.currentTarget.getAttribute('data-cmid');
//     let userid = event.currentTarget.getAttribute('data-userid');
//     let modelid = event.currentTarget.getAttribute('data-modelid');
//     let courseid = event.currentTarget.getAttribute('data-courseid');
//     let username = event.currentTarget.getAttribute('data-username');
//     // Modal estas seguro que quieres eliminar el certiifcado?.
//     const stringkeys = [
//         {key: 'revokecertificate_title', component: 'mod_certifygen'},
//         {key: 'revokecertificate_body', component: 'mod_certifygen', param: username},
//         {key: 'confirm', component: 'mod_certifygen'},
//         {key: 'revokecertificate_error', component: 'mod_certifygen'}
//     ];
//     let langStrings = await getStrings(stringkeys);
//
//     let modal = await ModalFactory.create({
//         title: langStrings[0],
//         body: langStrings[1],
//         type: ModalFactory.types.SAVE_CANCEL
//     });
//     modal.setSaveButtonText(langStrings[2]);
//     modal.getRoot().on(ModalEvents.save, () => {
//         let request = {
//             methodname: SERVICES.REVOKE_CERTIFICATE,
//             args: {
//                 issueid,
//                 userid,
//                 modelid,
//             }
//         };
//         modal.destroy();
//         Ajax.call([request])[0].done(function(response) {
//             if (response.result === true) {
//                 teacherReloadTable(modelid, courseid, cmid);
//             } else {
//                 Notification.alert('Error', response.message, langStrings[2]);
//             }
//         }).fail(Notification.exception);
//     });
//     modal.getRoot().on(ModalEvents.hidden, () => {
//         modal.destroy();
//     });
//     modal.show();
// };
// const emitCertificate = async(event) => {
//     let id = parseInt(event.currentTarget.getAttribute('data-id'));
//     let modelid = parseInt(event.currentTarget.getAttribute('data-modelid'));
//     let lang = event.currentTarget.getAttribute('data-lang');
//     let langstring = event.currentTarget.getAttribute('data-langstring');
//     let userid = parseInt(event.currentTarget.getAttribute('data-userid'));
//     let courseid = parseInt(event.currentTarget.getAttribute('data-courseid'));
//     let cmid = parseInt(event.currentTarget.getAttribute('data-cmid'));
//
//     // Modal estas seguro que quieres enviar el certiifcado?.
//     const stringkeys = [
//         {key: 'emitcertificate_title', component: 'mod_certifygen'},
//         {key: 'emitcertificate_body', component: 'mod_certifygen', param: langstring},
//         {key: 'confirm', component: 'mod_certifygen'},
//         {key: 'emitcertificate_error', component: 'mod_certifygen'}
//     ];
//     let langStrings = await getStrings(stringkeys);
//
//     let modal = await ModalFactory.create({
//         title: langStrings[0],
//         body: langStrings[1],
//         type: ModalFactory.types.SAVE_CANCEL
//     });
//     modal.setSaveButtonText(langStrings[2]);
//     modal.getRoot().on(ModalEvents.save, () => {
//         let request = {
//             methodname: SERVICES.EMIT_CERTIFICATE,
//             args: {
//                 id,
//                 modelid,
//                 lang,
//                 userid,
//                 courseid,
//             }
//         };
//         modal.destroy();
//         Ajax.call([request])[0].done(function(response) {
//             if (response.result === true) {
//                 studentsReloadTable(modelid, courseid, cmid, lang);
//             } else {
//                 Notification.alert('Error', response.message, langStrings[2]);
//             }
//         }).fail(Notification.exception);
//     });
//     modal.getRoot().on(ModalEvents.hidden, () => {
//         modal.destroy();
//     });
//     modal.show();
// };
// const teacherReloadTable = (modelid, courseid, cmid) => {
//     modelid = parseInt(modelid);
//     courseid = parseInt(courseid);
//     cmid = parseInt(cmid);
//     Templates.render(TEMPLATES.LOADING, {visible: true}).done(function(html) {
//         let identifier = jQuery(REGION.ROOT);
//         identifier.append(html);
//         let request = {
//             methodname: SERVICES.MYSTUDENTS_TABLE_DATA,
//             args: {modelid, courseid, cmid}
//         };
//         Ajax.call([request])[0].done(function(data) {
//             Templates.render(TEMPLATES.TEACHER_TABLE, data).then((html, js) => {
//                 identifier.html(html);
//                 Templates.runTemplateJS(js);
//             }).fail(Notification.exception);
//         }).fail((error) => {
//             Notification.alert('Error', error.message, 'cancel');
//         });
//     });
// };
// const studentsReloadTable = (modelid, courseid, cmid, lang) => {
//     modelid = parseInt(modelid);
//     courseid = parseInt(courseid);
//     cmid = parseInt(cmid);
//     Templates.render(TEMPLATES.LOADING, {visible: true}).done(function(html) {
//         let identifier = jQuery(REGION.ROOT);
//         identifier.append(html);
//         let request = {
//             methodname: SERVICES.GET_MYCERTIFICATE_TABLE_DATA,
//             args: {modelid, courseid, cmid, lang}
//         };
//         Ajax.call([request])[0].done(function(data) {
//             Templates.render(TEMPLATES.STUDENTS_TABLE, data).then(function(html, js) {
//                 identifier.html(html);
//                 Templates.runTemplateJS(js);
//             }).fail(Notification.exception);
//         }).fail((error) => {
//             Notification.alert('Error', error.message, 'cancel');
//         });
//     });
// };
export const init = () => {
    requestManagement();
};