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
 *
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace mod_certifygen\completion;

defined('MOODLE_INTERNAL') || die();

use core_completion\activity_custom_completion;
use mod_certifygen\persistents\certifygen_validations;
global $CFG;
require_once($CFG->dirroot . '/lib/completionlib.php');
/**
 * Activity custom completion subclass for the certifygen activity.
 *
 * Class for defining mod_certifygen's custom completion rules and fetching the completion statuses
 * of the custom completion rules for a given certifygen instance and a user.
 *
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 class custom_completion extends activity_custom_completion {
     /**
      * Fetches the completion state for a given completion rule.
      *
      * @param string $rule The completion rule.
      * @return int The completion state.
      * @throws \coding_exception
      * @throws \moodle_exception
      */
    public function get_state(string $rule): int {
        $this->validate_rule($rule);

        $userid = $this->userid;
        $cm = $this->cm;

        // Fetch the completion status of the custom completion rule.
        $status = COMPLETION_INCOMPLETE;
        if ($rule === 'completiondownload') {
            // Get certifygen details
            $certifygen = \mod_certifygen\persistents\certifygen::get_record(['id' => $cm->instance]);
            if ((int)$certifygen->get('completiondownload')) {
                $cvalidations = certifygen_validations::count_records(
                    [
                        'userid' => $userid,
                        'certifygenid' => (int)$cm->instance,
                        'isdownloaded' => 1,
                    ]
                );
                $status = ($cvalidations > 0) ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
            }
        }
        return $status;
    }

    /**
     * Fetch the list of custom completion rules that this module defines.
     *
     * @return array
     */
    public static function get_defined_custom_rules(): array {
        return ['completiondownload'];
    }

     /**
      * Returns an associative array of the descriptions of custom completion rules.
      *
      * @return array
      * @throws \coding_exception
      */
    public function get_custom_rule_descriptions(): array {
        return [
            'completiondownload' => get_string('completiondownloaddesc', 'mod_certifygen')
        ];
    }

    /**
     * Returns an array of all completion rules, in the order they should be displayed to users.
     *
     * @return array
     */
    public function get_sort_order(): array {
        return [
            'completionview',
            'completiondownload',
        ];
    }
}
