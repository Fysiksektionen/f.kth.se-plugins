<?php
require_once "class-gasquereg-admin.php";
$gasqueregAdmin = new GasqueregAdmin();
function gasquereg_add_admin_menu() {
	add_menu_page('Gasque-formulär', 'Gasque-formulär', 'manage_options', 'gasquereg', 'gasquereg_menu_page');
}
//Manages ordinairy pages
function gasquereg_menu_page() {
	global $gasqueregAdmin;
	switch($_GET['action']) {
		case 'new':
		case 'edit':
			if(isset($_POST['saveForm'])) {
				if($gasqueregAdmin->saveForm() > 0) echo '<div class="updated"><p>Dina ändringar har sparats!</p></div>';
			}
			if(!empty($gasqueregAdmin->$error_message)) echo '<p><em>'.$gasqueregAdmin->error_message.'</em></p>';
			if($_GET['message'] == '1') echo '<div class="updated"><p>Det nya formuläret har sparats!</p></div>';
			if(isset($_GET['form'])) $gasqueregAdmin->printExistingForm((int)$_GET['form']);
			else $gasqueregAdmin->printNewForm();
			break;
		case 'answers':
			$gasqueregAdmin->showAnswers();
			break;
		case 'save_new':
			//Oups, should not end up here as this will have been handled by admin_redirect. An error must have occured!
			echo '<p><em>'.$gasqueregAdmin->error_message.'</em></p>';
			break;
		case 'view':
		case 'list':
		default:
			$gasqueregAdmin->listForms();
	}
}
//Manages requests that should or could be redirected
function gasquereg_admin_redirect() {
	global $gasqueregAdmin;
	if($_GET['page'] != 'gasquereg') return;
	switch($_GET['action']) {
		case 'save_new':
			$newId = $gasqueregAdmin->saveForm();
			if($newId > 0) {
				wp_redirect('?page=gasquereg&action=edit&form='.$newId.'&message=1');
				exit();
			}
			break;
	}
}

function my_enqueue($hook) {
    //if( 'gasquereg.php' != $hook )
        //return;
		
	//jQuery is enqued by wordpress automatically. However, jquery-ui appearantly has to be enqued explicitly.
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-button');
	wp_enqueue_script('jquery-ui-sortable');
	wp_enqueue_script('jquery-ui-widget');
	wp_enqueue_style('jquery-ui-default-theme', 'http://code.jquery.com/ui/1.10.0/themes/base/jquery-ui.css');
	
    wp_enqueue_script( 'gasqueRegCreateFormJS', plugins_url('createForm.js', __FILE__),array(),false,true);
	
	wp_enqueue_style('gasqueRegCreateFormCSS', plugins_url('createForm.css', __FILE__));
}
add_action( 'admin_menu', 'gasquereg_add_admin_menu' );
add_action('wp_loaded','gasquereg_admin_redirect');
add_action( 'admin_enqueue_scripts', 'my_enqueue' );
?>