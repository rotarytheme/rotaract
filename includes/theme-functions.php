<?php
/**
 * Rotary theme functions and definitions
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
/*remove the admin bar*/

add_filter('show_admin_bar', '__return_false');
add_filter('wp_nav_menu_items','rotary_add_search_box', 10, 2);
function rotary_add_search_box($items) {
 
ob_start();
        get_search_form();
        $searchform = ob_get_contents();
        ob_end_clean();
 
$items .= '<li class="search">' . $searchform . '</li>';
 
return $items;
}
add_filter( 'wp_nav_menu_items', 'add_home_link', 10, 2 );
function add_home_link($items, $args) {
  
        if (is_front_page())
            $class = 'class="current_page_item homepage"';
        else
            $class = 'class="homepage"';
  
        $homeMenuItem =
                '<li ' . $class . '>' .
                $args->before .
                '<a href="' . home_url( '/' ) . '" title="Home">' .
				
                $args->link_before . '<span class="screen-reader-text">Home</span>' . $args->link_after .'<img src="'. get_template_directory_uri().'/rotary-sass/images/home-icon.png" alt="home" title="home"/></a>' .
                
                $args->after .
                '</li>';
  
        $items = $homeMenuItem . $items;
  
    return $items;
}

/**
 * overwrite default theme stylesheet uri
 * filter stylesheet_uri
 * @see get_stylesheet_uri()
 */
 add_filter('stylesheet_uri','rotary_stylesheet_uri',10,2);
function rotary_stylesheet_uri($stylesheet_uri, $stylesheet_dir_uri){

    return $stylesheet_dir_uri.'/rotary-sass/stylesheets/style.css';
}
/** Tell WordPress to run Rotary_setup() when the 'after_setup_theme' hook is run. */
add_action( 'after_setup_theme', 'rotary_setup' );

if ( ! function_exists( 'rotary_setup' ) ):
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * @since rotary 1.0
 */
function rotary_setup() {
    //support editor style
	add_editor_style();
	// This theme uses post thumbnails
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 130, 130, true);

	// Add default posts and comments RSS feed links to head
	add_theme_support( 'automatic-feed-links' );

	// Make theme available for translation
	// Translations can be filed in the /languages/ directory
	load_theme_textdomain( 'rotary', TEMPLATEPATH . '/languages' );

	$locale = get_locale();
	$locale_file = TEMPLATEPATH . "/languages/$locale.php";
	if ( is_readable( $locale_file ) )
		require_once( $locale_file );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary' => __( 'Primary Navigation', 'rotary' ),
	) );
}
endif;

if ( ! function_exists( 'rotary_menu' ) ):
/**
 * Set our wp_nav_menu() fallback, rotary_menu().
 *
 * @since rotary 1.0
 */
function rotary_menu() {
	$excludepage = get_page_by_title ('Home');
	echo '<nav id="mainmenu" class="menu-main-container"><ul id="menu-main" class="menu"><li><a href="'.get_bloginfo('url').'">Home</a></li>';
	wp_list_pages('title_li=&exclude='.$excludepage->ID);
	echo '</ul></nav>';
}
endif;
//content filter for tags
//add_filter('the_content','rotary_add_tags_to_title');
//function rotary_add_tags_to_title($content) {
//	return $content;
//}

//shortcodes
add_action( 'init', 'rotary_register_shortcodes');
function rotary_register_shortcodes(){
   add_shortcode('rotary-reveille-header', 'rotary_reveille_header_function');
}
function rotary_reveille_header_function($atts, $content = null) {
	extract( shortcode_atts( array(
		'id' => 'inthisissue',
		'class' => 'sectionheader',
	), $atts ) );
	$content = rotary_parse_shortcode_content( $content ); ?>
    <div class="sectioncontainer">
		<div id="<?php echo $id ?>" class="<?php echo $class;?>">
           <div class="sectioncontent">
     		<?php echo $content; ?>
           </div> 
    	</div>
    </div>    
<?php }
function rotary_parse_shortcode_content( $content ) {

    $content = do_shortcode( shortcode_unautop( $content ) ); 
   $content = preg_replace('#^<\/p>|^<br \/>|<p>$#', '', $content);
   $content = str_replace ('<p></p>', '', $content);
   
	return $content;
}
/**
 * Remove inline styles printed when the gallery shortcode is used.
 *
 * @since rotary HTML5 3.2
 */
add_filter( 'use_default_gallery_style', '__return_false' );

/**
 * @since rotary 1.0
 * @deprecated in rotary HTML5 3.2 for WordPress 3.1
 *
 * @return string The gallery style filter, with the styles themselves removed.
 */
