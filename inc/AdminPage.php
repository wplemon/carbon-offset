<?php
/**
 * Admin Page Handler.
 *
 * @package CarbonOffset
 * @since 1.0.0
 */

namespace CarbonOffset;

use Aristath\PayItForward;

/**
 * Admin Page Handler.
 *
 * @since 1.0.0
 */
class AdminPage {

	/**
	 * The saved data.
	 *
	 * @access protected
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Init the admin page.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init() {
		$this->data = new Data();

		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		add_action( 'carbon_offset_admin_tab_contents', [ $this, 'details_tab' ] );
		add_action( 'carbon_offset_admin_tab_contents', [ $this, 'settings_tab' ] );
		add_action( 'carbon_offset_settings_page_fields', [ $this, 'settings_fields' ], 5 );
		add_action( 'admin_init', [ $this, 'save_settings' ] );

		add_action(
			'carbon_offset_admin_tab_contents',
			/**
			 * Add sponsors details.
			 *
			 * @access public
			 * @since 1.0.0
			 * @param string $tab The admin-page tab.
			 * @return void
			 */
			function( $tab ) {
				if ( 'details' !== $tab ) {
					return;
				}
				include_once 'PayItForward.php';
				$sponsors = new PayItForward();
				$sponsors->sponsors_details();
			},
			999
		);
	}

	/**
	 * Add the admin page.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_menu_page() {
		add_menu_page(
			esc_html__( 'Carbon Offset', 'carbon-offset' ),
			esc_html__( 'Carbon Offset', 'carbon-offset' ),
			'manage_options',
			'carbon-offset',
			[ $this, 'page' ],
			'dashicons-carrot'
		);
	}

	/**
	 * The admin-page contents.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Carbon Offset', 'carbon-offset' ); ?></h1>

			<!-- Just adds some whitespace. -->
			<div style="height: 2em"></div>
			<?php
			$admin_page_tabs = apply_filters(
				'carbon_offset_admin_tabs',
				[
					[
						'id'    => 'details',
						'title' => __( 'Details', 'carbon-offset' ),
					],
					[
						'id'    => 'settings',
						'title' => __( 'Settings', 'carbon-offset' ),
					],
				]
			);

			$current_tab = 'details';
			if ( isset( $_GET['tab'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$current_tab = sanitize_text_field( wp_unslash( $_GET['tab'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			}
			?>

			<?php if ( 1 < count( $admin_page_tabs ) ) : ?>
				<nav class="nav-tab-wrapper wp-clearfix" aria-label="<?php esc_html_e( 'Secondary menu', 'carbon-offset' ); ?>">
					<?php foreach ( $admin_page_tabs as $tab ) : ?>
						<?php $tab_classes = ( $current_tab === $tab['id'] ) ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>
						<a href="<?php echo esc_url( admin_url( "admin.php?page=carbon-offset&tab={$tab['id']}" ) ); ?>" class="<?php echo esc_attr( $tab_classes ); ?>" aria-current="page">
							<?php echo esc_html( $tab['title'] ); ?></a>
					<?php endforeach; ?>
				</nav>
				<div style="height: 2em"></div>
			<?php endif; ?>
			<?php do_action( 'carbon_offset_admin_tab_contents', $current_tab ); ?>
		</div>
		<?php
	}

	/**
	 * Print the details tab.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 *
	 * @param string $current_tab The current tab.
	 *
	 * @return void
	 */
	public function details_tab( $current_tab ) {
		if ( 'details' !== $current_tab ) {
			return;
		}

		$carbon_data = $this->data->get();
		?>
		<div class="postbox">
			<div class="inside">
				<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(30em, 1fr));grid-gap:1px;background:#aaa;">
					<div class="carbon-offset-pending" style="padding:2em;background:#fff;">
						<h2 style="line-height:3;text-align:center;"><?php esc_html_e( 'Pending Carbon Footprint', 'carbon-offset' ); ?></h2>
						<p class="description"><?php esc_html_e( 'Each visit and transaction on your website generates carbon emissions. In this section you can see the impact these have, and offset your site\'s emissions to the planet.', 'carbon-offset' ); ?></p>
						<p style="font-size:4em;font-weight:200;text-align:center;line-height:1;"><?php echo esc_html( round( $carbon_data['carbon_pending'] / 1000, 1 ) ); ?>kg</p>
						<p style="font-size:1.5em;font-weight:200;text-align:center;">(<?php echo (float) $carbon_data['carbon_pending']; ?>grams)</p>
						<?php do_action( 'carbon_offset_admin_page_pending_inside' ); ?>
					</div>
					<div class="carbon-offset-complete" style="padding:2em;background:#fff;">
						<h2 style="line-height:3;text-align:center;"><?php esc_html_e( 'Carbon Footprint already offset', 'carbon-offset' ); ?></h2>
						<?php if ( 0 < $carbon_data['carbon_offset'] ) : ?>
							<p class="description"><?php esc_html_e( 'In this section you can see the carbon you have already offset. Future purchases will add up to this number.', 'carbon-offset' ); ?></p>
						<?php else : ?>
							<p class="description"><?php esc_html_e( 'Once you purchase a carbon offset, the weight of that carbon will be shown here.', 'carbon-offset' ); ?></p>
						<?php endif; ?>
						<p style="font-size:4em;font-weight:200;text-align:center;"><?php echo (float) round( $carbon_data['carbon_offset'] ) / 1000; ?>kg</p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Print the settings tab.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 *
	 * @param string $current_tab The current tab.
	 *
	 * @return void
	 */
	public function settings_tab( $current_tab ) {
		if ( 'settings' !== $current_tab ) {
			return;
		}
		?>
		<form method="post">
			<?php
			$values = get_option( 'carbon_offset_settings', [] );
			/**
			 * Add settings from hooks.
			 *
			 * @since 1.0.0
			 * @param array
			 */
			do_action( 'carbon_offset_settings_page_fields', $values );

			/**
			 * Add nonce field.
			 */
			wp_nonce_field( 'carbon-offset-settings' );
			?>

			<?php
			/**
			 * Add hidden input to denote the page - sanity check for save method.
			 */
			?>
			<input type="hidden" name="carbon-offset-settings" value="save">

			<?php
			/**
			 * The submit button.
			 */
			?>
			<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_html_e( 'Update Settings', 'carbon-offset' ); ?>"></p>
		<form>
		<?php
	}

	/**
	 * Save settings.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function save_settings() {

		// Sanity check.
		if ( ! isset( $_POST['carbon-offset-settings'] ) || 'save' !== $_POST['carbon-offset-settings'] ) {
			return;
		}

		// Security check:
		// Early exit if the current user doesn't have the correct permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Security check:
		// Early exit if nonce check fails.
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'carbon-offset-settings' ) ) {
			return;
		}

		/**
		 * Build the value we're going to save.
		 */
		$save_value = [];
		foreach ( $_POST as $key => $value ) {
			if ( in_array( $key, [ '_wpnonce', '_wp_http_referer', 'carbon-offset-settings', 'submit' ], true ) ) {
				continue;
			}
			if ( is_string( $value ) || is_numeric( $value ) ) {
				$save_value[ sanitize_key( $key ) ] = sanitize_text_field( $value );
			}
		}

		update_option( 'carbon_offset_settings', $save_value );
	}

	/**
	 * Add generic settings.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 *
	 * @param array $values An array of saved values.
	 *
	 * @return void
	 */
	public function settings_fields( $values ) {
		$footprint = isset( $values['footprint'] ) ? $values['footprint'] : 0;
		?>
		<h2><?php esc_html_e( 'Carbon Footprint Settings', 'carbon-offset' ); ?></h2>

		<label id="footprint-label">
			<strong>
				<?php esc_html_e( 'Carbon Footprint Per Page Load (fallback value)', 'carbon-offset' ); ?>
			</strong>
		</label>
		<p id="footprint-desciption" class="description" style="max-width: 50em;">
			<?php
			printf(
				/* Translators: Link to websitecarbon.com */
				esc_html__( 'We will automatically calculate the carbon footprint of your page loads. If you enter a value in this field, it will be used as a fallback value in case we can not detect the carbon-footprint of your page load. You can get this value by testing your website on %s.', 'carbon-offset' ),
				'<a href="https://www.websitecarbon.com/" target="_blank" rel="nofollow">websitecarbon.com</a>'
			);
			?>
		</p>
		<input
			name="footprint"
			type="number"
			min="0"
			max="10000"
			step="0.001"
			aria-label="footprint-label"
			aria-describedby="footprint-description"
			value="<?php echo esc_attr( $footprint ); ?>"
		>
		<hr style="margin:2em 0;">
		<?php
	}
}
