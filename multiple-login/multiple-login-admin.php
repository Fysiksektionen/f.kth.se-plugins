<?php
class ZetaMultipleLoginAdmin {
  private static $casversions = array('1.0', '2.0');
  private $options;

  public function __construct($options) {
    $this->options = $options->get_options();
  
    //Add admin actions
    if(is_admin()) {
      add_action( 'admin_menu', array(&$this, 'admin_menu' ) );
      add_action( 'admin_init', array(&$this, 'admin_init'));
    }
  }

   //Add entry in admin menu
  public function  admin_menu() {
    add_options_page( 'Multiple login options', 'Multiple login', 'manage_options', 'zeta-multiple-login', array(&$this, 'plugin_options') );
  }
  
  //Register settings, sections and fields
  public function admin_init() {
    add_settings_section(
      'basic_settings',
      'Basic settings',
      array($this, 'print_section_basic_settings'),
      'zeta-multiple-login'
    );
      
    add_settings_field(
      'basic_settings_login_chooser', 
      'Login chooser page ID', 
      array($this, 'print_setting_login_chooser'), 
      'zeta-multiple-login',
      'basic_settings'			
    );
    
    add_settings_field(
      'basic_settings_phpcas_path', 
      'Full path to phpCAS', 
      array($this, 'print_setting_phpcas_path'), 
      'zeta-multiple-login',
      'basic_settings'			
    );
    
    add_settings_field(
      'basic_settings_defaultrole', 
      'New users default role', 
      array($this, 'print_setting_defaultrole'), 
      'zeta-multiple-login',
      'basic_settings'			
    );
    
    add_settings_field(
      'basic_settings_email_suffix', 
      'New users email suffix', 
      array($this, 'print_setting_email_suffix'), 
      'zeta-multiple-login',
      'basic_settings'			
    );
    
    add_settings_section(
      'cas_settings',
      'CAS settings',
      array($this, 'print_section_cas_settings'),
      'zeta-multiple-login'
    );
    
    add_settings_field(
      'basic_settings_cas_host', 
      'CAS host', 
      array($this, 'print_setting_cas_host'), 
      'zeta-multiple-login',
      'cas_settings'			
    );
    
    add_settings_field(
      'basic_settings_cas_port', 
      'CAS port', 
      array($this, 'print_setting_cas_port'), 
      'zeta-multiple-login',
      'cas_settings'			
    );
    
    add_settings_field(
      'basic_settings_cas_path', 
      'CAS path', 
      array($this, 'print_setting_cas_path'), 
      'zeta-multiple-login',
      'cas_settings'			
    );
    
    add_settings_field(
      'basic_settings_cas_version', 
      'CAS version', 
      array($this, 'print_setting_cas_version'), 
      'zeta-multiple-login',
      'cas_settings'			
    );
    
    add_settings_section(
      'ldap_settings',
      'LDAP settings',
      array($this, 'print_section_ldap_settings'),
      'zeta-multiple-login'
    );
    
    add_settings_field(
      'basic_settings_ldap_host', 
      'LDAP host', 
      array($this, 'print_setting_ldap_host'), 
      'zeta-multiple-login',
      'ldap_settings'			
    );
    
    add_settings_field(
      'basic_settings_ldap_port', 
      'LDAP port', 
      array($this, 'print_setting_ldap_port'), 
      'zeta-multiple-login',
      'ldap_settings'			
    );
    
    add_settings_field(
      'basic_settings_ldap_useradd', 
      'Add new users?', 
      array($this, 'print_setting_ldap_useradd'), 
      'zeta-multiple-login',
      'ldap_settings'			
    );
    
    add_settings_field(
      'basic_settings_ldap_basedn', 
      'Base DN', 
      array($this, 'print_setting_ldap_basedn'), 
      'zeta-multiple-login',
      'ldap_settings'			
    );
    
    register_setting('zeta_multiple_login', 'zeta_multiple_login', array(&$this, 'check_options'));
  }
  
