<?php
/**
 * Admin Page Handler.
 *
 * @package CarbonOffset
 * @since 1.0.0
 */

namespace CarbonOffset;

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
	 * @since 1.0.0
	 * @var array
	 */
	protected $data;

	/**
	 * Init the admin page.
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init() {
		$this->data = new Data();

		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		add_action( 'carbon_offset_admin_tab_contents', [ $this, 'details_tab' ] );
	}

	/**
	 * Add the admin page.
	 *
	 * @access public
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
			<?php
			/**
			 * Add extra data after the postbox.
			 *
			 * @since 1.0.0
			 */
			do_action( 'carbon_offset_adminpage_end' );
			?>
		</div>
		<?php
	}

	/**
	 * Print the details tab.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param string $current_tab The current tab.
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
				<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(50em, 1fr));grid-gap:1px;background:#aaa;">
					<div class="carbon-offset-pending" style="padding:2em;background:#fff;">
						<h2 style="line-height:3;"><?php esc_html_e( 'Pending', 'carbon-offset' ); ?></h2>
						<p style="font-size:4em;font-weight:200;text-align:center;"><?php echo (float) $carbon_data['carbon_pending'] / 1000; ?>kg</p>
						<p>TODO: We need a text input here where users can select the number they want to offset. Default value for the field should be the pending grams. We also need a submit button so they can make the payment.</p>
					</div>
					<div class="carbon-offset-complete" style="padding:2em;background:#fff;">
						<h2 style="line-height:3;"><?php esc_html_e( 'Carbon Footprint already offset', 'carbon-offset' ); ?></h2>
						<p style="font-size:4em;font-weight:200;text-align:center;"><?php echo (float) $carbon_data['carbon_offset']; ?></p>
						<p>TODO: Add motivational text here and congratulations if they've already offset some of their carbon footprint.</p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
