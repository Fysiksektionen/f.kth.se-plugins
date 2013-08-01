<?php
/**
 * functions for the WordPress plugin "Role Scoping for NextGen Gallery"
 * rs-ngg_functions.php
 * 
 * @description 
 * These filters, when registered by the add_filter calls in the rs_ngg_init function, 
 * enable Role Scoper to regulate access based on plugin-defined capabilities.
 *
 * In this case, the capabilities in question all pertain to gallery/album editing within wp-admin -
 * a filtering limitation artificially imposed by the rs-config-ngg.php code if ( ! is_admin() ) return;
 * 
 * This code may serve as a model and reference point to define Role Scoping for other plugins.
 * Until the Role Scoper API documentation is expanded, see role-scoper/defaults_rs.php for additional properties.
 *
 * @author 		Kevin Behrens
 * @copyright 	Copyright 2012
 * 
 */
 
// Tell Role Scoper when a URI should be treated as wp-admin access
function rs_ngg_access_name( $access_name ) {
	if ( 'admin' != $access_name ) {
		$plugin_url = parse_url( constant('WP_PLUGIN_URL') );
		if ( 0 === strpos( $_SERVER['REQUEST_URI'], $plugin_url['path'] . '/' . constant( 'NGGFOLDER' ) . '/admin' ) )
			$access_name = 'admin';
	}
	return $access_name;	
}
 
 
// Tell Role Scoper about data sources which NextGenGallery provides
function rs_ngg_data_sources( $data_sources ) {
	$defining_module_name = 'nggallery';
	
	$src_name = 'ngg_gallery';
	$display_name = __('NGG Gallery', 'rs-config-ngg');
	$display_name_plural = __('NGG Galleries', 'rs-config-ngg');

	$table_basename = 'ngg_gallery';
	$col_id = 'gid';
	$col_name = 'title';
	
	$reqd_caps = array();
	$reqd_caps['edit']['ngg_gallery'][''] =	array( 'NextGEN Manage others gallery' );
	$reqd_caps['admin']['ngg_gallery'][''] = array( 'NextGEN Manage others gallery' );

	$args = array( 'reqd_caps' => $reqd_caps, 'uses_taxonomies' => array('ngg_album'), 
					'edit_url' => 'admin.php?page=nggallery-manage-gallery&mode=edit&gid=%s'
			);
	
	$src =& $data_sources->add( $src_name, $defining_module_name, $display_name, $display_name_plural, $table_basename, $col_id, $col_name, $args );
	$src->cols->owner = 'author';
	
	// === Object_types property setting is unnecessary for data sources with a single object type, if running RS 1.0.0-rc9.9226 or later
	if ( ! version_compare( SCOPER_VERSION, '1.0.0-rc9.9226', '>=' ) ) {
		$src->object_types = array( 'ngg_gallery' => (object) array( 'val' => 'ngg_gallery', 'display_name' => __('NGG Gallery', 'rs-config-ngg'), 'display_name_plural' => __('NGG Galleries', 'rs-config-ngg') ) );
		$src->collections = array ('type' => 'object_types');
	}
	// ===


	// This could be activated to provide listing query filtering if ngg adds a hook for it
	// ( Currently, the 'query' filter is run through rs_ngg_query_galleries instead ).
	//$src->query_hooks = (object) array( 'request' => 'ngg_galleries_request', 'distinct' => 'ngg_galleries_distinct' );
	
	// note: also requires 'taxonomies' definition
	$display_name = __('NGG Album', 'rs-config-ngg');
	$display_name_plural = __('NGG Albums', 'rs-config-ngg');
	$table_basename = 'ngg_album';
	$col_id = 'id';
	$col_name = 'name';

	$name = 'ngg_album';
	
	$args = array( 'is_taxonomy' => true,	'taxonomy_only' => true );
	$data_sources->add( 'ngg_album', $defining_module_name, $display_name, $display_name_plural, $table_basename, $col_id, $col_name, $args );

	return $data_sources;
}

