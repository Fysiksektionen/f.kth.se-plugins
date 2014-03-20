<?php
add_action( 'show_user_profile', 'show_extra_profile_fields', 1 );
add_action( 'edit_user_profile', 'show_extra_profile_fields', 1 );
 
function show_extra_profile_fields( $user ) { ?>

	<h3>nØllan!</h3>
 
	<table class="form-table">
 
		<tr>
			<th><label for="nollegrupp">nØllegrupp</label></th>
			
			<?php 
			$grupper = get_option('nsys_grupper');
 			$nollegrupp = get_user_meta( $user->ID , 'nollegrupp'); ?>
			<td>
				<select name="nollegrupp" id="nollegrupp">
					<option value="">&nbsp;</option>
					<?php foreach($grupper as $grupp): ?>
					<?php $ren = sanitize_title($grupp); ?>
					<option value="<?php echo $ren; ?>" <?php if (is_array($nollegrupp)) { if (in_array("$ren", $nollegrupp)) { ?>selected="selected"<?php } }?>><?php echo $grupp; ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
 
		<tr>
			<th><label for="nollekost">Allergier och annat till festansvariga</label></th>
			<td>
			<?php $nollekost = get_user_meta( $user->ID , 'nollekost' , true); ?>
				<textarea name="nollekost" id="nollekost" rows="5"><?php echo esc_attr( get_user_meta( $user->ID, 'nollekost', true ) ); ?></textarea>
			</td>			
		</tr>
		
		<tr>
			<th><label for="nollefester">Festanmälan</label></th>
			<td>
			<?php $nollefester = get_user_meta( $user->ID, 'nollefester' , true);
			$fester = get_option('nsys_fester'); ?>
				<ul>
					<?php foreach($fester as $fest): ?>
					<?php $ren = sanitize_title($fest); ?>
					<li><input value="<?php echo $ren; ?>" name="nollefester[]" <?php if (is_array($nollefester)) { if (in_array("$ren", $nollefester)) { ?>checked="checked"<?php } }?> type="checkbox" /> <?php echo $fest; ?></li>
					<?php endforeach; ?>
					</ul>
			</td>			
		</tr>
 
		<tr>
			<th><label for="arton">Under 18 år?</label></th>
			<td>
			<?php $arton = get_user_meta( $user->ID, 'agree' , true); ?>
				<ul>
					<li><input value="ja" name="arton" <?php if ($arton == 'ja' ) { ?>checked="checked"<?php }?> type="checkbox" /> Ja!</li>
				</ul>
			</td>			
		</tr>
 
	</table>
<?php }
 
add_action( 'personal_options_update', 'save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'save_extra_profile_fields' );
 
function save_extra_profile_fields( $user_id ) {
 
	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;
 
	update_user_meta( $user_id, 'nollegrupp', $_POST['nollegrupp'] );
	update_user_meta( $user_id, 'nollekost', $_POST['nollekost'] );
	update_user_meta( $user_id, 'nollefester', $_POST['nollefester'] );
	update_user_meta( $user_id, 'arton', $_POST['arton'] );
}




	if(isset($_POST['active'])){ //inställningar för grupper och fester
		$ta_bort = array_search('Ta Bort',$_POST);
		if($ta_bort){
			$listor = array('nsys_grupper' => $grupper, 'nsys_fester' => $fester); 
			$nya_listor = array();
			foreach($listor as $key => $lista){
				$ny_lista = array();
				foreach($lista as $element){
					if(!($ta_bort == sanitize_title($element))){
						$ny_lista[] = $element;
					}else{
						echo '<div class="nollesystem-meddelande bort">Tog bort '.$element.'</div>';
					}
				}
				$listor[$key] = $ny_lista;
				update_option($key,$ny_lista);
			}
			$grupper = $listor['nsys_grupper'];
			$fester = $listor['nsys_fester'];
		}elseif($ny = $_POST['ny-grupp']){
			$grupper[] = $ny;
			sort($grupper);
			update_option('nsys_grupper',$grupper);
			echo '<div class="nollesystem-meddelande till">La till '.$ny.'</div>';
		}elseif($ny = $_POST['ny-fest']){
			$fester[] = $ny;
			sort($fester);
			update_option('nsys_fester',$fester);
			echo '<div class="nollesystem-meddelande till">La till '.$ny.'</div>';
		}
	}
	
	
	?>
	<form id="nollesystem" action="<?php echo get_permalink(); ?>" method="post">
	<input type="hidden" value="active" name="active" />
	<h3>Hantera nØllegrupper<span class="nollesystem-switch">&nbsp;</span></h3>
	<div class="nolle-settings">
	<ul>
	<?php 
	foreach($grupper as $grupp): ?>
		<li><input type="submit" value="Ta Bort" name="<?php echo sanitize_title($grupp); ?>" /> <?php echo $grupp; ?></li>
	<?php endforeach; ?>
	<li><input type="text" name="ny-grupp" size="30" /><input type="submit" value="Lägg till" name="ny-grupp-knapp" /></li>
	</ul>
	</div>
	<h3>Hantera fester<span class="nollesystem-switch">&nbsp;</span></h3>
	<div class="nolle-settings">
	<ul>
	<?php 
	foreach($fester as $fest): ?>
		<li><input type="submit" value="Ta Bort" name="<?php echo sanitize_title($fest); ?>" /> <?php echo $fest; ?></li>
	<?php endforeach; ?>
	<li><input type="text" name="ny-fest" size="30" /><input type="submit" value="Lägg till" name="ny-fest-knapp" /></li>
	</ul>
	</form>
	</div>	