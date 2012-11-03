<?php
/*
Plugin Name: Captain Typekit
Plugin URI: http://captaintheme.com/plugins/typekit/
Description: Easily add Typekit to your Wordpress Site.
Author: Captain Theme
Author URI: http://captaintheme.com
Version: 1.0
Text Domain: cttypekit
License: GNU GPL V2
*/


// Load textdomain
function cttypekit_load_textdomain() {
  load_plugin_textdomain( 'cttypekit', false, dirname( plugin_basename( __FILE__ ) . '/languages/' ) ); 
}
add_action( 'plugins_loaded', 'cttypekit_load_textdomain' );


function cttypekit_embed_code()
{
	$cttypekit_options = get_option( 'cttypekit_options' );
	if ( $cttypekit_options['cttypekit_id'] != '' ) {
		ob_start();
	?>
		<!-- Default Typekit Embed Code (not asynchronous) --> 
		<script type="text/javascript" src="//use.typekit.net/<?php echo $cttypekit_options['cttypekit_id']; ?>.js"></script>
		<script type="text/javascript">try{Typekit.load();}catch(e){}</script>
	<?php
		echo ob_get_clean();
	}
}
add_action( 'wp_head', 'cttypekit_embed_code' );


// display settings link on plugin page
function cttypekit_action_links( $links, $file )
{
	if ( $file == plugin_basename( __FILE__ ) ) {
		$cttypekit_links = '<a href="'. get_admin_url() .'options-general.php?page=captain-typekit/captain-typekit.php">'. __( 'Settings', 'cttypekit' ) .'</a>';
		array_unshift( $links, $cttypekit_links );
	}
	return $links;
}
add_filter( 'plugin_action_links', 'cttypekit_action_links', 10, 2 );


// remove plugin settings after deletion
function cttypekit_delete_plugin_options()
{
	delete_option( 'cttypekit_options' );
}
register_uninstall_hook( __FILE__, 'cttypekit_delete_plugin_options' );


// define default settings
function cttypekit_add_defaults()
{
	$tmp = get_option( 'cttypekit_options' );
	if ( !is_array( $tmp ) ) {
		$arr = array( 'cttypekit_id' => '' );
		update_option( 'cttypekit_options', $arr );
	}
}
register_activation_hook( __FILE__, 'cttypekit_add_defaults' );


// whitelist settings
function cttypekit_init()
{
	register_setting( 'cttypekit_options', 'cttypekit_options', 'cttypekit_validate_options' );
}
add_action( 'admin_init', 'cttypekit_init' );


// sanitize and validate input
function cttypekit_validate_options( $input )
{
	$input['cttypekit_id'] = wp_filter_nohtml_kses( $input['cttypekit_id'] );
	return $input;
}


// add the options page
function cttypekit_add_options_page()
{
	add_options_page( __( 'Captain Typekit', 'cttypekit' ), __( 'Captain Typekit', 'cttypekit' ), 'manage_options', __FILE__, 'cttypekit_render_form' );
}
add_action( 'admin_menu', 'cttypekit_add_options_page' );


