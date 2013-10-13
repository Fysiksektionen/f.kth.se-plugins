<?php
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
function gasquereg_install_db() {
	global $wpdb;
	dbDelta("CREATE TABLE ".$wpdb->prefix."gasquereg_forms (
	  id int(11) NOT NULL AUTO_INCREMENT,
	  title text NOT NULL,
	  createdBy int(11) NOT NULL,
	  createdTime timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  requireLogedIn tinyint(1) NOT NULL DEFAULT '0',
	  maxNumberReplies int(11) NOT NULL,
	  maxNumberRepliesPerUser int(11) NOT NULL,
	  allowEdit tinyint(1) NOT NULL DEFAULT '1',
	  category TEXT NOT NULL,
	  PRIMARY KEY  (id)
	);");
	dbDelta("CREATE TABLE ".$wpdb->prefix."gasquereg_form_elements (
	  id int(11) NOT NULL AUTO_INCREMENT,
	  form int(11) NOT NULL,
	  description text NOT NULL,
	  tag text NOT NULL,
	  type text NOT NULL,
	  is_required tinyint(1) NOT NULL DEFAULT '1',
	  demand_unique tinyint(1) NOT NULL DEFAULT '0',
	  deleted tinyint(1) NOT NULL DEFAULT '0',
	  order_in_form int(11) NOT NULL DEFAULT '0',
	  PRIMARY KEY  (id)
	);");
	dbDelta("CREATE TABLE ".$wpdb->prefix."gasquereg_answers (
	  id int(11) NOT NULL AUTO_INCREMENT,
	  form int(11) NOT NULL,
	  user int(11) NOT NULL,
	  submitted timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  PRIMARY KEY  (id)
	);");
	dbDelta("CREATE TABLE ".$wpdb->prefix."gasquereg_answer_elements (
	  id int(11) NOT NULL AUTO_INCREMENT,
	  answer int(11) NOT NULL,
	  element int(11) NOT NULL,
	  val text NOT NULL,
	  PRIMARY KEY  (id)
	);");
}
?>