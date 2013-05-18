<?php
/**
 * Rotary widgets
 *
 * @package WordPress
 * @subpackage Rotaryforeach
 * @since Rotary 1.0
 */
  
 
 /* custom blogroll widget that pulls in the Rotary category. It customizes the code from the default links widget*/

class Rotary_Widget_Links extends WP_Widget {

    function Rotary_Widget_Links() {
            parent::WP_Widget('rotarylinks', $name = 'Rotary Blogroll');
     }
	function widget( $args, $instance ) {
		extract($args, EXTR_SKIP);

		$show_description = false;
		$show_name = isset($instance['name']) ? $instance['name'] : false;
		$show_rating = false;
		$show_images = false;
		$categorize = false;
		$term_id = term_exists( 'Rotary', 'link_category');
		$category = isset($term_id) ? $term_id  : false;
		$orderby = isset( $instance['orderby'] ) ? $instance['orderby'] : 'name';
		$order = $orderby == 'rating' ? 'DESC' : 'ASC';
		$limit = isset( $instance['limit'] ) ? $instance['limit'] : -1;

		$before_widget = preg_replace('/id="[^"]*"/','id="%id"', $before_widget);
		
		if (wp_list_bookmarks()) {
			$bookmarks = wp_list_bookmarks(apply_filters('widget_links_args', array(
				'title_before' => $before_title, 'title_after' => $after_title,
				'category_before' => $before_widget, 'category_after' => $after_widget,
				'show_images' => $show_images, 'show_description' => $show_description,
				'show_name' => false, 'show_rating' => $show_rating,
				'category' => $category, 'class' => 'linkcat widget',
				'orderby' => $orderby, 'order' => $order, 'echo' => false,
				'limit' => $limit,
			)));
			$bookmarks = str_replace('Rotary', 'Links', $bookmarks );
			echo $bookmarks;
		}
	}
	function update( $new_instance, $old_instance ) {
		$new_instance = (array) $new_instance;
		$instance = array( 'images' => 0, 'name' => 0, 'description' => 0, 'rating' => 0 );
		if ($instance) {
			foreach ( $instance as $field => $val ) {
				if ( isset($new_instance[$field]) )
					$instance[$field] = 1;
			}

			$instance['orderby'] = 'name';
			if ( in_array( $new_instance['orderby'], array( 'name', 'rating', 'id', 'rand' ) ) )
				$instance['orderby'] = $new_instance['orderby'];

		//$instance['category'] = intval( $new_instance['category'] );
			$instance['limit'] = ! empty( $new_instance['limit'] ) ? intval( $new_instance['limit'] ) : -1;

			return $instance;
		}
	}

	function form( $instance ) {

		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'images' => true, 'name' => true, 'description' => false, 'rating' => false, 'category' => false, 'orderby' => 'name', 'limit' => -1 ) );
		?>
		<label for="<?php echo $this->get_field_id('orderby'); ?>"><?php _e( 'Sort by:' ); ?></label>
		<select name="<?php echo $this->get_field_name('orderby'); ?>" id="<?php echo $this->get_field_id('orderby'); ?>" class="widefat">
			<option value="name"<?php selected( $instance['orderby'], 'name' ); ?>><?php _e( 'Link title' ); ?></option>
			<option value="rating"<?php selected( $instance['orderby'], 'rating' ); ?>><?php _e( 'Link rating' ); ?></option>
			<option value="id"<?php selected( $instance['orderby'], 'id' ); ?>><?php _e( 'Link ID' ); ?></option>
			<option value="rand"<?php selected( $instance['orderby'], 'rand' ); ?>><?php _e( 'Random' ); ?></option>
		</select>
		</p>
		<p>
		<input class="checkbox" type="checkbox" <?php checked($instance['name'], true) ?> id="<?php echo $this->get_field_id('name'); ?>" name="<?php echo $this->get_field_name('name'); ?>" />
		<label for="<?php echo $this->get_field_id('name'); ?>"><?php _e('Show Link Name'); ?></label><br />
		
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e( 'Number of links to show:' ); ?></label>
		<input id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" type="text" value="<?php echo $limit == -1 ? '' : intval( $limit ); ?>" size="3" />
		</p>
<?php
	}
}
// Run code and init
function rotary_register_rotary_widget_links() {
	register_widget('Rotary_Widget_Links');
}
add_action('widgets_init', 'rotary_register_rotary_widget_links');	

