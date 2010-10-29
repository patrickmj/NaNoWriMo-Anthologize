<?php

/*
Plugin Name: NaNoWriMo
Description: Experiment / Demo theming Anthologize special for NaNoWriMo

*/
require_once(WP_PLUGIN_DIR. '/anthologize/anthologize.php');
require_once(WP_PLUGIN_DIR. '/anthologize/includes/class-format-api.php');

anthologize_register_format( 'epub', __( 'NaNoWriMo - ePub', 'anthologize' ), WP_PLUGIN_DIR . '/nanowrimo/epub-output.php' );

//TODO: dropdown of items


anthologize_register_format_option( 'html', 'font-size', __( 'Font Size', 'anthologize' ), 'dropdown', $fontSizes, '14pt' );


?>
