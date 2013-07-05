<?php
class ZetaMultipleLoginSettings {
  private $options;

  public function __construct() {
    //Get options and setup defaults.
    $this->options = get_option('zeta_multiple_login', 
      array('login_chooser' => '',
        'phpcas_path' => '',
        'defaultrole' => '',
        'email_suffix' => '',
        'cas_host' => '',
        'cas_port' => 0,
        'cas_path' => '',
        'ldap_host' => '',
        'ldap_port' => 0,
        'ldap_adduser' => true,
        'ldap_basedn' => ''));
    if(empty($this->options['login_chooser'])) {
      $this->options['login_chooser'] = '';
    }
    if(empty($this->options['phpcas_path'])) {
      $this->options['phpcas_path'] = '';
    }
    if(empty($this->options['defaultrole'])) {
      $this->options['defaultrole'] = 'subscriber';
    }
    if(empty($this->options['email_suffix'])) {
      $this->options['email_suffix'] = 'example.com';
    }
    if(empty($this->options['cas_host'])) {
      $this->options['cas_host'] = 'login.example.com';
    }
    if(empty($this->options['cas_port'])) {
      $this->options['cas_port'] = 443;
    }
    if(empty($this->options['cas_path'])) {
      $this->options['cas_path'] = '/';
    }
    if(empty($this->options['ldap_host'])) {
      $this->options['ldap_host'] = 'ldap.example.com';
    }
    if(empty($this->options['ldap_port'])) {
      $this->options['ldap_port'] = 389;
    }
    if(empty($this->options['ldap_adduser'])) {
      $this->options['ldap_adduser'] = false;  
    }
    if(empty($this->options['ldap_basedn'])) {
      $this->options['ldap_basedn'] = '';
    }
  }
  
  public function get_options() {
    return $this->options;
  }
}
?>