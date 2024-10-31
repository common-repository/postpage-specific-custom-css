<?php


namespace Phylax\WordPress;


class ActionLinks {

	/** @var string */
	public $pluginFile;

	/** @var array */
	public $actionLinks;

	/**
	 * ActionLinks constructor.
	 *
	 * @param string $pluginFile
	 * @param array $actionLinks
	 */
	public function __construct( string $pluginFile, array $actionLinks ) {
		$this->pluginFile  = $pluginFile;
		$this->actionLinks = $actionLinks;
		$this->registerActionLinks();
	}

	/**
	 * Add filter
	 */
	public function registerActionLinks() {
		add_filter( 'plugin_action_links_' . plugin_basename( $this->pluginFile ), [ $this, 'filterLinks' ] );
	}

	/**
	 * @param array $links
	 *
	 * @return array
	 */
	public function filterLinks( array $links ): array {
		foreach ( $this->actionLinks as $url => $label ) {
			$links[] = '<a href="' . $url . '">' . $label . '</a>';
		}

		return $links;
	}

}