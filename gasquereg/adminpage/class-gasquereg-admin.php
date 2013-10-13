<?php
require "class-list-of-forms.php";
require "class-table-of-answers.php";
class GasqueregAdmin {
	public $error_message = "";
	function editPage($formId = -1) {
		global $wpdb;
		add_meta_box("gasquereg_options", "Alternativ", array( &$this, 'options_meta_box' ), 'gasquereq', 'side');
		add_meta_box("gasquereg_category", "Kategori", array( &$this, 'category_meta_box' ), 'gasquereq', 'side');
		
		
		echo '<div class="wrap">';
		if($formId > 0) {
			echo '<h2>Redigera formulär</h2>';
			echo '<form action="?page='.$_GET['page'].'&action=edit&form='.$formId.'" method="post">';
			if($wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."gasquereg_forms WHERE id = ".$formId) < 1)
				return $this->error('Kunde inte hitta formuläret');
			if($wpdb->get_var("SELECT createdBy FROM ".$wpdb->prefix."gasquereg_forms WHERE id = ".$formId) != $current_user->ID && !is_admin())
				return $this->error('Du har inte behörighet att redigera detta formulär.');
			$data = $wpdb->get_results("SELECT id,tag,description,type,is_required,demand_unique FROM ".$wpdb->prefix."gasquereg_form_elements WHERE form = ".$formId. " AND deleted=0 ORDER BY order_in_form",ARRAY_A);
			//Pass the elements to be printed by jQuery
			wp_localize_script( 'gasqueRegCreateFormJS', 'gasquereg', array('oldElements' => $data) );
			$prevForm = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."gasquereg_forms WHERE id = ".$formId);
			if($wpdb->num_rows<1) return $this->error('Kunde inte hitta formuläret.');
		} else {
			wp_localize_script( 'gasqueRegCreateFormJS', 'gasquereg', array('oldElements' => '') );
			$prevForm = (object) array('title' => '');
			echo '<h2>Nytt formulär</h2>';
			echo '<form action="?page='.$_GET['page'].'&action=save_new" method="post">';
		}
		wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
        wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
		echo '
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-'.(1 == get_current_screen()->get_columns() ? '1' : '2').'">
					<div id="post-body-content">
						<div id="titlediv">
							<div id="titlewrap">
								<label id="title-prompt-text" class="screen-reader-text" for="title">Formulärtitel</label>
								<input id="title" type="text" autocomplete="off" value="'.$prevForm->title.'" size="30" name="title" placeholder="Formulärtitel">
							</div>
						</div>
						<ul id="listOfFormElements"></ul>
						<button id="addButton" class="button">Nytt element</button>
						<input type="submit" name="saveForm" id="saveButton" value="Spara" class="button">
					</div>
					<div id="postbox-container-1" class="postbox-container">';
		do_meta_boxes('gasquereq','side',$prevForm);
		echo '			</div>
					<div id="postbox-container-2" class="postbox-container">';
		do_meta_boxes('gasquereq','normal',$prevForm);
		do_meta_boxes('gasquereq','advanced',$prevForm);
		echo '			</div>
				</div>
			</div>
		</form>
		</div>';
		//do_action('object_edit_ui_rs');
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
		$fieldsToPost = array(
					'title'=>$_POST['title'],
					'requireLogedIn'=>isset($_POST['requireLogedIn']),
					'maxNumberReplies'=>$_POST['maxNumberReplies'],
					'maxNumberRepliesPerUser'=>$_POST['maxNumberRepliesPerUser'],
					'allowEdit'=>isset($_POST['allowEdit']),
					'category'=>$_POST['category']
					);
		
		if(isset($_GET['form'])) {
			$wpdb->update($formsTableName,$fieldsToPost,array('id'=>$_GET['form']));
			/*if($wpdb->num_rows<1) {
				echo '<p><em>Det har uppstått ett fel, kunde inte spara!</em></p>';
				return;
			}*/
			$formId = (int)$_GET['form'];
		} else {
			$fieldsToPost['createdBy'] = $current_user->ID;
			$wpdb->insert($formsTableName,$fieldsToPost);
			$formId = $wpdb->insert_id;
		}
		$wpdb->delete($formElementsTableName,array('form'=>$formId));
		for($i=0;$i<$numberOfFormElements;$i++) {
			$toInsert = array('form'=>$formId,'description'=>$_POST['descr'][$i],'tag'=>$_POST['tag'][$i],'type'=>$_POST['type'][$i],'order_in_form'=>$i);
			if($_POST['elemId'][$i] > 0) $toInsert['id'] = $_POST['elemId'][$i];
			if(isset($_POST['deleted'][$i]) && $_POST['deleted'][$i] == 1) $toInsert['deleted'] = 1;
			
			//TODO: There must be a better way of doing this...
			$localId = $_POST['localId'][$i];
			$toInsert['is_required'] = 0;
			$toInsert['demand_unique'] = 0;
			if(isset($_POST['required']) && in_array($localId,$_POST['required'])) $toInsert['is_required'] = 1;
			if(isset($_POST['unique']) && in_array($localId,$_POST['unique'])) $toInsert['demand_unique'] = 1;
			
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
		$attr = $wpdb->get_row("SELECT title,createdBy FROM ".$wpdb->prefix."gasquereg_forms WHERE id = ".$formId);
		if($wpdb->num_rows <= 0) {
			echo '<p><em>Ett fel har uppstått, kunde inte hitta formuläret.</em></p>';
			return;
		}
		if($attr->createdBy != $current_user->ID && !is_admin()) {
			echo '<p><em>Du verkar inte ha behörighet att se svaren till detta formulär.</em></p>';
			return;
		}
		$list->prepare_items();
		echo '<div class="wrap"><h2>'.$attr->title.'</h2><h3>Inkommna svar</h3>';
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
	function options_meta_box($prevForm){
		?>
			<div class="misc-pub-section">
				<input type="checkbox" name="requireLogedIn" id="requireLogedInCheckbox"<?php if($prevForm->requireLogedIn==1) echo' checked'; ?>>
				<label for="requireLogedInCheckbox">Kräv inloggning för svar</label><br>
			</div>
			<div class="misc-pub-section">
				<label for="maxNumberRepliesText">Max</label>
				<input type="number" name="maxNumberReplies" id="maxNumberRepliesText" size="3" value="<?php echo ($prevForm->maxNumberReplies>0)?$prevForm->maxNumberReplies:''; ?>">
				<label for="maxNumberRepliesText">svar totalt</label>
			</div>
			<div class="misc-pub-section">
				<label for="maxNumberRepliesPerUserText">Max</label>
				<input type="number" name="maxNumberRepliesPerUser" id="maxNumberRepliesPerUserText" size="3" value="<?php echo ($prevForm->maxNumberRepliesPerUser>0)?$prevForm->maxNumberRepliesPerUser:''; ?>">
				<label for="maxNumberRepliesPerUserText">svar per användare</label>
			</div>
			<?php /*
			<div class="misc-pub-section">
				<input type="checkbox" name="allowEdit" id="allowEditCheckbox"<?php if($prevForm->allowEdit==1) echo' checked'; ?>>
				<label for="allowEditCheckbox">Låt användare ändra sina svar</label><br>
			</div>
			*/ ?>
		<?php
	}
	function category_meta_box($prevForm){
		echo '<p>För framtida statistik kan en kategori användas:</p>';
		echo '<select name="category" id="categorySelect">';
		$categories = array('Fest','Jobb','Kläder','Undersökning','Övrigt');
		echo '<option value=""><em>Ingen</em></option>';
		foreach($categories as $cat) {
			echo '<option value="'.$cat.'"';
			if($cat == $prevForm->category) echo ' selected';
			echo '>'.$cat.'</option>';
		}
		echo '</select>';
	}
}
?>