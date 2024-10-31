<?php
/**
 * Plugin Name: Post/Page specific custom CSS
 * Plugin URI: https://wordpress.org/plugins/postpage-specific-custom-css/
 * Description: Post/Page specific custom CSS will allow you to add cascade stylesheet to specific posts/pages. It will give you special area in the post/page edit field to attach your CSS. It will also let you decide if this CSS has to be
 * added in multi-page/post view (like archive posts) or only in a single view. Version: 0.3.0 Author: Łukasz Nowicki Author URI: https://lukasznowicki.info/ Requires at least: 5.0 Requires PHP: 7.1 Tested up to: 5.8 Text Domain:
 * postpage-specific-custom-css Domain Path: /languages
 */

namespace Phylax\WPPlugin\PPCustomCSS;

use Phylax\Autoloader;

defined( 'ABSPATH' ) or exit;

const TEXT_DOMAIN = 'postpage-specific-custom-css';
const PLUGIN_FILE = __FILE__;
const PLUGIN_DIR  = __DIR__;

if ( ! class_exists( 'Phylax\Autoloader' ) ) {
	require_once PLUGIN_DIR . '/Phylax/Autoloader.php';
}

$ppsccAutoloader = new Autoloader();
$ppsccAutoloader->registerHandler();
$ppsccAutoloader->addNamespace( 'Phylax\WPPlugin\PPCustomCSS', PLUGIN_DIR . '/Phylax/WPPlugin/PPCustomCSS' );



/*
const MENU_SLUG              = 'post-page-custom-css';
const PARENT_MENU_SLUG       = 'options-general.php';
const OPTION_GROUP           = 'ppcs_settings_group';
const OPTION_NAME            = 'ppcs_settings_name';
const SECTION_BEHAVIOR_ID    = 'plugin-behavior';
const SECTION_DEFAULT_VALUES = 'default-values';

if ( ! class_exists( 'Phylax\Autoloader' ) ) {
	require_once __DIR__ . '/Phylax/Autoloader.php';
}
*/

new Plugin();

