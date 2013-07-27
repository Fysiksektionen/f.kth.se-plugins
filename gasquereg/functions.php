<?php
function gasquereg_form_shortcode($atts) {
	$formId = (int)$atts['id'];
	if(isset($_POST['form_id']) && (int)$_POST['form_id']==(int)$atts['id']) gasquereg_submit_answer($formId);
	else gasquereg_print_form($formId);
}
function gasquereg_submit_answer($formId) {
	global $wpdb;
	$formsTableName = $wpdb->prefix.'gasquereg_forms';
	$formsElementsTableName = $wpdb->prefix.'gasquereg_form_elements';
	$answersTableName = $wpdb->prefix.'gasquereg_answers';
	$answerElementsTableName = $wpdb->prefix.'gasquereg_answer_elements';
	if($wpdb->get_var('SELECT COUNT(id) FROM '.$formsTableName.' WHERE id = '.$formId) <= 0) {
		echo '<p><em>Det har uppstått ett fel, kan inte längre hitta formuläret!</em></p>';
		return;
	}
	$elements = $wpdb->get_results('SELECT id FROM '.$formsElementsTableName.' WHERE form = '.$formId);
	
	//Check that everything is OK
	foreach($elements as $element) {
		$name = 'form_elem'.$element->id;
		if(!isset($_POST[$name]) || empty($_POST[$name])) {
			echo '<p><em>Vänligen fyll i svar i samtliga rutor.</em></p>';
			return;
		}
	}
	
	//Insert the answer/submission itself.
	$wpdb->insert($answersTableName,array('form' => $formId,'user' => $current_user->ID));
	$answerId = $wpdb->insert_id;
	
	//Insert the answer corresponding to each element
	foreach($elements as $element) {
		$name = 'form_elem'.$element->id;
		$wpdb->insert($answerElementsTableName,array(
			'answer' => $answerId,
			'element' => $element->id,
			'val' => $_POST[$name] //Should be raw according to WP Codex
		));
	}
	echo '<p>Tack för ditt svar!</p>';
}
function gasquereg_print_form($formId) {
	global $wpdb;
	$formsTableName = $wpdb->prefix.'gasquereg_forms';
	$formsElementsTableName = $wpdb->prefix.'gasquereg_form_elements';
	$query = 'SELECT title,id FROM '.$formsTableName.' WHERE id='.$formId;
	$formData = $wpdb->get_row($query);
	if($wpdb->num_rows <= 0) {
		echo '<p><em>Kunde inte hitta detta formulär.</em></p>';
		return;
	}
	$elements = $wpdb->get_results('SELECT id,description,type,tag FROM '.$formsElementsTableName.' WHERE form = '.$formId.' ORDER BY order_in_form');
	echo '<form class="gasquereg_form" method="post">';
	echo '<h2 class="gasquereg_title">'.$formData->title.'</h2>';
	echo '<input type="hidden" name="form_id" value="'.$formId.'">';
	foreach($elements as $element) {
		$name = 'form_elem'.$element->id;
		echo '<div class="gasquereg_element_wrapper"><label for="'.$name.'">'.$element->description.'</label><br>';
		switch($element->type) {
			case 'text':
				echo '<input type="text" size="30" id="'.$name.'" name="'.$name.'" value="'.$_POST[$name].'"></div>';
				break;
		}
	}
	echo '<div class="gasquereg_submit_wrapper"><input type="submit" value="Skicka" class="gasquereg_submit button"></div>';
	echo '</form>';	
}	
function gasquereg_queue_CSS() {
	wp_enqueue_style('gasquereg', plugins_url('gasquereg.css', __FILE__));
}
//TODO: This function is almost a duplicate of member function in Table_Of_Answers, maybe have a common lib?
function queryAndPivotData($formId) {
		global $wpdb;
		$formsTableName = $wpdb->prefix.'gasquereg_forms';
		$formsElementsTableName = $wpdb->prefix.'gasquereg_form_elements';
		$answersTableName = $wpdb->prefix.'gasquereg_answers';
		$answerElementsTableName = $wpdb->prefix.'gasquereg_answer_elements';
		//Get (general) data from answers table
		$answers = $wpdb->get_results('SELECT id AS answerId,user,submitted AS date FROM '.$answersTableName.' WHERE form = '.$formId,ARRAY_A);
		//No answers at all, might as well return an empty array straight away!
		if(count($answers) <= 0) return array();
		//Make the data fit the new data structure
		foreach($answers as $answer) $assoc_data[$answer['answerId']] = $answer;
        
		//Get detailed and actual data from answer elements table
		$query = 'SELECT '
					.$answerElementsTableName.'.val AS val,'
					.$answerElementsTableName.'.element AS elem,'
					.$answersTableName.'.id AS answerId '
					.'FROM '.$answerElementsTableName.','.$answersTableName.' '
					.'WHERE '.$answersTableName.'.id = '.$answerElementsTableName.'.answer '
					.'AND '.$answersTableName.'.form = '.$formId;
		$answer_elems = $wpdb->get_results($query);		
		foreach($answer_elems as $answer_elem) {
			$assoc_data[$answer_elem->answerId]['form_elem'.$answer_elem->elem] = $answer_elem->val;
		}
		//Convert the data from accociative to numbered, i.e. remove id as key.
		return array_values($assoc_data);
	}
function gasquereg_answers_shortcode($atts) {
	global $wpdb;
	$formId = (int)$atts['id'];
	$cols = $wpdb->get_results('SELECT id,description FROM '.$wpdb->prefix.'gasquereg_form_elements WHERE form = '.$formId.' ORDER BY order_in_form');
	echo '<table class="gasquereg_answers_table"><thead><tr>';
	foreach($cols as $col) echo '<td>'.$col->description.'</td>';
	echo '</tr></thead><tbody>';
	$data = queryAndPivotData($formId);
	//print_R($data);
	foreach($data as $row) {
		echo '<tr>';
		foreach($cols as $col) echo '<td>'.$row['form_elem'.$col->id].'</td>';
		echo '</tr>';
	}
	echo '</tbody></table>';
}
?>