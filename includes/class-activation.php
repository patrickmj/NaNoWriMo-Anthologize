<?php

if(! class_exists('Nanowrimo_Activation')) {

	class Nanowrimo_Activation {

		function nanowrimo_activation() {
			$tempDir = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'nanowrimo/temp';
			if(! is_dir($tempDir)) {
				mkdir($tempDir);
			}
		}
	}
}
?>