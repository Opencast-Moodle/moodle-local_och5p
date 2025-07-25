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
use local_och5p\local\theme_manager;

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
    $selfextendedthemes = [];

    foreach (\core\component::get_plugin_list('theme') as $name => $dir) {
        $humanreadablename = ucfirst(str_replace('_', ' ', $name));
        if (theme_manager::is_self_extended_theme($name, $dir)) {
            // Skip self-extended themes.
            $selfextendedthemes[] = $humanreadablename;
            continue;
        }
        $availablethemes[$name] = $humanreadablename;
    }

    if (!empty($selfextendedthemes)) {
        $selfextendedthemesinfo = \html_writer::div(
            get_string('setting_extended_themes_selfextended', 'local_och5p', implode('</li><li>', $selfextendedthemes)),
            'box py-3 generalbox alert alert-info'
        );
        $infodescsetting = new admin_setting_description(
            'local_och5p/self_extended_themes',
            get_string('setting_extended_themes_selfextended_label', 'local_och5p'),
            $selfextendedthemesinfo
        );
        $settings->add($infodescsetting);
    }

    $extendedthemessetting = new admin_setting_configempty('local_och5p/extended_themes',
                get_string('setting_extended_themes', 'local_och5p'),
                get_string('setting_extended_themes_noavailable', 'local_och5p'));
    if (!empty($availablethemes)) {
        $extendedthemessetting = new admin_setting_configmultiselect(
            'local_och5p/extended_themes',
            get_string('setting_extended_themes', 'local_och5p'),
            get_string('setting_extended_themes_desc', 'local_och5p'),
            [],
            $availablethemes
        );

        $extendedthemessetting->set_updatedcallback('local_och5p_extend_themes');
    }
    $settings->add($extendedthemessetting);

    // LTI Module Section.
    $settings->add(
        new admin_setting_heading('local_och5p/lti_header',
            get_string('setting_lti_header', 'local_och5p'),
            get_string('setting_lti_header_desc', 'local_och5p')
        ));

    // Make sure the lti credentials are set before offering any settings!
    $hasconfiguredlti = opencast_manager::is_lti_credentials_configured();

    // Providing use lti option, when the consumer key and secret are configured in tool_opencast.
    if ($hasconfiguredlti) {
        $settings->add(new admin_setting_configcheckbox('local_och5p/uselti',
            get_string('setting_uselti', 'local_och5p'),
            get_string('setting_uselti_desc', 'local_och5p'), 0));
    } else {
        // Otherwise, we will inform the admin about this setting with extra info to configure this if needed.
        $path = '/admin/category.php?category=tool_opencast';
        $toolopencasturl = new \moodle_url($path);
        $link = \html_writer::link($toolopencasturl,
            get_string('setting_uselti_tool_opencast_link_name', 'local_och5p'), ['target' => '_blank']);
        $description = get_string('setting_uselti_nolti_desc', 'local_och5p', $link);
        $settings->add(
            new admin_setting_configempty('local_och5p/uselti',
                get_string('setting_uselti', 'local_och5p'),
                $description));
    }

    $ADMIN->add('localplugins', $settings);
}
