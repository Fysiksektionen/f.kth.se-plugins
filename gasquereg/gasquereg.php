<?php
/*
Plugin Name: Gasque-forumlär
Plugin URI: http://f.kth.se
Description: Formulärsystem för anmälan till gasquer och andra event.
Version: 0.3
Author: Emil Wärnberg
Author URI: http://f.kth.se/~emilwa
License: Endast fysiksektionen
*/
require "adminpage/admin.php";
add_shortcode('gasque_form','gasque_form_shortcode');
function gasque_form_shortcode($atts) {
	$formId = (int)$atts['id'];
	if(isset($_POST['form_id']) && (int)$_POST['form_id']==(int)$atts['id']) submitAnswer($formId);
	else printForm($formId);
}
function submitAnswer($formId) {
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
function printForm($formId) {
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
	/*echo '<table>';
	echo '<tr><th>Formulär</th><th>Skapat av</th><th>Kod</th></tr>';
	foreach($myrows as $row) {
		echo '<tr><td>'.$row->title.'</td><td>'.$row->creator.'</td><td>[qasque_form id='.$row->formId.']</td></tr>';
	}
	echo '</table>';*/
	
}	
function queueCSS() {
	wp_enqueue_style('gasquereg', plugins_url('gasquereg.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'queueCSS', 100);
?>