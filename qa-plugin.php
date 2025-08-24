<?php

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

