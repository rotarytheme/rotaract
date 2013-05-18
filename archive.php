<?php
/**
 * The template for displaying Archive pages.
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */

get_header(); ?>

<?php
	if ( have_posts() )
		the_post();
?>

			<h1 class="pagetitle"><span><?php echo rotary_get_blog_title();?></span></h1>
<h2 class="pagesubtitle">
<?php if ( is_day() ) : ?>
				<?php printf( __( 'Daily Archives: %s', 'Rotary' ), '<span>'.get_the_date(). '</span>' ); ?>
<?php elseif ( is_month() ) : ?>
				<?php printf( __( 'Monthly Archives: %s', 'Rotary' ), '<span>'.get_the_date('F Y'). '</span>' ); ?>
<?php elseif ( is_year() ) : ?>
				<?php printf( __( 'Yearly Archives: %s', 'Rotary' ), '<span>'.get_the_date('Y').'</span>' ); ?>
<?php else : ?>
				<?php _e( 'Blog Archives', 'Rotary' ); ?>
<?php endif; ?>
</h2>
<?php
	rewind_posts(); ?>
<div id="content" role="main">
<?php	get_template_part( 'loop', 'archive' );
?>
</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>