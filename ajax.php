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
 * Ajax API calls to hanlde the actions respectively.
 *
 * @package    local_och5p
 * @copyright  2020 Farbod Zamani Boroujeni, ELAN e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_och5p\local\video_manager;
use local_och5p\local\opencast_manager;
use mod_hvp\editor_ajax;
use moodle_exception;
use context_course;

define('AJAX_SCRIPT', true);
require(__DIR__ . '/../../config.php');

require_login();

$action = required_param('action', PARAM_TEXT);
$courseid = optional_param('courseid', 0, PARAM_INT);
$contentid = optional_param('contentid', 0, PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);
$contextid = optional_param('contextId', 0, PARAM_INT);

$course = null;
$coursecontext = null;

if ($contextid) {
    list($coursecontext, $course, $cm) = get_context_info_array($contextid);
}

if ($id) {
    $cm = get_coursemodule_from_id('hvp', $id);
    $course = get_course($cm->course);
    $courseid = $course->id;
}

if (!$courseid && $contentid) {
    global $DB;
    $hvp = $DB->get_record('hvp', array('id' => $contentid));
    $courseid = $hvp->course;
    $course = get_course($courseid);
}

if (empty($coursecontext) && $courseid) {
    $coursecontext = context_course::instance($courseid);
}

if (empty($course) && $courseid) {
    $course = get_course($courseid);
}

header('Cache-Control: no-cache');
header('Content-Type: application/json; charset=utf-8');

// Validate token.
try {
    $editorajaxinterface = new editor_ajax();
    if ($action != 'ltiParams' &&
        !$editorajaxinterface->validateEditorToken(required_param('token', PARAM_RAW))) {
        print json_encode(['error' => get_string('invalidtoken_error', 'local_och5p')]);
        die;
    }
} catch (moodle_exception $e ) {
    print json_encode(['error' => $e->getMessage()]);
    die;
}

$data = array();

switch ($action) {
    case 'courseVideos':
        try {
            $data['result'] = video_manager::prepare_course_videos($course->id);
        } catch (moodle_exception $e) {
            $data['error'] = $e->getMessage();
        }
        break;
    case 'videoQualities':
        try {
            $identifier = required_param('identifier', PARAM_TEXT);
            $data['result'] = video_manager::get_video_flavors_with_qualities($identifier);
        } catch (moodle_exception $e) {
            $data['error'] = $e->getMessage();
        }
        break;
    case 'courseList':
        try {
            $data['result'] = video_manager::get_course_lists();
        } catch (moodle_exception $e) {
            $data['error'] = $e->getMessage();
        }
        break;
    case 'ltiParams':
        try {
            $data['result'] = opencast_manager::get_lti_params($course->id);
        } catch (moodle_exception $e) {
            $data['error'] = $e->getMessage();
        }
        break;
    case 'loadStrings':
        try {
            $data['result'] = video_manager::get_ui_strings();
        } catch (moodle_exception $e) {
            $data['error'] = $e->getMessage();
        }
        break;
    default:
        $data['error'] = get_string('no_action_error', 'local_och5p');
        break;
}
print json_encode($data);
