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

/**
 *
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Given XML multilinguage text, return relevant text according to
// current language:
// - look for multilang blocks in the text.
// - if there exists texts in the currently active language, print them.
// - else, if there exists texts in the current parent language, print them.
// - else, print the first language in the text.
// Please note that English texts are not used as default anymore!
//
// This version is based on original multilang filter by Gaetan Frenoy,
// rewritten by Eloy and skodak.
//
// Following new syntax is not compatible with old one:
// <span lang="XX" class="multilang">one lang</span><span lang="YY" class="multilang">another language</span>.
global $CFG;
require_once($CFG->dirroot . '/filter/multilang/filter.php');
/**
 * Certifygen filter
 * @package    mod_certifygen
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class certifygenfilter extends filter_multilang {
    /** @var string $lang */
    private string $lang;

    /**
     * Construct
     * @param $context
     * @param array $localconfig
     * @param string $lang
     */
    public function __construct($context, array $localconfig, string $lang) {
        $this->lang = $lang;
        $this->context = $context;
        $this->localconfig = $localconfig;
    }

    /**
     * filter
     * @param $text
     * @param array $options
     * @return array|float|int|string|string[]
     */
    public function filter($text, array $options = []) {
        global $CFG;

        if (empty($text) || is_numeric($text)) {
            return $text;
        }

        if (empty($CFG->filter_multilang_force_old) && !empty($CFG->filter_multilang_converted)) {
            // New syntax.
            $search = '/(<span(\s+lang="[a-zA-Z0-9_-]+"|\s+class="multilang"){2}\s*>.*?<\/span>)(\s*<span(\s+lang="[a-zA-Z0-9_-]+"|\s+class="multilang"){2}\s*>.*?<\/span>)+/is';
        } else {
            // Old syntax.
            $search = '/(<(?:lang|span) lang="[a-zA-Z0-9_-]*".*?>.*?<\/(?:lang|span)>)(\s*<(?:lang|span) lang="[a-zA-Z0-9_-]*".*?>.*?<\/(?:lang|span)>)+/is';
        }
        $result = preg_replace_callback($search, [$this, 'process_match'], $text);

        if (is_null($result)) {
            return $text; // Error during regex processing (too many nested spans?).
        } else {
            return $result;
        }
    }

    /**
     * This is the callback used by the preg_replace_callback call above.
     *
     * @param array $langblock one of the matches from the regex match.
     * @return string the replacement string (one of the possible translations).
     */
    protected function process_match(array $langblock): string {
        $searchtosplit = '/<(?:lang|span)[^>]+lang="([a-zA-Z0-9_-]+)"[^>]*>(.*?)<\/(?:lang|span)>/is';
        if (!preg_match_all($searchtosplit, $langblock[0], $rawlanglist)) {
            // Skip malformed blocks.
            return $langblock[0];
        }

        $langlist = [];
        foreach ($rawlanglist[1] as $index => $lang) {
            $lang = str_replace('-', '_', strtolower($lang)); // Normalize languages.
            $langlist[$lang] = $rawlanglist[2][$index];
        }

        // Follow the stream of parent languages.
        if (empty($this->lang)) {
            $lang = current_language();
        } else {
            $lang = $this->lang;
        }

        do {
            if (isset($langlist[$lang])) {
                return $langlist[$lang];
            }
        } while ($lang = $this->get_parent_lang($lang));

        // If we don't find a match, default to the first provided translation.
        return array_shift($langlist);
    }

    /**
     * Puts some caching around get_parent_language().
     *
     * Also handle parent == 'en' in a way that works better for us.
     *
     * @param string $lang a Moodle language code, e.g. 'fr'.
     * @return string the parent language.
     */
    protected function get_parent_lang(string $lang): string {
        static $parentcache;
        if (!isset($parentcache)) {
            $parentcache = ['en' => ''];
        }
        if (!isset($parentcache[$lang])) {
            $parentcache[$lang] = get_parent_language($lang);
            // The standard get_parent_language method returns '' for parent == 'en'.
            // That is less helpful for us, so change it back.
            if ($parentcache[$lang] === '') {
                $parentcache[$lang] = 'en';
            }
        }
        return $parentcache[$lang];
    }
}
