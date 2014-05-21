<?php
/*
   Plugin Name: Annonser Widget
   Description: Visar de senaste annonserna
 */

class AnnonserWidget extends WP_Widget
{
	function AnnonserWidget()
	{
		$widget_ops = array('classname' => 'AnnonserWidget', 'description' => 'Visar de 3 senaste annonserna' );
		$this->WP_Widget('AnnonserWidget', 'Annonser Widget', $widget_ops);
	}

	function form($instance)
	{
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = $instance['title'];
		?>
			<p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
			<?php
	}

	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		return $instance;
	}

	function widget($args, $instance)
	{
		extract($args, EXTR_SKIP);

		echo $before_widget;
		$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);

		if (!empty($title))
			echo $before_title . $title . $after_title;;

		// WIDGET CODE GOES HERE
		echo '<h2>Annonser</h2> </br>';
		$cat_id = 62; // Category ID: Annonser
		$latest_cat_post = new WP_Query( array('posts_per_page' => 3, 'category__in' => array($cat_id))); // 3 posts
		if( $latest_cat_post->have_posts() ) : while( $latest_cat_post->have_posts() ) : $latest_cat_post->the_post();
		echo '<h2>';
		the_title();
		echo '</h2>';
		the_content();
		endwhile; endif;

		echo '</br> <a href="http://f.kth.se/naringsliv/annonser/">Se fler annonser</a>';

		echo $after_widget;
	}

}
add_action( 'widgets_init', create_function('', 'return register_widget("AnnonserWidget");') );

?>
