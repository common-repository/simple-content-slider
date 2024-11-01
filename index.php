<?php
/*
Plugin Name: Simple Content Slider / Slideshow
Plugin URI: http://araujo.cc/portfolio/simple-content-slider-slideshow/
Description: A responsive content slider and slideshow plug-in for jQuery with features like touch and CSS3 transitions. 
Author: Arthur Araújo
Author URI: http://araujo.cc/
Version: 1.0.2
*/

define( 'scontentslider_POSTYPE', get_option( 'scontentslider_posttype', 'slides' ) );
define( 'scontentslider_SVN', get_option( 'scontentslider_svn', '1' ) );

if( is_admin() ) {
	add_action( 'admin_init', 'scontentslider_custom_settings' );
	add_action( 'admin_menu', 'scontentslider_custom_menu' );
}

add_action( 'init', 'scontentslider_add_posttype_slide' );
function scontentslider_add_posttype_slide() {
	
	$menutitle = get_option( 'scontentslider_menutitle', 'Slideshow' );
	
	if( $menutitle=='' )
		$menutitle = 'Slideshow';
	
	 $labels = array(
    'name' => _x( $menutitle, 'slides'),
    'singular_name' => _x('Slides', 'slides'),
    'add_new' => _x('Add Slide', 'slides'),
    'add_new_item' => __('Add new slide'),
    'edit_item' => __('Editar slide'),
    'new_item' => __('New'),
    'view_item' => __('View slide'),
    'search_items' => __('Search slides'),
    'not_found' =>  __('No slide found'),
    'not_found_in_trash' => __('No slides in trash'),
    'parent_item_colon' => ''
  );

  $supports = array('title', 'editor');

  register_post_type( scontentslider_POSTYPE,
    array(
      'labels' => $labels,
      'public' => true,
      'supports' => $supports,
      'show_in_menu' => true, 
    )
  );
}

function scontentslider_add_scripts() {
	
	# add SlideJS script for Slideshow
	wp_enqueue_script( 'SlidesJS', plugins_url( '/jquery.slides.min.js' , __FILE__ ), array( 'jquery' ), null, true );
	
	# add slideshow configuration
	add_action( 'wp_footer', 'scontentslider_js' );
}

function scontentslider_custom_menu(){

    add_submenu_page( 'edit.php?post_type='.scontentslider_POSTYPE, 'Options', 'Options', 'manage_options', 'scontentslider_options', 'scontentslider_custom_menu_page' );
      
}

function scontentslider_shortcode( $atts ) {
     return the_slideshow( false );
}
add_shortcode('slideshow', 'scontentslider_shortcode');

