<?php


namespace Phylax\WPPlugin\PPCustomCSS;


use Cassandra\Set;
use Phylax\WordPress\ActionLinks;

class Plugin {

	public function __construct() {
		if ( is_admin() ) {
			new ActionLinks( PLUGIN_FILE, [
				'#' => __( 'Settings', TEXT_DOMAIN ),
			] );
			new Settings();
		}
	}

}