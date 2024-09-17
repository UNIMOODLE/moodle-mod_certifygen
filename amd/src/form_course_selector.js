// This file is part of Moodle - https://moodle.org/
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
 * Provides the required functionality for an autocomplete element to select courses.
 *
 * @module    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import jQuery from 'jquery';
import Ajax from 'core/ajax';
import {render as renderTemplate} from 'core/templates';
import {get_string as getString} from 'core/str';
/**
 * Load the list of courses matching the query and render the selector labels for them.
 *
 * @param {String} selector The selector of the auto complete element.
 * @param {String} query The query string.
 * @param {Function} callback A callback function receiving an array of results.
 * @param {Function} failure A function to call in case of failure, receiving the error message.
 */
export async function transport(selector, query, callback, failure) {

    if (query.length < 1) {
        try {
            const msg = await getString('writealmost3characters', 'mod_certifygen');
            callback(msg);
        } catch (e) {
            failure(e);
        }
    } else {
        const request = {
            methodname: 'mod_certifygen_searchcourse',
            args: {
                query: query
            }
        };

        try {
            const response = await Ajax.call([request])[0];

            if (response.overflow) {
                const msg = await getString('toomanycoursestoshow', 'mod_certifygen', '>' + response.maxusersperpage);
                callback(msg);
            } else {
                jQuery('.modal-dialog-scrollable .modal-body').css('overflow-y', 'unset');
                let labels = [];
                response.list.forEach(course => {
                    labels.push(renderTemplate('mod_certifygen/form_category_selector_suggestion', course));
                });
                labels = await Promise.all(labels);

                response.list.forEach((course, index) => {
                    course.label = labels[index];
                });

                callback(response.list);
            }
        } catch (e) {
            failure(e);
        }
    }

}

/**
 * Process the results for auto complete elements.
 *
 * @param {String} selector The selector of the auto complete element.
 * @param {Array} results An array or results returned by {@see transport()}.
 * @return {Array} New array of the selector options.
 */
export function processResults(selector, results) {

    if (!Array.isArray(results)) {
        return results;
    } else {
        return results.map(result => ({value: result.id, label: result.label}));
    }
}
