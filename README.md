moodle-local_och5p
=====================
This local plugin helps to integrate Opencast Video into the Moodle H5P Plugin (<a href="https://moodle.org/plugins/mod_hvp">mod_hvp</a>).
The main purpose of this plugin is to make it possible for the teachers to select Opencast Video from within the H5P Editor when using H5P Interactive Videos feature.
In order to achieve this goal, it is necessary to customize Moodle H5P Plugin (mod_hvp), which is only possible through extending a theme in Moodle <a href="https://h5p.org/moodle-customization">Moodle Customization</a>.
This plugin is designed to overwrite the renderer.php and config.php files of the selected themes and append the necessary codes into these files. This design helps to adapt every installed themes instead of only extending a specific theme.
Using this integration now enables teachers to select opencast videos in a course, using a dropdown inside the H5P Interactive Videos' editor in a course. After selecting the opencast video, another dropdown will be shown to select different types of video flavor (Presenter/Presentation). By selecting the video flavor all available qualities of the video then will be inserted into H5P Editor videos list and the rest will be processed by H5P.

System requirements
------------------
1. Min. Moodle Version: (3.9 in v3.0-r1) or (3.8 < v3.0-r1)
2. Installed plugin:
   - versions (< v3.0-r1):
      - <a href="https://moodle.org/plugins/mod_hvp">mod_hvp</a> (Min. version: <a href="https://moodle.org/plugins/mod_hvp/1.22.3/24438">Interactive Content – H5P 1.22.3</a>)
      - <a href="https://github.com/Opencast-Moodle/moodle-block_opencast">block_opencast</a> (Min. version: <a href="https://github.com/Opencast-Moodle/moodle-block_opencast/releases/tag/v3.11-r1">v3.11-r1</a>)
   - versiion (> v3.0-r1):
      - <a href="https://moodle.org/plugins/mod_hvp">mod_hvp</a> (Min. version: <a href="https://moodle.org/plugins/mod_hvp/1.22.4/25878">Interactive Content – H5P 1.22.4</a>)
      - <a href="https://github.com/Opencast-Moodle/moodle-tool_opencast">tool_opencast</a> (Min. version: <a href="https://github.com/Opencast-Moodle/moodle-tool_opencast/releases/tag/v4.0-r1">v4.0-r1</a>)
      - <a href="https://github.com/Opencast-Moodle/moodle-block_opencast">block_opencast</a> (Min. version: <a href="https://github.com/Opencast-Moodle/moodle-block_opencast/releases/tag/v4.0-r1">v4.0-r1</a>)
 
Opencast configuration for multiple nodes setups:
------------------
If you use a constellation of opencast nodes, one for admin and another for presentation (i.e. engage node) it is <b>important</b> that your moodle user account in opentcast has the role that makes the services endpoint available. It is by default (and based on experience) included the  "ROLE_GROUP_MH_DEFAULT_ORG_SYSTEM_ADMINS" role.

Prerequisites
------------------
* Proper write permission on themes directories for the server user (e.g. "www-data" Apache User)

Features
------------------
* Extend/Remove extensions of several themes at once
* Display Opencast videos of the course inside H5P Interactive Videos Editor
* Extract and display Opencast video flavors inside H5P Interactive Videos Editor
* Extract and use different quality of the Opencast video inside H5P Interactive Videos
* Opencast LTI authentication (v2.0.0)
* Engage/Presentation node for search endpoint is retreived from Opencast services endpoint. (v2.1)

Settings
------------------
* In Admin Settings Page, there is the possibility to select multiple available themes to extend.
* Unselecting a theme will remove the extension changes.
* Only videos which are published to opencast engage player, can be displayed and process, because media index of the event must be available.
* LTI credential can be configured if the "Secure Static Files" in opencast setting is enabled.

Uninstall
------------------
In case the plugin triggers the uninstall event, all changes to the extended themes will be removed!
