<?php
/*
Plugin Name: Gasque-forumlär
Plugin URI: http://f.kth.se
Description: Formulärsystem för anmälan till gasquer och andra event.
Version: 0.31
Author: Emil Wärnberg
Author URI: http://f.kth.se/~emilwa
License: Endast fysiksektionen
*/
require "adminpage/admin.php";
require "functions.php";
add_shortcode('gasque_form','gasquereg_form_shortcode');
add_shortcode('gasque_answers','gasquereg_answers_shortcode');
add_action('wp_enqueue_scripts', 'gasquereg_queue_CSS', 100);
?>