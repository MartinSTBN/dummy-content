<?php

/**
 * @since 0.1
 */
class CPDC_Admin {

	private $cpdc;

	/**
	 * Constructor
	 *
	 * @since 0.1
	 */
	public function __construct( $cpdc ) {

		$this->cpdc = $cpdc;

		// Admin UI
		add_action( 'admin_menu', array( $this, 'settings_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_filter( 'plugin_action_links', array( $this, 'add_settings_link' ), 1, 2 );
	}

	/**
	 * Admin Menu.
	 *
	 * Create the admin menu link for the settings page.
	 *
	 * @since 0.1
	 */
	public function settings_menu() {
		add_menu_page( __( 'Dummy Content', 'dummy-content' ), __( 'Dummy Content', 'dummy-content' ), 'manage_options', dirname( plugin_basename( CPDC_FILE ) ), array( $this, 'plugin_settings_page' ), 'dashicons-nametag' );
	}

	/**
	 * Add Settings link to plugin page
	 *
	 * @since 0.1
	 */
	public function add_settings_link( $links, $file ) {
		if ( $file != plugin_basename( CPDC_FILE ) ) {
			return $links;
		}
		$slug = dirname( plugin_basename( CPDC_FILE ) );
		array_unshift( $links, '<a href="' . admin_url( "admin.php" ) . '?page=' . $slug . '">' . __( 'Settings', 'dummy-content' ) . '</a>' );

		return $links;
	}

	/**
	 * Sanitize options
	 *
	 * @since 0.1
	 */
	public function sanitize_options( $options ) {

		$options = array_map( 'sanitize_text_field', $options );
		$options = array_map( 'trim', $options );

		return $options;
	}

	/**
	 * Register plugin options
	 *
	 * @since 0.1
	 */
	public function register_settings() {
		if ( false === get_option( 'cpdc_options' ) ) {
			add_option( 'cpdc_options', $this->get_default_values() );
		}

		register_setting( 'cpdc-settings-group', 'cpdc_options', array( $this, 'sanitize_options' ) );
	}

	/**
	 * Returns the default plugin options.
	 *
	 * @since 0.1
	 */
	public function get_default_values() {
		return apply_filters( 'cpdc_defaults', array() );
	}

	/**
	 * @since 1.0
	 * @return array Posttypes
	 */
	public function get_post_types() {
		$post_types = get_post_types( array( 'show_ui' => true ) );

		$exclude = array(
			'acf-field-group',
			'shop_order',
			'shop_coupon',
			'attachment'
		);

		foreach ( $exclude as $pt ) {
			if ( isset( $post_types[ $pt ] ) ) {
				unset( $post_types[ $pt ] );
			}
		}

		return $post_types;
	}

	/**
	 * @since 1.0
	 * @return array Taxonomies
	 */
	public function get_taxonomies() {
		$taxonomies = get_taxonomies( array( 'public' => true ) );
		if ( isset( $taxonomies['post_format'] ) ) {
			unset( $taxonomies['post_format'] );
		}

		return $taxonomies;
	}

	private function has_post_content( $post_type ) {
		return dc()->get_generator( 'post' )->set_post_type( $post_type )->get_count();
	}

	private function has_user_content() {
		return dc()->get_generator( 'user' )->get_count();
	}

	/**
	 * Display actions
	 *
	 * @since 1.1
	 */
	public function display_actions( $meta_type = 'post' ) {
		if ( ! $generator = dc()->get_generator( $meta_type ) ) {
			return;
		}
		?>
		<p>
			<span><?php echo $generator->display_title(); ?>: </span>
			<?php $generator->display_create_link(); ?>
			<?php $generator->display_count(); ?>
			<?php $generator->display_delete_link(); ?>
			<?php $generator->display_view_link(); ?>
		</p>
		<?php
	}

	/**
	 * @since 1.0
	 */
	public function display_actions_post() {
		foreach ( $this->get_post_types() as $post_type ) {
			$generator = dc()->get_generator( 'post' )->set_post_type( $post_type );
			?>
			<p>
				<span><?php echo $generator->get_label(); ?>: </span>
				<?php $generator->display_create_link(); ?>
				<?php $generator->display_count(); ?>
				<?php $generator->display_delete_link(); ?>
				<?php $generator->display_view_link(); ?>
			</p>
			<?php
		}
	}

	/**
	 * @since 1.0
	 */
	public function display_actions_post_meta() {

		foreach ( $this->get_post_types() as $post_type ) {
			if ( ! $this->has_post_content( $post_type ) ) {
				continue;
			}

			$type = dc()->get_generator( 'post-meta' )->set_post_type( $post_type );
			?>
			<p>
				<span><?php echo $type->get_label(); ?>: </span>
				<?php $type->display_create_link(); ?>
				<?php $type->display_count(); ?>
				<?php $type->display_delete_link(); ?>
				<?php $type->display_view_link(); ?>
			</p>
			<?php
		}
	}

	/**
	 * @since 1.0
	 */
	public function display_actions_post_meta_acf() {
		foreach ( $this->get_post_types() as $post_type ) {
			if ( ! $this->has_post_content( $post_type ) ) {
				continue;
			}

			$type = dc()->get_generator( 'post-meta-acf' )->set_post_type( $post_type );
			?>
			<p>
				<span><?php echo $type->get_label(); ?>: </span>
				<?php $type->display_create_link(); ?>
				<?php $type->display_count(); ?>
				<?php $type->display_delete_link(); ?>
				<?php $type->display_view_link(); ?>
			</p>
			<?php
		}
	}

	/**
	 * @since 1.0
	 */
	public function display_actions_comment() {

		foreach ( $this->get_post_types() as $post_type ) {
			if ( ! $this->has_post_content( $post_type ) ) {
				continue;
			}

			$type = dc()->get_generator( 'comment' )->set_post_type( $post_type );
			?>
			<p>
				<span><?php echo $type->get_label(); ?>: </span>
				<?php $type->display_create_link(); ?>
				<?php $type->display_count(); ?>
				<?php $type->display_delete_link(); ?>
				<?php $type->display_view_link(); ?>
			</p>
			<?php
		}
	}

	/**
	 * @since 1.0
	 */
	public function display_actions_taxonomy() {

		foreach ( $this->get_taxonomies() as $taxonomy ) {
			$type = dc()->get_generator( 'taxonomy' )->set_taxonomy( $taxonomy );
			?>
			<p>
				<span><?php echo $type->get_label(); ?>: </span>
				<?php $type->display_create_link(); ?>
				<?php $type->display_count(); ?>
				<?php $type->display_delete_link(); ?>
				<?php $type->display_view_link(); ?>
			</p>
			<?php
		}
	}

	/**
	 * Settings Page Template.
	 *
	 * This function in conjunction with others usei the WordPress
	 * Settings API to create a settings page where users can adjust
	 * the behaviour of this plugin.
	 *
	 * @since 0.1
	 */
	public function plugin_settings_page() {
		$attachments = get_posts( array( 'post_type' => 'attachment', 'fields' => 'ids', 'numberposts' => 20 ) );
		?>
		<style type="text/css">
			.cpdc-table {
				margin-bottom: 40px;
			}

			.cpdc-table thead td {
				font-weight: bold;
				font-size: 21px;
				border-bottom: 1px solid #ccc;
				border-right: 22px solid #f1f1f1;
				background: #808080;
				color: #fff;
			}

			.cpdc-table tbody td {
				vertical-align: top;
				background: #fff;
				border-right: 22px solid #f1f1f1;
			}

			.cpdc-table tbody td:nth-child(0) {
				background: red;
			}

			.cpdc-table tbody td a {
				padding-left: 10px;
			}

		</style>
		<div id="cpdc" class="wrap">
			<h2><?php _e( 'Dummy Content', 'dummy-content' ); ?></h2>

			<?php if ( count( $attachments ) < 20 ) : ?>
				<p>
					Found <?php echo count( $attachments ); ?> attachments. <a href="<?php echo admin_url( 'media-new.php' ); ?>">Upload more dummy media</a>.
				</p>
			<?php endif; ?>
			<p>
				For the best dummy data create them from left to right, starting with Users.
			</p>

			<form method="post" action="options.php">

				<?php settings_fields( 'cptw-settings-group' ); ?>

				<table class="form-table cpdc-table">
					<thead>
					<tr>
						<td>Users</td>
						<td>Taxonomy Terms</td>
						<td>Posttypes</td>
						<td>Comments</td>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td><?php $this->display_actions( 'user' ); ?></td>
						<td><?php $this->display_actions_taxonomy(); ?></td>
						<td><?php $this->display_actions_post(); ?></td>
						<td><?php $this->display_actions_comment(); ?></td>
					<tr>
					</tbody>
				</table>

				<table class="form-table cpdc-table">
					<thead>
					<tr>
						<td>Meta</td>
						<td>Meta - ACF</td>
						<td>WooCommerce</td>
						<td>Randomize Authors</td>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td>
							<?php $this->display_actions_post_meta(); ?>
							<?php $this->display_actions( 'user-meta' ); ?>
						</td>
						<td>
							<?php $this->display_actions_post_meta_acf(); ?>
							<?php $this->display_actions( 'user-meta-acf' ); ?>
						</td>
						<td>
							<?php $this->display_actions( 'wc-product' ); ?>
						</td>
						<td><?php $this->display_actions( 'randomize-authors' ); ?></td>
					<tr>
					</tbody>
				</table>

			</form>
		</div>
		<?php
	}
}