function scontentslider_js() {
	
	# get the sizes
	$height = get_option( 'scontentslider_max_H', '400' );
	$width  = get_option( 'scontentslider_max_W', '800' );
	
	# return a js error alert
	if( !is_numeric($height) ) {
		echo '<script type="text/javascript">alert("'.__('Content slider has no defined HEIGHT!').'")</script>';
		return;
	}
	
	# return a js error alert
	if( !is_numeric($width) ) {
		echo '<script type="text/javascript">alert("'.__('Content slider has no defined HEIGHT!').'")</script>';
		return;
	}
	
	# defaults
	$navigation = 'navigation: { active: false },';
	$pagination = 'pagination: { active: false },';
	$css  		= '';
	$arrows		= '';
	
	$htmltag   = get_option( 'scontentslider_html5', 1 )? 'section' : 'div';
	$effect    = get_option( 'scontentslider_effect', 'slide' );
	$autoplay  = get_option( 'scontentslider_autoplay', 'true' )=='true'? 'true' : 'false';
	$interval  = (int)get_option( 'scontentslider_interval', '4000' );
	$navoutsize  = (int)get_option( 'scontentslider_navoutside', '0' );
	
	if( get_option( 'scontentslider_css', 'true' )=='true' )
		$css = str_replace("\n", '', '
$("head").append("
<style type=\'text/css\'>
a.slidesjs-previous, a.slidesjs-next { background:url('.plugin_dir_url(__FILE__).'arrows.png) no-repeat; display:block; float:left; height:20px; width:12px; overflow:hidden; line-height:100px; }
a.slidesjs-next { background-position:-18px 0; }
a.slidesjs-next:hover { background-position:-18px -20px; }
a.slidesjs-previous:hover { background-position:0 -20px; }
</style>").append(\'<link rel=\\\'stylesheet\\\' href=\\\''.plugin_dir_url(__FILE__).'slidesjs.css\\\' type=\\\'text/css\\\' />\');');
	
	# navigation
	if( get_option( 'scontentslider_navigation', 'true' )=='true' || get_option( 'scontentslider_navigation', 'true' )=='absolute' )
		$navigation = 'navigation: { effect: "'.$effect.'" },';
	
	# pagination
	if( get_option( 'scontentslider_pagination', 'true' )=='true' )
		$pagination = 'pagination: { effect: "'.$effect.'" },';
	
	if( get_option( 'scontentslider_navigation', 'true' )=='absolute' )
		$arrows = str_replace( "\n", '', 'setInterval(\'
$=jQuery;
o=$(".slidesjs-container").offset();
os='.$navoutsize.';
h=$(".slidesjs-container").height();
h2=$(".slidesjs-next").height();
w=$(".slidesjs-container").width();
w2=$(".slidesjs-next").width();

$(".slidesjs-previous").css({ "position":"absolute", "left":o.left-os, "top": (o.top+(h/2)-(h2/2)) });
$(".slidesjs-next").css({ "position":"absolute", "left":o.left+w-w2+os, "top": (o.top+(h/2)-(h2/2)) });

\', 100);');
	
	# print scripts
	echo '<script type="text/javascript">
jQuery(function($){
	$("'.$htmltag.'.simple-content-slider")
		.css("max-width", "'.$width.'px")
		.slidesjs({
			width: '.$width.',
			height: '.$height.',
			play: {
				active: false,
				interval: '.$interval.',
				auto: '.$autoplay.',
				effect: "'.$effect.'"
			},
			'.$pagination.'
			'.$navigation.'
			effect: { fade: { speed: 600 } }
		});
	'.$css.'
	'.$arrows.'
});
</script>';
}

function scontentslider_custom_settings(){
	register_setting( 'scontentslider_settings', 'scontentslider_html5' );
	register_setting( 'scontentslider_settings', 'scontentslider_menutitle' );
	register_setting( 'scontentslider_settings', 'scontentslider_posttype' );
	register_setting( 'scontentslider_settings', 'scontentslider_max_H' );
	register_setting( 'scontentslider_settings', 'scontentslider_max_W' );
	register_setting( 'scontentslider_settings', 'scontentslider_effect' );
	register_setting( 'scontentslider_settings', 'scontentslider_autoplay' );
	register_setting( 'scontentslider_settings', 'scontentslider_navigation' );
	register_setting( 'scontentslider_settings', 'scontentslider_navoutside' );
	register_setting( 'scontentslider_settings', 'scontentslider_pagination' );
	register_setting( 'scontentslider_settings', 'scontentslider_interval' );
	register_setting( 'scontentslider_settings', 'scontentslider_css' );
}

function scontentslider_custom_menu_page(){ ?>
    
    <div class="wrap">
    
		<?php
			$external_plugin_name = 'Simple Content Slider';
			$external_plugin_url = 'http://araujo.cc/portfolio/simple-content-slider-and-slideshow-plugin-wordpress/';
		?>
		<div style="float:right;width:400px">
			<div style="float:right; margin-top:10px">
				 <iframe src="http://www.facebook.com/plugins/like.php?href=<?php echo urlencode($external_plugin_url) ?>&amp;layout=box_count&amp;show_faces=false&amp;width=450&amp;action=like&amp;font=arial&amp;colorscheme=light&amp;height=21"
					scrolling="no" frameborder="0" style="overflow:hidden; width:90px; height:61px; margin:0 0 0 10px; float:right" allowTransparency="true"></iframe>
					<strong style="line-height:25px;">
						<?php echo __("Do you like <a href=\"{$external_plugin_url}\" target=\"_blank\">{$external_plugin_name}</a> Plugin? "); ?>
					</strong>
			</div>
		</div>

		<div id="icon-options-general" class="icon32"><br></div>
		<h2><?php _e('Slideshow Options') ?></h2>
		
		<?php if( isset($_GET['settings-updated']) ) { ?>
		<div class="updated"> 
			<p><strong><?php _e('Settings saved.') ?></strong></p>
		</div>
		<?php } ?>
		
		<form action="options.php" method="post" class="form-table">
			<?php settings_fields('scontentslider_settings'); ?>
			<table>
				<tr>
					<th>HTML5</th>
					<td>
						<input type="checkbox" name="scontentslider_html5" value="1" <?php if( get_option( 'scontentslider_html5', 1 ) ) echo 'checked' ?> />
						Uses tags &lt;aside&gt; and &lt;session&gt; instead &lt;div&gt;
					</td>
				</tr>
				<tr>
					<th><?php _e('Menu Title') ?></th>
					<td>
						<input  name="scontentslider_menutitle" value="<?php echo get_option( 'scontentslider_menutitle', 'Slideshow' ) ?>" size="25" />
					</td>
				</tr>
				<tr>
					<th><?php _e('Post type') ?></th>
					<td>
						<input  name="scontentslider_posttype" value="<?php echo get_option( 'scontentslider_posttype', scontentslider_POSTYPE ) ?>" size="25" />
					</td>
				</tr>
				<tr>
					<th>Max-width<br /></th>
					<td>
						<input name="scontentslider_max_W" value="<?php echo get_option( 'scontentslider_max_W', '800' ) ?>" size="6" /> px <br />
					</td>
				</tr>
				<tr>
					<th>Max-height<br /></th>
					<td>
						<input name="scontentslider_max_H" value="<?php echo get_option( 'scontentslider_max_H', '300' ) ?>" size="6" /> px
					</td>
				</tr>
				<tr>
					<th><?php _e('Transitions') ?><br /></th>
					<td>
						<label><input type="radio" name="scontentslider_effect" value="slide" <?php echo get_option( 'scontentslider_effect', 'slide' )=='slide'? 'checked="checked"':'' ?> /> Slide </label> <br />
						<label><input type="radio" name="scontentslider_effect" value="fade" <?php echo get_option( 'scontentslider_effect', 'slide' )=='fade'? 'checked="checked"':'' ?> /> Fade </label>
					</td>
				</tr>
				<tr>
					<th><?php _e('Navigation') ?><br /><small><?php _e('Next and previous buttons.') ?></small></th>
					<td>
						<label><input type="radio" name="scontentslider_navigation" value="" <?php echo get_option( 'scontentslider_navigation', 'true' )!='true'? 'checked="checked"':'' ?> /> <?php _e('None') ?></label> <br />
						<label><input type="radio" name="scontentslider_navigation" value="true" <?php echo get_option( 'scontentslider_navigation', 'true' )=='true'? 'checked="checked"':'' ?> /> <?php _e('Normal align') ?></label> <br />
						<label><input type="radio" name="scontentslider_navigation" value="absolute" <?php echo get_option( 'scontentslider_navigation', 'true' )=='absolute'? 'checked="checked"':'' ?> /> <?php _e('Absolute align') ?></label> with <input name="scontentslider_navoutside" value="<?php echo (int)get_option( 'scontentslider_navoutside', '0' ) ?>" size="2" /> <?php _e('px') ?> <?php _e('of outside') ?> </div>
					</td>
				</tr>
				<tr>
					<th><?php _e('Pagination') ?><br /></th>
					<td>
						<label><input type="checkbox" name="scontentslider_pagination" value="true" <?php echo get_option( 'scontentslider_pagination', 'true' )=='true'? 'checked="checked"':'' ?> /> <?php _e('Pagination buttons.') ?> </label>
					</td>
				</tr>
				<tr>
					<th><?php _e('Autoplay') ?><br /></th>
					<td>
						<label><input type="checkbox" name="scontentslider_autoplay" value="true" <?php echo get_option( 'scontentslider_autoplay', 'true' )=='true'? 'checked="checked"':'' ?> /> </label>
					</td>
				</tr>
				<tr>
					<th><?php _e('Interval') ?><br /></th>
					<td>
						<input name="scontentslider_interval" value="<?php echo (int)get_option( 'scontentslider_interval', '4000' ) ?>" size="6" /> <?php _e('milliseconds') ?>
					</td>
				</tr>
				<tr>
					<th><?php _e('Styles') ?><br /></th>
					<td>
						<label><input type="checkbox" name="scontentslider_css" value="true" <?php echo get_option( 'scontentslider_css', 'true' )=='true'? 'checked="checked"':'' ?> /> <?php _e('Include SlidesJS stylesheet.') ?> </label>
					</td>
				</tr>
			</table>
			<p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e('Save changes') ?>"></p>
		</form>
		
    </div>
    
<?php }

function the_contentslider( $echo=true ) {
	return the_slideshow( $echo );
}

function the_slideshow( $echo=true ) {
	
	global $wpdb;
	
	# add jQuery and SlideJS at the footer
	scontentslider_add_scripts();
	
	if( is_string($echo) || is_array($echo) ) {
		$query = new WP_Query( $echo );
		if( $query->have_posts() )
			$slides = $query->posts;
	} else
		# get slides
		$slides = $wpdb->get_results( $wpdb->prepare("
			SELECT post_content FROM $wpdb->posts WHERE post_type='%s' AND post_status='publish' ORDER BY ID ASC
		", scontentslider_POSTYPE));
	
	#print_r($slides);
	
	if( !$slides )
		return false;
	
	# html tags
	$htmltag = get_option( 'scontentslider_html5', 1 )? 'section' : 'div';
	
	# generate slider
	$code = "<$htmltag class=\"simple-content-slider\">";
	
	foreach( $slides as $slide )
		$code .= "<$htmltag>".apply_filters( 'the_content', $slide->post_content )."</$htmltag>";
		
	$code .= "</$htmltag>";
	
	if( $echo!=false )
		echo $code;
	
	return $code;
}
