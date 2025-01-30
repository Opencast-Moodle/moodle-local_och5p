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
 * Plugin administration pages are defined here.
 *
 * @package    local_och5p
 * @copyright  2020 Farbod Zamani Boroujeni, ELAN e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_och5p\local\opencast_manager;

defined('MOODLE_INTERNAL') || die;

global $ADMIN, $CFG;

if ($hassiteconfig) {

    require_once($CFG->dirroot.'/local/och5p/lib.php');

    $settings = new admin_settingpage( 'local_och5p_settings', get_string('pluginname', 'local_och5p'));

    // Themes Section.
    $settings->add(
        new admin_setting_heading('local_och5p/extended_themes_header',
            get_string('setting_extended_themes_header', 'local_och5p'),
        ''));

    $availablethemes = [];

    foreach (\core\component::get_plugin_list('theme') as $name => $dir) {
        $availablethemes[$name] = ucfirst(str_replace('_', ' ', $name));
    }

    $extendedthemessetting = new admin_setting_configmultiselect(
        'local_och5p/extended_themes',
        get_string('setting_extended_themes', 'local_och5p'),
        get_string('setting_extended_themes_desc', 'local_och5p'),
        [],
        $availablethemes
    );

    $extendedthemessetting->set_updatedcallback('local_och5p_extend_themes');
    $settings->add($extendedthemessetting);

    // LTI Module Section.
    $settings->add(
        new admin_setting_heading('local_och5p/lti_header',
            get_string('setting_lti_header', 'local_och5p'),
            get_string('setting_lti_header_desc', 'local_och5p')
        ));

    $lticonsumerkeysetting = new admin_setting_configtext('local_och5p/lticonsumerkey',
        get_string('setting_lti_consumerkey', 'local_och5p'),
        get_string('setting_lti_consumerkey_desc', 'local_och5p'), '');
    $settings->add($lticonsumerkeysetting);

    $lticonsumersecretsetting = new admin_setting_configpasswordunmask('local_och5p/lticonsumersecret',
        get_string('setting_lti_consumersecret', 'local_och5p'),
        get_string('setting_lti_consumersecret_desc', 'local_och5p'), '');
    $settings->add($lticonsumersecretsetting);

    $ADMIN->add('localplugins', $settings);
}
