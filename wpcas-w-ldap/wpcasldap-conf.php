<?php
/*
// Optional configuration file for wpCASLDAP plugin
// 
// Settings in this file override any options set in the 
// wpCASLDAP menu in Options. Any settings added to the
// $wpcasldap_options array, will not show up on the
// Options Page. 
//
// I would suggest commenting out the settings you want 
// to appear on the options page.
//
*/


// the configuration array
$wpcasldap_options = array (
	'cas_version' => '2.0',
	'include_path' => '/var/apache2/htdocs-www.f.kth.se/CAS/CAS.php',
	'server_hostname' => 'login.kth.se',
	'server_port' => '443',
	'server_path' => '',

	'ldaphost' => 'ldap.kth.se',
	'ldapport' => '389',
	'ldapbasedn' => 'ou=People,ou=Unix,dc=kth,dc=se',

	'useradd' => 'yes',
	'useldap' => 'yes',
	'email_suffix' => 'kth.se',
	'userrole' => 'subscriber'
);
		
?>
