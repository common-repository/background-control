<?php
/*
Plugin Name: Background Control
Plugin URI: http://www.swedishboy.dk/wordpress/background-control/
Description: A plugin to control your blogs '&lt;body&gt;' background. With built in uploading of images.
Version: 1.0.5
Author: Johan Str&ouml;m
Author URI: http://www.swedishboy.dk/
*/


// this is the function that outputs the background as a style tag in the <head> 
if ( ! defined( 'WP_CONTENT_URL' ) )
      define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
      define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
      
function bg_control_header() {

$options = get_option('background_control_configuration');

$bg_dir = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)).'bg/';

if(isset($options)) {
	$pos1 = $options['left'];
	$pos2 = $options['top'];
	//if(is_numeric(substr($pos
	
	$bg_attributes = $options['repeat']." ".$options['left']." ".$options['top']." ".$options['attachment'];
	
	if($options['bg_mode']==1) $bg = "url('".$bg_dir.$options['bg_file']."') ".$bg_attributes;
	else if($options['bg_mode']==2) $bg = "url('".$options['bg_url']."') ".$bg_attributes;
	else $bg="";
	
	echo '
<style>
<!--
body {background: '.$options['bg_col']." ".$bg.' ;}
-->
</style>';
}

}
// This is the function that outputs our configuration page	

function bg_control_conf() {
		if ( function_exists('current_user_can') && !current_user_can('manage_options') )
			die(__('Cheatin&#8217; uh?'));

		$bg_dir = WP_PLUGIN_DIR.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)).'bg/';
		
		function write_check($d) {
			if(is_writable($d)) return true;
			else {
				if(chmod($d, 0777)) return true;
				else return false;				
			}
		}
		$dir_ok=write_check($bg_dir);
		if($dir_ok==false) $error_msg[]="To use file uploading, please make the directory 'background-control/bg/' writeable";

		$options = get_option('background_control_configuration');
		if ( !is_array($options) )
			$options = array('top'=>'top', 'left'=>'left', 'bg_col'=>'transparent', 'bg_url' => null, 'repeat' => 'repeat', 'attachment' => 'scroll', 'bg_file'=>null, 'bg_mode'=>3);
		if ( $_POST['bg-control-submit'] ) {

			if($file = $_FILES['bg_file']['name']) {
				$type = $_FILES['bg_file']['type'];
				$valid_types = array('image/jpg', 'image/gif', 'image/png','image/jpeg','image/pjpeg');
				if(in_array($type,$valid_types)) {
					if (move_uploaded_file($_FILES["bg_file"]['tmp_name'], $bg_dir.$file))
						$options['bg_file'] = $file;
					else
						$error_msg[]="Upload of $file to $bg_dir failed";
	
				}else{
					$error_msg[]="Failed to detect a supported file format";
					$file = null;
				}
			}else{
				$options['bg_file']	= isset($_POST['bg_file']) ? $_POST['bg_file'] : null;
			}

			// Remember to sanitize and format use input appropriately.
			$options['top']			=	$_POST['top'];			
			$options['left']		=	$_POST['left'];
			$options['bg_col']		= 	$_POST['bg_col'];
			$options['bg_url']		= 	strip_tags(stripslashes($_POST['bg_url']));			
			$options['repeat']		=	$_POST['repeat'];
			$options['attachment']	=	$_POST['attachment'];
			$options['bg_mode']		= 	$file ? 1 : $_POST['bg_mode'];


			update_option('background_control_configuration', $options);
		}
		
		if($_POST['bgc-delete-submit']) {
			$delete_file=$bg_dir.$_POST['delete'];
			if(file_exists($delete_file)) 
				unlink($delete_file);
			else 
				$error_msg[]="Failed deleting ".$_POST['delete'];

			$options['bg_mode']=3;
			update_option('background_control_configuration', $options);
				
		}

		// Be sure you format your options to be valid HTML attributes.
		$bg_url = htmlspecialchars($options['bg_url'], ENT_QUOTES);
	?>
		<div class="wrap">
		<div id="icon-options-general" class="icon32"><br /></div>
		<h2>Background Control</h2>
<?php
	if(!is_dir($bg_dir)) die("<font color='red'><strong>Please reinstall Background Control. $bg_dir doesn't exist</strong></span>");

	if(isset($options['bg_file']) && !is_readable($bg_dir.$options['bg_file'])) $error_msg[]='Your uploaded images are for some reason not readable. Please fix this.';

	if(isset($error_msg)) {
		foreach($error_msg as $e) {
		echo "# <font color=red><strong>".$e."</strong></font><br />";
		}
	}