// Tell Role Scoper about taxonomies which apply to NGG data sources
// note: These would typically use the WP taxonomy tables, but in this case are based on custom taxonomy tables.
function rs_ngg_taxonomies( $taxonomies ) {
	$display_name = __('NGG Album', 'rs-config-ngg');
	$display_name_plural = __('NGG Albums', 'rs-config-ngg');
	$table_basename = 'nggalbum';
	$defining_module_name = 'nggallery';
	$uses_standard_schema = false;
	$requires_term = false;			// since galleries are not required to be in at least one album, access limiting based on album membership (Album Restrictions) will not be applied

	$args = array( 
	'hierarchical' => false,
	'source' => 'ngg_album', 	'table_term2obj_basename' => 'ngg_album2gallery_rs', 	'table_term2obj_alias' => '',
	'cols' => (object) array( 
		'count' => '', 'term2obj_tid' => 'album_id', 	'term2obj_oid' => 'gallery_id' 
		),
	'edit_url' => 'admin.php?page=nggallery-manage-album'
	);

	$temp = $taxonomies->add( 'ngg_album', $defining_module_name, $display_name, $display_name_plural, $uses_standard_schema, $requires_term, $args );

	return $taxonomies;
}

// Tell Role Scoper which capability is THE administrator cap for NGG, ensuring the owning user is unhindered in defining RS role restrictions and assignments
function rs_ngg_administrator_caps( $admin_caps ) {
	$admin_caps['nggallery'] = 'NextGEN Change options';
	return $admin_caps;
}

// Tell Role Scoper about capabilities WHICH ARE ALREADY USED in current_user_can calls in the NextGenGallery source code.
// A plugin's testing of custom capabilities using current_user_can is what qualifies it for custom role scoping.
function rs_ngg_capabilities( $cap_defs ) {
	$defining_module_name = 'nggallery';
	$src_name = 'ngg_gallery';				// note: plugins can also define blogwide-only caps by defining and referencing a "fake" data source which merely provides a meaningful display name.  Set data source property no_object_roles to true.
	$object_type = 'ngg_gallery';
	
	$op_type = 'edit';
	$args = array( 'owner_privilege' => true );
	$cap_defs->add( 'NextGEN Manage gallery', $defining_module_name, $src_name, $object_type, $op_type, $args );
	
	$args = array( 'attributes' => array('others'),	'base_cap' => 'NextGEN Manage gallery', 'no_custom_add' => true, 'no_custom_remove' => true );
	$cap_defs->add( 'NextGEN Manage others gallery', $defining_module_name, $src_name, $object_type, $op_type, $args );

	$args = array( 'owner_privilege' => true );
	$cap_defs->add( 'NextGEN Upload images', $defining_module_name, $src_name, $object_type, $op_type, $args );
	$cap_defs->add( 'NextGEN Use TinyMCE', $defining_module_name, $src_name, $object_type, $op_type, $args );

	$op_type = 'admin';
	$args = array();
	$cap_defs->add( 'NextGEN Gallery overview', $defining_module_name, $src_name, $object_type, $op_type, $args );
	$cap_defs->add( 'NextGEN Manage tags', $defining_module_name, $src_name, $object_type, $op_type, $args );
	$cap_defs->add( 'NextGEN Edit album', $defining_module_name, $src_name, $object_type, $op_type, $args );
	$cap_defs->add( 'NextGEN Change style', $defining_module_name, $src_name, $object_type, $op_type, $args );
	
	$args = array( 'no_custom_remove' => true, 'no_custom_add' => true );	// we define this via define_administrator_caps_rs as THE administrator cap
	$cap_defs->add( 'NextGEN Change options', $defining_module_name, $src_name, $object_type, $op_type, $args );
	
	return $cap_defs;
}

