<?php
/*
Plugin Name: phpinfo Viewer
Description: A simple plugin to display phpinfo() in the WP admin area.
Version: 0.9
Author: Teagan G.
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/


// Add a new menu item under Tools
function phpinfo_viewer_panel_menu() {

    // https://developer.wordpress.org/reference/functions/add_management_page/
    add_management_page(
        'phpinfo viewer', // $page_title - The text to be displayed in the title tags of the page when the menu is selected.
        'phpinfo viewer', // $menu_title - The text to be used for the menu.
        'manage_options', // $capability - The capability required for this menu to be displayed to the user.
        'phpinfo-viewer', // $menu_slug  - The slug name to refer to this menu by (should be unique for this menu).
        'phpinfo_viewer_panel_display' // $callback (Optional, default '') - The function to be called to output the content for this page.
        // $position (int Optional, default null) - The position in the menu order this item should appear.
    );
}
add_action('admin_menu', 'phpinfo_viewer_panel_menu');


function phpinfo_viewer_get_log_files($directory) {
    $files = glob($directory . '/*.{log,txt}', GLOB_BRACE);
    return is_array($files) ? $files : array();
}

function phpinfo_viewer_file_availability_message($logPath) {

    if (PHP_OS_FAMILY !== 'Windows' && is_link($logPath)) {
        $target = readlink($logPath);
        if (in_array($target, ['/dev/stdout', '/dev/stderr'])) {
            return "Log is a symbolic link pointing to {$target}. It may not be available. It usually happens when running inside a docker container and the log output is mapped to docker output console";
        }
    }    

    if (!file_exists($logPath)) {
        return "Log file does not exist.";
    }
    
    if (!is_readable($logPath)) {
        return "Log file is not readable.";
    }



    return false;
}

// Display the phpinfo content
function phpinfo_viewer_panel_display() {
    ob_start();
    phpinfo();
    $phpinfo = ob_get_contents();
    ob_end_clean();

    // Extract log locations
    preg_match('/APACHE_LOG_DIR<\/td><td class="v">([^<]+)<\/td>/', $phpinfo, $apache_matches);
    preg_match('/xdebug\.log<\/td><td class="v">([^<]+)<\/td>/', $phpinfo, $xdebug_matches);

    $apache_log_location = $apache_matches[1] ?? '/var/log/apache2/error.log'; // default apache log location
    $xdebug_log_location = $xdebug_matches[1] ?? '/tmp/xdebug.log'; // default xdebug log location

    echo "<div class='wrap'><h1>PHPInfo</h1>";

    $apache_log_location = $apache_matches[1] ?? '/var/log/apache2/error.log'; // default apache log location
    $xdebug_log_location = $xdebug_matches[1] ?? '/tmp/xdebug.log'; // default xdebug log location


    // Get logs from specified locations
    $apache_logs = phpinfo_viewer_get_log_files(dirname($apache_log_location));
    $xdebug_logs = phpinfo_viewer_get_log_files(dirname($xdebug_log_location));

    // Display buttons for Apache logs
    foreach ($apache_logs as $log) {
        echo "<button onclick=\"fetchLog('{$log}')\">Show " . basename($log) . " Logs</button> ";
    }

    // Display buttons for Xdebug logs
    foreach ($xdebug_logs as $log) {
        echo "<button onclick=\"fetchLog('{$log}')\">Show " . basename($log) . " Logs</button> ";
    }    

    // Display a div for the logs
    echo "<pre id='logOutput' style='overflow:auto;height:600px;margin-top:10px;'></pre>";

    // JavaScript function to fetch and display logs
    echo "<script>
        function fetchLog(logPath) {
            fetch('?page=phpinfo-viewer&logPath=' + encodeURIComponent(logPath))
            .then(response => response.text())
            .then(data => {
                document.getElementById('logOutput').textContent = data;
            });
        }
    </script>";


    // Only display the phpinfo if no log is requested
    if (!isset($_GET['logPath'])) {
        $phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);
        echo "<div>{$phpinfo}</div>";
    }

    echo "</div>";
}

// Use an action hook to handle log fetching
add_action('admin_init', 'phpinfo_viewer_fetch_log_data');
function phpinfo_viewer_fetch_log_data() {
    if (isset($_GET['page']) && $_GET['page'] === 'phpinfo-viewer' && current_user_can('manage_options') && isset($_GET['logPath'])) {
        $logPath = filter_input(INPUT_GET, 'logPath', FILTER_SANITIZE_STRING);
        
        // Use the utility function to check the file availability
        $fileMessage = phpinfo_viewer_file_availability_message($logPath);

        if ($fileMessage) {
            echo $fileMessage; // If there's a message from the function, print it
            exit;
        } else {
            $logData = shell_exec("tail -n 200 " . escapeshellarg($logPath));
            echo nl2br($logData);
            exit;
        }
    }
}
