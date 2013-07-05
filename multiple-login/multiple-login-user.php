<?php
class ZetaMultipleLogin_LDAPCASUser
{
	private $data = NULL;
  private $role = NULL;

	function __construct($member_array, $role) {
		$this->data = $member_array;
    $this->role = $role;
	}

	function get_user_name() {
		if(isset($this->data[0]['cn'][0]))
			return $this->data[0]['cn'][0];
		else
			return FALSE;
	}
	
	function get_user_data() {
		if (isset($this->data[0]['uid'][0]))
			return array(
				'user_login' => $this->data[0]['uid'][0],
				'user_password' => substr( md5( uniqid( microtime( ))), 0, 8 ),
				'user_email' => $this->data[0]['mail'][0],
				'first_name' => $this->data[0]['givenname'][0],
				'last_name' => $this->data[0]['sn'][0],
				'role' => $this->role,
				'nickname' => $this->data[0]['cn'][0],
				'user_nicename' => $this->data[0]['uid'][0],
				'display_name' => $this->data[0]['givenname'][0] . " " . $this->data[0]['sn'][0]
			);
		else 
			return false;
	}
}
?>