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

let TEMPLATES = {
    LOADING: 'core/overlay_loading',
    ACTIVITY_TABLE: 'mod_certifygen/activity',
};
let ACTION = {
    EMIT_CERTIFICATE: '[data-action="emit-certificate"]',
    REVOKE_CERTIFICATE: '[data-action="revoke-certificate"]',
    DOWNLOAD_CERTIFICATE: '[data-action="download-certificate"]',
};
let SERVICES = {
    EMIT_CERTIFICATE: 'mod_certifygen_emitcertificate',
    GET_MYCERTIFICATE_TABLE_DATA: 'mod_certifygen_getmycertificatedata',
    REVOKE_CERTIFICATE: 'mod_certifygen_revokecertificate',
    DOWNLOAD_CERTIFICATE: 'mod_certifygen_downloadcertificate',
};
const certificateManagement = () => {
    jQuery(ACTION.EMIT_CERTIFICATE).on('click', emitCertificate);
    jQuery(ACTION.REVOKE_CERTIFICATE).on('click', revokeCertificate);
    jQuery(ACTION.DOWNLOAD_CERTIFICATE).on('click', downloadCertificate);
};

const downloadCertificate = async(event) => {
    let modelid = parseInt(event.currentTarget.getAttribute('data-modelid'));
    let code = event.currentTarget.getAttribute('data-code');
    let langstring = event.currentTarget.getAttribute('data-langstring');
    let id = parseInt(event.currentTarget.getAttribute('data-id'));
    let instanceid = parseInt(event.currentTarget.getAttribute('data-instanceid'));
    let courseid = parseInt(event.currentTarget.getAttribute('data-courseid'));

    // Modal estas seguro que quieres enviar el certiifcado?.
    const stringkeys = [
        {key: 'downloadcertificate_title', component: 'mod_certifygen'},
        {key: 'downloadcertificate_body', component: 'mod_certifygen', param: langstring},
        {key: 'confirm', component: 'mod_certifygen'},
        {key: 'downloadcertificate_error', component: 'mod_certifygen'}
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
            methodname: SERVICES.DOWNLOAD_CERTIFICATE,
            args: {
                id,
                instanceid,
                modelid,
                code,
                courseid,
            }
        };
        modal.destroy();
        Ajax.call([request])[0].done(function(response) {
            if (response.result === true) {
                jQuery(event.currentTarget).parent().parent().find(".code").html(response.codetag);
                window.open(response.url, "_blank");
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
const revokeCertificate = async(event) => {
    let issueid = event.currentTarget.getAttribute('data-issueid');
    let cmid = event.currentTarget.getAttribute('data-cmid');
    let userid = event.currentTarget.getAttribute('data-userid');
    let modelid = event.currentTarget.getAttribute('data-modelid');
    let courseid = event.currentTarget.getAttribute('data-courseid');
    let username = event.currentTarget.getAttribute('data-username');
    let lang = event.currentTarget.getAttribute('data-lang');
    // Modal estas seguro que quieres eliminar el certiifcado?.
    const stringkeys = [
        {key: 'revokecertificate_title', component: 'mod_certifygen'},
        {key: 'revokecertificate_body', component: 'mod_certifygen', param: username},
        {key: 'confirm', component: 'mod_certifygen'},
        {key: 'revokecertificate_error', component: 'mod_certifygen'}
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
            methodname: SERVICES.REVOKE_CERTIFICATE,
            args: {
                issueid,
                userid,
                modelid,
            }
        };
        modal.destroy();
        Ajax.call([request])[0].done(function(response) {
            if (response.result === true) {
                studentsReloadTable(modelid, courseid, cmid, lang);
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
const emitCertificate = async(event) => {
    let id = parseInt(event.currentTarget.getAttribute('data-id'));
    let modelid = parseInt(event.currentTarget.getAttribute('data-modelid'));
    let lang = event.currentTarget.getAttribute('data-lang');
    let langstring = event.currentTarget.getAttribute('data-langstring');
    let userid = parseInt(event.currentTarget.getAttribute('data-userid'));
    let courseid = parseInt(event.currentTarget.getAttribute('data-courseid'));
    let cmid = parseInt(event.currentTarget.getAttribute('data-cmid'));
    let instanceid = parseInt(event.currentTarget.getAttribute('data-instanceid'));

    // Modal estas seguro que quieres enviar el certiifcado?.
    const stringkeys = [
        {key: 'emitcertificate_title', component: 'mod_certifygen'},
        {key: 'emitcertificate_body', component: 'mod_certifygen', param: langstring},
        {key: 'confirm', component: 'mod_certifygen'},
        {key: 'emitcertificate_error', component: 'mod_certifygen'}
    ];
    Templates.render(TEMPLATES.LOADING, {visible: true}).done(async function(html) {
        let identifier = jQuery('[data-region="activity-view"]');
        identifier.append(html);
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
                    instanceid,
                    modelid,
                    lang,
                    userid,
                    courseid,
                }
            };
            Ajax.call([request])[0].done(function(response) {
                modal.destroy();
                if (response.result === true) {
                    studentsReloadTable(modelid, courseid, cmid, lang);
                } else {
                    Notification.alert('Error', response.message, langStrings[2]);
                }
                identifier.find('.overlay-icon-container').remove();
            }).fail(Notification.exception);
        });
        modal.getRoot().on(ModalEvents.hidden, () => {
            modal.destroy();
        });
        modal.getRoot().on(ModalEvents.cancel, () => {
            identifier.find('.overlay-icon-container').remove();
        });
        modal.show();
    });
};
const studentsReloadTable = (modelid, courseid, cmid, lang) => {
    modelid = parseInt(modelid);
    courseid = parseInt(courseid);
    cmid = parseInt(cmid);
    Templates.render(TEMPLATES.LOADING, {visible: true}).done(function(html) {
        let identifier = jQuery('[data-region="activity-view"]');
        identifier.append(html);
        let request = {
            methodname: SERVICES.GET_MYCERTIFICATE_TABLE_DATA,
            args: {modelid, courseid, cmid, lang}
        };
        Ajax.call([request])[0].done(function(data) {
            Templates.render(TEMPLATES.ACTIVITY_TABLE, data).then(function(html, js) {
                identifier.replaceWith(html);
                Templates.runTemplateJS(js);
            }).fail(Notification.exception);
        }).fail((error) => {
            identifier.find('.overlay-icon-container').remove();
            Notification.alert('Error', error.message, 'cancel');
        });
    });
};
export const init = () => {
    certificateManagement();
};