// Tell Role Scoper about new NGG-specific roles
function rs_ngg_roles( $role_defs ) {
	$defining_module_name = 'nggallery';
	
	$args = array( 'valid_scopes' => array('blog' => 1) );
	$display_name = __( 'Gallery Author', 'rs-config-ngg' );
	$abbrev = __( 'Author' );
	$role_defs->add( 'gallery_author' , $defining_module_name, $display_name, $abbrev, 'rs', $args );
	
	// note: term roles here would pertain to albums, but will be unavailable unless the db is revised to store the gallery-album relationship relationally.
	$args = array( 'valid_scopes' => array('blog' => 1, 'term' => 1, 'object' => 1), 'objscope_equivalents' => array('rs_gallery_author') );
	$display_name = __( 'Gallery Editor', 'rs-config-ngg' );
	$abbrev = __( 'Editor' );
	$role_defs->add( 'gallery_editor' , $defining_module_name, $display_name, $abbrev, 'rs', $args );
	
	$args = array( 'valid_scopes' => array('blog' => 1) );
	$display_name = __( 'Gallery Administrator', 'rs-config-ngg' );
	$abbrev = __( 'Administrator' );
	$role_defs->add( 'gallery_administrator' , $defining_module_name, $display_name, $abbrev, 'rs', $args );
	
	return $role_defs;
}

// Tell Role Scoper which NGG capabilities are included in each role.
// Note: these associations may be edited by the site administrator unless the Capability properties no_custom_add, no_custom_remove properties lock them into/out of the role in question.
function rs_ngg_role_caps( $role_caps ) {

	$role_caps['rs_gallery_author'] = array( 'NextGEN Manage gallery' => true, 'NextGEN Upload images' => true, 'NextGEN Use TinyMCE' => true );
	
	$role_caps['rs_gallery_editor'] = array( 'NextGEN Manage gallery' => true, 'NextGEN Upload images' => true, 'NextGEN Manage others gallery' => true, 'NextGEN Use TinyMCE' => true );
	
	$role_caps['rs_gallery_administrator'] = array( 'NextGEN Manage gallery' => true, 'NextGEN Upload images' => true, 'NextGEN Manage others gallery' => true,
													'NextGEN Gallery overview' => true, 'NextGEN Manage tags' => true, 'NextGEN Edit album' => true,
													'NextGEN Change style' => true,		'NextGEN Change options' => true, 'NextGEN Use TinyMCE' => true );
												
	return $role_caps;
}

// Define Role Scoper options which allow the site administrator to specify which NGG object types can have roles assinged term-wide or object-wide
// The value setting indicates default behavior.
function rs_ngg_default_otype_options( $options ) {
	$options['use_object_roles']['ngg_gallery:ngg_gallery'] = 1;
	$options['use_term_roles']['ngg_gallery:ngg_gallery'] = 1;
	return $options;
}

// *** not needed with Role Scoper >= 1.3
// Tell Role Scoper which NextGenGallery current_user_can checks for blog-wide user capability should be
// met with an attempt to determine (by URI or POST parameters) which Gallery the capability check actually pertains to.
function rs_ngg_generate_object_id_caps($cap_names) {
	if ( empty($_POST['action']) || ! in_array( $_POST['action'], array( 'rotateImage', 'createNewThumb' ) ) )
		$cap_names = array_merge( (array) $cap_names, array( 'NextGEN Manage gallery', 'NextGEN Manage others gallery', 'NextGEN Upload images' ) );
	
	return $cap_names;
}

// *** not needed with Role Scoper >= 1.3
// Tell Role Scoper which NextGenGallery current_user_can checks for blog-wide user capability should be
// validated by the Gallery-specific assignment of any role which contains the required capability.
function rs_ngg_any_objrole_caps($cap_names) {
	if ( empty($_POST['addgallery']) && ( ! isset($_POST['zipgalselect']) || 0 != $_POST['zipgalselect'] ) )
		$cap_names = array_merge( (array) $cap_names, array( 'NextGEN Manage gallery', 'NextGEN Manage others gallery', 'NextGEN Upload images' ) );
	
	return $cap_names;
}


