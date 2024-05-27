/**
 * @module    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
"use strict";

import jQuery from 'jquery';
import {get_strings as getStrings, get_string as getString} from 'core/str';
import Ajax from 'core/ajax';
import Templates from 'core/templates';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Notification from 'core/notification';
// import mEvent from 'core/event';
import ModalForm from 'core_form/modalform';

let SERVICES = {
    DELETE_MODEL : 'mod_certifygen_deletemodel',
    GET_LIST_TABLE : 'mod_certifygen_getmodellisttable',
};
let ACTION = {
    DELETE_MODEL: '[data-action="delete-model"]',
    CREATE_MODEL: '[data-action="create-model"]',
    EDIT_MODEL: '[data-action="edit-model"]',
    ASSIGN_CONTEXTS: '[data-action="assign-context"]',
};
let REGION = {
    ROOT: '[data-region="model-list-view"]',
    LIST_TABLE: '[data-region="model-list-table"]',
};
let TEMPLATES = {
    LOADING: 'core/overlay_loading',
    MODELLISTTABLE: 'mod_certifygen/model_list_table',
};
const ModelManagement = () => {
    jQuery(ACTION.DELETE_MODEL).on('click', DeleteModel);
    jQuery(ACTION.CREATE_MODEL).on('click', CreateModel);
    jQuery(ACTION.EDIT_MODEL).on('click', CreateModel);
    jQuery(ACTION.ASSIGN_CONTEXTS).on('click', AssignContext.bind(this));
};

const AssignContext = (e) => {
    e.preventDefault();
    const element = e.target;
    let mainElement = event.currentTarget;
    let id = event.currentTarget.getAttribute('data-id');
    let modelid = event.currentTarget.getAttribute('data-modelid');
    let name = event.currentTarget.getAttribute('data-name');
    const modalCForm = new ModalForm({
        // Name of the class where form is defined (must extend \core_form\dynamic_form):
        formClass: "mod_certifygen\\forms\\associatecontextform",
        // Add as many arguments as you need, they will be passed to the form:
        args: {id: id, modelid: modelid},
        // Pass any configuration settings to the modal dialogue, for example, the title:
        modalConfig: {title: getString('assigncontextto', 'mod_certifygen', name)},
        // DOM element that should get the focus after the modal dialogue is closed:
        returnFocus: element,
    });
    // Listen to events if you want to execute something on form submit. Event detail will contain everything the process()
    // function returned:
    modalCForm.addEventListener(modalCForm.events.FORM_SUBMITTED, (result) => {
        if (id === '0') {
            mainElement.setAttribute('data-id', result.detail);
        }
    });
    // Show the form.
    modalCForm.show();
};
const CreateModel = (e) => {
    e.preventDefault();
    const element = e.target;
    let id = 0;
    if (event.currentTarget.getAttribute('data-action') === 'edit-model') {
        id = event.currentTarget.getAttribute('data-id');
    }
    const modalForm = new ModalForm({
        // Name of the class where form is defined (must extend \core_form\dynamic_form):
        formClass: "mod_certifygen\\forms\\modelform",
        // Add as many arguments as you need, they will be passed to the form:
        args: {id},
        // Pass any configuration settings to the modal dialogue, for example, the title:
        modalConfig: {title: getString('create_model', 'mod_certifygen')},
        // DOM element that should get the focus after the modal dialogue is closed:
        returnFocus: element,
    });
    // Listen to events if you want to execute something on form submit. Event detail will contain everything the process()
    // function returned:
    modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, () => {
        // Recargar la tabla.
        reloadModelListTable();

    });
    // Show the form.
    modalForm.show();
};
const reloadModelListTable = () => {
    Templates.render(TEMPLATES.LOADING, {visible: true}).done(function(html) {
        let identifier = jQuery(REGION.LIST_TABLE);
        identifier.append(html);
        let request = {
            methodname: SERVICES.GET_LIST_TABLE,
            args: {}
        };
        Ajax.call([request])[0].done(function(data) {
            Templates.render(TEMPLATES.MODELLISTTABLE, data).then((html, js) => {
                identifier.html(html);
                Templates.runTemplateJS(js);
            }).fail(Notification.exception);
        }).fail(Notification.exception);
    });
};
const DeleteModel = (event) => {
    let name = event.currentTarget.getAttribute('data-name');
    let modelId = event.currentTarget.getAttribute('data-id');
    const stringkeys = [
        {key: 'deletemodeltitle', component: 'mod_certifygen'},
        {key: 'deletemodelbody', component: 'mod_certifygen', param: name},
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
                    methodname: SERVICES.DELETE_MODEL,
                    args: {
                        id: modelId,
                    }
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
export const init = () => {
    ModelManagement();
};