function rotary_remove_gallery_css( $css ) {
	return preg_replace( "#<style type='text/css'>(.*?)</style>#s", '', $css );
}
// Backwards compatibility with WordPress 3.0.
if ( version_compare( $GLOBALS['wp_version'], '3.1', '<' ) )
	add_filter( 'gallery_style', 'rotary_remove_gallery_css' );

if ( ! function_exists( 'rotary_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * @since rotary 1.0
 */
function rotary_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case '' :
	?>
	<article <?php comment_class(); ?> id="comment-<?php comment_ID() ?>">
			<?php echo get_avatar( $comment, 40 ); ?>
            <div>
			<?php printf( __( '%s says:', 'rotary' ), sprintf( '%s', get_comment_author_link() ) ); ?>
		<?php if ( $comment->comment_approved == '0' ) : ?>
			<?php _e( 'Your comment is awaiting moderation.', 'rotary' ); ?>
			<br />
		<?php endif; ?>

		<p><a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
			<?php
				/* translators: 1: date, 2: time */
				printf( __( '%1$s at %2$s', 'rotary' ), get_comment_date(),  get_comment_time() ); ?></a><?php edit_comment_link( __( '(Edit)', 'rotary' ), ' ' );
			?><p>
         </div>
         <div class="commenttop"></div>
         <div class="commenttext">
		<?php comment_text(); ?>
        </div>
		
			<?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>

	<?php
			break;
		case 'pingback'  :
		case 'trackback' :
	?>
	<article <?php comment_class(); ?> id="comment-<?php comment_ID() ?>">
		<p><?php _e( 'Pingback:', 'rotary' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __('(Edit)', 'rotary'), ' ' ); ?></p>
	<?php
			break;
	endswitch;
}
endif;

/**
 * Closes comments and pingbacks with </article> instead of </li>.
 *
 * @since rotary 1.0
 */
function rotary_comment_close() {
	echo '</article>';
}

/**
 * Adjusts the comment_form() input types for HTML5.
 *
 * @since rotary 1.0
 */
function rotary_comment_fields($fields) {
$commenter = wp_get_current_commenter();
$req = get_option( 'require_name_email' );
$aria_req = ( $req ? " aria-required='true'" : '' );
$fields =  array(
	'author' => '<p><label for="author">' . __( 'Name' ) . '</label> ' . ( $req ? '*' : '' ) .
	'<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' /></p>',
	'email'  => '<p><label for="email">' . __( 'Email' ) . '</label> ' . ( $req ? '*' : '' ) .
	'<input id="email" name="email" type="email" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' /></p>',
	'url'    => '<p><label for="url">' . __( 'Website' ) . '</label>' .
	'<input id="url" name="url" type="url" value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" /></p>',
);
return $fields;
}
add_filter('comment_form_default_fields','rotary_comment_fields');


/**
 * Removes the default styles that are packaged with the Recent Comments widget.
 *
 * @updated rotary HTML5 3.2
 */
function rotary_remove_recent_comments_style() {
	add_filter( 'show_recent_comments_widget_style', '__return_false' );
}
add_action( 'widgets_init', 'rotary_remove_recent_comments_style' );

if ( ! function_exists( 'rotary_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post—date/time and author.
 *
 * @since rotary 1.0
 */
function rotary_posted_on() {
	printf( __( 'Posted on <br/>%2$s', 'rotary' ),
		'meta-prep meta-prep-author',
		sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><time datetime="%3$s" pubdate>%4$s</time></a>',
			get_permalink(),
			esc_attr( get_the_time() ),
			get_the_date('Y-m-d'),
			get_the_date('M j, Y')
		)
	);
}
endif;

if ( ! function_exists( 'rotary_posted_in' ) ) :
/**
 * Prints HTML with meta information for the current post (category, tags and permalink).
 *
 * @since rotary 1.0
 */
function rotary_posted_in() {
	// Retrieves tag list of current post, separated by commas.
	echo '<div class="postedin">';
	$tag_list = get_the_tag_list( '', ', ' );
	if ( $tag_list ) {
		$posted_in = __( 'This entry was posted in %1$s and tagged %2$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'rotary' );
	} elseif ( is_object_in_taxonomy( get_post_type(), 'category' ) ) {
		$posted_in = __( 'This entry was posted in %1$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'rotary' );
	} else {
		$posted_in = __( 'Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'rotary' );
	}
	// Prints the string, replacing the placeholders.
	printf(
		$posted_in,
		get_the_category_list( ', ' ),
		$tag_list,
		get_permalink(),
		the_title_attribute( 'echo=0' )
	);
	echo '</div>';
}
endif;
//custom post types for slideshows   
      add_action('init', 'rotary_slides_register');  
      
    function rotary_slides_register() {
		$labels = array(
			'add_new_item' => 'Add Slides Item',
			'edit_item' => 'Edit Slides Item',
			'new_item' => 'New Slides Item',
			'view_item' => 'View Slides Item',
			'search_items' => 'Search Slides'
		);   
  
        $args = array(  
            'label' => __('Slides'),  
			'labels' => $labels,
            'singular_label' => __('Slides Item'),
			'query_var' => true,  
            'public' => true,  
            'show_ui' => true, 
	        'capability_type' => 'post',  
            'hierarchical' => false,  
			'rewrite' => array("slug" => "slides"),
            'supports' => array('title','editor', 'thumbnail', 'excerpt')  
           );  
      
        register_post_type( 'rotary-slides' , $args );  
		// Register custom taxonomy
		$labels_taxo = array(
			'name' => _x('Slides Category', 'post type general name'),
			'all_items' => _x('All Slides', 'all items'),
			'add_new_item' => _x('Add Slides Category', 'adding a new item'),
			'new_item_name' => _x('New Slides Category Name', 'adding a new item'),
		);
		
    }  
 
/*gets the featured post*/

function rotary_get_featured_post(){
	if (post_type_exists( 'rotary-reveille') ) {
		$args = array(	
			'order' => 'ASC',
			'post_type' => 'rotary-reveille',
		);
	}
	else {	
		$args = array(
			'posts_per_page' => 1,
			'category_name' => 'featured',
		);
	}
	
	$query = new WP_Query( $args );
	global $more;
	if ( $query->have_posts() ) : ?>
		<div id="featured">
        <?php  while ( $query->have_posts() ) : $query->the_post(); ?>
         <?php  $more = 0; ?>
		<section class="featuredheader">
        	<h3><?php echo get_theme_mod( 'rotary_home_featured_header', 'Featured' ); ?></h3>
        	<p>by <span><?php echo get_the_author();?></span></p>
        </section>
        <h4><?php the_title(); ?></h4>
        <section class="featuredcontent">
           <?php  if ( has_post_thumbnail() ) { // check if the post has a Post Thumbnail assigned to it.
  				the_post_thumbnail('medium'); ?>
				<div class="hasthumb">
					<?php the_content(); ?>
				</div>
			<?php } 
            else {?>
            	<div class="nothumb">
        			<?php the_content(); ?>
            	</div>
           <?php  } ?>
        </section>
		<?php endwhile; ?>
 		</div>
 		<div id="featuredbottom">
		</div>
    <?php endif;
	// Reset Post Data
	wp_reset_postdata();	
}

/*gets the slide show*/
function rotary_get_slideshow(){
	$args = array(
	'order' => 'ASC',
	'post_type' => 'rotary-slides',
);
$query = new WP_Query( $args );
$count = 0;

if ( $query->have_posts() ) : ?>
	<div id="slideshowcontainer">
		<div id="slideshowleft">
        	<div id="slideshowright">
            	<div id="slideshow">
 <?php  while ( $query->have_posts() ) : $query->the_post();
	if (has_post_thumbnail()) { 
	    echo '<div class="slide'; 
		if ($count > 0) {
			echo ' hide';
		}
	 	echo'">';
	 $count++;
			echo '<div class="slideinfo">';
			the_title('<h2>', '</h2>');
			echo '<p>'.get_the_excerpt().'</p>';
			$slidelink = get_post_meta(get_the_ID(), 'slidelink', true); 
			if ($slidelink) {
				echo '<p><a href="'.$slidelink.'">Keep Reading...</a></p>';
			}
			else {
				echo '<p><a href="'.get_permalink().'">Keep Reading...</a></p>';
			}
	
			edit_post_link( __( 'Edit', 'Rotary' ), '<p>', '</p>' ); 
			echo '</div>'; //end slideinfo
			
			if ($slidelink) { 
				echo '<a href="'.$slidelink.'">';
			}
			else {
				echo '<a href="'.get_permalink().'">';
			}
			the_post_thumbnail('slideshow-size');
			
		echo '</a></div>';  //end the slide
	}
	
  endwhile; ?>
				</div>	<!--end slideshow-->   
            </div>	<!--end slideshowright-->    
		</div>	<!--end slideshowleft-->
   
     	<div id="controls">
     		<a class ="pause" id="playpause" href="#"><span class="play">> Play</span><span class="pause"> > Pause</span></a>
     	<section id="navsection">
     	</section>
        <section id="sharing">
        <a id="shareshare" target="_blank" href="http://sharethis.com/share?title=<?php echo urlencode(get_the_title()) . '&amp;url=' . urlencode(get_permalink());?>">+ Share</a>
        <a id="facebookshare" class="icon-alone" target="_blank" href="https://www.facebook.com/sharer.php?u=<?php echo urlencode(get_permalink()).'&amp;t='.urlencode(get_the_title()); ?>">
  <span class="screen-reader-text">Share on Facebook</span></a>
        <a id="twittershare" class="icon-alone" target="_blank" href="http://twitter.com/?status=<?php echo urlencode(get_permalink()); ?>">
  <span class="screen-reader-text">Share on Twitter</span></a>
        </section>
		</div>	<!--end controls-->  

		    
   </div>	<!--end slideshowcontainer-->   
    
<?php endif;
// Reset Post Data
wp_reset_postdata();

	
}//custom images sizes for slideshow
if ( function_exists( 'add_image_size' ) ) {
	add_image_size( 'slideshow-size', 486, 313, true ); //(cropped)

}
 
add_filter('image_size_names_choose', 'rotary_image_sizes');
function rotary_image_sizes($sizes) {
        $new_sizes = array();
	     
	    $added_sizes = get_intermediate_image_sizes();
	     
	    // $added_sizes is an indexed array, therefore need to convert it
	    // to associative array, using $value for $key and $value
	    foreach( $added_sizes as $key => $value) {
	        $new_sizes[$value] = $value;
	    }
		
	    // This preserves the labels in $sizes, and merges the two arrays
	    $new_sizes = array_merge( $new_sizes, $sizes );
	    return $new_sizes;
}

function rotary_excerpt_length( $length ) {
	return 40;
}
add_filter( 'excerpt_length', 'rotary_excerpt_length', 999 );
function rotary_auto_excerpt_more( $more ) {
	if (is_archive()) {
		return '<a href="'. get_permalink() . '">' .' [&hellip;]</a>';
	}
}
add_filter( 'excerpt_more', 'rotary_auto_excerpt_more' );
//gets the blog title for the current posts page
function rotary_get_blog_title() {
	$blogPage = "Posts";
	$blogID = get_option( 'page_for_posts');
	if ($blogID) {
		$blogPage = get_the_title($blogID);
	}
	return $blogPage;
}
//custom meta box for slides
add_action( 'add_meta_boxes', 'rotary_add_slide_link_metabox');
function rotary_add_slide_link_metabox() {
	add_meta_box( 'slidelink', __( 'Slide Link' ),  'rotary_show_slide_link_metabox', 'rotary-slides', 'normal', 'high' );
}
add_action( 'save_post', 'rotary_save_slide_link_metabox', 10, 2);
function rotary_save_slide_link_metabox($post_id, $post) {
	 if ( !isset( $_POST['rotary_slide_link_nonce'] ) || !wp_verify_nonce( $_POST['rotary_slide_link_nonce'], basename( __FILE__ ) ) )
         return $post_id;
		 
		/* Get the post type object. */
	    $post_type = get_post_type_object( $post->post_type );
	 
	    /* Check if the current user has permission to edit the post. */
	    if ( !current_user_can( $post_type->cap->edit_post, $post_id ) ) {
	        return $post_id;	
		}
		if (!isset($_POST['slidelink'])) {	 
			return $post_id;	
		} 
	    /* Get the meta key. */
    	$meta_key = 'slidelink';	 	    /* Get the meta value of the custom field key. */
	    $meta_value = get_post_meta( $post_id, $meta_key, true );
		$new_meta_value = strip_tags($_POST['slidelink']);
		
		/* If a new meta value was added and there was no previous value, add it. */
	    if ( $new_meta_value && '' == $meta_value )
	        add_post_meta( $post_id, $meta_key, $new_meta_value, true );
	 
	    /* If the new meta value does not match the old value, update it. */
	    elseif ( $new_meta_value && $new_meta_value != $meta_value )
	        update_post_meta( $post_id, $meta_key, $new_meta_value );	 
	    /* If there is no new meta value but an old value exists, delete it. */
	    elseif ( '' == $new_meta_value && $meta_value )
	        delete_post_meta( $post_id, $meta_key, $meta_value );
		 
}
function rotary_show_slide_link_metabox($object) {
	wp_nonce_field( basename( __FILE__ ), 'rotary_slide_link_nonce' );?>
		 <h3>Enter full URL to create a link for your slide</h3>
		 <p><label for="slidelink">Slide Link:<br />
	        <input id="slidelink" type="url" size="20" name="slidelink" value="<?php echo esc_attr( get_post_meta( $object->ID, 'slidelink', true ) ); ?>" /></label></p>
<?php }