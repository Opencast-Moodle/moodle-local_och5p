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
 * API Manager for och5p
 *
 * @package    local_och5p
 * @copyright  2021 Farbod Zamani Boroujeni, ELAN e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_och5p\local;

use moodle_exception;
use dml_exception;

defined('MOODLE_INTERNAL') || die();

/**
 * API Manager for och5p
 *
 * @package    local_och5p
 * @copyright  2021 Farbod Zamani Boroujeni, ELAN e.V.
 * @author     Farbod Zamani Boroujeni <zamani@elan-ev.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class api_manager extends \curl {
    /** @var string the api username */
    private $username;
    /** @var string the api password */
    private $password;
    /** @var int the curl timeout in seconds */
    private $timeout;
    /** @var string the api baseurl */
    private $baseurl;

    /**
     * Constructor of the OCH5P API Manager.
     * @param int $instanceid Opencast instance id
     * @param array $settings additional curl settings.
     * @param array $customconfigs custom api config.
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function __construct($settings = array(), $customconfigs = array()) {
        parent::__construct($settings);

        $this->baseurl = array_key_exists('apiurl', $customconfigs) ?
            $customconfigs['apiurl'] : get_config('tool_opencast', 'apiurl');

        $this->username = array_key_exists('apiusername', $customconfigs) ?
            $customconfigs['apiusername'] : get_config('tool_opencast', 'apiusername');

        $this->password = array_key_exists('apipassword', $customconfigs) ?
            $customconfigs['apipassword'] : get_config('tool_opencast', 'apipassword');

        $this->timeout = array_key_exists('apitimeout', $customconfigs) ?
            $customconfigs['apitimeout'] : get_config('tool_opencast', 'apitimeout');

        // The base url is a must and cannot be empty, so we check its existence for both scenarios.
        if (empty($this->baseurl)) {
            throw new moodle_exception('apiurlempty', 'local_och5p');
        }

        if (empty($this->username)) {
            throw new moodle_exception('apiusernameempty', 'local_och5p');
        }

        if (empty($this->password)) {
            throw new moodle_exception('apipasswordempty', 'local_och5p');
        }

        // If the admin omitted the protocol part, add the HTTPS protocol on-the-fly.
        if (!preg_match('/^https?:\/\//', $this->baseurl)) {
            $this->baseurl = 'https://'.$this->baseurl;
        }
    }

    /**
     * Get http status code
     *
     * @return int|boolean status code or false if not available.
     */
    public function get_http_code() {

        $info = $this->get_info();
        if (!isset($info['http_code'])) {
            return false;
        }
        return $info['http_code'];
    }

    /**
     * Get an digest authentication header.
     *
     * @return array of authentification headers
     * @throws \moodle_exception
     */
    private function get_authentication_header() {

        $options = array('CURLOPT_HEADER' => true);
        $this->setopt($options);

        $this->setopt('CURLOPT_CONNECTTIMEOUT', $this->timeout);

        $basicauth = base64_encode($this->username . ":" . $this->password);

        $header = array();

        $header[] = sprintf(
            'Authorization: Basic %s', $basicauth
        );

        return $header;
    }

    /**
     * Do a GET call to opencast API.
     *
     * @param string $resource path of the resource.
     * @return string JSON String of result.
     * @throws \moodle_exception
     */
    public function oc_get($resource) {
        $url = $this->baseurl . $resource;

        $this->resetHeader();
        $header = $this->get_authentication_header();
        $header[] = 'Content-Type: application/json';
        $this->setHeader($header);
        $this->setopt(array('CURLOPT_HEADER' => false));

        return $this->get($url);
    }
}