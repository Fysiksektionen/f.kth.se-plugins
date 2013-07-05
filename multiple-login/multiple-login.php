<?php
/*
Plugin Name: Multiple Login
Plugin URI: TODO
Description: Allows to login from multiple authentication sources.
Version: 0.0.1
Author: Calle "Zeta Two" Svensson <calle.svensson@zeta-two.com>
Author URI: http://zeta-two.com
License: MIT
*/
?>
<?php
require_once 'multiple-login-user.php';
require_once 'multiple-login-settings.php';
require_once 'multiple-login-admin.php';
?>
<?php
class ZetaMultipleLogin {
  private static $authtypes = array('wp' => 'Wordpress', 'ldap-cas' => 'LDAP & CAS'); 
  
  private $options;
  private $cas_valid = false;
  
  public function __construct($options) {
    $this->options = $options->get_options();
  
    //Add actions and shortcodes
    add_action('wp_authenticate', array($this, 'authenticate'));
    add_action('login_form', array($this, 'login_form'));
    add_shortcode( 'multiple-login', array($this, 'login_chooser_tag' ) );
    add_action( 'wp_enqueue_scripts', array($this, 'add_style' ) );
  }
  
  //Called when authentication is needed
  public function authenticate() {
    
    $authtype = 'none';
    if(!empty($_GET['authtype']) && in_array($_GET['authtype'], array_keys(self::$authtypes))) {
      $authtype = $_GET['authtype'];
    } elseif(!empty($_POST['authtype']) && in_array($_POST['authtype'], array_keys(self::$authtypes))) {
      $authtype = $_POST['authtype'];
    }
    
    switch($authtype) {
      case 'wp':
        return;
        break;
      case 'ldap-cas':
        $this->authenticate_ldap_cas();
        break;
      case 'none':
        wp_redirect(get_page_link($this->options['login_chooser']) . ((isset($_GET['redirect_to']))?'?redirect_to='.$_GET['redirect_to']:''));
        exit;
        break;
    }
    
    $this->redirect();
  }
  
  public function redirect() {
    //Authentication done, redirect
    if( isset( $_GET['redirect_to'] )) {
      wp_redirect( preg_match( '/^http/', $_GET['redirect_to'] ) ? $_GET['redirect_to'] : site_url());
    } else {
      wp_redirect( site_url( '/wp-admin/' ));
    }
  }
  
  public function authenticate_ldap_cas() {
    if( phpCAS::isAuthenticated() ) {
      $user_id = NULL;
    
      // CAS was successful
      $user_cas_id = phpCAS::getUser();
			$user_ldap_data = $this->get_ldap_user($user_cas_id);
			$userdata = $user_ldap_data->get_user_data();
      
			if( $wp_user = get_user_by('login', $userdata['user_login'])) { // User has a WP account.
				
        //Make sure user is member of current site
				if (!get_usermeta( $wp_user->ID, 'wp_'.$blog_id.'_capabilities')) {
					if (function_exists('add_user_to_blog')) { add_user_to_blog($blog_id, $wp_user->ID, $this->options['default_userrole']); }
				}
		
				$user_id = $wp_user->ID;
			} else { // User does not has a WP account.
				if ($this->options['ldap_adduser']) {
					$user_id = $this->no_wp_user($user_cas_id, $user_ldap_data);
        } else {
          die( __( 'You do not have permission here', 'ZetaMultipleLogin' ));
        }
			}
      
      wp_set_auth_cookie($user_id);
    } else {
      phpCAS::forceAuthentication();
    }
  }
  