/*  This code is based on the Norman Advanced Archive Widget

	Plugin Name: Norman Advanced Archive Widget
	Plugin URI: http://www.andreasnorman.se/norman-archive-widget
	Description: Norman Advanced Archive Widget is a free replacement for the standard WordPress archive widget. Lots of customization options to satisfy your needs.
	Author: Andreas Norman
	Version: 1.1
	Author URI: http://www.andreasnorman.se
*/

class RotaryArchiveWidget extends WP_Widget {
	
	function RotaryArchiveWidget() {
		parent::WP_Widget(false, $name = 'Rotary Archive Widget');	
	}
	
	function get_years($current_category_id) {
		global $wpdb;
		
		if ($current_category_id) {
	    $where = apply_filters('getarchives_where', "WHERE post_type = 'post' AND post_status = 'publish' AND $wpdb->term_taxonomy.taxonomy = 'category' AND $wpdb->term_taxonomy.term_id IN ($current_category_id)");
			$join = apply_filters('getarchives_join', " INNER JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)");
		} else {
	    $where = apply_filters('getarchives_where', "WHERE post_type = 'post' AND post_status = 'publish'");
			$join = apply_filters('getarchives_join', "");
		}

    $sql = "SELECT DISTINCT YEAR(post_date) AS `year`, count(ID) as posts ";
    $sql .="FROM {$wpdb->posts} {$join} {$where} ";
    $sql .="GROUP BY YEAR(post_date) ORDER BY post_date DESC";

    return $wpdb->get_results($sql);
	}

	function get_months($year, $current_category_id) {
	    global $wpdb;

			if ($current_category_id) {
		    $where = apply_filters('getarchives_where', "WHERE post_type = 'post' AND post_status = 'publish' AND YEAR(post_date) = {$year} AND $wpdb->term_taxonomy.taxonomy = 'category' AND $wpdb->term_taxonomy.term_id IN ($current_category_id)");
				$join = apply_filters('getarchives_join', " INNER JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)");
			} else {
		    $where = apply_filters('getarchives_where', "WHERE post_type = 'post' AND post_status = 'publish' AND YEAR(post_date) = {$year}");
		    $join = apply_filters('getarchives_join', "");
			}

	    $sql = "SELECT DISTINCT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) as posts ";
	    $sql .="FROM {$wpdb->posts} {$join} {$where} ";
	    $sql.= "GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date DESC";

	    return $wpdb->get_results($sql);
	}

	function get_posts($year, $month, $current_category_id) {
    global $wpdb;

    if (empty($year) || empty($month))
        return null;

		if ($current_category_id) {
	    $where = apply_filters('getarchives_where', "WHERE post_type = 'post' AND post_status = 'publish' AND YEAR(post_date) = {$year} AND MONTH(post_date) = {$month} AND $wpdb->term_taxonomy.taxonomy = 'category' AND $wpdb->term_taxonomy.term_id IN ($current_category_id)");
			$join = apply_filters('getarchives_join', " INNER JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)");
		} else {
	    $where = apply_filters('getarchives_where', "WHERE post_type = 'post' AND post_status = 'publish' AND YEAR(post_date) = {$year} AND MONTH(post_date) = {$month}");
	    $join = apply_filters('getarchives_join', "");
		}

    $sql = "SELECT ID, post_title, post_name FROM {$wpdb->posts} ";
    $sql .="$join $where ORDER BY post_date DESC";

    return $wpdb->get_results($sql);
	}	

