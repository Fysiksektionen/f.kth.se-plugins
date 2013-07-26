<?php
require "class-list-of-forms.php";
require "class-table-of-answers.php";
class GasqueregAdmin {
	public $error_message = "";
	function printExistingForm($formId) {
		global $wpdb;
		
		$data = $wpdb->get_results("SELECT id,tag,description,type FROM ".$wpdb->prefix."gasquereg_form_elements WHERE form = ".$formId. " ORDER BY order_in_form",ARRAY_A);
		//Pass the elements to be printed by jQuery
		wp_localize_script( 'gasqueRegCreateFormJS', 'gasquereg', array('oldElements' => $data) );
		$title = $wpdb->get_var("SELECT title FROM ".$wpdb->prefix."gasquereg_forms WHERE id = ".$formId);
		if($wpdb->num_rows<1) return $this->error('Kunde inte hitta formuläret.');
		echo '
		<div class="wrap"><h1>Redigera formulär</h1>
		<form action="?page='.$_GET['page'].'&action=edit&form='.$formId.'" method="post">
			<div id="titlediv">
				<div id="titlewrap">
					<label id="title-prompt-text" class="screen-reader-text" for="title">Formulärtitel</label>
					<input id="title" type="text" autocomplete="off" value="'.$title.'" size="30" name="title">
				</div>
			</div>
			<ul id="listOfFormElements"></ul>
			<button id="addButton" class="button">Nytt element</button>
			<input type="submit" name="saveForm" id="saveButton" value="Spara" class="button">
		</form></div>';
	}
	function printNewForm() {
		global $wpdb;
		
		//Pass no elements (an empty array()) to be printed by jQuery
		wp_localize_script( 'gasqueRegCreateFormJS', 'gasquereg', array('oldElements' => array()) );
		echo '
		<div class="wrap"><h1>Skapa ett nytt formulär</h1>
		<form action="?page='.$_GET['page'].'&action=save_new" method="post">
			<div id="titlediv">
				<div id="titlewrap">
					<label id="title-prompt-text" class="screen-reader-text" for="title">Formulärtitel</label>
					<input id="title" type="text" autocomplete="off" size="30" name="title" placeholder="Formulärtitel">
				</div>
			</div>
			<ul id="listOfFormElements"></ul>
			<button id="addButton" class="button">Nytt element</button>
			<input type="submit" name="saveForm" id="saveButton" value="Spara" class="button">
		</form></div>';
	}
	function saveForm() {
		global $wpdb;
		$numberOfFormElements = count($_POST['descr']);
		//echo 'Sparar '.$numberOfFormElements.' element...<br>';
		if($numberOfFormElements<=0) {
			$this->error_message = "Formuläret måste ha minst ett element";
			return -1;
		}
		$formsTableName = $wpdb->prefix.'gasquereg_forms';
		$formElementsTableName = $wpdb->prefix.'gasquereg_form_elements';
		$current_user = wp_get_current_user();
		
		if(isset($_GET['form'])) {
			$wpdb->update($formsTableName,array('title'=>$_POST['title']),array('id'=>$_GET['form']));
			/*if($wpdb->num_rows<1) {
				echo '<p><em>Det har uppstått ett fel, kunde inte spara!</em></p>';
				return;
			}*/
			$formId = (int)$_GET['form'];
		} else {
			$wpdb->insert($formsTableName,array('title'=>$_POST['title'],'createdBy'=>$current_user->ID));
			$formId = $wpdb->insert_id;
		}
		$wpdb->delete($formElementsTableName,array('form'=>$formId));
		for($i=0;$i<$numberOfFormElements;$i++) {
			$toInsert = array('form'=>$formId,'description'=>$_POST['descr'][$i],'tag'=>$_POST['tag'][$i],'type'=>$_POST['type'][$i],'order_in_form'=>$i);
			if($_POST['elemId'][$i] > 0) $toInsert['id'] = $_POST['elemId'][$i];
			//TODO: All elements should be gathered into a single query
			$wpdb->insert( $formElementsTableName,$toInsert);//This escapes the strings automatically, right?
		}
		return $formId;
	}
	function listForms() {
		global $wpdb;
		$list = new List_Of_Forms();
		//Fetch, prepare, sort, and filter the data
		$list->prepare_items();
		echo '<div class="wrap"><h2>Registreringsformulär<a class="add-new-h2" href="?page='.$_GET['page'].'&action=new">Nytt</a></h2>';
		echo '<form id="gasquereg-forms-filter" method="get">';
		echo '<input type="hidden" name="page" value="'.$_REQUEST['page'].'" />';
		$list->display();
		echo '</form></div>';
	}
	function showAnswers() {
		global $wpdb;
		$list = new Table_Of_Answers();
		$formId = (int)$_GET['form'];
		//Fetch, prepare, sort, and filter our data...
		$title = $wpdb->get_var("SELECT title FROM ".$wpdb->prefix."gasquereg_forms WHERE id = ".$formId);
		if($wpdb->num_rows <= 0) {
			echo '<p><em>Ett fel har uppstått, kunde inte hitta formuläret.</em></p>';
			return;
		}
		$list->prepare_items();
		echo '<div class="wrap"><h2>'.$title.'</h2><h3>Inkommna svar</h3>';
		echo '<form id="gasquereg-forms-filter" method="get">';
		echo '<input type="hidden" name="page" value="'.$_REQUEST['page'].'" />';
		$list->display();
		echo '</form></div>';
	}
	function error($msg) {
		$this->error_message = $msg;
		echo '<p><em>'.$msg.'</em></p>';
		return -1;
	}
}
?>