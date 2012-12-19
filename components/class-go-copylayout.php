<?php

class GO_CopyLayout
{
	/**
	 * Add the "Copy Layout" link to the admin sidebar.
	 */
	public function admin_menu()
	{
		add_theme_page('Copy Layout', 'Copy Layout', 'edit_theme_options', 'copy-layout', array( $this, 'page' ) );
	}//end admin_menu

	/**
	 * fixes the arguments so they are all snazzy-like
	 */
	public function fixup_args( $args = '' )
	{
		$defaults = array(
			'which' => 'sidebars_widgets,widgets',
			'base64' => true,
		);

		$args = wp_parse_args( $args, $defaults );

		if( is_string( $args['which'] ) )
		{
			$args['which'] = explode( ',', $args['which'] );
		}//end if

		return $args;
	}//end fixup_args

	/**
	 * Get the sidebar/widget options from the options table
	 */
	public function get_options( $args = '' )
	{
		$args = $this->fixup_args( $args );

		$options = get_alloptions();

		$return = array();

		if( in_array( 'sidebars_widgets', $args['which'] ) )
		{
			$return['sidebars_widgets'] = $options['sidebars_widgets'];
		}//end if

		$do_widgets = in_array( 'widgets', $args['which'] );

		$widgets = array();

		foreach( $options as $name => $value )
		{
			if( $do_widgets && 'widget_' === substr($name, 0, 7) )
			{
				$widgets[ $name ] = $value;
			}//end if
		}//end foreach

		if( $do_widgets )
		{
			$return['widgets'] = $widgets;
		}//end if

		// array is full, return it

		$return = serialize( $return );

		if( $args['base64'] )
		{
			$return = base64_encode( $return );
		}//end if

		return $return;
	}//end get_options

	/**
	 * Display the page getting/setting the layout.
	 */
	public function page()
	{
		if( ! current_user_can('edit_theme_options') )
		{
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}//end if

		if( isset( $_POST['layout'] ) )
		{
			return $this->replace_layout( $_POST['layout'] );
		}//end if

		$args = $this->fixup_args();

		$current = $this->get_options( $args );

		include_once __DIR__ . '/templates/admin.php';
	}//end page

	/**
	 * Replace the current layout with a user-submitted layout.
	 */
	public function replace_layout( $layout )
	{
		echo '<div class="wrap"><h2>Applying New Layout</h2><div class="content-container">';

		$layout = base64_decode($layout);

		if( false === $layout )
		{
			wp_die( 'Error during Base64 decoding. <a href="themes.php?page=copy-layout">Go back</a>?' );
		}//end if

		$layout = unserialize($layout);

		if( false === $layout )
		{
			wp_die( 'Error during unserialize operation. <a href="themes.php?page=copy-layout">Go back</a>?' );
		}//end if

		$options = get_alloptions();

		//
		// what do we have in the incoming array?
		//

		$has_widgets = isset($layout['widgets']);
		$has_sidebars = isset($layout['sidebars_widgets']);

		//
		// delete things that need to be replaced
		//

		echo '<h3>Deleting options...</h3><ol>';

		if( $has_sidebars )
		{
			echo '<li>Deleting sidebar_widgets...</li>';
			delete_option('sidebars_widgets');
		}//end if

		foreach($options as $name => $value)
		{
			if( $has_widgets && 'widget_' === substr($name, 0, 7) )
			{
				echo '<li>Deleting ' . esc_html($name) . '...</li>';
				delete_option($name);
			}//end if
		}//end foreach

		echo '</ol>';

		//
		// add layout pieces back in
		//
		//
		echo '<h3>Adding options...</h3><ol>';

		if( $has_sidebars )
		{
			echo '<li>Adding sidebar_widgets...</li>';
			update_option( 'sidebars_widgets', unserialize( $layout['sidebars_widgets'] ) );
		}//end if

		foreach( $layout['widgets'] as $name => $value )
		{
			echo '<li>Adding ' . esc_html( $name ) . '...</li>';
			update_option( $name, unserialize( $value ) );
		}//end foreach

		echo '</div></div>';
	}//end replace_layout
}//end class
