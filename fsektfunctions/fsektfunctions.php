<?php
/*
   Plugin Name: Site Plugin for f.kth.se
   Description: Site specific code changes for f.kth.se
 */

class FysikFileShareLink extends WP_Widget
{
	function FysikFileShareLink()
	{
		$widget_ops = array('classname' => 'FysikFileShareLink', 'description' => 'Visar länken till Fysik File Share' );
		$this->WP_Widget('FysikFileShareLink', 'Fysik File Share Link', $widget_ops);
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
		echo "<a href=\"https://ffs.afsa.se\"><h2>Fysik File Share</h2></a>";

		echo $after_widget;
	}

}
add_action( 'widgets_init', create_function('', 'return register_widget("FysikFileShareLink");') );

