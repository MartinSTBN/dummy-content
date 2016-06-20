<?php
/*

Plugin Name: 		Dummy Content
Version: 			1.0
Description: 		Create dummy posts, media, users and comments.
Author: 			Codepress
Author URI: 		http://www.codepress.nl
Plugin URI: 		http://www.codepress.nl
Text Domain: 		dummy-content
Domain Path: 		/languages
License:			GPLv2

Copyright 2011-2014  Codepress  info@codepress.nl

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License version 2 as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CPDC_VERSION', '1.0' );
define( 'CPDC_URL', plugin_dir_url( __FILE__ ) );
define( 'CPDC_DIR', plugin_dir_path( __FILE__ ) );
define( 'CPDC_FILE', __FILE__ );

if ( ! is_admin() ) {
	return false;
}

/**
 * Requests:
 *
 * ?codepress_dummy_content&meta_type=post&action=create&post_type=post
 * ?codepress_dummy_content&meta_type=post&action=delete&post_type=post
 *
 * ?codepress_dummy_content&meta_type=user&action=create
 * ?codepress_dummy_content&meta_type=user&action=delete
 *
 * ?codepress_dummy_content&meta_type=user-meta&action=create
 * ?codepress_dummy_content&meta_type=user-meta&action=delete
 *
 * ?codepress_dummy_content&meta_type=post-meta&action=create
 * ?codepress_dummy_content&meta_type=post-meta&action=delete
 *
 * ?codepress_dummy_content&action=randomize_author&post_type=post
 *
 * @since 1.0
 */
class Dummy_Content {

	/**
	 * @since 0.1
	 */
	private $notices = array();

	public $generators = array();

	/**
	 * @since 2.5
	 */
	protected static $_instance = null;

	/**
	 * @since 2.5
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * @since 1.0
	 */
	public function __construct() {

		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'init', array( $this, 'init_admin' ) );
		add_action( 'init', array( $this, 'localize' ) );
		//add_action( 'wp_loaded', array( $this, 'set_content_types' ), 9 );
		add_action( 'wp_loaded', array( $this, 'handle_requests' ), 10 );
	}

	/**
	 * @since 1.0
	 */
	public function localize() {
		load_plugin_textdomain( 'dummy-content', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	public function get_generator( $id ) {
		$generators = $this->get_generators();

		return isset( $generators[ $id ] ) ? $generators[ $id ] : false;
	}

	private function get_generators() {
		if ( ! $this->generators ) {

			require CPDC_DIR . 'classes/class-data-generator.php';
			require CPDC_DIR . 'classes/class-type.php';

			$types = array(
				'comment',
				'media',
				'post',
				'meta',
				'post-meta',
				'post-meta-acf',
				'user',
				'user-meta',
				'user-meta-acf',
				'randomize-authors',
				'taxonomy',

				// WooCommerce
				'wc-product',
			);

			foreach ( $types as $id ) {
				require 'classes/class-type-' . $id . '.php';
				$classname = 'CPDC_Type_' . str_replace( '-', '_', ucwords( $id ) );
				$this->generators[ $id ] = new $classname;
			}
		}

		return $this->generators;
	}

	/**
	 * @since 1.0
	 */
	public function init_admin() {
		require_once 'classes/class-admin.php';
		new CPDC_Admin( $this );
	}

	/**
	 * Admin Notices
	 *
	 * @since 1.0
	 */
	public function admin_notices() {

		if ( ! $this->notices ) {
			return;
		}

		foreach ( $this->notices as $notice ) {
			?>
			<div class="<?php echo $notice->class; ?>">
				<p><?php echo $notice->message; ?></p>
			</div>
			<?php
		}
	}

	/**
	 * @since 1.0
	 */
	public function handle_requests() {

		if ( ! isset( $_GET['page'] ) || 'dummy-content' !== $_GET['page'] ) {
			return;
		}

		$action = filter_input( INPUT_GET, 'action' );
		$meta_type = filter_input( INPUT_GET, 'meta_type' );

		if ( ! $action ) {
			return;
		}

		if ( ! $meta_type ) {
			$this->add_notice( 'Missing meta type.' );

			return;
		}

		$model = $this->get_generator( $meta_type );

		if ( ! $model ) {
			$this->add_notice( 'Meta type is not supported.' );

			return;
		}

		$processed_ids = array();

		// for development only, should be done using $_POST next version as $_POST['options']
		switch ( $meta_type ) {
			case 'post':
			case 'post-meta':
			case 'post-meta-acf':
			case 'comment':
				$model->set_post_type( $_GET['post_type'] );
				break;
			case 'taxonomy':
				$model->set_taxonomy( $_GET['taxonomy'] );
				break;
		}

		if ( 'create' == $action ) {
			$processed_ids = $model->create();
		}
		if ( 'delete' == $action ) {
			$processed_ids = $model->delete();
		}
		if ( 'randomize' == $action ) {
			$processed_ids = $model->randomize();
		}

		if ( $notices = $this->notices ) {
			$this->notices = array_merge( $this->notices, $notices );
		}

		if ( is_wp_error( $processed_ids ) ) {
			$this->add_notice( $processed_ids->get_error_message(), 'error' );

			return;
		}

		if ( ! $processed_ids ) {
			$this->add_notice( 'No processed Id\'s', 'error' );

			return;
		}

		$this->add_notice( "Total number of processed ID's: " . count( $processed_ids ) );
	}

	/**
	 * @since 1.0
	 */
	public function add_notice( $message, $type = 'updated' ) {
		$this->notices[] = (object) array(
			'message' => $message,
			'class'   => $type
		);
	}
}

function dc() {
	return Dummy_Content::instance();
}

dc();
