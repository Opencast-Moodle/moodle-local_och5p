<?php
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
 * Local och5p uninstall process.
 *
 * @package    local_och5p
 * @copyright  2020 Farbod Zamani Boroujeni, ELAN e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_och5p\local\theme_manager;

/**
 * Call the functions and methods during uninstall.
 */
function xmldb_local_och5p_uninstall() {

    $themes = core_component::get_plugin_list('theme');
    if (count($themes) > 0) {
        theme_manager::remove_themes_extension(array_keys($themes));
    }

    return true;
}
