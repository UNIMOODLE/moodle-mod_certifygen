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
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * certifygenpdf
 * @package    certifygenreport_basic
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace certifygenreport_basic;

defined('MOODLE_INTERNAL') || die();

global $CFG;
use pdf;
require_once($CFG->dirroot . '/lib/pdflib.php');

/**
 * certifygenpdf
 * @package    certifygenreport_basic
 * @copyright  2024 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class certifygenpdf extends pdf {
    /** @var string $footertext */
    private string $footertext;

    /**
     * Set footer text
     * @param $text
     * @return void
     */
    public function set_footer_text($text): void {
        $this->footertext = $text;
    }

    /**
     * Footer
     * @return void
     */
    public function footer() {
        $cury = $this->y;
        $this->setTextColorArray($this->footer_text_color);
        // Set style for cell border.
        $linewidth = (0.85 / $this->k);
        $this->setLineStyle(['width' => $linewidth, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0,
            'color' => $this->footer_line_color]);
        // Print document barcode.
        $barcode = $this->getBarcode();
        if (!empty($barcode)) {
            $this->Ln($linewidth);
            $barcodewidth = round(($this->w - $this->original_lMargin - $this->original_rMargin) / 3);
            $style = [
                'position' => $this->rtl ? 'R' : 'L',
                'align' => $this->rtl ? 'R' : 'L',
                'stretch' => false,
                'fitwidth' => true,
                'cellfitalign' => '',
                'border' => false,
                'padding' => 0,
                'fgcolor' => [0, 0, 0],
                'bgcolor' => false,
                'text' => false,
            ];
            $this->write1DBarcode(
                $barcode,
                'C128',
                '',
                $cury + $linewidth,
                '',
                (($this->footer_margin / 3) - $linewidth),
                0.3,
                $style,
                ''
            );
        }
        $wpage = isset($this->l['w_page']) ? $this->l['w_page'] . ' ' : '';
        if (empty($this->pagegroups)) {
            $pagenumtxt = $wpage . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages();
        } else {
            $pagenumtxt = $wpage . $this->getPageNumGroupAlias() . ' / ' . $this->getPageGroupAlias();
        }
        $this->setY($cury);
        if (!empty($this->footertext)) {
            $this->SetY(-25);
            // Set font.
            $this->SetFont('helvetica', 'I', 8);
            // Page number.
            $this->Cell(
                0,
                10,
                $this->footertext,
                0,
                false,
                'C',
                0,
                '',
                0,
                false,
                'T',
                'M'
            );
        }

        // Print page number.
        if ($this->getRTL()) {
            $this->setX($this->original_rMargin);
            $this->Cell(0, 0, $pagenumtxt, 'T', 0, 'L');
        } else {
            $this->setX($this->original_lMargin);
            $this->Cell(0, 0, $this->getAliasRightShift() . $pagenumtxt, 'T', 0, 'R');
        }
    }
}
