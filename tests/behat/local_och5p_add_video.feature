@local @local_och5p
Feature: Add Opencast Video into H5P Activity Module via hvp plugin
  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                | idnumber |
      | teacher1 | Teacher   | 1        | teacher1@example.com | T1       |
      | student1 | Student   | 1        | student1@example.com | S1       |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | admin    | C1     | manager        |
    And the following config values are set as admin:
      | config                    | value                                                         | plugin          |
      | apiurl_1                  | https://stable.opencast.org                                   | tool_opencast   |
      | apiusername_1             | admin                                                         | tool_opencast   |
      | apipassword_1             | opencast                                                      | tool_opencast   |
      | ocinstances               | [{"id":1,"name":"Default","isvisible":true,"isdefault":true}] | tool_opencast   |
      | hub_is_enabled            | 1                                                             | mod_hvp         |
      | send_usage_statistics     | 1                                                             | mod_hvp         |
      | lticonsumerkey_1          | CONSUMERKEY                                                   | tool_opencast   |
      | lticonsumersecret_1       | CONSUMERSECRET                                                | tool_opencast   |
      | uselti                    | 1                                                             | local_och5p     |
    And I log in as "admin"
    And I setup the opencast video block for the course with och5p
    And I update the mod hvp content type cache
    And I run the scheduled task "\mod_hvp\task\look_for_updates"
    And I navigate to "Plugins > Local plugins > H5P Opencast Extension" in site administration
    And I set the following fields to these values:
      | Available themes to extend  | Boost           |
    And I press "Save changes"
    Then I should see "Changes saved"
    And I log out

  @javascript @_switch_iframe
  Scenario: Teacher should be able to add and edit Opencast Video in H5P Interactive Videos, student should be able to see the video
    # To have the list of libraries available, we need to log in as admin.
    Given I log in as "admin"
    # We need to increase the size of the window in order for the h5p iframe contents to be visible and in the view port, otherwise
    # it won't see some of the flags and texts to validate.
    And I change window size to "1366x968"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "Opencast Videos" block
    And I add a "Interactive Content" to section "1" using the activity chooser
    And I wait until the page is ready
    And I scroll to "iframe.h5p-editor-iframe" in och5p
    And I switch to "h5p-editor-iframe" class iframe
    Then I should see "Interactive Video"
    When I click on ".h5p-hub-content-type-list li#h5p-interactivevideo" "css_element"
    Then I wait "1" seconds
    When I click on ".h5p-hub-content-type-detail-button-bar button" "css_element"
    And I wait until h5p interactive video content is installed in och5p
    And I wait until "Interactive Video successfully installed!" "text" exists
    And I click on "Use" "button"
    And I switch to the main frame
    And I wait "2" seconds
    And I scroll to "iframe.h5p-editor-iframe" in och5p
    And I switch to "h5p-editor-iframe" class iframe
    And I click on ".shepherd-cancel-link" "css_element"
    And I set the field "Title" to "Test Opencast Video"
    And I scroll to "div.h5p-add-file" in och5p
    When I click on ".h5p-add-file[title='Add file']" "css_element"
    Then I should see "Opencast Videos"
    And I set the field "Select a video file" to "Spring"
    And I set the field "Select the video's flavor and quality" to "Presentation (mp4)"
    And I switch to the main frame
    When I click on "Save and display" "button"
    And I wait until the page is ready
    Then I should see "Test Opencast Video"
    And I switch to "h5p-iframe" class iframe
    And I should see "Interactive Video"
    And I switch to the main frame
    And I am on "Course 1" course homepage with editing mode on
    And I open "Test Opencast Video" actions menu
    And I choose "Edit settings" in the open action menu
    And I wait until the page is ready
    And I scroll to "iframe.h5p-editor-iframe" in och5p
    And I switch to "h5p-editor-iframe" class iframe
    And I set the field "Title" to "Test Opencast Video Edited"
    # A 2 seconds wait is needed here.
    And I wait "2" seconds
    And I scroll to ".h5p-av-row .h5p-remove" in och5p
    When I click on ".h5p-av-row .h5p-remove" "css_element"
    And I should see "Remove file"
    And I click on "Confirm" "button"
    When I click on ".h5p-add-file[title='Add file']" "css_element"
    Then I should see "Opencast Videos"
    And I set the field "Select a video file" to "Spring"
    And I set the field "Select the video's flavor and quality" to "Presentation (mp4)"
    And I switch to the main frame
    When I click on "Save and display" "button"
    And I wait until the page is ready
    # A 20 seconds wait is needed here.
    And I wait "20" seconds
    And I wait until ".h5p-iframe" "css_element" exists
    Then I should see "Test Opencast Video"
    And I switch to "h5p-iframe" class iframe
    And I should see "Interactive Video"
    And I switch to the main frame
    Then I log out
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    Then I should see "Test Opencast Video"
    When I click on "Test Opencast Video" "link"
    And I wait until the page is ready
    And I switch to "h5p-iframe" class iframe
    And I should see "Interactive Video"
