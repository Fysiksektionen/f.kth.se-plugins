<?php
/* Denna sida listar alla maillistor som man kan redigera. */
function fysik_maillistor_list() {
	global $wpdb;
	$user = wp_get_current_user();
	require_once(SCOPER_ABSPATH . "/scoped-user.php");
	$groups = WP_Scoped_User::get_groups_for_user($user->id);
	echo '<h2>Maillistor</h2>';
	$roles = $user->roles;
	$query = "SELECT * FROM ".$wpdb->prefix."maillists";// WHERE ".$subquery;
	$results = $wpdb->get_results($query);
	foreach ($results as $row){
		if($groups[$row->group] == 1 || is_super_admin()) { 
			echo "<a class='name' href='users.php?page=fysik_maillistor_edit&maillist=".$row->id."'>".$row->name."</a><br>";
		} else {
			echo $row->name." (Du har inte behörighet att se denna maillista)<br>";
		}
	}
}
?>