<?php
/*
    Plugin Name: Activity Heatmap
    Plugin URI: https://github.com/sujik18/q2a-activity-heatmap
    Plugin Description: Displays a Cal-Heatmap visualization of user activity.
    Plugin Version: 1.0
    Plugin Date: 2025-08-15
    Plugin Author: Sujith Kanakkassery
    Plugin Author URI: https://gateoverflow.in/user/SUJITH%27
    Plugin License: GPLv2
    Plugin Minimum Question2Answer Version: 1.8
*/

if (!defined('QA_VERSION')) exit;

qa_register_plugin_module(
    'widget',
    'qa-activity-heatmap-widget.php',
    'qa_activity_heatmap_widget',
    'Activity Heatmap Widget' 
);

qa_register_plugin_layer(
    'qa-activity-heatmap-layer.php', 
    'Activity Heatmap Layer'
);