	function widget($args, $instance) {
		global $wp_locale;
    extract( $args );
		$plugin_url = plugins_url ( plugin_basename ( dirname ( __FILE__ ) ) );
		$showcount = false;
		$linkcounter = false;
		$truncmonth = false;
		$jsexpand = 1;
		$groupbyyear = 1;
		$limitbycategory = false;
		#$hideonnoncategory = empty($instance['hideonnoncategory']) ? 0 : $instance['hideonnoncategory'];
		$title = empty($instance['title']) ? 'Archives' : $instance['title'];
		
		if ($limitbycategory) {
			$current_category_id = get_query_var('cat');
		} else {
			$current_category_id = false;
		}
		
		if ($jsexpand == 1) {
			$groupbyyear = 1;
		}
		if ($groupbyyear == 1) {
			$jsexpand = 1;
		}
		
		$years = $this->get_years($current_category_id);
		$post_year = $years[0]->year;
		
		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo '<ul>';
	  for ($i = 0; $x < count($years[$i]); $i++) {
			$this_year = $jal_options['expandcurrent'] && $years[$i]->year == $post_year;
			$months = $this->get_months($years[$i]->year, $current_category_id);
			
			if ($groupbyyear) {
				$year_url = get_year_link($years[$i]->year);

				$count_text = '';
				echo '<li class="rotary-adv-archive-year rotary-adv-archive-year-groupby"><a class="icon more" href="'.$year_url.'">'.$years[$i]->year;
				
				echo '</a>'.$count_text;
				
				echo '<ul class="monthlist">';
				echo '<li><a  href="'.$year_url.'">'.__( 'All Months', 'rotary' ).'</a></li>';
				
				
	      foreach ($months as $month) {
					$month_url = get_month_link($years[$i]->year, $month->month);
	        $this_month = $this_year && (($post_id >= 0 && $month->month == $post_month) || ($post_id < 0 && $month == $months[0]));
					$count_text = '';
					
					$monthname = $wp_locale->get_month($month->month);
					

					echo '<li><a href="'.$month_url.'">'.$monthname.' '.$years[$i]->year;
					
					echo '</a>'.$count_text;
				
					echo '</li>';
	      }
				echo '</ul></li>';
			} else {
	      foreach ($months as $month) {
					$month_url = get_month_link($years[$i]->year, $month->month);
	        $this_month = $this_year && (($post_id >= 0 && $month->month == $post_month) || ($post_id < 0 && $month == $months[0]));
					$count_text = '';
					
					$monthname = $wp_locale->get_month($month->month);

					echo '<li><a href="'.$month_url.'">'.$monthname.' '.$years[$i]->year;
					echo '</a>'.$count_text;
					echo '</li>';
	      }
			}
		}	
		echo '</ul>';
		echo $after_widget;
  }

	function update($new_instance, $old_instance) {				
		$instance = $old_instance;
		$instance['showcount'] = strip_tags($new_instance['showcount']);
		$instance['linkcounter'] = strip_tags($new_instance['linkcounter']);
		$instance['truncmonth'] = strip_tags($new_instance['truncmonth']);
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['jsexpand'] = strip_tags($new_instance['jsexpand']);
		$instance['groupbyyear'] = strip_tags($new_instance['groupbyyear']);
		$instance['limitbycategory'] = strip_tags($new_instance['limitbycategory']);
		#$instance['hideonnoncategory'] = strip_tags($new_instance['hideonnoncategory']);
		
		return $instance;
	}

	function form($instance) {
		$title = empty($instance['title']) ? 'Archive' : esc_attr($instance['title']);
		$showcount = empty($instance['showcount']) ? 0 : esc_attr($instance['showcount']);
		$linkcounter = empty($instance['linkcounter']) ? 0 : esc_attr($instance['linkcounter']);
		$truncmonth = empty($instance['truncmonth']) ? 0 : esc_attr($instance['truncmonth']);
		$jsexpand = empty($instance['jsexpand']) ? 0 : esc_attr($instance['jsexpand']);
		$groupbyyear = empty($instance['groupbyyear']) ? 0 : esc_attr($instance['groupbyyear']);
		$limitbycategory = empty($instance['limitbycategory']) ? 0 : esc_attr($instance['limitbycategory']);
		#$hideonnoncategory = empty($instance['hideonnoncategory']) ? 0 : esc_attr($instance['hideonnoncategory']);
		
		if ($jsexpand == 1) {
			$groupbyyear = 1;
		}
		if ($groupbyyear == 1) {
			$jsexpand = 1;
		}
		?>
    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
      <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
    </p>

  
<!--
    <p>
      <input <?php echo ($hideonnoncategory=='1'?'checked="checked"':''); ?> id="<?php echo $this->get_field_id('hideonnoncategory'); ?>" name="<?php echo $this->get_field_name('hideonnoncategory'); ?>" type="checkbox" value="1" />
      <label for="<?php echo $this->get_field_id('hideonnoncategory'); ?>"><?php _e('Hide on none category pages'); ?></label> 
    </p>
-->

 	  <?php 
	}
}

function rotary_register_rotary_archive_widget() {
	register_widget("RotaryArchiveWidget");
}
add_action('widgets_init', 'rotary_register_rotary_archive_widget');


/*---------------------------------------------------------------------------------*/

/* Deregister Default Widgets */

/*---------------------------------------------------------------------------------*/

function rotary_deregister_widgets(){

    unregister_widget('WP_Widget_Links');  
	unregister_widget('WP_Widget_Archives');        

}

add_action('widgets_init', 'rotary_deregister_widgets');  
