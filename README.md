moodle-local_och5p
=====================
The **moodle-local_och5p** plugin extends the [H5P plugin for Moodle](https://moodle.org/plugins/mod_hvp) (**mod_hvp**) by integrating support for [Opencast](https://opencast.org/) videos within the H5P Interactive Video content type.

This plugin allows teachers to select Opencast videos directly from the H5P editor when creating or editing Interactive Video content. Once a video is selected, users can choose the desired video flavor (e.g., *Presenter* or *Presentation*). The corresponding video qualities then appear automatically in the H5P editor for selection and are further handled by H5P.

Due to the limited extensibility of the `mod_hvp` plugin, this functionality is implemented by injecting custom code into theme files, specifically `renderer.php` and `config.php`. The plugin dynamically modifies these files across all installed themes (by selection), which avoids the need to create and maintain a separate theme. More information about the customization process can be found in the [H5P documentation](https://h5p.org/moodle-customization).

System requirements
------------------
1. Min. Moodle Version: (3.9 in v3.0-r1) or (3.8 < v3.0-r1)
2. Installed plugin:
   - versions (< v3.0-r1):
      - <a href="https://moodle.org/plugins/mod_hvp">mod_hvp</a> (Min. version: <a href="https://moodle.org/plugins/mod_hvp/1.22.3/24438">Interactive Content – H5P 1.22.3</a>)
      - <a href="https://github.com/Opencast-Moodle/moodle-block_opencast">block_opencast</a> (Min. version: <a href="https://github.com/Opencast-Moodle/moodle-block_opencast/releases/tag/v3.11-r1">v3.11-r1</a>)
   - version (> v3.0-r1 & < v5.x):
      - <a href="https://moodle.org/plugins/mod_hvp">mod_hvp</a> (Min. version: <a href="https://moodle.org/plugins/mod_hvp/1.22.4/25878">Interactive Content – H5P 1.22.4</a>)
      - <a href="https://github.com/Opencast-Moodle/moodle-tool_opencast">tool_opencast</a> (Min. version: <a href="https://github.com/Opencast-Moodle/moodle-tool_opencast/releases/tag/v4.0-r1">v4.0-r1</a>)
      - <a href="https://github.com/Opencast-Moodle/moodle-block_opencast">block_opencast</a> (Min. version: <a href="https://github.com/Opencast-Moodle/moodle-block_opencast/releases/tag/v4.0-r1">v4.0-r1</a>)

   - version (v5.x):
     - Interactive Content – H5P 1.27.1 or newer.
      - <a href="https://github.com/Opencast-Moodle/moodle-tool_opencast">tool_opencast</a> (Min. version: <a href="https://github.com/Opencast-Moodle/moodle-tool_opencast/releases/tag/v5.0-r4">v5.0-r4</a>)

Opencast configuration for multiple nodes setups:
------------------
If you use a constellation of opencast nodes, one for admin and another for presentation (i.e. engage node) it is <b>important</b> that your moodle user account in opentcast has the role that makes the services endpoint available. It is by default (and based on experience) included in the "ROLE_GROUP_MH_DEFAULT_ORG_SYSTEM_ADMINS" role.

**NOTE**: In version **v4.5-r1**, we switched from using the Opencast services endpoint to the API base endpoint to retrieve the Engage node URL. As a result, the role `ROLE_GROUP_MH_DEFAULT_ORG_SYSTEM_ADMINS` **no longer has any effect** for this plugin. Instead, you need to **assign** the role `ROLE_UI_EVENTS_EMBEDDING_CODE_VIEW` to the Opencast API User.

Prerequisites
------------------
* Proper write permission on themes directories for the server user (e.g. "www-data" Apache User)

Features
------------------
* Extend several themes at once via Moodle's multiselect feature by holding the Ctrl key.
* Remove extensions applied to several themes at once via Moodle's multiselect feature by holding the Ctrl key.
* Display Opencast videos of the course inside H5P Interactive Videos Editor.
* Extract and display Opencast video flavors inside H5P Interactive Videos Editor.
* Extract and use different quality of the Opencast video inside H5P Interactive Videos.
* Opencast LTI authentication (v2.0.0)
* Engage/Presentation node for search endpoint is retreived from Opencast services endpoint. ([v2.1 - v3.x])
* The Engage/Presentation node for search endpoint is retrieved from the Opencast API base endpoint. (v4.5-r1)

How it works
------------------
* In the admin setting page, there is the possibility to select multiple available themes to extend.
* Deselecting a theme will remove the extension changes.
* Only videos which are published to opencast engage player, can be displayed and process, because media index of the event must be available.
* LTI credential can be configured if the "Secure Static Files" in opencast setting is enabled.

Important for admins to know:
------------------
* This plugin creates new files within the Moodle core installation.
* By extending a theme, the plugin attempts to add own code into the files of selected themes.
* By deselecting a theme, the plugin attempts to remove the (added) code from the files of selected themes.

How to revert the changes:
------------------
* Through the admin setting page, deselecting a theme will revert the changes.
* Uninstalling the plugin will also trigger the uninstallation event, by which all changes to the extended themes will be removed!

Revert changes manually:
------------------
It is possible to revert the changes manually, but it is not recommended doing so. However, the plugin only changes the files as follows:
* (rootdir) > themes > {your installed theme dir} > renderers.php
* (rootdir) > themes > {your installed theme dir} > config.php
Changes made by this plugin can be identified as a code block started with a comment containing "// Added by local_och5p plugin" and ends with a comment containing "// End of local_och5p plugin code block."

Repair the loss of changes on renderers.php:
----------------
In case the changes on renderers.php or even the file itself is gone, the plugin will repeat the changes by itself which can be done simply via admin setting page:

1. Deselect the defected theme, to let the plugin know that the changes should not be there anymore!
2. Save changes.
3. Select the defected theme again, to repeat the changes.
4. Save changes.

Settings
------------------
* In Admin Settings Page, there is the possibility to select multiple available themes to extend.
* Unselecting a theme will remove the extension changes.
* Only videos which are published to opencast engage player, can be displayed and process, because media index of the event must be available.
* LTI credential can be configured if the "Secure Static Files" in opencast setting is enabled.

Uninstall
------------------
In case the plugin triggers the uninstall event, all changes to the extended themes will be removed!

Common issues
------------------
* If using LTI authentication with Secure Static Files option in Opencast, and the selected Opencast video is not displayed with error showing "Video format not supported", you might need to try Partitioning the cookies in your Opencast Nginx/Apache server.
