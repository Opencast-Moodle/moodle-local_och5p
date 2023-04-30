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
 * Behat steps definitions for och5p.
 *
 * @package    local_och5p
 * @copyright  2023 Farbod Zamani Boroujeni, ELAN e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

use tool_opencast\seriesmapping;

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Behat steps definitions for och5p.
 *
 * @package    local_och5p
 * @copyright  2023 Farbod Zamani Boroujeni, ELAN e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_och5p extends behat_base {

    /**
     * adds a breakpoints
     * stops the execution until you hit enter in the console
     * @Then /^breakpoint in och5p/
     */
    public function breakpoint_in_och5p() {
        fwrite(STDOUT, "\033[s    \033[93m[Breakpoint] Press \033[1;93m[RETURN]\033[0;93m to continue...\033[0m");
        while (fgets(STDIN, 1024) == '') {
            continue;
        }
        fwrite(STDOUT, "\033[u");
        return;
    }

    /**
     * Upload a testvideo.
     * @Given /^I setup the opencast video block for the course with och5p$/
     */
    public function i_setup_the_opencast_video_block_for_the_course_with_och5p() {
        $courses = core_course_category::search_courses(array('search' => 'Course 1'));

        // When we are using stable.opencast.org, the series Blender Foundation Productions with id: ID-blender-foundation,
        // is by default avaialble. Therefore, and as for make things simpler, we use this series in our course.
        $mapping = new seriesmapping();
        $mapping->set('courseid', reset($courses)->id);
        $mapping->set('series', 'ID-blender-foundation');
        $mapping->set('isdefault', '1');
        $mapping->set('ocinstanceid', 1);
        $mapping->create();
    }

    /**
     * Update the hvp content type cache to get the latest libraries.
     * @Given /^I update the mod hvp content type cache$/
     */
    public function i_update_the_mod_hvp_content_type_cache() {
        $core = \mod_hvp\framework::instance();
        $core->updateContentTypeCache();
    }

    /**
     * Scrolling to an element in och5p
     * @Given /^I scroll to "(?P<element_selector_string>(?:[^"]|\\")*)" in och5p$/
     * @param string $elementselector Element we look for
     */
    public function i_scroll_to_in_och5p($elementselector) {
        $function = <<<JS
(function(){document.querySelector("$elementselector").scrollIntoView();})()
JS;
        try {
            $this->getSession()->executeScript($function);
        } catch (\moodle_exception $e ) {
            throw new \moodle_exception('behat_error_unabletofind_h5piframe', 'local_och5p');
        }
    }

    /**
     * Waits until h5p interactive video content is downloaded and installed.
     * @Given /^I wait until h5p interactive video content is installed in och5p$/
     */
    public function i_wait_until_h5p_interactive_video_content_is_installed_in_och5p() {
        $core = \mod_hvp\framework::instance();
        $libraries = $core->h5pF->loadLibraries();
        $isinstalled = false;
        do {
            if (array_key_exists('H5P.InteractiveVideo', $libraries) &&
                array_key_exists('H5PEditor.InteractiveVideo', $libraries)) {
                $isinstalled = true;
            } else {
                // In order to prevent performance issues, we load libraries every 5 seconds.
                sleep(5);
                $libraries = $core->h5pF->loadLibraries();
            }
        } while (!$isinstalled);
        // Here we wait one more seconds to let other dependencies be installed.
        $this->getSession()->wait(1000);
        return;
    }
}