  function get_ldap_user($uid) {
    $ds = ldap_connect($this->options['ldap_host'], $this->options['ldap_port']);

    //Can't connect to LDAP.
    if(!$ds) {
      $error = 'Error in contacting the LDAP server.';
    } else {
    
      // Make sure the protocol is set to version 3
      if(!ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3)) {
        $error = 'Failed to set protocol version to 3.';
      } else {
        //Connection made -- bind anonymously and get dn for username.
        $bind = ldap_bind($ds);
        
        //Check to make sure we're bound.
        if(!$bind) {
          $error = 'Anonymous bind to LDAP failed.';
        } else {
          $search = ldap_search($ds, $this->options['ldap_basedn'], "ugkthid=$uid");
          $info = ldap_get_entries($ds, $search);
          
          ldap_close($ds);
          return new ZetaMultipleLogin_LDAPCASUser($info, $this->options['defaultrole']);
        }
        ldap_close($ds);
      }
    }
    return FALSE;
  }
  
  function no_wp_user($user_cas_id, $user_ldap_data = NULL) {
    $userdata = array();
    if ($user_ldap_data !== NULL) {
      $userdata = $user_ldap_data->get_user_data();
    } else {
      $userdata = array(
        'user_login' => $user_cas_id,
        'user_password' => substr( md5( uniqid( microtime( ))), 0, 8 ),
        'user_email' => $user_cas_id . '@' . $this->options['email_suffix'],
        'role' => $this->options['defaultrole'],
      );
    }
    
    if(!$userdata['user_email']) {
      $userdata['user_email'] = $userdata['user_login']. '@' .$this->options['email_suffix'];
    }

    $user_id = wp_insert_user($userdata);
    $user = get_userdata($user_id);
    
    if (!$user_id || !$user) {
      $errors['registerfail'] = sprintf(__('<strong>ERROR</strong>: The login system couldn\'t register you in the local database. Please contact the <a href="mailto:%s">webmaster</a> !'), get_option('admin_email'));
      return NULL;
    } else {
      wp_new_user_notification($user_id, $user_pass);
      return $user_id;
    }
  }
  
  public function add_style() {
    wp_register_style( 'zeta-multiple-login', plugins_url('style.css', __FILE__) );
    wp_enqueue_style( 'zeta-multiple-login' );
  }
  
  //Editor tag: [multiple-login]
  public function login_chooser_tag($atts) {
    //Don't display login options if already logged in
    if(is_user_logged_in()) {
      return '<a href="'. wp_logout_url() . '" title="Logga ut">Logga ut</a>';
    }
  
    if(!isset($atts['type'])) {
      $atts['type'] = 'text';
    }
    
    $login_url = wp_login_url($_GET['redirect_to']) . ((isset($_GET['redirect_to']))?'&':'?') . 'authtype=';
  
    $result = '';
    switch($atts['type']) {
    case 'text':
      //Print out authentication options
      $result .= '<ul>';
      foreach(self::$authtypes as $type => $name) {
        $result .= '<li><a href="'. $login_url . $type .'">'. $name .'</a></li>';
      }
      $result .= '</ul>';
      break;
    case 'image':
      //Print out authentication options
      foreach(self::$authtypes as $type => $name) {
        $result .= '<a href="'. $login_url . $type .'"><img class="zeta-multiple-login-image" src="'. plugins_url( 'img/' . $type . '.png' , __FILE__ ) .'"></a>';
      }
    
    }
    
    return $result;
  }
  
  //Add authentication type field to standard WP login form.
  public function login_form() {
    echo '<input type="hidden" name="authtype" value="wp">';
  }
  
  //CAS related functions
  public function validCASPath() {
    return !empty($this->options['phpcas_path']) && file_exists($this->options['phpcas_path']);
  }
  
  public function getCASPath() {
    return $this->options['phpcas_path'];
  }
  
  public function ConfigureCAS() {
    $this->cas_valid = true;
    
    phpCAS::client($this->options['cas_version'], 
      $this->options['cas_host'], 
      intval($this->options['cas_port']), 
      $this->options['cas_path']);
    phpCAS::setNoCasServerValidation();
  }
  
  public function isCASValid() {
    return $this->cas_valid;
  }
}
?>
<?php
$options = new ZetaMultipleLoginSettings();
$ZetaMultipleLogin = new ZetaMultipleLogin($options);
$ZetaMultipleLoginAdmin = new ZetaMultipleLoginAdmin($options);
if($ZetaMultipleLogin->validCASPath()) {
  require_once $ZetaMultipleLogin->getCASPath();
  $ZetaMultipleLogin->ConfigureCAS();
}
?>