?>
		<div class="narrow">
		<script language="javascript">
		oldcol='<?=$options['bg_col'];?>';
		function checkHex(value){
			return /^#([A-Fa-f0-9]{3}$)|([A-Fa-f0-9]{6}$)/.test(value);
		}
		function in_array(needle) {
		haystack = Array('aqua', 'black', 'blue', 'fuchsia', 'gray', 'green', 'lime', 'maroon', 'navy', 'olive', 'purple', 'red', 'silver', 'teal', 'white', 'yellow', 'transparent');
			 for (key in haystack) {
				if (haystack[key] === needle) {
					return true;
				}
			}
		}
		function validateColor(col) {
			if(in_array(col) || checkHex(col)) {
				document.getElementById('col_error').style.display='none';
				document.getElementById('blob').style.background=col;				
			}else{
				document.getElementById('blob').style.background=oldcol;
				document.getElementById('col_error').style.display='inline';			
			}
		}
		</script>
		<form action="" name="bgc_form" method="post" id="background-control-conf" enctype="multipart/form-data">
		<table class="form-table">
		<tr>
		<th scope="row">
		<font style="font-size: 1.2em;"><strong>Default Settings</strong></font><br />
		Control your document's main background.<br /><br />
		</th>
		</tr>
		</table>
		<table class="form-table">
		<tr>
		 <th scope="row">
		 Background Color
		 </th>
		 <td>
		 <input type="text" size="10" name="bg_col" value="<?=$options['bg_col'];?>" onchange="validateColor(this.value);"> <span id="blob" style="border: 1px solid #555; padding: 1px 6px; background:<?=$options['bg_col'];?>;">&nbsp;</span> <span id="col_error" style="clear: none; display: none; color: red; font-weight: bold;"> << Not a valid color!</span>
		 <span class="description"><br />Hexvalue or valid color name: 
		 <small>aqua, black, blue, fuchsia, gray, green, lime, maroon, navy, olive, purple, red, silver, teal, white, yellow or <a href="javascript:document.bgc_form.bg_col.value='transparent';">transparent</a>.</small></span>
		 </td>
		</tr>
		<tr>
		 <th scope="row">
		 Background Image<br />
		 </th>
		 <td><?php
		 if($dir_ok==true) {
		 echo 'Upload file <input type="file" name="bg_file" style="border: none;">
			 <hr noshade color="#ccc" size=1/>'; 
		 $files = array_diff(scandir($bg_dir), Array( ".", ".." )); 
			 if($files) {
			 echo '<input type="radio" name="bg_mode" value="1"';
			 if($options['bg_mode']==1) echo ' checked';
			 echo '> Uploaded images <select name="bg_file" onclick="document.bgc_form.bg_mode[0].checked=true">';
			 $img_menu;
			 foreach($files as $f) {
			 		$selected = $options['bg_file']==$f ? ' selected' : '';
			 		$img_menu.= '<option'.$selected.'>'.$f.'</option>';
				}
			 echo $img_menu;
			 echo '</select><br />';
			 }
		 }
		 ?>
		 <input type="radio" name="bg_mode" value="2" <?php if($options['bg_mode']==2) echo 'checked';?>>
		 Url <input type="text" name="bg_url" size="45" maxlength="200" value="<?=$bg_url;?>"><br />
		 <input type="radio" name="bg_mode" value="3" <?php if($options['bg_mode']==3) echo 'checked';?>> None <br /><br />
		 <span class="description">
		 <?php
		 if($options['bg_mode']==1)  
		 	echo 'You are using '.$options['bg_file'].' as background';
		 else if($options['bg_mode']==2) 
		 	echo 'You are using '.substr($options['bg_url'],strrpos($options['bg_url'],"/")+1).' as background';
		 else 
		 	echo 'No background image set.';
		 ?>
		 </span>
		 </td>
		</tr>
		<tr>
		 <th scope="row">
		 Background Position<br />
		 </th>
		 <td>
			Top: <input type="text" name="top" size="6" maxlength="6" value="<?=$options['top'];?>"> <span class="description">Y</span><br />
			Left: <input type="text" name="left" size="6" maxlength="6" value="<?=$options['left'];?>"> <span class="description">X</span><br />
		 <span class="description">Either top, bottom, center, left, right or number of pixels. <a href="javascript:document.bgc_form.top.value='top';document.bgc_form.left.value='left';">Revert to default</a></span>
		 </td>
		</tr>		
		<tr>
		 <th scope="row">
		 Background Repeat
		 </th>
		 <td>
		 <select name="repeat">
		 <option <?php if($options['repeat']=='repeat') echo 'selected';?>>repeat</option>
		 <option <?php if($options['repeat']=='repeat-x') echo 'selected';?>>repeat-x</option>
		 <option <?php if($options['repeat']=='repeat-y') echo 'selected';?>>repeat-y</option>
		 <option <?php if($options['repeat']=='no-repeat') echo 'selected';?>>no-repeat</option>
		 </select>
		 </td>
		</tr>
		<tr>
		 <th scope="row">
		 Background Attachment
		 </th>
		 <td colspan="2">
		  <select name="attachment">
		 <option <?php if($options['attachment']=='fixed') echo 'selected';?>>fixed</option>
		 <option <?php if($options['attachment']=='scroll') echo 'selected';?>>scroll</option>
		 </select>
		 <span class="description">Background scrolls with document or stays in fixed position.</span>
		 </td>
		</tr>
		<tr>
		 <td colspan="4" align="right">
			<p class="submit"><input type="submit" name="bg-control-submit" value="<?php _e('Update options &raquo;'); ?>" /></p>
		 </td>
		</tr>
		</table>
		</form>
		<hr noshade color="#999" size=1 />
		<form action="" name="bgc_delete_images" method="post">
		<table class="form-table">
		  <tr>
			 <th scope="row">
			 Delete files
			 </th>
			 <td>
				 <select name="delete">
				 <?=$img_menu;?>
				 </select>
				<span class="submit"><input type="submit" name="bgc-delete-submit" value="Delete selected"></span>
			</td>
 		  </tr>
		</table>
		</form>
	</div>
	</div>
	<?php

}

function bg_control_init() {
	add_action('admin_menu', 'bg_control_config_page');
}

// This is the function that adds a configuration page to settings menu group
function bg_control_config_page() {
	if ( function_exists('add_submenu_page') )
		add_submenu_page('options-general.php', __('Background Configuration'), __('Background Control'), 'manage_options', 'bg-control', 'bg_control_conf');

}

add_action('init', 'bg_control_init');

// Put out the styling inside head tag.
add_action('wp_head', 'bg_control_header');

?>