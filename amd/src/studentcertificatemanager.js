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
// import Templates from 'core/templates';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Notification from 'core/notification';

let REGION = {
    ROOT: '',
};
// let TEMPLATES = {
//     LOADING: 'core/overlay_loading',
//     STUDENT_TABLE: 'mod_certifygen/student',
// };
let ACTION = {
    DOWNLOAD_CERTIFICATE: '[data-action="download-certificate"]',
};
let SERVICES = {
    DOWNLOAD_CERTIFICATE: 'mod_certifygen_downloadcertificate',
    GET_MYCERTIFICATE_TABLE_DATA: 'mod_certifygen_getmycertificatedata',
};
const certificateManagement = () => {
    jQuery(ACTION.DOWNLOAD_CERTIFICATE).on('click', downloadCertificate);
};
const downloadCertificate = async(event) => {
    let modelid = parseInt(event.currentTarget.getAttribute('data-modelid'));
    let lang = event.currentTarget.getAttribute('data-lang');
    let langstring = event.currentTarget.getAttribute('data-langstring');
    let userid = parseInt(event.currentTarget.getAttribute('data-userid'));
    let courseid = parseInt(event.currentTarget.getAttribute('data-courseid'));
    // let cmid = parseInt(event.currentTarget.getAttribute('data-cmid'));

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
                modelid,
                lang,
                userid,
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
export const init = (root) => {
    REGION.ROOT = root;
    certificateManagement();
};