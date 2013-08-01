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

 
// Tell Role Scoper about data sources which NextGenGallery provides
function rs_gasquereg_data_sources( $data_sources ) {
	$defining_module_name = 'gasquereg';
	
	$src_name = 'gasquereg_form';
	$display_name = 'Anmälningsformulär';
	$display_name_plural = 'Anmälningsformulär';

	$table_basename = 'gasquereg_forms';
	$col_id = 'id';
	$col_name = 'title';
	
	$reqd_caps = array();
	$reqd_caps['edit']['gasquereg_form'][''] =	array( 'Gasquereg Manage others form' );
	$reqd_caps['admin']['gasquereg_form'][''] = array( 'Gasquereg Manage others form' );

	$args = array( 'reqd_caps' => $reqd_caps, 'edit_url' => 'admin.php?page=gasquereg&action=edit&form=%s');
	$src =& $data_sources->add( $src_name, $defining_module_name, $display_name, $display_name_plural, $table_basename, $col_id, $col_name, $args );
	$src->cols->owner = 'createdBy';
	error_log('Gasquereg rs data sources added');
	return $data_sources;
}

// Tell Role Scoper which capability is THE administrator cap for NGG, ensuring the owning user is unhindered in defining RS role restrictions and assignments
function rs_gasquereg_administrator_caps( $admin_caps ) {
	$admin_caps['nggallery'] = 'NextGEN Change options';
	return $admin_caps;
}

// Tell Role Scoper about capabilities WHICH ARE ALREADY USED in current_user_can calls in the NextGenGallery source code.
// A plugin's testing of custom capabilities using current_user_can is what qualifies it for custom role scoping.
function rs_gasquereg_capabilities( $cap_defs ) {
	$defining_module_name = 'gasquereg';
	$src_name = 'gasquereg_form';				// note: plugins can also define blogwide-only caps by defining and referencing a "fake" data source which merely provides a meaningful display name.  Set data source property no_object_roles to true.
	$object_type = 'gasquereg_form';
	
	$op_type = 'edit';
	$args = array( 'owner_privilege' => true );
	$cap_defs->add( 'Gasquereg Manage form', $defining_module_name, $src_name, $object_type, $op_type, $args );
	
	$args = array( 'attributes' => array('others'),	'base_cap' => 'Gasquereg Manage form', 'no_custom_add' => true, 'no_custom_remove' => true );
	$cap_defs->add( 'Gasquereg Manage others form', $defining_module_name, $src_name, $object_type, $op_type, $args );
/*
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
	*/
	return $cap_defs;
}

// Tell Role Scoper about new NGG-specific roles
function rs_gasquereg_roles( $role_defs ) {
	$defining_module_name = 'gasquereg';
	
	/*$args = array( 'valid_scopes' => array('blog' => 1) );
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
	$role_defs->add( 'gallery_administrator' , $defining_module_name, $display_name, $abbrev, 'rs', $args );*/
	
	$args = array( 'valid_scopes' => array('blog' => 1, 'object' => 1) );
	$display_name = 'Författare av formulär - hej';//__( 'Gallery Author', 'rs-config-ngg' );
	$abbrev = 'Författare';
	$role_defs->add( 'form_author' , $defining_module_name, $display_name, $abbrev, 'rs', $args );
	
	return $role_defs;
}

// Tell Role Scoper which NGG capabilities are included in each role.
// Note: these associations may be edited by the site administrator unless the Capability properties no_custom_add, no_custom_remove properties lock them into/out of the role in question.
function rs_gasquereg_role_caps( $role_caps ) {
	$role_caps['rs_form_author'] = array('Gasquereg Manage form' => true);
	return $role_caps;
}

// Define Role Scoper options which allow the site administrator to specify which NGG object types can have roles assinged term-wide or object-wide
// The value setting indicates default behavior.
function rs_gasquereg_default_otype_options( $options ) {
	$options['use_object_roles']['gasquereg:gasquereg'] = 1;
	//$options['use_term_roles']['ngg_gallery:ngg_gallery'] = 1;
	return $options;
}

?>