<?php
/*
 * Plugin Name: THS nØllesystem
 * Description: För att hålla koll på schlemet. Fett med fulhakkk...
 * Version: 1
 * Author: Leo Fidjeland
 * */ 
 
global $nollefields;
$nollefields = array(//Vilka egenskaper ska nØllan ha?
		'Namn' => 'textfield',
		'nØllegrupp' => 'grupper',
 		'Telefonnummer' => 'textfield',
		'Allergi' => 'textarea',
		'ICE' => 'textarea',
		'Annan info' => 'textarea'
);
//Vilka av dessa egenskaper ska festfixare kunna se?
global $festfields;
$festfields = array('Namn','nØllegrupp','Telefonnummer','Allergi','biljett','Kommentar');

global $avprickfields;
$avprickfields = array('Namn','biljett','nØllegrupp','Allergi','Kommentar','avprickad');

global $limits;
$limits = array(
	'Lunchpres. Bombardier' => '16 aug 2011 18:30',
	'Lunchpres. Vattenfall' => '16 aug 2011 18:20',
	'Välkomstgasquen' => '17 aug 2011 20:00',
	'nØllebanquetten' => '17 aug 2011 20:00',
	'Vision och karriär med FSN' => '18 aug 2011 13:00',
	'Lunchpres. SI' => '22 aug 2011 16:00',
	'FN-dagen' => '23 aug 2011 16:00',
	'Stugan' => '19 aug 2011 10:00',
	'Kårspexet' => '30 aug 2011 16:00'
);

global $nollegrupper;
$nollegrupper = array("Beeblebrox for President","Borg","DSVG 8.0","Fail","Den Felande Linken","G-Force","Sanningen");

$festtyp = array(
	'spritfest' => array(
		'Alk' => 'biljett',
		'Alkfri' => 'biljett',
		'Nej' => 'biljett',
		'Kommentar' => 'textarea',
		'avprickad' => 'avprick'
	),
	'lunchpres' => array(
		'Ja' => 'biljett',
		'Nej' => 'biljett',
		'Kommentar' => 'textarea',
		'avprickad' => 'avprick'
	)
);

global $nollefester;
$nollefester = array(
	'Övningsgasquen Beeblebrox' => $festtyp['spritfest'],
	'Övningsgasquen Borg' => $festtyp['spritfest'],
	'Övningsgasquen DSVG' => $festtyp['spritfest'],
	'Övningsgasquen Fail' => $festtyp['spritfest'],
	'Övningsgasquen Felande linken' => $festtyp['spritfest'],
	'Övningsgasquen G-Force' => $festtyp['spritfest'],
	'Övningsgasquen Sanningen' => $festtyp['spritfest'],
	'Lunchpres. Bombardier' => $festtyp['lunchpres'],
	'Lunchpres. Vattenfall' => $festtyp['lunchpres'],
	'Välkomstgasquen' => $festtyp['spritfest'],
	'nØllebanquetten' => $festtyp['spritfest'],
	'Vision och karriär med FSN' => $festtyp['lunchpres'],
	'Lunchpres. SI' => $festtyp['lunchpres'],
	'Draquebyggarmiddag 3 drakar' => $festtyp['spritfest'],
	'Draquebyggarmiddag Sanningen & DSVG' => $festtyp['spritfest'],
	'Draquebyggarmiddag Borg & Fail' => $festtyp['spritfest'],
	'FN-dagen' => $festtyp['lunchpres'],
	'Stugan' => $festtyp['spritfest'],	
	'Kårspexet' => $festtyp['lunchpres']
);

global $undantag;
$undantag = array(
	'Övningsgasquen Beeblebrox' => array('Beeblebrox for President'),
	'Övningsgasquen Borg' => array('Borg'),
	'Övningsgasquen DSVG' => array('DSVG 8.0'),
	'Övningsgasquen Fail' => array('Fail'),
	'Övningsgasquen Felande linken' => array('Den Felande Linken'),
	'Övningsgasquen G-Force' => array('G-Force'),
	'Övningsgasquen Sanningen' => array('Sanningen'),
	'Draquebyggarmiddag 3 drakar' => array('Beeblebrox for President','Den Felande Linken','G-Force'),
	'Draquebyggarmiddag Sanningen & DSVG' => array('DSVG 8.0','Sanningen'),
	'Draquebyggarmiddag Borg & Fail' => array('Borg','Fail')
);

