<?php

abstract class CPDC_Type {

	CONST STORE_KEY = '_created_by_dummy_content';

	protected $id;

	public $meta_type;

	protected $label;

	public $notices;

	/**
	 * Admin Notices
	 *
	 * @since 0.1
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
	protected function add_notice( $message, $type = 'updated' ) {
		$this->notices[] = (object) array(
			'message' => $message,
			'class'   => $type
		);
	}

	/**
	 * @since 1.0
	 */
	public function get_option_name() {
		return 'cpdc_' . $this->meta_type . '_' . $this->id;
	}

	private function get_storage_key(  ) {
		return self::STORE_KEY . $this->id;
	}

	/**
	 * @since 1.0
	 */
	public function store_id( $id ) {
		$ids = $this->get_stored_ids();
		if ( ! in_array( $id, $ids ) ) {
			$ids[] = $id;
		}
		update_option( $this->get_storage_key(), $ids );
	}

	public function store_ids( $ids ) {
		if ( $ids ) {
			if ( $stored_ids = $this->get_stored_ids() ) {
				$ids = array_unique( array_merge( $ids, $stored_ids ) );
			}
			update_option( $this->get_storage_key(), $ids );
		}
	}

	/**
	 * @since 1.0
	 */
	public function get_stored_ids() {
		return get_option( $this->get_storage_key(), array() );
	}

	/**
	 * @since 1.0
	 */
	public function delete_ids() {
		delete_option( $this->get_storage_key() );
	}

	/**
	 * @since 1.0
	 */
	public function delete_cache( $name ) {
		delete_transient( '_cpdcache_' . $this->id . $name );
	}

	/**
	 * @since 1.0
	 */
	public function set_cache( $name, $value ) {
		set_transient( '_cpdcache_' . $this->id . $name, $value );
	}

	/**
	 * @since 1.0
	 */
	public function get_cache( $name ) {
		return get_transient( '_cpdcache_' . $this->id . $name );
	}

	/**
	 * @since 1.0
	 */
	public function get_count() {
		$count = $this->get_cache( 'count' );
		if ( ! $count ) {
			$ids = $this->get_stored_ids();
			$count = $ids ? count( $ids ) : false;
			$this->set_cache( 'count', $count );
		}

		return $count;
	}

	/**
	 * @since 1.0
	 */
	public function display_create_link() { ?>
		<a href="<?php echo $this->get_create_link(); ?>"><?php _e( 'Create', 'cpdc' ); ?></a>
		<?php
	}

	/**
	 * @since 1.0
	 */
	public function display_count() {
		if ( $count = $this->get_count() ) : ?>
			(<?php echo $count; ?>)
		<?php endif;
	}

	/**
	 * @since 1.0
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * @since 1.0
	 */
	public function get_view_link() {
	}

	/**
	 * @since 1.0
	 */
	public function display_view_link() {
		return; ?>
		<a href="<?php echo $this->get_view_link(); ?>"><?php _e( 'View', 'cpdc' ); ?></a>
		<?php
	}

	/**
	 * @since 1.0
	 */
	public function get_create_link() {

		$defaults = array(
			'action'    => 'create',
			'meta_type' => $this->meta_type,
		);

		return add_query_arg( $defaults, admin_url( 'admin.php?page=dummy-content' ) );
	}

	/**
	 * @since 1.0
	 */
	public function display_delete_link() {
		if ( $this->get_delete_link() ) : ?>
			<a href="<?php echo $this->get_delete_link(); ?>"><?php _e( 'Delete', 'cpdc' ); ?></a>
			<?php
		endif;
	}

	/**
	 * @since 1.0
	 */
	public function get_delete_link() {

		$create_args = array(
			'action'    => 'delete',
			'meta_type' => $this->meta_type,
			'id'        => $this->id
		);

		return add_query_arg( $create_args, admin_url( 'admin.php?page=dummy-content' ) );
	}

	/**
	 * @since 1.0
	 */
	protected function random( $array ) {
		return CPDC_Data_Generator::random( $array );
	}

	/**
	 * @since 1.0
	 */
	public function display_title() {
		echo apply_filters( 'the_title', $this->label );
	}
}