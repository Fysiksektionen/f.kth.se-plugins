<?php
/*
Plugin Name: Role Scoping for NextGen Gallery
Plugin URI: http://agapetry.net/news/rs-config-ngg/
Description: Enables convenient and flexible administration of NextGen Gallery edit permissions via the Role Scoper plugin.
Version: 1.0.3
Author: Kevin Behrens
Author URI: http://agapetry.net/news/rs-config-ngg/
Min WP Version: 3.0
License: GPL version 2 - http://www.opensource.org/licenses/gpl-license.php

*/

/*
Copyright (c) 2009-2011, Kevin Behrens.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
version 2 as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

if ( ! is_admin() && ! strpos( $_SERVER['REQUEST_URI'], 'nextgen' ) ) // Gallery viewing permissions are covered by restrictions / roles on the associated page
	return;

if( basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME']) )
	die();

define ('RS_CONFIG_NGG_VERSION', '1.0.3-dev');

define ('RS_CONFIG_NGG_FOLDER', dirname( plugin_basename(__FILE__) ) );

// We'll generate a relational table so we can treat albums as terms and support Album Roles
// Use of WP term_relationships would be better, but converting existing album_ids into auto-incremented WP term_id would be messy
global $wpdb;
$wpdb->ngg_album2gallery_rs = $wpdb->prefix . 'ngg_album2gallery_rs'; 

require_once('agapetry_wp_core_lib.php');
require_once( 'rs-ngg_functions.php' );

add_action( 'init', 'rs_ngg_init', 1 );	// rs filters must be added before RS initialization

register_activation_hook(__FILE__, 'rs_ngg_activate');
register_deactivation_hook(__FILE__, 'rs_ngg_deactivate');
?>