function nsys_fire( $atts ) {
	extract( shortcode_atts( array(
		'grupp' => 'settings',
		'fest' => ''
	), $atts ) );

	global $nollegrupper;
	global $nollefester;
	
	?>
	<style>
	.fest-wrapper{
		width:940px;
	}
	.fest-wrapper h2{
		margin: 20px 0;
	}
	
	.nollan-table td.nollan-namn{
		width:134px;
	}
	
	.inte-avprickad tr{
		background-color: #FFD2D2;
	}
	.inte-avprickad tr.nollan-even{
		background-color: #FEB0B0;
	}
	.avprickad tr{
		background-color: #ACEDB6;
	}
	.avprickad tr.nollan-even{
		background-color: #70C67E;
	}
	.nollan-even{
		background-color: #ADDFED;
	}
	#sidebar{
		display: none;
	}
	.fest-table td,
	.fest-table th{
		padding: 5px;
	}
	.ajax-loading{
		display: none;
	}
	.fest-comment-logo{
		background: url(<?php echo get_bloginfo('wpurl').'/wp-content/plugins/THS-nolle/comment.png'; ?>) no-repeat scroll left top transparent;
	    display: block;
	    height: 21px;
	    margin: 0 auto;
	    width: 22px;
	}
	.com-exists{
		background-position: 0 -24px;
	}
	.fest-comment-box{
		position: absolute;
		display: none;
	}
	.nollan-table td{
		width: 60px;
	}
	.nolle-settings td{
		border-bottom: 1px solid #000;
		text-align: left;
	}
	.nollefield{
		float: left;
		margin: 10px;
	}
	.nollesystem-meddelande {
    border: 1px solid;
    margin: 10px 0;
    padding: 10px;
    text-align: center;
    width: 940px;
	}
	.till{
	    background-color: #D9FED9;
	}
	.bort{
		background-color: #FFC5C5;
	}
	#nollesystem{
		text-align: left;
	}
	.nollesystem-switch {
	background: url(<?php echo get_bloginfo('wpurl').'/wp-admin/images/menu-bits.gif'; ?>) no-repeat scroll left -207px transparent;
    float: left;
    height: 25px;
    margin-left: 7px;
    position: absolute;
    width: 20px;
}
	.nolle-settings{
		display: none;
	}
		.fester {
    height: 53px;
    overflow: hidden;
    padding-top: 150px;
    padding-left: 68px;
    width: 940px;
}
	.fester .fest {
    -ms-filter: "progid:DXImageTransform.Microsoft.Matrix(M11=0.70710678, M12=-0.70710678, M21=0.70710678, M22=0.70710678,sizingMethod='auto expand')";
	filter: progid:DXImageTransform.Microsoft.Matrix(M11=0.70710678, M12=-0.70710678, M21=0.70710678, M22=0.70710678,sizingMethod='auto expand');
	-moz-transform:  matrix(0.70710678, 0.70710678, -0.70710678, 0.70710678, 0, 0);
	-webkit-transform:  matrix(0.70710678, 0.70710678, -0.70710678, 0.70710678, 0, 0);
	-o-transform:  matrix(0.70710678, 0.70710678, -0.70710678, 0.70710678, 0, 0);
    border-bottom: 1px solid black;
    float: left;
    height: 21px;
    margin-right: -35px;
    width: 100px;
	}
		
		.festnamn {
    float: right;
    text-align: right;
    width: 500px;
}
	</style>
	<script type="text/javascript">
		jQuery(document).ready( function($) {
			jQuery('.nollesystem-switch').click(function(){
				jQuery(this).parent().next().slideToggle();			
			});
		});
	</script>
	<?php
	
	if($fest){
		$festnr = intval($fest);
		if(!($festnr > count($nollefester) || $festnr < 0)){
			$count = 1;
			foreach($nollefester as $festnamn => $typ){
				if($count == $festnr){
					$fest = $festnamn;
				}
				$count++;
			}
			nsys_fest($fest);
			return;
		}else{
			return 'Felaktigt val av fest. Specificera ett nummer mellan 1 och '.count($nollefester);
		}
	}
	
	if($grupp == 'settings'){
		nsys_settings();
		return;
	}
	if($grupp == 'alla'){
		foreach($nollegrupper as $grupp){
			nsys_grupp($grupp,true);
		}
		return;
	}
	
	$gruppnr = intval($grupp);
	if(!($gruppnr > count($nollegrupper) || $gruppnr < 0)){
		$grupp = $nollegrupper[$gruppnr - 1];
		nsys_grupp($grupp);
		return;
	}else{
		return 'Felaktigt val av nØllegrupp. Specificera ett nummer mellan 1 och '.count($grupper);
	}
	
	return; //borde aldrig ropas
}
add_shortcode( 'nollesystem', 'nsys_fire' );

