// This file is part of Moodle - http://moodle.org/
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

/**
 * Provides the required functionality for creating a model.
 * 
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
    jQuery('body').on('change', '.custom-select', function (event) {
        if (event.target && event.target.name === 'type' && event.target.value == '2') {
            addTemplateidValue();
        } else if (event.target && event.target.name === 'type' && event.target.value == '1'
        && jQuery('form [name="templateid"]').length > 1) {
            removeTemplateidVlaue();
        } else {
            checkTemplateidValue();
        }
    });
    jQuery('body').on('focusout', 'input[type="text"]', function () {
        checkTemplateidValue();
    });
};
const checkTemplateidValue = () => {
    let typeElement = document.querySelector('form [name="type"]');
    if (typeElement &&  typeElement.value == 2) {
        addTemplateidValue();
    }
};
const removeTemplateidVlaue = () => {
    jQuery('form [name="templateid"]').each(function () {
        if (this.type == 'hidden') {
            jQuery(this).remove();
        }
    });
};
const addTemplateidValue = () => {
    const element = document.querySelector('form [name="templateid"]');
    if (element) {
        element.setAttribute('data-initial-value', '0');
    }
    addHiddenTemplateidInput();
};
const addHiddenTemplateidInput = () => {
    jQuery("<input>").attr({
        name: "templateid",
        type: "hidden",
        value: 0
    }).appendTo("form");
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
        // Reload the list table after the form is submitted.
        reloadModelListTable();

    });
    // Show the form.
    modalForm.show();
};
const reloadModelListTable = () => {
    Templates.render(TEMPLATES.LOADING, {visible: true}).done(function (html) {
        let identifier = jQuery(REGION.LIST_TABLE);
        identifier.append(html);
        let request = {
            methodname: SERVICES.GET_LIST_TABLE,
            args: {}
        };
        Ajax.call([request])[0].done(function (data) {
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