// Since NGG does not store album-gallery information in a convenient DB schema, 
// this plugin creates an album2gallery table and resynchronizes with each album insertion, update or deletion.
function rs_ngg_sync_albums() {
	global $wpdb;

	if ( empty($wpdb->nggalbum) || empty($wpdb->ngg_album2gallery_rs) )
		return false;
	
	$db_action = false;
		
	$ngg_album_galleries = array();
	if ( $results = $wpdb->get_results("SELECT id, name, sortorder FROM $wpdb->nggalbum") )
		foreach ( $results as $row )
			$ngg_album_galleries[ $row->id ] = unserialize($row->sortorder);

	$rs_album_galleries = array();
	if ( $results = $wpdb->get_results("SELECT rel_id, album_id, gallery_id FROM $wpdb->ngg_album2gallery_rs") )
		foreach ( $results as $row )
			$rs_album_galleries[ $row->album_id ] []= $row->gallery_id;
					
	if ( $ngg_album_galleries ) {
		foreach( array_keys($ngg_album_galleries) as $album_id ) {
			$_ngg = ! empty( $ngg_album_galleries[$album_id] ) ? $ngg_album_galleries[$album_id] : array();
			$_rs = ! empty( $rs_album_galleries[$album_id] ) ? $rs_album_galleries[$album_id] : array();

			if ( $insert_gallery_ids = array_diff( $_ngg, $_rs ) ) {
				$db_action = true;
				
				foreach ( $insert_gallery_ids as $gallery_id ) {
					$wpdb->query( "INSERT INTO $wpdb->ngg_album2gallery_rs (album_id, gallery_id) VALUES ('$album_id', '$gallery_id')" );
				}	
			}
		}
	}
	
	if ( $rs_album_galleries ) {
		foreach( array_keys($rs_album_galleries) as $album_id ) {
			$_ngg = isset( $ngg_album_galleries[$album_id] ) ? $ngg_album_galleries[$album_id] : array();
			$_rs = isset( $rs_album_galleries[$album_id] ) ? $rs_album_galleries[$album_id] : array();

			if ( $delete_gallery_ids = array_diff( $_rs, $_ngg ) ) {
				$db_action = true;
				
				foreach ( $delete_gallery_ids AS $gallery_id ) {
					$wpdb->query( "DELETE FROM $wpdb->ngg_album2gallery_rs WHERE album_id = '$album_id' AND gallery_id = '$gallery_id'" );
				}
			}
		}
	}	
				
	if ( $db_action && defined('wpp_cache_flush') )
		wpp_cache_flush();

	update_option ('rs_ngg_album_sync_done', true);
	return true;
}