function nsys_settings(){

	global $nollegrupper;
	global $nollefester;
	global $nollefields;

	if(isset($_POST['nya-nollan'])){
		foreach($_POST as $key => $value){
			if($key != 'nya-nollan'){
				if($value['namn']){//we must have a name
					$user_id = username_exists( sanitize_title($value['namm']) );
					if ( !$user_id ) {
						$random_password = wp_generate_password( 12, false );
						$id = wp_insert_user(array('user_pass' => $random_password, 'user_login' => sanitize_title($value['namn']), 'display_name' => $value['namn'], 'user_email' => $random_password.'@nollan.se'));
						$uppdaterat .= $value['namn'] .', ';
						$nollesystem = array('personligt' => $value);
						update_user_meta($id,'nollesystem',$nollesystem);
						update_user_meta($id,'nollegrupp',$value[sanitize_title('nØllegrupp')]); //for easy selection of nØllan
					}
				}
			}
		}
		echo '<div class="nollesystem-meddelande till">La till nya nØllan: '.$uppdaterat.'</div>';
	}
 

	$nollan = get_users(array(
		'meta_key' => 'nollesystem'
		));
	
	nsys_hantera($nollan,true);
	
	////////////////// ?>
	
	<h3>Lägg till nØllan<span class="nollesystem-switch">&nbsp;</span></h3>
	<div class="nolle-settings">
	
	<form action="<?php the_permalink(); ?>" method="post">
	<table>
	<tbody>
	<?php for($i = 0; $i < 10; $i++): ?>
		<tr>
			<td>
			<?php foreach($nollefields as $field => $type){
				$ren = sanitize_title($field);
				echo "<div class='nollefield'>$field";
					switch($type){
						case 'textarea':
							echo "<br /><textarea name='ny-nollan-$i [$ren]' ></textarea>";
							break;
						case 'checkbox':
							echo "<br /><input type='checkbox' name='ny-nollan-$i [$ren]' value='ja' />";
							break;
						case 'textfield':
							echo "<br /><input type='textfield' name='ny-nollan-$i [$ren]' />";
							break;
						case 'grupper':
							echo "<br /><select name='ny-nollan-$i [$ren]'><option>&nbsp;</option>";
							foreach ($nollegrupper as $grupp){ 
								$ren = sanitize_title($grupp);
								echo "<option value='$ren'>$grupp</option>";
							}
							echo "</select>";
							break;						
					}
					
				echo '</div>';
			} ?>
			
	</tr>
	
	<?php endfor; ?>
	</tbody>
	</table>
	<input type="submit" name="nya-nollan" value="Lägg till nØllan" />
	</form>
	</div>
	
	<h3>Ta bort alla nØllan!<span class="nollesystem-switch">&nbsp;</span></h3>
	<div class="nolle-settings">
	<?php
	if (current_user_can( 'delete_users' )){ ?>
		<a class="delete-post" href="<?php
		$url = '';
		foreach($nollan as $nolle){
			$url .= "&users[]=".$nolle->ID;
		}
		echo get_bloginfo('wpurl').'/wp-admin/users.php?action=remove'.$url.'&_wpnonce='.wp_create_nonce('bulk-users');
		echo '">Ta bort alla användare i nØllesystemet.</a>';
	}else{
		echo 'Du har inte tillräckligt med rättigheter för att ta bort användare, kontakta administratören.';
	} ?>
	</div>
	<?php

	return;
}
function nsys_grupp($grupp,$master = false){

	echo "<h2>$grupp</h2>";
	
	$nollan = get_users(array(
		'meta_key' => 'nollegrupp',
		'meta_value' => sanitize_title($grupp)
		));
		
	nsys_anmalningar($nollan,$grupp,$master);
	nsys_hantera($nollan,$master);
	
}

