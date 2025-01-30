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
 * Local och5p upgrade process.
 *
 * @package    local_och5p
 * @copyright  2021 Farbod Zamani Boroujeni, ELAN e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_och5p\local\theme_manager;
use local_och5p\local\opencast_manager;

/**
 * Execute och5p upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_och5p_upgrade($oldversion) {
    if ($oldversion < 2021111000) {
        // Due to changing the renderers flags, we remove the codes and extensions of the old version.
        theme_manager::cleaup_themes_extension();

        // Local och5p savepoint reached.
        upgrade_plugin_savepoint(true, 2021111000, 'local', 'och5p');
    }

    if ($oldversion < 2023042801) {

        // Remove old LTI configurations of the plugin and bring the tool_opencast LTI configuration in the game.
        $hasconfiguredlti = opencast_manager::is_lti_credentials_configured();

        // Set the uselti configuration to use the LTI configuration from tool_opencast.
        set_config('uselti', $hasconfiguredlti, 'local_och5p');

        // Remove the old LTI configurations.
        unset_config('lticonsumerkey', 'local_och5p');
        unset_config('lticonsumersecret', 'local_och5p');

        // Local och5p savepoint reached.
        upgrade_plugin_savepoint(true, 2023042801, 'local', 'och5p');
    }

    return true;
}