// This is the primary "workaround" function, taking a peek at all queries and modifying them as necessary to making up for some missing hooks in NextGenGallery.
function rs_ngg_query_hardway( $query ) {
	global $wpdb;
	
	// no recursion
	if ( function_exists('scoper_querying_db') && scoper_querying_db() )
		return $query;
	
	if ( false !== strpos( $query, 'ngg_' ) ) {
		// gallery listing
		if ( strpos( $query, "* FROM $wpdb->nggallery" ) ) {
			if ( strpos( $query, $wpdb->nggpictures ) ) {
				$query = str_replace( ' AS tt ', ' ', $query );
				$query = str_replace( ' AS t ', ' ', $query );
				$query = str_replace( 'tt.', "$wpdb->nggpictures.", $query );
				$query = str_replace( 't.', "$wpdb->nggallery.", $query );

				if ( version_compare( SCOPER_VERSION, '1.1', '<' ) ) {
					// need DISTINCT due to LEFT JOIN on album2gallery table.  TODO: modify RS filter to apply term roles via WHERE clause SUBSELECT if term table is not already INNER JOINed
					// (but RS 1.1 eliminates the LEFT JOIN)
					$query = str_replace( "SELECT * FROM $wpdb->nggallery", "SELECT DISTINCT $wpdb->nggallery.* FROM", $query );
					$query = str_replace( "SELECT SQL_CALC_FOUND_ROWS * FROM $wpdb->nggallery", "SELECT SQL_CALC_FOUND_ROWS DISTINCT $wpdb->nggallery.* FROM", $query );
					$query = str_replace( "SELECT SQL_CALC_FOUND_ROWS wp_trunk_ngg_pictures.*", "SELECT SQL_CALC_FOUND_ROWS DISTINCT wp_trunk_ngg_pictures.*", $query);
					$query = str_replace( "SELECT SQL_CALC_FOUND_ROWS wp_trunk_ngg_pictures.*", "SELECT SQL_CALC_FOUND_ROWS DISTINCT wp_trunk_ngg_pictures.*", $query);
				}
			}

			if ( ! is_administrator_rs('ngg_gallery') ) {
				$query = apply_filters( 'objects_request_rs', $query, 'ngg_gallery', 'ngg_gallery' );	
			}

		// album dropdown in TinyMCE popup
		} elseif( ( 'window.php' == $GLOBALS['pagenow'] ) && strpos( $query, "* FROM $wpdb->nggalbum" ) ) {
			global $scoper, $current_user;

			if ( empty( $current_user->allcaps['NextGEN Edit album'] ) ) {  // All albums selectable with blogwide NextGEN Edit album cap.  Otherwise only albums for which user has Gallery Editor role assignment.
				$terms = $scoper->qualify_terms( array( 'NextGEN Manage others gallery' ), 'ngg_album' );
	
				if ( ! strpos( $query, 'WHERE' ) && ! strpos( $query, 'JOIN' ) )
					$query = str_replace( "FROM $wpdb->nggalbum", "FROM $wpdb->nggalbum WHERE", $query );
	
				$query = str_replace( "FROM $wpdb->nggalbum WHERE", "FROM $wpdb->nggalbum WHERE id IN ('" . implode( "','", $terms ) . "')", $query );
			}	
		// track changes to Album-gallery relationships
		} elseif ( strpos($query, " $wpdb->nggalbum ") && ( ( false !== strpos($query, "UPDATE") ) || ( false !== strpos($query, "DELETE") ) || ( false !== strpos($query, "INSERT") ) ) ) {  // new albums are generally not inserted with galleries, but resync on INSERT just in case
			add_action( 'admin_footer', 'rs_ngg_sync_albums' );
		}
	
	} // endif this is an NGG admin URL
	
	// pages dropdown
	if ( false !== strpos( $query, "SELECT ID, post_parent, post_title FROM $wpdb->posts" ) ) {
		if ( ! is_administrator_rs('ngg_gallery') ) {
			global $scoper, $wpdb;
			if ( $gallery_id = $scoper->data_sources->detect( 'id', 'ngg_gallery' ) ) {
				
				$stored_page_id = scoper_get_var( "SELECT pageid FROM $wpdb->nggallery WHERE gid = '$gallery_id' LIMIT 1" );

				// replace the NGG parent_dropdown() call with scoped equivalent
				echo ScoperAdminUI::dropdown_pages( 0, $stored_page_id );

				// prevent the NGG parent_dropdown() from returning any more pages
				$query = "SELECT ID, post_parent, post_title FROM $wpdb->posts WHERE 1=2";
			}
		}
	}
	
	return $query;
}

// Another "workaround" function, using the check_admin_referer action to disallow page adds and page parent settings for which this user's Page Roles do not qualify.
function rs_ngg_restrict_page_add( $action ) {
	if ( ('ngg_updategallery' == $action) && ! empty($_POST['addnewpage']) ) {
		$parent_id = $_POST['parent_id'];
		
		if ( empty($parent_id) ) {
			global $scoper, $current_user;
			$reqd_caps = array('edit_others_pages');
			$roles = $scoper->role_defs->qualify_roles($reqd_caps, '');
			if ( ! array_intersect_key($roles, $current_user->blog_roles) )
				wp_die( __('You are not allowed to associate a page with the Main Page.', 'scoper') );
		} else {
			global $wpdb, $scoper;
			$args = array();
			
			$args['force_reqd_caps']['page'] = array();
			foreach (array_keys( $scoper->data_sources->member_property('post', 'statuses') ) as $status_name )
				$args['force_reqd_caps']['page'][$status_name] = array('edit_others_pages');
				
			$args['alternate_reqd_caps'][0] = array('create_child_pages');
			
			$qry_parents = "SELECT DISTINCT ID FROM $wpdb->posts WHERE post_type = 'page'";
			$qry_parents = apply_filters('objects_request_rs', $qry_parents, 'post', 'page', $args);

			$valid_parents = scoper_get_col($qry_parents);

			if ( ! in_array($parent_id, $valid_parents) )
				wp_die( __('You are not allowed to select that page as page parent.', 'scoper') );
		}
	}
}