function nsys_anmalningar($nollan,$grupp,$master = false){
	global $nollefester;
	global $undantag;
	global $limits;
	
	setlocale(LC_TIME,'sv_SE');
	date_default_timezone_set('Europe/Stockholm');
	
	if(isset($_POST['uppdatera-fester'])){
		foreach($_POST as $id => $value){
			if(is_int($id)){
				$nollesystem = (array) get_user_meta($id,'nollesystem',true);
				$nollesystem['fester'] = $value;
				update_user_meta($id,'nollesystem',$nollesystem);
			}
		}
		echo '<div class="nollesystem-meddelande till">Uppdaterade alla nØllan!</div>';
	}

	?>
	<script type="text/javascript">
		jQuery(document).ready( function(){
			jQuery('.fest-comment-button').click( function(){
				jQuery(this).next().toggle();
			});
		});
	</script>
	<div class="fest-wrapper">
	<form id="nollesystem" action="<?php the_permalink(); ?>" method="post">
	<div class="fester">
	<?php foreach($nollefester as $fest => $typ):
			if(is_array($undantag[$fest])){ //Festen har undantag
				if(!in_array($grupp,$undantag[$fest])){continue;} //Festen gäller inte den här gruppen
			} ?>
		<div class="fest"><span class="festnamn"><?php echo $fest; ?><?php if(isset($limits[$fest])){
		 
		 if(date('U') > strtotime($limits[$fest])){
		 	echo ' [STÄNGD]';
		 }
		 //echo '<br /><h5>'.$limits[$fest].'</h5>';
		 } ?></span></div>
	<?php endforeach; ?>
	</div>
	<table class="nollan-table">
	<tbody>
	<?php
	$even = 1;
	foreach($nollan as $nolle):
		$id = $nolle->ID;
		$nollesystem = get_user_meta( $id, 'nollesystem' , true);
		?>
		<tr<?php if($even % 2 == 0){echo ' class="nollan-even"';} ?>>
		<?php echo "<td class='nollan-namn'>".$nolle->display_name."</td>";
		foreach($nollefester as $fest => $festfields){
			if(is_array($undantag[$fest])){ //Festen har undantag
				if(!in_array($grupp,$undantag[$fest])){continue;} //Festen gäller inte den här gruppen
			}
			$ren_fest = sanitize_title($fest);
			echo '<td>';
			foreach($festfields as $field => $type){
					$ren = sanitize_title($field);
					echo "<div class='festfield'>";
					switch($type){
						case 'textarea':
							echo '<a class="fest-comment-button" href="javascript:void(0);" title=""><div class="fest-comment-logo';
							if($nollesystem['fester'][$ren_fest][$ren]){
								echo ' com-exists';
							}
							echo '">&nbsp;</div></a>';
							echo '<div class="fest-comment-box">';
							echo "<textarea name='$id"."[$ren_fest][$ren]' >".$nollesystem['fester'][$ren_fest][$ren]."</textarea>";
							echo '</div>';
							break;
						case 'checkbox':
							echo "<input type='checkbox' name='$id"."[$ren_fest][$ren]' value='ja'";
							if ($nollesystem['fester'][$ren_fest][$ren] == 'ja' ) {
								echo "checked='checked'";
							}
							echo " />";
							echo $field;
							break;
						case 'textfield':

							echo "<input type='textfield' name='$id"."[$ren_fest][$ren]' value='".$nollesystem['fester'][$ren_fest][$ren]."' />";
							echo $field;
							break;
						case 'biljett':
							echo "<input type='radio' name='$id"."[$ren_fest][biljett]' value='$ren'";
							if(	$nollesystem['fester'][$ren_fest]['biljett'] || (isset($limits[$fest]) && (date('U') > strtotime($limits[$fest])) )){
								if(!$master){
									echo ' disabled="disabled"';
								}
							}
							if($nollesystem['fester'][$ren_fest]['biljett'] == $ren){
								echo ' checked="checked" />';
								echo "<input type='hidden' name='$id"."[$ren_fest][biljett]' value='$ren' />";
							}else{
								echo ' />';
							}
							echo $field;
							break;
						case 'avprick':
							echo "<input type='hidden' name='$id"."[$ren_fest][$ren]' value='".$nollesystem['fester'][$ren_fest][$ren]."' />";
							break;
					}
					echo '</div>';
				}
				echo '</td>';
		} ?>
		</tr>
	<?php 
	$even++;
	endforeach; ?>
	</tbody>
	</table>
	<input type="submit" name="uppdatera-fester" id="submit" value="Uppdatera nØllan">
	</form>
	</div>
	<?php

}

