<?php
function gasquereg_form_shortcode($atts) {
	$formId = (int)$atts['id'];
	if(isset($_POST['form_id']) && (int)$_POST['form_id']==(int)$atts['id']) return gasquereg_submit_answer($formId);
	else return gasquereg_print_form($formId);
}
function gasquereg_submit_answer($formId) {
	global $wpdb;
	$formsTableName = $wpdb->prefix.'gasquereg_forms';
	$formsElementsTableName = $wpdb->prefix.'gasquereg_form_elements';
	$answersTableName = $wpdb->prefix.'gasquereg_answers';
	$answerElementsTableName = $wpdb->prefix.'gasquereg_answer_elements';
	$formOptions = $wpdb->get_row('SELECT * FROM '.$formsTableName.' WHERE id = '.$formId);
	if($wpdb->num_rows <= 0) {
		return '<p><em>Det har uppstått ett fel, kan inte längre hitta formuläret!</em></p>';
	}
	$elements = $wpdb->get_results('SELECT id,type FROM '.$formsElementsTableName.' WHERE form = '.$formId);
	
	//---Check that everything is OK---
	//Check max number of responses, $formOptions->maxNumberReplies == 0 means no limit
	if($formOptions->maxNumberReplies > 0) {
		$num_responses = $wpdb->get_var('SELECT COUNT(*) FROM '.$answersTableName.' WHERE form = '.(int)$formId);
		if($num_responses >= $formOptions->maxNumberReplies) {
			return '<p><em>Formuläret kan tyvärr inte ta emot fler svar.</em></p>';
		}
	}
	//Check max number of responses, $formOptions->maxNumberReplies == 0 means no limit
	if($formOptions->requireLogedIn) {
		if(!is_user_logged_in()) {
			return '<p><em>Du måste vara inloggad för att kunna svara på detta formulär.</em></p>';
		}
	}
	//Check max number of responses per user, and only if the user is logged in
	if($formOptions->maxNumberRepliesPerUser > 0 && is_user_logged_in()) {
		$num_responses_from_user = $wpdb->get_var('SELECT COUNT(*) FROM '.$answersTableName.' WHERE form = '.(int)$formId).' AND user = '.$current_user->ID;
		if($num_responses_from_user >= $formOptions->maxNumberRepliesPerUser) {
			return '<p><em>Formuläret kan tyvärr inte ta emot fler svar från dig.</em></p>';
		}
	}
	
	foreach($elements as $element) {
		if($element->type == "text") {
			$name = 'form_elem'.$element->id;
			if(!isset($_POST[$name]) || empty($_POST[$name])) {
				return '<p><em>Vänligen fyll i svar i samtliga rutor.</em></p>';
			}
		} else if($element->type == "textifcheck") {
			if(isset($_POST['form_checkelem'.$element->id])) {
				$name = 'form_elem'.$element->id;
				if(!isset($_POST[$name]) || empty($_POST[$name])) {
					return '<p><em>Vänligen fyll specifikation av alla dina kryssvar.</em></p>';
				}
			}
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
	return '<p>Tack för ditt svar!</p>';
}
function gasquereg_print_form($formId) {
	global $wpdb;
	$formsTableName = $wpdb->prefix.'gasquereg_forms';
	$formsElementsTableName = $wpdb->prefix.'gasquereg_form_elements';
	$query = 'SELECT title,id FROM '.$formsTableName.' WHERE id='.$formId;
	$formData = $wpdb->get_row($query);
	if($wpdb->num_rows <= 0) {
		return '<p><em>Kunde inte hitta detta formulär.</em></p>';
		//return;
	}
	$elements = $wpdb->get_results('SELECT id,description,type,tag FROM '.$formsElementsTableName.' WHERE form = '.$formId.' ORDER BY order_in_form');
	$formHtml = '<form class="gasquereg_form" method="post">';
	$formHtml .= '<h2 class="gasquereg_title">'.$formData->title.'</h2>';
	$formHtml .= '<input type="hidden" name="form_id" value="'.$formId.'">';
	foreach($elements as $element) {
		$name = 'form_elem'.$element->id;
		switch($element->type) {
			case 'text':
				$formHtml .= '<div class="gasquereg_element_wrapper"><label for="'.$name.'">'.$element->description.'</label><br>';
				$formHtml .= '<input type="text" size="30" id="'.$name.'" name="'.$name.'" value="'.$_POST[$name].'"></div>';
				break;
			case 'textifcheck':
				$descriptions = explode(';',$element->description);			
				$formHtml .= '<div class="gasquereg_element_wrapper"><p><input type="checkbox" name="form_checkelem'.$element->id.'" id="form_checkelem'.$element->id.'" class="condTextSwitch">';
				$formHtml .= '<label for="form_checkelem'.$element->id.'">'.$descriptions[0].'</label></p>';
				$formHtml .= '<div class="condTextBox">'.$descriptions[1].'<br><textarea name="form_elem'.$element->id.'" style="width: 80%"></textarea></div></div>';
				break;
		}
	}
	$formHtml .= '<div class="gasquereg_submit_wrapper"><input type="submit" value="Skicka" class="gasquereg_submit button"></div>';
	$formHtml .= '</form>';
	return $formHtml;
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
	$tableHtml = '<table class="gasquereg_answers_table"><thead><tr>';
	foreach($cols as $col) {
		if(strlen($col->description) > 15) $colHead = substr($col->description,0,12).'...';
		else $colHead = $col->description;
		$tableHtml .= '<td>'.$colHead.'</td>';
	}
	$tableHtml .= '</tr></thead><tbody>';
	$data = queryAndPivotData($formId);
	//print_R($data);
	foreach($data as $row) {
		$tableHtml .= '<tr>';
		foreach($cols as $col) $tableHtml .= '<td>'.$row['form_elem'.$col->id].'</td>';
		$tableHtml .= '</tr>';
	}
	$tableHtml .= '</tbody></table>';
	return $tableHtml;
}
?>