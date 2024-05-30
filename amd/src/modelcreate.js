/**
 * @module    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
"use strict";

import jQuery from 'jquery';
import {get_string as getString} from 'core/str';
import Ajax from 'core/ajax';
import Templates from 'core/templates';
// import ModalFactory from 'core/modal_factory';
// import ModalEvents from 'core/modal_events';
import Notification from 'core/notification';
import ModalForm from 'core_form/modalform';

let SERVICES = {
    GET_LIST_TABLE : 'mod_certifygen_getmodellisttable',
};
let ACTION = {
    CREATE_MODEL: '[data-action="create-model"]',
};
let REGION = {
    ROOT: '[data-region="model-list-view"]',
    LIST_TABLE: '[data-region="model-list-table"]',
};
let TEMPLATES = {
    LOADING: 'core/overlay_loading',
    MODELLISTTABLE: 'mod_certifygen/model_list_table',
};
const InitModelCreate = () => {
    jQuery(ACTION.CREATE_MODEL).on('click', CreateModel);
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

export const init = () => {
    InitModelCreate();
};