// Since NGG does not store album-gallery information in a convenient DB schema, 
// create an album2gallery table whose contents we will maintain.
function rs_ngg_update_schema() {
	global $wpdb;

	if ( empty($wpdb->nggalbum) || empty($wpdb->nggallery) )
		return false;

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	$tabledefs = "CREATE TABLE $wpdb->ngg_album2gallery_rs (
	 rel_id bigint(20) NOT NULL auto_increment,
	 album_id bigint(20) NOT NULL default '0',
	 gallery_id bigint(20) NOT NULL default '0',
	 	PRIMARY KEY  (rel_id),
	 	KEY gallery_id (gallery_id,album_id),
	 	KEY album_id (album_id)
	);
	";
	
	// apply all table definitions
	dbDelta($tabledefs);
	
	update_option ('rs_ngg_db_setup_done', true);
	return true;
}

function rs_ngg_confirm_dependencies() {
	$min_scoper_version = '1.0.0';
	$min_ngg_version = '0.9.7';

	// Give a heads-up and download link if Role Scoper is not active
	$err_msg = '';
	$active_scoper_file = $active_ngg_file = false;
	$plugins = get_option('active_plugins');
	foreach ( $plugins as $plugin_file ) {
		if ( false !== strpos($plugin_file, 'role-scoper') )
			$active_scoper_file = $plugin_file;
		
		if ( false !== strpos($plugin_file, 'nggallery') )
			$active_ngg_file = $plugin_file;
	}
	
	$ngg_version = defined( 'NGGVERSION' ) ? NGGVERSION : 0;
	$scoper_version = defined( 'SCOPER_VERSION' ) ? SCOPER_VERSION : 0;
	
	if ( ( ! $active_ngg_file || ! $active_scoper_file ) && is_multisite() ) {
		$plugins = (array) get_site_option('active_sitewide_plugins');
		
		foreach ( array_keys($plugins) as $plugin_file ) {
			if ( false !== strpos($plugin_file, 'role-scoper') )
				$active_scoper_file = $plugin_file;
			
			if ( false !== strpos($plugin_file, 'nggallery') )
				$active_ngg_file = $plugin_file;
		}
	}
	
	if ( ! $active_ngg_file ) {
		$err_msg = sprintf(__('Role Scoping for %1$s won&#39;t work until %2$s%1$s%3$s is installed.', 'rs-config-ngg'), __('NextGen Gallery', 'rs-config-ngg'), "<a href='__plg-info__'>", '</a>');
	
	} elseif ( defined('NGGVERSION') && version_compare( $ngg_version, $min_ngg_version, '<' ) ) {
		$err_msg = sprintf(__('Role Scoping for %1$s won&#39;t work until you upgrade %2$s%1$s%3$s to a newer version.', 'rs-config-ngg'), __('NextGen Gallery', 'rs-config-ngg'), "<a href='__plg-update__'>", '</a>');
	
	} elseif ( ! $active_scoper_file || ( $scoper_need_update = version_compare( $scoper_version, $min_scoper_version, '<' ) ) ) {
		if ( ! $active_scoper_file )
			$err_msg = sprintf(__('Note: For enhanced control of %1$s edit permissions, you must also install the %2$s Role Scoper%3$s plugin.', 'rs-config-ngg'), __('NextGen Gallery', 'rs-config-ngg'), "<a href='__rs-info__'>", '</a>');
		elseif ( $scoper_need_update )
			$err_msg = sprintf(__('Role Scoping for %1$s won&#39;t work until you upgrade %2$s Role Scoper%3$s to a newer version.', 'rs-config-ngg'), __('NextGen Gallery', 'rs-config-ngg'), "<a href='__rs-update__'>", '</a>');
	}
	
	if ( $err_msg ) {
		$func_body = '$msg = str_replace( "__rs-info__", awp_plugin_info_url("role-scoper"), "' . $err_msg . '");';
		$func_body .= '$msg = str_replace( "__plg-info__", awp_plugin_info_url("nextgen-gallery"), $msg);';
		$func_body .= '$msg = str_replace( "__plg-update__", awp_plugin_update_url("' . $active_ngg_file . '"), $msg);';
		$func_body .= '$msg = str_replace( "__rs-update__", awp_plugin_update_url("' . $active_scoper_file . '"), $msg);';
		$func_body .= "echo '" 
		. '<div id="message" class="error fade" style="color: black"><p><strong>' 
		. "'" 
		. ' . $msg . ' 
		. "'</strong></p></div>';";
	
		if ( is_admin() )
			add_action('admin_notices', create_function('', $func_body) );

		return false;
	}
	
	return true;
}

