/**
 * @module    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
"use strict";

import jQuery from 'jquery';
import {get_strings as getStrings} from 'core/str';
import Ajax from 'core/ajax';
import Templates from 'core/templates';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Notification from 'core/notification';

let REGION = {
    ROOT: '[data-region="certificate-validator"]',
};
let TEMPLATES = {
    LOADING: 'core/overlay_loading',
    TABLE: 'mod_certifygen/my_certificates_validator',
};
let ACTION = {
    EMIT_CERTIFICATE: '[data-action="emit-certificate"]',
};
let SERVICES = {
    EMIT_CERTIFICATE: 'mod_certifygen_emitcertificate',
    GET_TABLE_DATA: 'mod_certifygen_getcontextcertificatedata',
};
const CertificateManagement = () => {
    jQuery(ACTION.EMIT_CERTIFICATE).on('click', EmitCertificate);
};
const EmitCertificate = async (event) => {
    let id = event.currentTarget.getAttribute('data-id');
    let modelid = event.currentTarget.getAttribute('data-modelid');
    let lang = event.currentTarget.getAttribute('data-lang');
    let langstring = event.currentTarget.getAttribute('data-langstring');
    let userid = event.currentTarget.getAttribute('data-userid');
    let courseid = event.currentTarget.getAttribute('data-courseid');

    // Modal estas seguro que quieres enviar el certiifcado?.
    const stringkeys = [
        {key: 'emitcertificate_title', component: 'mod_certifygen'},
        {key: 'emitcertificate_body', component: 'mod_certifygen', param: langstring},
        {key: 'confirm', component: 'mod_certifygen'},
        {key: 'emitcertificate_error', component: 'mod_certifygen'}
    ];
    let langStrings = await getStrings(stringkeys);

    let modal = await ModalFactory.create({
        title: langStrings[0],
        body: langStrings[1],
        type: ModalFactory.types.SAVE_CANCEL
    });
    modal.setSaveButtonText(langStrings[2]);
    modal.getRoot().on(ModalEvents.save, () => {
        let request = {
            methodname: SERVICES.EMIT_CERTIFICATE,
            args: {
                id,
                modelid,
                lang,
                userid,
                courseid,
            }
        };
        modal.destroy();
        Ajax.call([request])[0].done(function(response) {
            if (response.result === true) {
                reloadTable(modelid, courseid);
            } else {
                Notification.alert('Error', response.message, langStrings[2]);
            }
        }).fail(Notification.exception);
    });
    modal.getRoot().on(ModalEvents.hidden, () => {
        modal.destroy();
    });
    modal.show();
};
const reloadTable = (modelid, courseid) => {
    modelid = parseInt(modelid);
    courseid = parseInt(courseid);
    Templates.render(TEMPLATES.LOADING, {visible: true}).done(function(html) {
        let identifier = jQuery(REGION.ROOT);
        identifier.append(html);
        let request = {
            methodname: SERVICES.GET_TABLE_DATA,
            args: {modelid, courseid}
        };
        Ajax.call([request])[0].done(function(data) {
            Templates.render(TEMPLATES.TABLE, data).then((html, js) => {
                identifier.html(html);
                Templates.runTemplateJS(js);
            }).fail(Notification.exception);
        }).fail((error) => {
            Notification.alert(error);
        });
    });
};
export const init = () => {
    CertificateManagement();
};