  //Validate basic options
  public function check_options($input) {
    if(!is_numeric($input['login_chooser']) || !get_page($input['login_chooser'])) {
      $input['login_chooser'] = '';
    }
    if(!file_exists($input['phpcas_path'])) {
      if($input['phpcas_path'] != '') {
        $input['phpcas_path'] = $this->options['phpcas_path'];
      }
    }
    if(!in_array($input['cas_version'], self::$casversions)) {
      $input['cas_version'] = self::$casversions[0];
    }

    $input['ldap_adduser'] = ($input['ldap_adduser'] != '0');
  
    return $input;
  }
  
  //Basic settings section
  public function print_section_basic_settings() {
    echo 'Basic settings for the plugin';
  }
  
  public function print_setting_login_chooser() {
    ?><input type="text" name="zeta_multiple_login[login_chooser]" value="<? echo $this->options['login_chooser'];?>" /><?php
  }
  
  public function print_setting_phpcas_path() {
    ?><input type="text" name="zeta_multiple_login[phpcas_path]" value="<? echo $this->options['phpcas_path'];?>" /><?php
  }
  
  public function print_setting_defaultrole() {
    global $wp_roles;
    ?>
    <select name="zeta_multiple_login[defaultrole]">
     <?php
      foreach($wp_roles->get_names() as $name => $role) {
        ?><option value="<?php echo $name; ?>" <?php echo ($this->options['defaultrole'] == $name)?'selected="selected"':''; ?>><?php echo $role; ?></option><?php
      }
     ?>
    </select>
    <?php
  }
  
  public function print_setting_email_suffix() {
    ?><input type="text" name="zeta_multiple_login[email_suffix]" value="<? echo $this->options['email_suffix'];?>" /><?php
  }
  
  //CAS settings section
  public function print_section_cas_settings() {
    echo 'CAS settings for the plugin';
  }
  
  public function print_setting_cas_host() {
    ?><input type="text" name="zeta_multiple_login[cas_host]" value="<? echo $this->options['cas_host'];?>" /><?php
  }
  
  public function print_setting_cas_port() {
    ?><input type="text" name="zeta_multiple_login[cas_port]" value="<? echo $this->options['cas_port'];?>" /><?php
  }
  
  public function print_setting_cas_path() {
    ?><input type="text" name="zeta_multiple_login[cas_path]" value="<? echo $this->options['cas_path'];?>" /><?php
  }
  
  public function print_setting_cas_version() {
    ?>
    <select name="zeta_multiple_login[cas_version]">
      <option value="2.0" <?php echo ($this->options['cas_version'] == '2.0')?'selected="selected"':''; ?>>2.0</option>
      <option value="1.0" <?php echo ($this->options['cas_version'] == '1.0')?'selected="selected"':''; ?>>1.0</option>
    </select>
    <?php
  }
  
  //LDAP settings section
  public function print_section_ldap_settings() {
    echo 'LDAP settings for the plugin';
  }
  
  public function print_setting_ldap_host() {
    ?><input type="text" name="zeta_multiple_login[ldap_host]" value="<? echo $this->options['ldap_host'];?>" /><?php
  }
  
  public function print_setting_ldap_port() {
    ?><input type="text" name="zeta_multiple_login[ldap_port]" value="<? echo $this->options['ldap_port'];?>" /><?php
  }
  
  public function print_setting_ldap_useradd() {
    ?>
    <input type="radio" name="zeta_multiple_login[ldap_adduser]" value="1" <?php echo ($this->options['ldap_adduser'] === true)?'checked="checked"':''; ?>>Yes
    <input type="radio" name="zeta_multiple_login[ldap_adduser]" value="0" <?php echo ($this->options['ldap_adduser'] === false)?'checked="checked"':''; ?>>No
    <?php
  }
  
  public function print_setting_ldap_basedn() {
    ?><input type="text" name="zeta_multiple_login[ldap_basedn]" value="<? echo $this->options['ldap_basedn'];?>" /><?php
  }
  
  //Display settings page
  public function plugin_options() {
    ?>
    <div class="wrap">
      <?php screen_icon(); ?>
      <h2>Settings</h2>			
      <form method="post" action="options.php">
        <?php // This prints out all hidden setting fields
        settings_fields('zeta_multiple_login');	
        do_settings_sections('zeta-multiple-login');
        ?>
        <?php submit_button(); ?>
      </form>
    </div>
    <?php
  }
}
?>