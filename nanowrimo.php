<?php
/*
Plugin Name: NaNoWriMo
Description: Experiment / Demo theming Anthologize special for NaNoWriMo

*/



/*
Copyright (C) 2010 Patrick Murray-John

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <http://www.gnu.org/licenses/>.

*/



//when in doubt, do what Boone does.
//most all of this is copied from anthologize.php

if ( !class_exists( 'Nanowrimo_Loader' ) ) {

	class Nanowrimo_Loader {

		function nanowrimo_loader() {

			add_action( 'anthologize_init', array( $this, 'export_formats' ) );

			// activation sequence
			register_activation_hook( __FILE__, array( $this, 'activation' ) );
		}

		function export_formats() {

			anthologize_register_format( 'nano-epub', __( 'NaNoWriMo - ePub', 'anthologize' ), WP_PLUGIN_DIR . '/nanowrimo/epub-output.php' );
			anthologize_register_format( 'nano-html', __( 'NaNoWriMo - HTML', 'anthologize' ), WP_PLUGIN_DIR . '/nanowrimo/html-output.php' );


			$fontSizes = array('48pt'=>'48 pt', '36pt'=>'36 pt', '18pt'=>'18 pt', '14pt'=>'14 pt', '12pt'=>'12 pt');
			$fontFaces = array(
				'times' => __( 'Times New Roman', 'anthologize' ),
				'helvetica' => __( 'Helvetica', 'anthologize' ),
				'courier' => __( 'Courier', 'anthologize' )
			);

			anthologize_register_format_option( 'nano-html', 'total-count', __('Include Total Word Count?', 'anthologize'), 'checkbox');
			anthologize_register_format_option( 'nano-html', 'item-count', __('Include Item Word Count?', 'anthologize'), 'checkbox');

			anthologize_register_format_option( 'nano-epub', 'total-count', __('Include Total Word Count?', 'anthologize'), 'checkbox');
			anthologize_register_format_option( 'nano-epub', 'item-count', __('Include Item Word Count?', 'anthologize'), 'checkbox');


			anthologize_register_format_option( 'nano-epub', 'font-face', __( 'Font Face', 'anthologize' ), 'dropdown', $fontFaces, '14pt' );
			anthologize_register_format_option( 'nano-html', 'font-face', __( 'Font Face', 'anthologize' ), 'dropdown', $fontFaces, '14pt' );

			anthologize_register_format_option( 'nano-html', 'font-size', __( 'Font Size', 'anthologize' ), 'dropdown', $fontSizes, '14pt' );
			anthologize_register_format_option( 'nano-html', 'download', __('Download HTML?', 'anthologize'), 'checkbox');

		}

		function activation() {
			require_once( dirname( __FILE__ ) . '/includes/class-activation.php' );
			$activation = new Nanowrimo_Activation();
		}

	}

}

$nanowrimo_loader = new Nanowrimo_Loader();

?>
