<?php


namespace Phylax\WPPlugin\PPCustomCSS;


class Settings {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'adminMenu' ] );
		add_action( 'admin_init', [ $this, 'adminInit' ] );
	}

	public function adminInit() {
		register_setting( OPTION_GROUP, OPTION_NAME );
		add_settings_section( SECTION_BEHAVIOR_ID, __( 'General Settings', TEXT_DOMAIN ), [
			$this,
			'sectionBehavior'
		], MENU_SLUG );
		add_settings_section( SECTION_DEFAULT_VALUES, __( 'Default values', TEXT_DOMAIN ), [
			$this,
			'sectionDefaultValues',
		], MENU_SLUG );

	}

	public function adminMenu() {
		$subMenuSuffix = add_submenu_page(
			PARENT_MENU_SLUG,
			__( 'Post/Page specific custom CSS', TEXT_DOMAIN ),
			__( 'Post/Page CSS', TEXT_DOMAIN ),
			'manage_options',
			MENU_SLUG, [
			$this,
			'subMenuView',
		] );
		$settings      = (array) get_option( OPTION_NAME );
		$field         = 'enable_highlighting_in_settings';
		$value         = (int) ( $settings[ $field ] ?? 0 );
		if ( 1 === $value ) {
			add_action( 'load-' . $subMenuSuffix, [
				$this,
				'options_admin_enqueue_scripts',
			] );
		}

	}

	public function adminFooter() {
		wp_enqueue_style( 'ppscc_options_style', plugins_url( '/assets/options.min.css', PLUGIN_FILE ), [], null );
		wp_enqueue_script( 'ppscc_options_script', plugins_url( '/assets/options.min.js', PLUGIN_FILE ), [], null, true );
	}

	public function subMenuView() {
		add_action( 'admin_footer', [ $this, 'adminFooter' ] );
		$post_types = $this->getAvailablePostTypes();
		?>
        <div class="wrap">
            <h1><?php echo __( 'Post/Page Custom CSS Settings', TEXT_DOMAIN ); ?></h1>
            <form action="options.php" method="post">
				<?php settings_fields( OPTION_GROUP ); ?>
                <div class="" id="ppscc-settings">
                    <ul class="ppscc-tabs-heading">
                        <li><a href="#general_settings"><?php echo __( 'General', TEXT_DOMAIN ); ?></a></li>
                        <li><a href="#use_post_types"><?php echo __( 'Use post types', TEXT_DOMAIN ); ?></a></li>
                        <li><a href="#default_values"><?php echo __( 'Default values', TEXT_DOMAIN ); ?></a></li>
                        <li><a href="#archive-styles"><?php echo __( 'Archive styles', TEXT_DOMAIN ); ?></a></li>
                        <li><a href="#site-wide"><?php echo __( 'Site-wide styles', TEXT_DOMAIN ); ?></a></li>
                    </ul>
                    <div id="general_settings" class="ppscc-tab">
                        <h3><?php echo __( 'General Settings', TEXT_DOMAIN ); ?></h3>
                        <fieldset class="checkboxes">
                            <label>
                                <input type="hidden" name="general[highlight_posts]" value="0">
                                <input type="checkbox" name="general[highlight_posts]" value="1">
								<?php echo __( 'Add CSS highlighting in posts area', TEXT_DOMAIN ); ?>
                            </label>
                            <label>
                                <input type="hidden" name="general[highlight_admin]" value="0">
                                <input type="checkbox" name="general[highlight_admin]" value="1">
								<?php echo __( 'Add CSS highlighting in admin area (save options to reload and activate)', TEXT_DOMAIN ); ?>
                            </label>
                        </fieldset>
                    </div>
                    <div id="use_post_types" class="ppscc-tab">
                        <h3><?php echo __( 'Available post types', TEXT_DOMAIN ); ?></h3>
                        <fieldset class="checkboxes">
                            <div id="ppscc_mark_unmark">
								<?php echo __( 'Mark/unmark all', TEXT_DOMAIN ); ?>
                            </div>
							<?php foreach ( $post_types as $post_name => $post_label ) : ?>
                                <label>
                                    <input type="hidden" name="use[<?php echo $post_name; ?>" value="0">
                                    <input type="checkbox" name="use[<?php echo $post_name; ?>]" value="1">
									<?php echo $post_label; ?> <small>(slug: <?php echo $post_name; ?>)</small>
                                </label>
							<?php endforeach; ?>
                        </fieldset>
                    </div>
                    <div id="default_values" class="ppscc-tab">
                        <h3><?php echo __( 'Default values for available post types', TEXT_DOMAIN ); ?></h3>
                    </div>
                    <div id="archive-styles" class="ppscc-tab">
                        <h3><?php echo __( 'Archive styles', TEXT_DOMAIN ); ?></h3>
                    </div>
                    <div id="site-wide" class="ppscc-tab">
                        <h3><?php echo __( 'Site-wide styles', TEXT_DOMAIN ); ?></h3>
                        <p>
							<?php echo __( 'Use this field to attach CSS styles to all site views, regardless of the current context and/or content. This style will be apply on front-end only.', TEXT_DOMAIN ); ?>
                        </p>
                        <label>
                            <textarea id="site_wide_front" class="ppscc_enter_style" name="site_wide_front"
                                      rows="12"></textarea>
                        </label>
                        <p>
							<?php echo __( 'Use this field to attach CSS styles to admin panel/back-end only.', TEXT_DOMAIN ); ?>
                        </p>
                        <label>
                            <textarea id="site_wide_front" class="ppscc_enter_style" name="site_wide_front"
                                      rows="12"></textarea>
                        </label>
                    </div>
                </div>
				<?php submit_button(); ?>
            </form>
        </div>
		<?php
	}

	public function getAvailablePostTypes(): array {
		$posts      = [];
		$post_types = get_post_types( [
			'public' => true,
		], 'objects' );
		foreach ( $post_types as $post_type ) {
			$posts[ $post_type->name ] = $post_type->label;
		}

		return $posts;
	}

	public function sectionBehavior() {
		echo '@@@';
	}

	public function sectionDefaultValues() {
		?>
        <p><?php echo __( 'You can set the pre-filled content for your newly created posts or pages.', TEXT_DOMAIN ); ?></p>
		<?php
	}

}