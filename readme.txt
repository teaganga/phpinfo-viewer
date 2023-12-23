=== Plugin Name ===
Contributors: teaganganda
Donate link: https://wordpressfoundation.org/donate/
Tags: phpinfo, logs
Requires at least: 5.1
Tested up to: 6.3.1
Stable tag: 1.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==
This plugin is intended to be used to debug WordPress installations. It displays the phpinfo output and provides a mechanism to read and display log files.

== Changelog ==

= 0.9 =
- Initial release of the plugin.
- Added functionality to display phpinfo() information within the WordPress admin area.
- Implemented file handling to read and display Apache and Xdebug log files.

= 1.0 =
- Replaced direct shell commands with PHP file handling functions for reading log files.
- Utilized WordPress Filesystem API for file operations to ensure compatibility across different hosting environments.
- Implemented nonce verification for AJAX requests to protect against CSRF attacks.
- Adjusted the CSS to initially hide the log output element and reveal it only after a log file is selected.
- Bug fixes and performance improvements.

== Upgrade Notice ==

= 1.0 =
- Recommended update for improved security and compatibility.