/*
class Plugin {

	private $newPlugin = true;
	private $flagUrl;
	private $myTransient;

	public function register_settings() {
		register_setting( $this->option_group, $this->option_name );
		add_settings_section( 'plugin-behavior', __( 'Options', TEXT_DOMAIN ), [
			$this,
			'section_plugin_behavior',
		], $this->menu_slug );
		add_settings_field( 'default_post_css', __( 'Default stylesheet for new posts', TEXT_DOMAIN ), [
			$this,
			'default_post_css',
		], $this->menu_slug, 'default-values' );
		add_settings_field( 'default_page_css', __( 'Default stylesheet for new pages', TEXT_DOMAIN ), [
			$this,
			'default_page_css',
		], $this->menu_slug, 'default-values' );
		add_settings_field( 'enable_highlighting_in', __( 'Code highlight', TEXT_DOMAIN ), [
			$this,
			'enable_highlighting_in',
		], $this->menu_slug, 'plugin-behavior' );
		add_settings_field( 'bigger_textarea', __( 'Bigger input field', TEXT_DOMAIN ), [
			$this,
			'bigger_textarea',
		], $this->menu_slug, 'plugin-behavior' );
	}

	public function __construct() {
		add_action( 'init', [
			$this,
			'init',
		] );
		add_filter( 'the_content', [
			$this,
			'the_content',
		] );
		if ( is_admin() ) {
			add_action( 'add_meta_boxes', [
				$this,
				'add_meta_boxes',
			] );
			add_action( 'save_post', [
				$this,
				'save_post',
			] );
			add_action( 'admin_menu', [
				$this,
				'add_options_page',
			] );
			add_action( 'admin_init', [
				$this,
				'pluginInfo'
			] );
			add_action( 'admin_enqueue_scripts', [
				$this,
				'admin_enqueue_scripts',
			] );
		}
		add_action( 'admin_notices', [ $this, 'adminNotices' ] );

		add_action( 'after_setup_theme', function () {
			if ( is_admin() ) {
				$current_user      = wp_get_current_user();
				$this->myTransient = 'ppsc_lastview_' . $current_user->ID . '_' . date( 'Ymd' );
				$dayBefore         = 'ppsc_lastview_' . $current_user->ID . '_' . date( 'Ymd', time() - DAY_IN_SECONDS );
				delete_transient( $dayBefore );
			}
			if ( isset( $_GET['ppscTransient'] ) && ( $_GET['ppscTransient'] === 'true' ) ) {
				delete_transient( $this->myTransient );
			}
		} );

		$today   = date( 'md' );
		$birtday = '0502';
		if ( $birtday === $today ) {
			$this->isBirthday = true;
		}
		$daybefore = '0501';
		if ( $daybefore === $today ) {
			$this->isDayBefore = true;
		}
		$today   = (int) $today;
		$daynext = (int) '0503';
		$daylast = (int) '0510';
		if ( ( $today >= $daynext ) && ( $today <= $daylast ) ) {
			$this->isWeekAfter = true;
		}
	}

	public function options_admin_enqueue_scripts() {
		wp_enqueue_code_editor( [ 'type' => 'text/css' ] );
	}

	public function admin_enqueue_scripts( $hook ) {
		$screen = get_current_screen();
		if ( false === is_a( $screen, 'WP_Screen' ) ) {
			return;
		}
		if ( 'post' !== $screen->base ) {
			return;
		}
		$field = '';
		if ( ( $screen->id === 'post' ) && ( $screen->post_type === 'post' ) ) {
			$field = 'enable_highlighting_in_posts';
		}
		if ( ( $screen->id === 'page' ) && ( $screen->post_type === 'page' ) ) {
			$field = 'enable_highlighting_in_pages';
		}
		if ( '' === $field ) {
			return;
		}
		$settings = (array) get_option( $this->option_name );
		$value    = (int) ( $settings[ $field ] ?? 0 );
		if ( 1 === $value ) {
			wp_enqueue_code_editor( [
				'type'       => 'text/javascript',
				'codemirror' => [
					'autoRefresh' => true,
				],
			] );
		}
	}

	public function bigger_textarea() {
		$settings = (array) get_option( $this->option_name );
		$field    = 'bigger_textarea';
		$value    = (int) ( $settings[ $field ] ?? 0 );
		?>
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php echo __( 'Make input boxes bigger', TEXT_DOMAIN ); ?></span>
            </legend>
            <input type="hidden" name="<?php echo $this->option_name; ?>[<?php echo $field; ?>]" value="0">
            <label for="item_<?php echo $field; ?>">
                <input id="item_<?php echo $field; ?>" type="checkbox"
                       name="<?php echo $this->option_name; ?>[<?php echo $field; ?>]"
                       value="1"<?php echo( ( $value === 1 ) ? ' checked="checked"' : '' ); ?>> <?php echo __( 'Make input boxes on Posts and Pages bigger', TEXT_DOMAIN ); ?>
            </label>
        </fieldset>
		<?php
	}

	public function enable_highlighting_in() {
		$settings = (array) get_option( $this->option_name );
		$field    = 'enable_highlighting_in_settings';
		$value    = (int) ( $settings[ $field ] ?? 0 );
		?>
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php echo __( 'Enable code highlighting', TEXT_DOMAIN ); ?></span>
            </legend>
            <input type="hidden" name="<?php echo $this->option_name; ?>[<?php echo $field; ?>]" value="0">
            <label for="item_<?php echo $field; ?>">
                <input id="item_<?php echo $field; ?>" type="checkbox"
                       name="<?php echo $this->option_name; ?>[<?php echo $field; ?>]"
                       value="1"<?php echo( ( $value === 1 ) ? ' checked="checked"' : '' ); ?>> <?php echo __( 'Enable code highlighting for fields on settings page', TEXT_DOMAIN ); ?>
            </label>
            <br>
			<?php
			$field = 'enable_highlighting_in_posts';
			$value = (int) ( $settings[ $field ] ?? 0 );
			?>
            <input type="hidden" name="<?php echo $this->option_name; ?>[<?php echo $field; ?>]" value="0">
            <label for="item_<?php echo $field; ?>">
                <input id="item_<?php echo $field; ?>" type="checkbox"
                       name="<?php echo $this->option_name; ?>[<?php echo $field; ?>]"
                       value="1"<?php echo( ( $value === 1 ) ? ' checked="checked"' : '' ); ?>> <?php echo __( 'Enable code highlighting for Posts fields', TEXT_DOMAIN ); ?>
            </label>
            <br>
			<?php
			$field = 'enable_highlighting_in_pages';
			$value = (int) ( $settings[ $field ] ?? 0 );
			?>
            <input type="hidden" name="<?php echo $this->option_name; ?>[<?php echo $field; ?>]" value="0">
            <label for="item_<?php echo $field; ?>">
                <input id="item_<?php echo $field; ?>" type="checkbox"
                       name="<?php echo $this->option_name; ?>[<?php echo $field; ?>]"
                       value="1"<?php echo( ( $value === 1 ) ? ' checked="checked"' : '' ); ?>> <?php echo __( 'Enable code highlighting for Pages fields', TEXT_DOMAIN ); ?>
            </label>
        </fieldset>
        <p class="description"><?php echo __( '<strong>Warning</strong> Please consider that on weaker computers, enabling CSS highlighting may slow you down.', TEXT_DOMAIN ); ?></p>
		<?php
	}

	public function default_post_css() {
		$settings = (array) get_option( $this->option_name );
		$field    = 'default_post_css';
		$value    = wp_unslash( $settings[ $field ] ?? '' );
		?>
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php echo __( 'Default stylesheet for new posts', TEXT_DOMAIN ); ?></span>
            </legend>
            <p>
                <textarea id="defaultPostCSS" name="<?php echo $this->option_name; ?>[<?php echo $field; ?>]"
                          class="large-text code" rows="10" cols="50"><?php echo $value; ?></textarea>
            </p>
        </fieldset>
		<?php
	}

	public function default_page_css() {
		$settings = (array) get_option( $this->option_name );
		$field    = 'default_page_css';
		$value    = wp_unslash( $settings[ $field ] ?? '' );
		?>
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php echo __( 'Default stylesheet for new pages', TEXT_DOMAIN ); ?></span>
            </legend>
            <p>
                <textarea id="defaultPageCSS" name="<?php echo $this->option_name; ?>[<?php echo $field; ?>]"
                          class="large-text code" rows="10" cols="50"><?php echo $value; ?></textarea>
            </p>
        </fieldset>
		<?php
	}

	public function section_plugin_behavior() {
		if ( $this->isBirthday || $this->isDayBefore || $this->isWeekAfter ) {
			echo '<p>' . sprintf( __( 'See <a href="%s">My Birthday</a> information :)', TEXT_DOMAIN ), admin_url() . 'options-general.php?page=post-page-custom-css&ppscTransient=true' ) . '</p>';
		}
	}

	public function page_settings_link_filter( $links ) {
		if ( ! is_array( $links ) ) {
			$links = [];
		}
		$links[] = '<a href="' . $this->build_settings_link() . '">' . __( 'Settings', TEXT_DOMAIN ) . '</a>';
		if ( $this->isBirthday || $this->isDayBefore || $this->isWeekAfter ) {
			$links[] = '<a href="' . get_admin_url() . 'plugins.php?ppscTransient=true">' . __( 'My birthday!', TEXT_DOMAIN ) . '</a>';
		}

		return $links;
	}

	private function build_settings_link() {
		return admin_url( $this->menu_parent_slug . '?page=' . $this->menu_slug );
	}

	public function add_options_page() {
		$sub_menu_suffix = add_submenu_page( $this->menu_parent_slug, __( 'Post/Page specific custom CSS', TEXT_DOMAIN ), __( 'Post/Page CSS', TEXT_DOMAIN ), 'manage_options', $this->menu_slug, [
			$this,
			'options_page_view',
		] );
		$settings        = (array) get_option( $this->option_name );
		$field           = 'enable_highlighting_in_settings';
		$value           = (int) ( $settings[ $field ] ?? 0 );
		if ( 1 === $value ) {
			add_action( 'load-' . $sub_menu_suffix, [
				$this,
				'options_admin_enqueue_scripts',
			] );
		}
	}

	public function options_page_view() {

		?>
        <div class="wrap">
            <h1><?php echo __( 'Post/Page Custom CSS', TEXT_DOMAIN ); ?></h1>
            <form action="options.php" method="POST">
				<?php settings_fields( $this->option_group ); ?>
                <div>
					<?php do_settings_sections( $this->menu_slug ); ?>
                </div>
				<?php submit_button(); ?>
            </form>
        </div>
		<?php
		$settings = (array) get_option( $this->option_name );
		$field    = 'enable_highlighting_in_settings';
		$value    = (int) ( $settings[ $field ] ?? 0 );
		if ( 1 === $value ) :
			?>
            <script>
                jQuery(function ($) {
                    var defaultPageCSS = $('#defaultPageCSS');
                    var defaultPostCSS = $('#defaultPostCSS');
                    var editorSettings;
                    var editor;
                    if (defaultPageCSS.length === 1) {
                        editorSettings = wp.codeEditor.defaultSettings ? _.clone(wp.codeEditor.defaultSettings) : {};
                        editorSettings.codemirror = _.extend({}, editorSettings.codemirror, {
                            indentUnit: 2, tabSize: 2, mode: 'css',
                        });
                        editor = wp.codeEditor.initialize(defaultPageCSS, editorSettings);
                    }
                    if (defaultPostCSS.length === 1) {
                        editorSettings = wp.codeEditor.defaultSettings ? _.clone(wp.codeEditor.defaultSettings) : {};
                        editorSettings.codemirror = _.extend({}, editorSettings.codemirror, {
                            indentUnit: 2, tabSize: 2, mode: 'css',
                        });
                        editor = wp.codeEditor.initialize(defaultPostCSS, editorSettings);
                    }
                });
            </script>
		<?php
		endif;
	}

	public function the_content( $content ) {
		if ( isset( $GLOBALS['post'] ) ) {
			$post_id                    = $GLOBALS['post']->ID;
			$phylax_ppsccss_single_only = get_post_meta( $post_id, '_phylax_ppsccss_single_only', true );
			$phylax_ppsccss_css         = get_post_meta( $post_id, '_phylax_ppsccss_css', true );
			if ( '' != $phylax_ppsccss_css ) {
				# mamy css!
				if ( is_single() || is_page() ) {
					$content = $this->join( $content, $phylax_ppsccss_css );
				} elseif ( '0' == $phylax_ppsccss_single_only ) {
					$content = $this->join( $content, $phylax_ppsccss_css );
				}
			}
		}

		return $content;
	}

	public function join( $content, $css ) {
		return '<!-- ' . __( 'Added by Post/Page specific custom CSS plugin, thank you for using!', TEXT_DOMAIN ) . ' -->' . PHP_EOL . '<style type="text/css">' . $css . '</style>' . PHP_EOL . $content;
	}

	public function add_meta_boxes() {
		if ( current_user_can( 'manage_options' ) ) {
			add_meta_box( 'phylax_ppsccss', __( 'Custom CSS', TEXT_DOMAIN ), [
				$this,
				'render_phylax_ppsccss',
			], [
				'post',
				'page',
			], 'advanced', 'high' );
		}
	}

	public function save_post( $post_id ) {
		$test_id = (int) $post_id;
		if ( $test_id < 1 ) {
			return $post_id;
		}
		if ( ! isset( $_POST['phylax_ppsccss_nonce'] ) ) {
			return $post_id;
		}
		$nonce = $_POST['phylax_ppsccss_nonce'];
		if ( ! wp_verify_nonce( $nonce, 'phylax_ppsccss' ) ) {
			return $post_id;
		}
		if ( ( 'page' != $_POST['post_type'] ) && ( 'post' != $_POST['post_type'] ) ) {
			return $post_id;
		}
		if ( ( 'post' == $_POST['post_type'] ) && ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}
		if ( ( 'page' == $_POST['post_type'] ) && ! current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && \DOING_AUTOSAVE ) {
			return $post_id;
		}
		$phylax_ppsccss_css         = trim( strip_tags( $_POST['phylax_ppsccss_css'] ) );
		$phylax_ppsccss_single_only = (int) $_POST['phylax_ppsccss_single_only'];
		if ( ( $phylax_ppsccss_single_only < 0 ) || ( $phylax_ppsccss_single_only > 1 ) ) {
			$phylax_ppsccss_single_only = 0;
		}
		update_post_meta( $post_id, '_phylax_ppsccss_css', $phylax_ppsccss_css );
		update_post_meta( $post_id, '_phylax_ppsccss_single_only', $phylax_ppsccss_single_only );
	}

	public function render_phylax_ppsccss( $post ) {
		wp_nonce_field( 'phylax_ppsccss', 'phylax_ppsccss_nonce' );
		$screen   = '';
		$settings = (array) get_option( $this->option_name );
		switch ( $post->post_type ) {
			case 'post':
				$field  = 'enable_highlighting_in_posts';
				$dField = 'default_page_css';
				$screen = __( 'Post custom CSS', TEXT_DOMAIN );
				break;
			case 'page':
				$field  = 'enable_highlighting_in_pages';
				$dField = 'default_post_css';
				$screen = __( 'Page custom CSS', TEXT_DOMAIN );
				break;
		}
		if ( '' == $screen ) {
			return;
		}
		$enable_highlighting = (int) ( $settings[ $field ] ?? 0 );
		$post_meta           = get_post_meta( $post->ID );
		$brand_new           = false;
		if ( false === isset( $post_meta['_phylax_ppsccss_css'] ) ) {
			$brand_new = true;
		}
		$phylax_ppsccss_css = get_post_meta( $post->ID, '_phylax_ppsccss_css', true );
		if ( ( '' === $phylax_ppsccss_css ) && ( true === $brand_new ) ) {
			$phylax_ppsccss_css .= (string) ( $settings[ $dField ] );
		}
		$phylax_ppsccss_single_only = get_post_meta( $post->ID, '_phylax_ppsccss_single_only', true );
		if ( '' == $phylax_ppsccss_single_only ) {
			$phylax_ppsccss_single_only = 0;
		}
		if ( $phylax_ppsccss_single_only ) {
			$checked = ' checked="checked"';
		} else {
			$checked = '';
		}
		$biggerBox = (int) ( $settings['bigger_textarea'] ?? 0 );
		?>
        <p class="post-attributes-label-wrapper">
            <label for="phylax_ppsccss_css"><?php echo $screen; ?></label>
        </p>
        <div id="phylax_ppsccss_css_outer">
            <textarea name="phylax_ppsccss_css" id="phylax_ppsccss_css" class="widefat textarea"
                      rows="<?php echo( ( 0 === $biggerBox ) ? '10' : '25' ) ?>"><?php echo esc_textarea( $phylax_ppsccss_css ); ?></textarea>
        </div>
        <p class="post-attributes-label-wrapper">
            <label for="phylax_ppsccss_single_only"><input type="hidden" name="phylax_ppsccss_single_only"
                                                           value="0"><input type="checkbox"
                                                                            name="phylax_ppsccss_single_only" value="1"
                                                                            id="phylax_ppsccss_single_only"<?php echo $checked; ?>> <?php echo __( 'Attach this CSS code only on single page view', TEXT_DOMAIN ); ?>
            </label>
        </p>
        <p>
			<?php
			echo __( 'Please add only valid CSS code, it will be placed between &lt;style&gt; tags.', TEXT_DOMAIN ); ?>
        </p>
		<?php
		if ( $enable_highlighting ) :
			?>
            <script>
                jQuery(function ($) {
                    var phylaxCSSEditorDOM = $('#phylax_ppsccss_css');
                    var phylaxCSSEditorSettings;
                    var phylaxCSSEditorInstance;
                    if (phylaxCSSEditorDOM.length === 1) {
                        phylaxCSSEditorSettings = wp.codeEditor.defaultSettings ? _.clone(wp.codeEditor.defaultSettings) : {};
                        phylaxCSSEditorSettings.codemirror = _.extend({}, phylaxCSSEditorSettings.codemirror, {
                            indentUnit: 2, tabSize: 2, mode: 'css',
                        });
                        phylaxCSSEditorInstance = wp.codeEditor.initialize(phylaxCSSEditorDOM, phylaxCSSEditorSettings);
                        //console.log( 'Next', phylaxCSSEditorDOM.next('.CodeMirror').find('.CodeMirror-code') );
                        $(document).on('keyup', '#phylax_ppsccss_css_outer .CodeMirror-code', function () {
                            console.clear();
                            phylaxCSSEditorDOM.html(phylaxCSSEditorInstance.codemirror.getValue());
                            phylaxCSSEditorDOM.trigger('change');
                        });
                    }
                });
            </script>
			<?php
			if ( 1 === $biggerBox ) :
				?>
                <style>
                    #phylax_ppsccss_css_outer .CodeMirror {
                        height: 600px;
                    }
                </style>
			<?php
			endif;
		endif;
	}

	public function pluginInfo() {
	}

	public function adminNotices( $forceShow = false ) {
		if ( ! $forceShow ) {
			if ( ! $this->isBirthday && ! $this->isDayBefore && ! $this->isWeekAfter ) {
				return;
			}
		}
		$current_user = wp_get_current_user();
		$transient    = 'ppsc_lastview_' . $current_user->ID . '_' . date( 'Ymd' );
		$tValue       = get_transient( $transient );
		if ( ( false !== $tValue ) && ! $forceShow ) {
			return;
		}
		set_transient( $transient, '1', 6 * HOUR_IN_SECONDS );
		add_action( 'admin_print_footer_scripts', function () {
			?>
            <script>
                jQuery(function ($) {
                    $(document).on('click', '.ppsc_show', function (event) {
                        event.preventDefault();
                        var id = '#acinfofor_' + $(this).data('ppsc');
                        $('.ppsc_hide_all').hide(0);
                        $(id).show(0);
                    });
                    console.clear();
                    console.log('DOM is READY');
                });
            </script>
            <style>
                .ppsc_hide_all {
                    margin: 1rem 0;
                    text-align: center;
                }

                .ppsc_info_line {
                    text-align: center;
                    margin: .1rem auto .1rem 1rem;
                }

                .ppsc_info_line span {
                    display: inline-block;
                    border: #ccc solid 1px;
                    padding: .5rem 1rem;
                    font-weight: bold;
                    font-size: 16px;
                }

                .ppsc_hide_all {
                    display: none;
                }

                .ppsc_story {
                    text-align: center;
                    font-weight: bold;
                    letter-spacing: .5px;
                }
            </style>
			<?php
		} );
		$this->flagUrl = plugins_url( '/assets/flags/', __FILE__ );
		$flagList      = [ 'us', 'gb', 'eu', 'ch', 'pl' ];
		?>
        <div class="notice notice-success is-dismissible">
            <p><span class="dashicons dashicons-buddicons-groups"
                     style="font-size:120px;width: 120px;height: 120px;float:left;color:#0a0;"></span>
                <strong><?php echo sprintf( __( 'Hello %s!', TEXT_DOMAIN ), $current_user->display_name ); ?></strong>
            </p>
            <p>
				<?php
				if ( $this->isBirthday ) {
					echo __( 'Today is my birthday.', TEXT_DOMAIN ) . ' ';
					echo sprintf( __( 'I hope I just turned <strong>%d</strong>.', TEXT_DOMAIN ), ( (int) date( 'Y' ) ) - 1977 ) . ' ';
				}
				if ( $this->isDayBefore ) {
					echo __( 'Tomorrow will be my birthday.', TEXT_DOMAIN ) . ' ';
					echo sprintf( __( 'I hope I will turn <strong>%d</strong> tomorrow.', TEXT_DOMAIN ), ( (int) date( 'Y' ) ) - 1977 ) . ' ';
				}
				if ( $this->isWeekAfter ) {
					echo __( 'This week were my birthday.', TEXT_DOMAIN ) . ' ';
					echo sprintf( __( 'I hope I turned <strong>%d</strong>...', TEXT_DOMAIN ), ( (int) date( 'Y' ) ) - 1977 ) . ' ';
				}
				echo sprintf( __( 'I just think, maybe you want to <a href="%s">give me a review</a> for my plugin?', TEXT_DOMAIN ), 'https://wordpress.org/support/plugin/postpage-specific-custom-css/reviews/#new-post' ) . ' ';
				echo __( 'Or maybe you have such a good situation that you would like to consider a small donation? Click on the currency flag and the account number will show if you would like to repay my work. <strong>I do not insist!</strong> It would be just nice to get a birthday present.', TEXT_DOMAIN ) . '<br>';
				?>
            </p>
            <p class="ppsc_story"><?php
				echo __( 'Or maybe you would like to know my story? I weighed 230.6 kg (508.5lb), I couldn\'t move, I almost became a cripple. Thanks to my fiancee I started to lose weight. I\'m halfway there. Currently, I weigh about 165kg (363lb). Or maybe less?', TEXT_DOMAIN ) . ' ';
				echo __( '<a href="%s">Follow me on Instagram</a> and <a href="%s">follow the Facebook page</a>. You can also send me wishes there if you like, thank you :)', TEXT_DOMAIN ) . ' ';
				?></p>
            <p style="text-align: center"><?php echo __( 'Click on the flag to see account details. For every, even the smallest payment - thank you a lot!', TEXT_DOMAIN ); ?></p>
            <p style="text-align: center">
				<?php foreach ( $flagList as $code ) : ?>
                    <a href="#" class="ppsc_show"
                       data-ppsc="<?php echo $code; ?>"><?php echo $this->flag( $code ); ?></a>
				<?php endforeach; ?>
            </p>
			<?php
			$accounts = [
				'us' => 'PL04249010570000990143895083',
				'gb' => 'PL39249010570000990443895083',
				'eu' => 'PL48249010570000990243895083',
				'ch' => 'PL92249010570000990343895083',
				'pl' => '57249010570000990043895083',
			];
			$currency = [
				'us' => __( 'United States dollar - USD', TEXT_DOMAIN ),
				'gb' => __( 'Pound sterling - GBP', TEXT_DOMAIN ),
				'eu' => __( 'Euro - EUR', TEXT_DOMAIN ),
				'ch' => __( 'Swiss franc - CHF', TEXT_DOMAIN ),
				'pl' => __( 'Polish złoty - PLN', TEXT_DOMAIN ),
			];
			foreach ( $flagList as $code ) {
				$this->accountLine( $code, $accounts[ $code ], $currency[ $code ] );
			}
			?>
        </div>
		<?php
	}

	public function flag( $code ) {
		$alt = '';
		switch ( $code ) {
			case 'us':
				$alt = __( 'United States dollar - USD', TEXT_DOMAIN );
				break;
			case 'gb':
				$alt = __( 'Pound sterling - GBP', TEXT_DOMAIN );
				break;
			case 'eu':
				$alt = __( 'Euro - EUR', TEXT_DOMAIN );
				break;
			case 'ch':
				$alt = __( 'Swiss franc - CHF', TEXT_DOMAIN );
				break;
			case 'pl':
				$alt = __( 'Polish złoty - PLN', TEXT_DOMAIN );
				break;
		}

		return '<img src="' . $this->flagUrl . $code . '.png' . '" alt="' . $alt . '" title="' . $alt . '" style="width:32px;height:22px;">';
	}

	public function accountLine( $code, $account, $currency ) {
		?>
        <div class="ppsc_hide_all" id="acinfofor_<?php echo $code; ?>">
            <div class="ppsc_info_line"><?php echo __( 'BIC/SWIFT:', TEXT_DOMAIN ); ?>
                <span>ALBPPLPW</span>
				<?php echo __( 'Currency:', TEXT_DOMAIN ) . ' <span> ' . $currency; ?></span></div>
            <div class="ppsc_info_line"><?php echo __( 'Account number:', TEXT_DOMAIN ); ?>
                <span><?php echo $account; ?></span></div>
        </div>
		<?php
	}

	public function init() {
		load_plugin_textdomain( TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	private function text() {
		__( 'Post/Page specific custom CSS will allow you to add cascade stylesheet to specific posts/pages. It will give you special area in the post/page edit field to attach your CSS. It will also let you decide if this CSS has to be added in multi-page/post view (like archive posts) or only in a single view.', TEXT_DOMAIN );
	}

}

new Plugin();
*/
