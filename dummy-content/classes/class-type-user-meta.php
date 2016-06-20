<?php

class CPDC_Type_User_Meta extends CPDC_Type_Meta {

	/**
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->id = 'user-meta';
		$this->meta_type = 'user-meta';
		$this->label = 'User Meta';
	}

	/**
	 * @since 1.0.0
	 */
	public function get_label() {

		return 'User Meta';
	}

	/**
	 * @since 1.0.0
	 */
	public function delete() {
		global $wpdb;

		$deleted_ids = array();

		$ids = $this->get_stored_ids();
		if ( ! $ids ) {
			return new WP_Error( 'no_stored', 'No stored meta found.' );
		}

		foreach ( $ids as $id ) {

			$deleted = $wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE umeta_id = {$id}" );

			if ( $deleted ) {
				$deleted_ids[] = $id;
			}
		}

		$this->delete_ids();

		if ( ! $deleted_ids ) {
			return new WP_Error( 'no_meta', 'No meta deleted.' );
		}

		return $deleted_ids;
	}

	/**
	 * @since 1.0.0
	 */
	public function create() {

		$added_ids = array();

		$user_ids = get_users( array( 'fields' => 'ids' ) );
		foreach ( $user_ids as $id ) {

			$metadata = $this->get_random();
			foreach ( $metadata as $key => $value ) {

				delete_user_meta( $id, $key );
				$meta_id = update_user_meta( $id, $key, $value );

				if ( $meta_id ) {
					$added_ids[] = $meta_id;
				}
			}
		}

        if ( empty( $added_ids ) ) {
        	return new WP_Error( 'failed', 'No meta added.' );
        }

		$this->delete_cache( 'count' );
		$this->store_ids( $added_ids );

        return $added_ids;
	}
}