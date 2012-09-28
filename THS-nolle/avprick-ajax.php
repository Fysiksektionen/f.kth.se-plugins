<?php 


require_once($_SERVER['DOCUMENT_ROOT'].'/wordpress/wp-config.php');

$nollan = (array) $_GET;

$nollesystem = get_user_meta( $nollan['id'], 'nollesystem' , true);
if($nollan['here'] == 'true'){
	$nollesystem['fester'][$nollan['fest']]['avprickad'] = 'ja';
}else{
	$nollesystem['fester'][$nollan['fest']]['avprickad'] = false;
}
$nollesystem = update_user_meta($nollan['id'],'nollesystem',$nollesystem);

return $nollesystem;

?>