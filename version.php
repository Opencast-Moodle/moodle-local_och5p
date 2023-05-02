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
 * Plugin version and other meta-data are defined here.
 *
 * @package    local_och5p
 * @copyright  2020 Farbod Zamani Boroujeni, ELAN e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

// Defining this plug-in metadata.
$plugin->component = 'local_och5p';
$plugin->release = '3.0-r1';
$plugin->version = 2023042800;
$plugin->maturity = MATURITY_STABLE;
$plugin->requires = 2020061500; // Requires Moodle 3.9+.
$plugin->dependencies = array(
    'mod_hvp' => 2022012000, // This version seems to work with behat testing, so we set it here.
    'block_opencast' => 2023030100,
    'tool_opencast' => 2023030100
);
