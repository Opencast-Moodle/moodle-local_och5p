### v2.0
- LTI authentication is possible.
- Opencast Instance for search calls (engage/presentation node) can be selected. (Deprecated in v2.1)
- More enhanced extension procedures.
- Clear the previous extension codes in themes files.

### v2.1
- Opencast Instance selection for search endpoint is removed.
- The search endpoint is retrieved from Opencast services endpoint.

### v2.1.1
- Improved readme and minor changes.

### v2.1.1-r1
- More rubost handler for dual opencast node (engage and main) LTI connectivity
- Improved readme description

### v3.0-r1
- Github Actions are included
- Behat testing is also included
- Compatibility for Moodle v4.0 and Moodle Opencast Plugins v4.0-r1
- Added support for privacy policy (null)
- Improved UI/UX by adding clearer labels for the dropdowns
- Remove support for Moodle 3.8

### v4.5-r1
- new moodle coding styles! e.g. array() to [], lang sorting, etc.
- core namespace changes: core_component to \core\component
- use LTI credentials from Opencast API tool plugin!
- Use the opencast API library instead of pure curl calls if necessary!
- Support for OC 16 more in the area of either eliminating the use of search endpoint or making it (backward) compatible.
- Upgrade behat test!
- Update GH moodle ci workflows!
- Swtich from opencast services endpoint to api base endpoint (#13)
