<?php
	
	function createNewForm() {
		/*if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}*/
		global $wpdb;
		
		if(isset($_GET['form'])) $data = $wpdb->get_results("SELECT id,tag,description,type FROM ".$wpdb->prefix."gasquereg_form_elements WHERE form = ".((int)$_GET['form']). " ORDER BY order_in_form",ARRAY_A);
		else $data = array();
		wp_localize_script( 'gasqueRegCreateFormJS', 'gasquereg', array('oldElements' => $data) );
		if(isset($_GET['form'])) {
			$title = $wpdb->get_var("SELECT title FROM ".$wpdb->prefix."gasquereg_forms WHERE id = ".((int)$_GET['form']));
			if($wpdb->num_rows<1) {
				echo '<p><em>Kunde inte hitta det formuläret</em></p>';
				return;
			}
		} else $title = "";
		echo '	<h1>Skapa ett nytt formulär</h1> 
				<form action="?page='.$_GET['page'].'&action=edit'.(isset($_GET['form'])?'&form='.$_GET['form']:'').'" method="post">
				<label for="formTitle">Formulärtitel</label>
				<input type="text" name="title" id="formTitle" value="'.$title.'">
				 <ul id="listOfFormElements"></ul>
				 <button id="addButton">Nytt element</button><input type="submit" name="saveForm" id="saveButton" value="Spara">
				 </form>';
	}
	function saveNewForm() {
		global $wpdb;
		$numberOfFormElements = count($_POST['descr']);
		//echo 'Sparar '.$numberOfFormElements.' element...<br>';
		if($numberOfFormElements<=0) {
			return 'Formuläret måste ha minst ett element.';
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
		return '';
	}
?>