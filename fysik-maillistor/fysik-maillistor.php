<?php
/*
 * Plugin Name: Fysiksektionens maillistor
 * Description: Möjliggör redigering av maillistor på AFS från Wordpress.
 * Version: 1.0
 * Author: Joar Bagge, Oliver Gäfvert och flera
 */

// TODO: kolla rättigheter när man sparar!


// Lägg till saker i menyn.
add_action('admin_menu', 'add_fysik_maillistor_menu');


/* Lägger till maillistor i menyn. */
function add_fysik_maillistor_menu() {
  add_submenu_page('users.php', 'Maillistor (lista)', 'Maillistor (lista)', 'edit_themes', 'fysik_maillistor_list', 'fysik_maillistor_list');
  add_submenu_page('users.php', 'Maillistor (redigera)', 'Maillistor (redigera)', 'edit_themes', 'fysik_maillistor_edit', 'fysik_maillistor_edit');
}


require_once('all-maillists.php');


/* Denna sida tillåter redigering av en maillista. */
function fysik_maillistor_edit() {
  if ($_POST['save']) {
    // Vi sparar den inskickade maillistan.
    $maillist_id = $_POST['maillist'];
    $info = get_maillist_info($maillist_id);
    if (!$info) {
      return;
    }
    $text = $_POST['text'];
    //write_maillist($info['file'], $text);
    $saved = true;
  } else {
    // Vi läser in önskad maillista.
    $maillist_id = $_GET['maillist'];
    $info = get_maillist_info($maillist_id);
    if (!$info) {
      return;
    }
    // Läs in maillistan från AFS
    $status = 0;
    $output = fetch_maillist($info['file'], &$status);
    $text = '';
    foreach ($output as $line) {
      $text = $text . $line . "\n";
    }
    $saved = false;
  }
  $text = htmlspecialchars($text)

?>
<div class="wrap">
  <h2>Redigerar maillistan <?php echo($info['name']); ?></h2>
<?php
  if ($status != 0) {
    echo("<p><̄b>Ett fel uppstod när maillistan skulle läsas in!</b></p>\n");
    echo("<pre>$text</pre>");
    return;
  }
  if ($saved) {
    echo("<p><b>Maillistan har sparats.</b></p>\n");
  }
?>
  <p>Skriv en mailadress per rad. Glöm inte att spara när du är klar.</p>
  <form id="edit-maillist" action="/wp-admin/users.php?page=fysik_maillistor_edit" method="post">
    <input type="hidden" name="maillist" value="<?php echo($info['id']); ?>" />
    <div>
      <textarea name="text" id="text" class="code" rows="20" cols="50"><?php echo($text); ?></textarea>
    </div>
    <p class="submit">
      <input type="submit" name="save" id="save" class="button-primary" value="Spara" />
    </p>
  </form>
</div>
<?php
}


/* Returnerar information om en maillista.
 *
 * @param $maillist_id ID:et för maillistan i SQL-tabellen.
 * @returns En array som innehåller information om maillistan i
 * nycklarna 'id', 'name', 'file' och 'group'.
 */
function get_maillist_info($maillist_id) {
  global $wpdb;
  $query = "SELECT * FROM ".$wpdb->prefix."maillists WHERE id=$maillist_id";
  $row = $wpdb->get_row($query);
  if ($row == null) {
    return null;
  }
  $info = array(
    'id' => $maillist_id,
    'name' => $row->name,
    'file' => '/afs/nada.kth.se/misc/info/fsekt/maillistor/' . $row->path,
    'group' => $row->group,
  );
  return $info;
}


/* Returns true if the user can edit the given maillist.
 *
 * @param $maillist_id ID:et för maillistan i SQL-tabellen.
 * @returns true or false.
 */
function check_user_can_edit($maillist_id) {
  return true;
}


/* Hämtar innehållet från en maillistefil på AFS.
 *
 * @param $file Absolut sökväg till maillistefilen.
 * @param &$status Kommer sättas till exit statusen från kauth.
 *        Om detta är nollskilt bör man anta att det är ett
 *        felmeddelande snarare än en maillista som returneras.
 * @returns Maillistan som text.
 */
function fetch_maillist($file, &$status) {
  $get_content_cmd = 'cat ' . $file;
  $auth_cmd = '/usr/heimdal/bin/kauth --keytab=/etc/fsekt.keytab fsektweb ';
  $command = $auth_cmd . $get_content_cmd . ' 2>&1';

  $output = array();
  exec($command, &$output, &$status);

  return $output;
}


/* Skriver en maillistefil till AFS.
 *
 * @param $file Absolut sökväg till maillistefilen.
 * @param $text Maillistan som text.
 */
function write_maillist($file, $text) {
  // TODO: Vi skulle vilja returnera status för kommandot så man
  // ser om sparningen lyckades.
  // TODO: Kanske vill man ha en array med strängar snarare än
  // text som input.
  $put_content_cmd = 'tee ' . $file;
  $auth_cmd = '/usr/heimdal/bin/kauth --keytab=/etc/fsekt.keytab fsektweb ';
  $command = $auth_cmd . $put_content_cmd;

  $handle = popen($command, 'w');
  fwrite($handle, $text);
  pclose($handle);
}

?>