// create the options page
function cttypekit_render_form()
{
	ob_start();
	?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2><?php _e( 'Captain Typekit Settings', 'cttypekit' ) ?></h2>
		<p><?php printf( __( 'Enter the Typekit Kit ID for your Typekit kit below. Need Help? View %sCaptain Typekit Documentation%s.', 'cttypekit' ), '<a href="' . esc_url( 'http://captaintheme.com/docs/captain-typekit-documentation' ) . '">', '</a>' ); ?></p>

		<form method="post" action="options.php">
			<?php settings_fields( 'cttypekit_options' ); ?>
			<?php $cttypekit_options = get_option( 'cttypekit_options' ); ?>

			<table class="form-table">
				<tr>
					<th scope="row"><label class="description" for="cttypekit_options[cttypekit_id]"><?php _e( 'Typekit Kit ID', 'cttypekit' ) ?></label></th>
					<td><input type="text" size="20" maxlength="20" name="cttypekit_options[cttypekit_id]" value="<?php echo $cttypekit_options['cttypekit_id']; ?>" /></td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e( 'Save Settings', 'cttypekit' ) ?>" />
			</p>
		</form>
	
	<?php if ( $cttypekit_options['cttypekit_id'] != '' ) {
		
		screen_icon( 'themes' ); ?>
		<h2><?php _e( 'Kit Fonts Used', 'cttypekit' ) ?></h2>
		
		
		<p><?php printf( __( 'In the table below you can find all the fonts contained in your Typekit Kit. You can use the %sFont Family CSS Value%s in your CSS, like so:', 'cttypekit' ), '<strong>', '</strong>' ); ?></p>
		
		<pre>
		h1 {
			font-family: "proxima-nova", sans-serif;
		}
		</pre>
		
		<p><?php printf( __( 'Where %sproxima-nova%s is the Font Family CSS Value, ', 'cttypekit' ), '<code>', '</code>' ); ?> <?php printf( __( 'and %ssans-serif%s is the fallback font for older browsers (it depends on the font classification what this value is).', 'cttypekit' ), '<code>', '</code>' ); ?></p>
		
		<p><?php printf( __( 'In the %sVariation/Weights%s column you\'ll find all the different Weights & Variations you\'ve added to your Kit. You can use these in your CSS, like so:', 'cttypekit' ), '<strong>', '</strong>' ); ?></p>
		
		<pre>
		p {
			font-family: "adelle", serif;
			font-weight: 500;
			font-style: italic;
		}
		</pre>
		
		<p><?php printf( __( 'Forgot what the font looked like? Just view the font on Typekit by visitting the font\'s %sURL%s!', 'cttypekit' ), '<strong>', '</strong>' ); ?></p>
		
		
		<?php
		$kit = $cttypekit_options['cttypekit_id'];
		$json = file_get_contents( 'http://typekit.com/api/v1/json/kits/' . $kit . '/published' );
		$kits = json_decode( $json );
		$fonts = array(); ?>
		
		<table class="widefat">
		<thead>
			<tr>
				<th>Font</th>
				<th>Font Family CSS Value</th>
				<th>Variations/Weights</th>
				<th>URL</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th>Font</th>
				<th>Font Family CSS Value</th>
				<th>Variations/Weights</th>
				<th>URL</th>
			</tr>
		</tfoot>
		<tbody>
		
		<?php
		// Need to remove the strong/code html and target with Table CSS Styles
		foreach ($kits->kit->families AS $fontFamily)
		{
			echo '<tr><td><strong>';
			
			echo $fontFamily->name;
			
			echo '</strong></td><td><code>';
			
			echo $fontFamily->slug;
			
			echo '</code></td><td>';
			
			$variations = $fontFamily->variations;
			$italic = __( 'Italic', 'cttypekit' );
			
			// Dear Developers reading the following. I am SURE there is a better way to do the following, but at the time of writing this I couldn't think of it (especially due to be NOT REALLY being a plugin developer. I would love for you to let me know a better way. Better yet, make a pull request on the GitHub Repo for it. PS. I'm thinking like another foreach statement within the first one? With conditionals for stuff like Italic/Bold/etc.? Something like a switch is needed. Anyway, I'll worry about that real soon!
			
			foreach ( $variations as $variation => $value ){
				if ( $value == 'n3' ) {
					echo '300';
					if ( $value == 'n3' && 'i3' ) {
						echo ' <em>+ ' . $italic . '</em>';
					}
					echo '<br />';
				} elseif ( $value == 'n4' ) {
					echo '400';
					if ( $value == 'n4' && 'i4' ) {
						echo ' <em>+ ' . $italic . '</em>';
					}
					echo '<br />';
				} elseif ( $value == 'n5' ) {
					echo '500';
					if ( $value == 'n5' && 'i5' ) {
						echo ' <em>+ ' . $italic . '</em>';
					}
					echo '<br />';
				} elseif ( $value == 'n6' ) {
					echo '<strong>600';
					if ( $value == 'n6' && 'i6' ) {
						echo ' <em>+ ' . $italic . '</em>';
					}
					echo '</strong><br />';
				} elseif ( $value == 'n7' ) {
					echo '<strong>700';
					if ( $value == 'n7' && 'i7' ) {
						echo ' <em>+ ' . $italic . '</em>';
					}
					echo '</strong><br />';
				} elseif ( $value == 'n8' ) {
					echo '<strong>800';
					if ( $value == 'n8' && 'i8' ) {
						echo ' <em>+ ' . $italic . '</em>';
					}
					echo '</strong><br />';
				} elseif ( $value == 'n9' ) {
					echo '<strong>900';
					if ( $value == 'n9' && 'i9' ) {
						echo ' <em>+ ' . $italic . '</em>';
					}
					echo '</strong><br />';
				}
			}
			echo '</td><td>';
			
			echo '<a href="http://typekit.com/fonts/' . $fontFamily->slug . '">';
			_e( 'View on Typekit', 'cttypekit' );
			echo '</a></td></tr>';
			
		}
		
		?>
		
		</tbody>
		
		</table>		
	
	<?php } ?>
		
	</div>
<?php
	echo ob_get_clean();
}

// Omit closing PHP tag baby!