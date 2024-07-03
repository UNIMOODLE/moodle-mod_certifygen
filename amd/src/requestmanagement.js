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
};
let ACTION = {
    CREATE_REQUEST: '[data-action="create-request"]',
    DELETE_REQUEST: '[data-action="delete-request"]',
    SEE_COURSES: '[data-action="see-courses"]',
    EMIT: '[data-action="emit"]',
};
let SERVICES = {
    GET_TEACHER_REQUEST_VIEW_DATA: 'mod_certifygen_getteacherrequestviewdata',
    DELETE_TEACHER_REQUEST: 'mod_certifygen_deleteteacherrequest',
    GET_COURSES_NAMES: 'mod_certifygen_getcoursesnames',
    EMIT_TEACHER_REQUEST: 'mod_certifygen_emitteacherrequest',
};
const requestManagement = () => {
    jQuery(ACTION.CREATE_REQUEST).on('click', createRequest);
    jQuery(ACTION.DELETE_REQUEST).on('click', deleteRequest);
    jQuery(ACTION.SEE_COURSES).on('click', seeCourses);
    jQuery(ACTION.EMIT).on('click', emitCertificate);
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

export const init = () => {
    requestManagement();
};