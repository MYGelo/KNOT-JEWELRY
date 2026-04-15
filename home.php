<?php get_header(); ?>

<main>
	<?php
	$page_for_posts_id = get_option( 'page_for_posts' );
	$page_for_posts_obj = get_post( $page_for_posts_id );
	echo apply_filters( 'the_content', $page_for_posts_obj->post_content );
	?>
</main>

<?php get_footer(); ?>
