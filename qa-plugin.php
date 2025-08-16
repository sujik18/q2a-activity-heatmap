<?php
/*
    Plugin Name: Q2A Activity Heatmap
    Plugin URI: https://github.com/sujik18
    Plugin Description: Displays a Cal-Heatmap visualization of user activity.
    Plugin Version: 1.0
    Plugin Date: 2025-08-15
    Plugin Author: Sujith Kanakkassery
    Plugin Author URI: https://www.linkedin.com/in/sujith18/
    Plugin License: GPLv2
    Plugin Minimum Question2Answer Version: 1.8
    * This plugin uses Cal-Heatmap (https://cal-heatmap.com)
*/

if (!defined('QA_VERSION')) { header('Location: ../../'); exit; }
qa_register_plugin_module('widget', 'qa-activity-heatmap-widget.php', 'qa_activity_heatmap_widget', 'Activity Heatmap');
qa_register_plugin_phrases('qa-activity-heatmap-lang-default.php', 'activity_heatmap');
// qa_register_plugin_module('module', 'qa-activity-heatmap-admin.php', 'qa_activity_heatmap_admin', 'Activity Heatmap Admin');


// Enqueue CSS for plugin
qa_register_plugin_module('widget', 'qa-activity-heatmap-widget.php', 'qa_activity_heatmap_widget', 'Activity Heatmap Widget');
// qa_register_plugin_phrases('qa-activity-heatmap-lang-*.php', 'activity_heatmap');
// qa_register_plugin_layer('activity-heatmap-layer.php', 'Activity Heatmap Layer');