function rs_ngg_hardway_uris($arr) {
	$arr []= 'p-admin/admin.php?page=nggallery-manage-gallery';
	return $arr;
}

// Register all the above filters, if nothing is wrong with this plugin's DB setup
// This function itself is added on the WP 'init' action
function rs_ngg_init() {
	if ( ! rs_ngg_confirm_dependencies() )
		return;

	load_plugin_textdomain('rs-config-ngg', false, PLUGINDIR . '/' . RS_CONFIG_NGG_FOLDER . '/languages');

	if ( ! $db_okay = get_option( 'rs_ngg_db_setup_done' ) )
		$db_okay = rs_ngg_update_schema();
	
	if ( $db_okay && ! get_option( 'rs_ngg_album_sync_done' ) )
		rs_ngg_sync_albums();
		
	if ( $db_okay ) {
		add_filter( 'caps_granted_from_any_objrole_rs', 'rs_ngg_any_objrole_caps' );		// not needed with Role Scoper >= 1.3
		add_filter( 'caps_to_generate_object_id_rs', 'rs_ngg_generate_object_id_caps' );	// not needed with Role Scoper >= 1.3
		
		add_filter( 'scoper_admin_hardway_uris', 'rs_ngg_hardway_uris');
		
		add_action( 'check_admin_referer', 'rs_ngg_restrict_page_add' );
		add_filter( 'query', 'rs_ngg_query_hardway' );
	} else {
		$message = sprintf(__('Role Scoping for NextGen Gallery could not be activated because the NGG database tables are missing.', 'rs-config-ngg') );
		$func_body = 'echo ' . "'" . '<div id="message" class="error fade"><p><strong>' . $message . '</strong></p></div>' . "'" . ';';
		add_action('admin_notices', create_function('', $func_body) );
	}
}

// Runs database update scripts, and anything else which should run upon plugin activation
// This function itself is added by the register_activation_hook function
function rs_ngg_activate() {
	rs_ngg_update_schema();
	rs_ngg_sync_albums();
}

function rs_ngg_deactivate() {
	delete_option('rs_ngg_db_setup_done');  // force db schema confirmation on reactivation
	delete_option('rs_ngg_album_sync_done');
}

add_filter( 'define_administrator_caps_rs', 'rs_ngg_administrator_caps' );
add_filter( 'scoper_access_name', 'rs_ngg_access_name' );
add_filter( 'define_data_sources_rs', 'rs_ngg_data_sources' );
add_filter( 'define_taxonomies_rs', 'rs_ngg_taxonomies' );
add_filter( 'define_capabilities_rs', 'rs_ngg_capabilities' );
add_filter( 'define_roles_rs', 'rs_ngg_roles' );
add_filter( 'define_role_caps_rs', 'rs_ngg_role_caps' );
add_filter( 'default_otype_options_rs', 'rs_ngg_default_otype_options' );
?>