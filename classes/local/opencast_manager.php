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
 * Opencast Manager class contains all related functions to handle opencast related functionalities.
 *
 * @package    local_och5p
 * @copyright  2021 Farbod Zamani Boroujeni, ELAN e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_och5p\local;

use tool_opencast\exception\opencast_api_response_exception;
use tool_opencast\local\api;
use block_opencast\local\apibridge;
use tool_opencast\local\settings_api;
use oauth_helper;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/lti/locallib.php');
require_once($CFG->dirroot . '/lib/oauthlib.php');

/**
 * Opencast Manager class contains all related functions to handle opencast related functionalities.
 *
 * @package    local_och5p
 * @copyright  2021 Farbod Zamani Boroujeni, ELAN e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class opencast_manager {
    /**
     * Get videos avaialble in the course.
     *
     * @param int $courseid the id of the course.
     *
     * @return array the list of opencast course videos.
     */
    public static function get_course_videos($courseid) {
        // Get an instance of apibridge.
        $apibridge = apibridge::get_instance();

        // Initialize the course videos object.
        $coursevideos = new \stdClass();

        // Get series for the course.
        $courseseries = $apibridge->get_course_series($courseid);
        // Initialize the series videos array.
        $seriesvideos = [];
        $haserror = 0;

        foreach ($courseseries as $series) {
            // Get videos of each series.
            $videos = $apibridge->get_series_videos($series->series);

            // Merge videos into $seriesvideo, when there is something.
            if ($videos->error == 0 && !empty($videos->videos)) {

                // In order to process the video later on, we need to accept those video that has engage publication.
                $engagepublishedvideos = array_filter($videos->videos, function($video) {
                    return in_array('engage-player', $video->publication_status);
                });
                $seriesvideos = array_merge($seriesvideos, $engagepublishedvideos);
            }

            if ($videos->error != 0) {
                $haserror = 1;
            }
        }

        // Check if there is any video to initialize the $coursevideos relatively.
        $coursevideos->videos = !$haserror ? $seriesvideos : [];
        $coursevideos->error = $haserror;

        return $coursevideos;
    }

    /**
     * Get videos avaialble in the course.
     *
     * @param string $identifier the opencast event (video) identifier.
     *
     * @return array the list of consumable opencast events tracks.
     */
    public static function get_episode_tracks($identifier) {
        // Get tool_opencast api instance for search service.
        $api = self::get_opencast_search_service_api_instance();

        // Prepare params for search.
        $params = [
            'id' => $identifier,
        ];
        // Perform the search call.
        $response = $api->opencastapi->search->getEpisodes($params);
        $code = $response['code'];

        // Make sure everything is good.
        if ($code != 200) {
            throw new opencast_api_response_exception($response);
        }

        // Parse the response body to work with arrays, which is easier.
        $searchresult = json_decode(json_encode($response['body']), true);

        // Extract the tracks from mediapackage, for Opencast < 16.
        $tracks = (isset($searchresult['search-results']['result']) ?
            $searchresult['search-results']['result']['mediapackage']['media']['track'] :
            null);

        // Opencast >= 16 support.
        if (empty($tracks)) {
            $tracks = (isset($searchresult['result'][0]) ?
                $searchresult['result'][0]['mediapackage']['media']['track'] :
                null);
        }

        // If tracks does not exists, we return moodle_exception.
        if (!$tracks) {
            throw new moodle_exception('no_tracks_error', 'local_och5p');
        }

        $videotracks = [];
        // If there is video key inside the tracks array, that means it is a single track.
        if (array_key_exists('video', $tracks)) {
            if (strpos($tracks['mimetype'], 'video') !== false) {
                $videotracks[] = $tracks;
            }
        } else {
            // Otherwise, there are more than one track.
            // Extract videos from tracks.
            $videotracks = array_filter($tracks, function($track) {
                return strpos($track['mimetype'], 'video') !== false;
            });
        }

        // Initialise the sorted videos array.
        $sortedvideos = [];

        foreach ($videotracks as $videotrack) {

            // Double check if the track is 100% video track.
            if (strpos($videotrack['mimetype'], 'video') === false) {
                continue;
            }

            $quality = '';

            if (isset($videotrack['tags'])) {
                foreach ($videotrack['tags']['tag'] as $tag) {
                    if (strpos($tag, 'quality') !== false && empty($quality)) {
                        $quality = str_replace('-quality', '', $tag);
                    }
                }
            }

            if (empty($quality) && isset($videotrack['video']) && isset($videotrack['video']['resolution'])) {
                $quality = $videotrack['video']['resolution'];
            }

            $sortedvideos["{$videotrack['type']} ({$videotrack['mimetype']})"][$quality] =
                ["id" => $videotrack['id'], "url" => $videotrack['url']];
        }

        return $sortedvideos;
    }


    /**
     * Get api instance from tool_opencast for search service.
     *
     * @param boolean $returnbaseurl whether to return only the baseurl or the api object back
     *
     * @return tool_opencast\local\api opencast api instance.
     */
    public static function get_opencast_search_service_api_instance($returnbaseurl = false) {
        // Get api instance from tool_opencast.
        $api = api::get_instance();

        // Get the search service data.
        $response = $api->opencastapi->services->getServiceJSON('org.opencastproject.search');
        $code = $response['code'];

        // Make sure everything is good.
        if ($code != 200 && $code != 404) {
            throw new opencast_api_response_exception($response);
        }

        // If it could not find the search service, we return the the api instance.
        if ($code == 404) {
            return $api;
        }
        // Get the services object from the get call.
        $servicesobj = $response['body'];

        // Check if the get call returns any services, if not we return the default oc instance api.
        if (!property_exists($servicesobj, 'services') || !property_exists($servicesobj->services, 'service')
            || empty($servicesobj->services->service)) {
            return $api;
        }

        // Parse the service object to array, which is easier to use!
        $searchservice = (array) $servicesobj->services->service;

        // Check if the search service is active and online to make calls.
        if (!empty($searchservice) && $searchservice['active'] && $searchservice['online']) {
            // We are working with default opencast instance for now.
            $defaultocinstanceid = settings_api::get_default_ocinstance()->id;
            // Initialize the custom configs with the search service's host.
            $customconfigs = [
                'apiurl' => preg_replace(["/\/docs/"], [''], $searchservice['host']),
                'apiusername' => settings_api::get_apiusername($defaultocinstanceid),
                'apipassword' => settings_api::get_apipassword($defaultocinstanceid),
                'apitimeout' => settings_api::get_apitimeout($defaultocinstanceid),
                'apiconnecttimeout' => settings_api::get_apiconnecttimeout($defaultocinstanceid),
            ];
            // Create the tool_opencast api instance with search service's host url.
            $api = api::get_instance(null, [], $customconfigs);
        }
        // If only the baseurl is needed.
        if ($returnbaseurl) {
            return preg_replace(["/\/docs/"], [''], $searchservice['host']);
        }
        // Finally, we return the tool_opencast api instance to make search calls.
        return $api;
    }

    /**
     * Gets LTI parameters to perform the LTI authentication.
     * It only support the default opencast instance.
     *
     * @param int $courseid id of the course.
     * @return array lti parameters.
     */
    public static function get_lti_params($courseid) {
        // Check if it is configured to use LTI.
        $uselti = get_config('local_och5p', 'uselti');
        if (!$uselti) {
            return [];
        }
        $params = [];
        // Get the endpoint url of the default oc instance.
        $defaultocinstanceid = settings_api::get_default_ocinstance()->id;
        $mainltiendpoint = settings_api::get_apiurl($defaultocinstanceid);
        // Generate lti params for the main oc instance.
        $params['main'] = self::generate_lti_params($defaultocinstanceid, $courseid, $mainltiendpoint);
        // Get the endpoint url of the search node instance.
        $searchnodeltiendpoint = self::get_opencast_search_service_api_instance(true);

        // Check if the opencast uses different nodes.
        if ($mainltiendpoint != $searchnodeltiendpoint) {
            // Generate lti params for the search node.
            $params['search'] = self::generate_lti_params($defaultocinstanceid, $courseid, $searchnodeltiendpoint);
        }

        return $params;
    }

    /**
     * generate LTI parameters to perform the LTI authentication.
     *
     * @param int $ocinstanceid opencast instance id.
     * @param int $courseid id of the course.
     * @param string $endpoint the lti endpoint.
     * @return array lti parameters.
     */
    public static function generate_lti_params($ocinstanceid, $courseid, $endpoint) {
        global $CFG, $USER;

        // Get the course object.
        $course = get_course($courseid);

        // Get configured consumerkey and consumersecret.
        $lticredentials = self::get_lti_credentials($ocinstanceid);
        $consumerkey = $lticredentials['consumerkey'];
        $consumersecret = $lticredentials['consumersecret'];

        // Check if all requirements are correctly configured.
        if (empty($consumerkey) || empty($consumersecret) || empty($endpoint)) {
            throw new moodle_exception('no_lti_config_error', 'local_och5p');
        }

        // Validate the url and add lti endpoint to make the call.
        if (strpos($endpoint, 'http') !== 0) {
            $endpoint = 'http://' . $endpoint;
        }
        $endpoint .= '/lti';

        $helper = new oauth_helper(['oauth_consumer_key'    => $consumerkey,
                                        'oauth_consumer_secret' => $consumersecret]);

        // Set all necessary parameters.
        $params = [];
        $params['oauth_version'] = '1.0';
        $params['oauth_nonce'] = $helper->get_nonce();
        $params['oauth_timestamp'] = $helper->get_timestamp();
        $params['oauth_consumer_key'] = $consumerkey;

        $params['context_id'] = $course->id;
        $params['context_label'] = trim($course->shortname);
        $params['context_title'] = trim($course->fullname);
        $params['resource_link_id'] = 'o' . random_int(1000, 9999) . '-' . random_int(1000, 9999);
        $params['resource_link_title'] = 'Opencast';
        $params['context_type'] = ($course->format == 'site') ? 'Group' : 'CourseSection';
        $params['launch_presentation_locale'] = current_language();
        $params['ext_lms'] = 'moodle-2';
        $params['tool_consumer_info_product_family_code'] = 'moodle';
        $params['tool_consumer_info_version'] = strval($CFG->version);
        $params['oauth_callback'] = 'about:blank';
        $params['lti_version'] = 'LTI-1p0';
        $params['lti_message_type'] = 'basic-lti-launch-request';
        $urlparts = parse_url($CFG->wwwroot);
        $params['tool_consumer_instance_guid'] = $urlparts['host'];
        $params['custom_tool'] = '/ltitools';

        // User data.
        $params['user_id'] = $USER->id;
        $params['lis_person_name_given'] = $USER->firstname;
        $params['lis_person_name_family'] = $USER->lastname;
        $params['lis_person_name_full'] = $USER->firstname . ' ' . $USER->lastname;
        $params['ext_user_username'] = $USER->username;
        $params['lis_person_contact_email_primary'] = $USER->email;
        $params['roles'] = lti_get_ims_role($USER, null, $course->id, false);

        if (!empty($CFG->mod_lti_institution_name)) {
            $params['tool_consumer_instance_name'] = trim(html_to_text($CFG->mod_lti_institution_name, 0));
        } else {
            $params['tool_consumer_instance_name'] = get_site()->shortname;
        }

        $params['launch_presentation_document_target'] = 'iframe';
        $params['oauth_signature_method'] = 'HMAC-SHA1';
        $params['oauth_signature'] = $helper->sign("POST", $endpoint, $params, $consumersecret . '&');

        // Additional params.
        $params['endpoint'] = $endpoint;
        return $params;
    }

    /**
     * Checks if LTI credentials are configured for a given Opencast instance.
     *
     * This function verifies whether both the LTI consumer key and consumer secret
     * are set for the specified Opencast instance.
     *
     * @param int $ocinstanceid The ID of the Opencast instance to check, default 0 falls back to default instance id.
     *
     * @return bool Returns true if both LTI consumer key and secret are configured,
     *              false otherwise.
     */
    public static function is_lti_credentials_configured(int $ocinstanceid = 0) {
        if (empty($ocinstanceid)) {
            $ocinstanceid = settings_api::get_default_ocinstance()->id;
        }
        $lticredentials = self::get_lti_credentials($ocinstanceid);
        return !empty($lticredentials['consumerkey']) && !empty($lticredentials['consumersecret']);
    }

    /**
     * Retrieves the LTI consumer key and consumer secret for a given Opencast instance ID.
     *
     * @param int $ocinstanceid The ID of the Opencast instance, default 0 falls back to default instance id.
     *
     * @return array An associative array containing the 'consumerkey' and 'consumersecret' for the given Opencast instance.
     *               If the credentials are not found, an empty array is returned.
     */
    public static function get_lti_credentials(int $ocinstanceid = 0) {
        if (empty($ocinstanceid)) {
            $ocinstanceid = settings_api::get_default_ocinstance()->id;
        }
        $lticonsumerkey = settings_api::get_lticonsumerkey($ocinstanceid);
        $lticonsumersecret = settings_api::get_lticonsumersecret($ocinstanceid);
        return ['consumerkey' => $lticonsumerkey, 'consumersecret' => $lticonsumersecret];
    }
}