function nsys_hantera($nollan,$master = false){

	global $nollefields;
	global $nollegrupper;

	if(isset($_POST['hantera-nollan'])){
		foreach($_POST as $id => $value){
			if(is_int($id)){
				wp_update_user( array( 'ID' => $id, 'display_name' => $value['namn']));
				$nollesystem = (array) get_user_meta($id,'nollesystem',true);
				$nollesystem['personligt'] = $value;
				update_user_meta($id,'nollesystem',$nollesystem);
				update_user_meta($id,'nollegrupp',$nollesystem['personligt'][sanitize_title('nØllegrupp')]); //for easy selection of nØllan
			}
		}
		echo '<div class="nollesystem-meddelande till">Uppdaterade alla nØllan!</div>';
	}
	
	?>
	<h3>Hantera nØllan<span class="nollesystem-switch">&nbsp;</span></h3>
	<div class="nolle-settings">
	<form action="<?php echo get_permalink(); ?>" method="post">
	<table>
	<tbody>
		<?php
		foreach($nollan as $nolle):
		$id = $nolle->ID;
		$nollesystem = (array) get_user_meta($id,'nollesystem',true);

		 ?>
		<tr>
			<td>
			<?php foreach($nollefields as $field => $type){
				$ren = sanitize_title($field);
				echo "<div class='nollefield'>$field";
					switch($type){
						case 'textarea':
							echo "<br /><textarea name='$id"."[$ren]' >".$nollesystem['personligt'][$ren]."</textarea>";
							break;
						case 'checkbox':
							echo "<br /><input type='checkbox' name='$id"."[$ren]' value='ja'";
							if ($nollesystem['personligt'][$ren] == 'ja' ) {
								echo "checked='checked'";
							}
							echo " />";
							break;
						case 'textfield':
							echo "<br /><input type='textfield' name='$id"."[$ren]' value='".$nollesystem['personligt'][$ren]."' />";
							break;
						case 'grupper':
							echo "<br /><select name='$id"."[$ren]'";
							if(!$master){
								echo " disabled='disabled'";
							}
							echo " ><option>&nbsp;</option>";
							foreach ($nollegrupper as $grupp){ 
								$ren_grupp = sanitize_title($grupp);
								echo "<option value='$ren_grupp'";
								if($nollesystem['personligt'][$ren] == $ren_grupp){
									echo " selected='selected'";
								}
								echo " >$grupp</option>";
							}
							echo "</select>";
							if(!$master){
								echo "<input type='hidden' name='$id"."[$ren]' value='".$nollesystem['personligt'][$ren]."' />";
							}
							break;						
					}
				echo '</div>';
			} ?>
		<?php
		if (current_user_can( 'delete_users' )){?>
			<a class="delete-post" href="<?php
			$url = "&users[]=$id";
			echo get_bloginfo('wpurl').'/wp-admin/users.php?action=remove'.$url.'&_wpnonce='.wp_create_nonce('bulk-users');
			echo '">Ta bort nØllan</a>';
		} ?>
		</td>
	</tr>
	
	<?php endforeach; ?>
	
	</tbody>
	</table>
	<input type="submit" name="hantera-nollan" value="Uppdatera" />
	</form>
	</div>
	<hr />
	
	<?php
}

function nsys_fest($fest){
	global $festfields;

	$ren_fest = sanitize_title($fest);

	//börja med att sortera ut alla nØllan som har lämna besked angående festen
	$nollan = get_users(array(
		'meta_key' => 'nollesystem'
		));


	
	$visitors = array();
	foreach($nollan as $nolle){
		$id = $nolle->ID;
		$nollesystem = (array) get_user_meta($id,'nollesystem',true);
		if($nollesystem['fester'][$ren_fest]['biljett']){ //nØllan har svarat!
			$visitors[$id] = $nollesystem;
		}
	}
	?>
	<div class="fest-wrapper">
	<h2><?php echo $fest; ?></h2>
	<form action="<?php the_permalink(); ?>" method="post">
	<input type="submit" value="Avpricknings Mode" name="avprickning" />
	</form>
	<?php 
	if($_POST['avprickning']){
		nsys_avprickning($ren_fest,$visitors);
	}else{
		nsys_festinfo($ren_fest,$visitors);
	}
	
	?>
	</div>

	<?php
	
}

function nsys_festinfo($ren_fest,$visitors){
	global $festfields;
	?>
	<h3>Antal Registrerade svar: <?php echo count($visitors); ?></h3>
	<?php 
	$counters = array();
	foreach($visitors as $nollan){
		$counters[$nollan['fester'][$ren_fest]['biljett']] += 1;
	}
	foreach($counters as $type => $antal){
		echo "<h4>Varav $type: $antal</h4>";
	}
	 ?>
	<table class="fest-table">
	<thead>
		<?php foreach($festfields as $field){
			echo "<th>$field</th>";
		} ?>
	</thead>
	<tbody>
	<?php
	$even = 1;
	foreach($visitors as $id => $nolle):
			?>
			<tr<?php if($even % 2 == 0){ echo ' class="nollan-even"'; } ?>>
				<?php foreach($festfields as $field){
					$ren = sanitize_title($field);
					$info = $nolle['personligt'][$ren];
					if(!$info){
						$info = $nolle['fester'][$ren_fest][$ren];
					}
					echo "<td>".$info."</td>";
				} ?>
			</tr>
			
	<?php 
	$even++;
	endforeach; ?>
	</tbody>
	</table>
	<?php
}

