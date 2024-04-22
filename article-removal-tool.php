<?php
/*
Plugin Name: Article Removal Tool
Description: Plugin to unpublish old articles, remove associated media, and create redirects.
Version: 1.0
Author: Siphiwo Julayi
*/

defined('ABSPATH') || exit;

// Include the main plugin class file
require_once plugin_dir_path(__FILE__) . 'includes/class-article-removal-tool.php';

// Instantiate the main plugin class
if (class_exists('Article_Removal_Tool')) {
    $article_removal_tool = new Article_Removal_Tool();
}
