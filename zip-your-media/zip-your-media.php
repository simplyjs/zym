<?php
/**
 * Plugin Name: Zip Your Media
 * Plugin URI: 
 * Description: Download the files from the Media Library in ZIP format.
 * Author: simplyjs
 * Author URI: http://simplyjs.fr/
 * Version: 0.0.1
 * License: GPLv2 or later
 * Text Domain: zym
 * Domain Path: /languages/
 */
 
// NO scripts kiddies
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Sets the plugin path/url.
define( 'ZYM_PATH', plugin_dir_path( __FILE__ ) );
$upload_dir = wp_upload_dir();
$upload_basedir = $upload_dir['basedir'];
define( 'ZYM_UPLOAD_DIR', $upload_basedir.'/zym' );
$upload_baseurl = $upload_dir['baseurl'];
define( 'ZYM_UPLOAD_URL', $upload_baseurl.'/zym' );

// Load textdomain.
load_plugin_textdomain( 'zym', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

if(isset($_GET['zym_delete'])){
	$files = $_GET['zym_delete'];
	foreach($files as $file){
		$filepath = ZYM_UPLOAD_DIR.'/'.$file;
		if(is_file($filepath)){
			unlink($filepath);
		}
	}
}

//enqueue scripts and styles
function zym_scripts() {
	wp_enqueue_style( 'zym-admin', plugins_url( '/css/styles.css', __FILE__ ),'','0.0.1');
	wp_enqueue_script( 'zym-admin', plugins_url( '/js/scripts.js', __FILE__ ),'','0.0.1' );
	$zym_variables = array(
		'url' => plugin_dir_url( __FILE__ ),
	);
	wp_localize_script( 'zym-admin', 'zym', $zym_variables );	
}
add_action( 'admin_init', 'zym_scripts' );


//Add subpage
add_action('admin_menu', 'zym_menu');
function zym_menu() {
    add_submenu_page('upload.php',__( 'Zip Your Media', 'zym' ),	__( 'Zip Your Media', 'zym' ), 'manage_options', 'zym', 'zym_setting');
}

function zym_filesize($bytes, $decimals = 2) {
    $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}

//function for setting
function zym_setting(){
	$curr_page = (isset($_GET['page'])) ? $_GET['page'] : '';
	global $wp_version;
	//Check WordPress Version
	if ( $wp_version < 2.6 && $curr_page == 'zym') {
		echo '<div class="error"><p><strong>';
		echo __( 'This plugin not is supported in current WordPress version. <a href="./update-core.php">Please update the WordPress for version 3.6 or above.</a>', 'zym' );
		echo '</strong></p></div><style type="text/css">p.submit{display:none}</style>';
	}else{
	global $wpdb;
	
	$error = (isset($_GET['error'])) ? $_GET['error'] : '';
	
	$post_types = get_post_types(array('public' => true));
	$mime_types = $wpdb->get_results( "SELECT post_mime_type FROM $wpdb->posts WHERE post_type = 'attachment' GROUP BY post_mime_type", ARRAY_N );
	$html = '<div id="zym">';
	$html .= '<h1>'. __('Zip Your Media', 'zym').' v0.0.1</h1>';
	if($error == 'no-files'){
		$html .= '<div class="error"><p><strong>'.__( 'No files found for this criteria.', 'zym' ).'</strong></p></div>';
	}
	$html .= '<form id="zym_form" action="'.$_SERVER['REQUEST_URI'].'" method="post">';
	$html .= '<h2>'. __('Choose the file types you want to zip :', 'zym').'</h2>';
	$i = 0;
	foreach($mime_types as $mime_type){
		$html .= '<p>
			<input type="checkbox" id="mime_type_'.$i.'" name="mime_type[]" value="'.$mime_type[0].'" checked class="mime"/>
			<label for="mime_type_'.$i.'">'.$mime_type[0].'</label>
		</p>';
		$i++;
	}
	$html .= '<p>
				<input type="submit" class="button button-primary button-large" id="zym_download" value="'. __('Download .zip', 'zym').'" />
				<img style="display:none" class="loading" src="'.get_admin_url().'/images/spinner.gif" alt="" />
			</p>
			</form>';
	
	$files = scandir( ZYM_UPLOAD_DIR, SCANDIR_SORT_DESCENDING );
	
	if(count($files) > 2){
		$css = ' active';
	}else{
		$css = ' inactive';
	}
	
	$html .= '<div class="existing_files'.$css.'">';
	$html .= '<h2>'. __('Download a zip you have already done :', 'zym').'</h2>';
	
	$html .= '<form action="upload.php" method="get">';
	$html .= '	<input type="hidden" name="page" value="zym">';
	$html .= '<div class="tablenav top">
		<div class="alignleft actions bulkactions">
			<input type="submit" id="doaction" class="button action" value="'.__('Delete selected files','zym').'">
		</div>
	</div>';
	$html .= '<table class="wp-list-table widefat fixed striped">';
	$html .= '<thead>
		<tr>
			<td id="cb" class="manage-column column-cb check-column">
				<label class="screen-reader-text" for="cb-select-all-1">Tout sélectionner</label>
				<input id="cb-select-all-1" type="checkbox">
			</td>
			<th class="manage-column column-primary">'. __('Name of the file','zym').'</th>
			<th class="manage-column">'. __('Size of the file','zym').'</th>
		</tr>
	</thead>';
	$html .= '<tbody id="zym_tbody">';
	foreach($files as $f){
		if($f!='.' && $f!='..'){
			$html .= '<tr>
				<th class="check-column"><input type="checkbox" value="'.$f.'" name="zym_delete[]" id="zym_delete[]"></th>
				<td class="has-row-actions">
					<strong><a href="'.ZYM_UPLOAD_URL.'/'.$f.'">'.$f.'</a></strong>
					<div class="row-actions">
						<a href="upload.php?page=zym&zym_delete[]='.$f.'">'.__('delete this file','zym').'</a>
					</div>
				</td>
				<td>'.zym_filesize(filesize(ZYM_UPLOAD_DIR.'/'.$f)).'</td>
			</tr>';
		}
	}
	$html .= '</tbody>';
	$html .= '<tfoot>
		<tr>
			<td id="cb" class="manage-column column-cb check-column">
				<label class="screen-reader-text" for="cb-select-all-2">Tout sélectionner</label><input id="cb-select-all-2" type="checkbox">
			</td>
			<th class="manage-column column-primary">'. __('Name of the file','zym').'</th>
			<th class="manage-column">'. __('Size of the file','zym').'</th>
		</tr>
	</tfoot>';
	$html .= '</table>';
	$html .= '<div class="tablenav bottom">
		<div class="alignleft actions bulkactions">
			<input type="submit" id="doaction" class="button action" value="'.__('Delete selected files','zym').'">
		</div>
	</div>';	
	$html .= '</div>';

	
	
	$html .= '</form>';
	$html .= '</div>';
	echo $html;
	}
}

add_action( 'wp_ajax_zym_download', 'zym_download' );
function zym_download() {
	global $wpdb; // this is how you get access to the database
	$base = dirname(__FILE__);
	$mimes = $_POST['mimes'];
	$date = date('Y-m-d-H\hi\ms\s', time());
	if ( ! file_exists( ZYM_UPLOAD_DIR ) ) {
		wp_mkdir_p( ZYM_UPLOAD_DIR );
	}	
	
	$args = array(
		'post_type' => 'attachment',
		'post_mime_type' => $mimes,
		'posts_per_page' => -1,
		'post_status' => 'any',
		'post_parent' => null
	);
	$posts = get_posts( $args );
	
	$zip = new ZipArchive;
	$res = $zip->open(ZYM_UPLOAD_DIR.'/zym-'.$date.'.zip', ZipArchive::CREATE);
	if ($res === TRUE) {
		foreach($posts as $post){
			$url = wp_get_attachment_url( $post->ID );
			$purl = parse_url($url);
			$path = $purl['path'];
			$zip->addFromString(substr($path,1), file_get_contents($url));
		}
		$zip->close();
		$name = 'zym-'.$date.'.zip';
		$file = array(
			'url' => ZYM_UPLOAD_URL,
			'name' => $name,
			'filesize' => zym_filesize(filesize(ZYM_UPLOAD_DIR.'/'.$name))
		);
		header('Content-type: application/json');
		echo json_encode($file);
	}else{
		echo 'échec';
	}
	wp_die(); // this is required to terminate immediately and return a proper response
}