function nsys_avprickning($ren_fest,$visitors){
	global $avprickfields;

?>

	<script type="text/javascript">
		jQuery(document).ready( function(){
			jQuery('.avprickare').live('click', function(){
				jQuery(this).prev().show();
				jQuery(this).hide();
				var id = jQuery(this).attr('name');
				var denna = jQuery(this);
				console.log(jQuery(this).parentsUntil('table').parent().hasClass('avprickad'));
				if(jQuery(this).parentsUntil('table').parent().hasClass('avprickad')){
					var here = 'false';
				}else{
					var here = 'true';
				}
				jQuery.ajax({
					url: "<?php echo plugins_url(); ?>/THS-nolle/avprick-ajax.php",
					cache: false,
					data: "id="+id+'&here='+here,
					type: 'get',
			  		success: function(){
			  			denna.show();
			  			denna.prev().hide();
			    		var flytta = denna.parentsUntil('tr').parent().html();
			    		denna.parentsUntil('tr').parent().slideUp('normal',function(){
			    			jQuery(this).remove();	    			
			    		});
			    		if(here == 'true'){
			    			console.log(jQuery('.avprickad'));
			    			jQuery('.avprickad').append('<tr>'+flytta+'</tr>');
			    		}else{
			    			jQuery('.inte-avprickad').append('<tr>'+flytta+'</tr>');
			    		}
			  		}
				});
			});
		});
	</script>
	<h2 style="text-align:center;">Anmälda nØllan som inte kommit än</h2>
	<table class="fest-table">
	<thead>
		<?php foreach($avprickfields as $field){
			echo "<th>$field</th>";
		} ?>
	</thead>
	<tbody class="inte-avprickad">
	<?php
	$even = 1;
	foreach($visitors as $id => $nolle):
		if($nolle['fester'][$ren_fest]['avprickad']){continue;}
			?>
			<tr<?php if($even % 2 == 0){ echo ' class="nollan-even"'; } ?>>
				<?php foreach($avprickfields as $field){
					$ren = sanitize_title($field);
					$info = $nolle['personligt'][$ren];
					if(!$info){
						$info = $nolle['fester'][$ren_fest][$ren];
					}
					switch($field){
						case 'avprickad':
							echo "<td><img alt='' class='ajax-loading' src='".get_bloginfo('wpurl')."/wp-admin/images/wpspin_light.gif'><input type='button' class='avprickare' name='$id&fest=$ren_fest' value='nØllan är här!' /></td>";
							break;
						default:
							echo "<td>".$info."</td>";
					}
				} ?>
			</tr>
			
	<?php 
	$even++;
	endforeach; ?>
	</tbody>
	</table>
	
	<h2 style="text-align:center;">Avprickade nØllan</h2>
	<table class="fest-table">
	<thead>
		<?php foreach($avprickfields as $field){
			echo "<th>$field</th>";
		} ?>
	</thead>
	<tbody class="avprickad">
	<?php
	$even = 1;
	foreach($visitors as $id => $nolle):
		if(!$nolle['fester'][$ren_fest]['avprickad']){continue;}
			?>
			<tr<?php if($even % 2 == 0){ echo ' class="nollan-even"'; } ?>>
				<?php foreach($avprickfields as $field){
					$ren = sanitize_title($field);
					$info = $nolle['personligt'][$ren];
					if(!$info){
						$info = $nolle['fester'][$ren_fest][$ren];
					}
					switch($field){
						case 'avprickad':
							echo "<td><img alt='' class='ajax-loading' src='".get_bloginfo('wpurl')."/wp-admin/images/wpspin_light.gif'><input type='button' class='avprickare' name='$id&fest=$ren_fest' value='inte här…' /></td>";
							break;
						default:
							echo "<td>".$info."</td>";
					}
				} ?>
			</tr>
			
	<?php 
	$even++;
	endforeach; ?>
	</tbody>
	</table>
	<?php

}
 
 ?>