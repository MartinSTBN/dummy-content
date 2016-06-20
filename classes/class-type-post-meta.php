<?php

class CPDC_Type_Post_Meta extends CPDC_Type_Meta {

	protected $post_type;

	public function __construct() {
		$this->meta_type = 'post-meta';
	}

	public function set_post_type( $post_type ) {

		$this->id = $post_type . '-meta';
		$this->post_type = $post_type;
		$this->label = 'Meta ' . $post_type;

		return $this;
	}

	public function get_create_link() {
		return add_query_arg( array( 'post_type' => $this->post_type ), parent::get_create_link() );
	}

	public function get_delete_link() {
		return add_query_arg( array( 'post_type' => $this->post_type ), parent::get_delete_link() );
	}

	public function delete() {
		$this->delete_cache( 'count' );

		global $wpdb;

		$ids = $this->get_stored_ids();
		if ( ! $ids ) {
			return new WP_Error( 'no_stored', 'No stored meta found.' );
		}

		$deleted_ids = array();
		foreach ( $ids as $id ) {
			$deleted = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE meta_id = %d", $id ) );
			if ( $deleted ) {
				$deleted_ids[] = $id;
			}
		}

		$this->delete_ids();

		return $deleted_ids;
	}

	public function create() {

		$added_ids = array();

		$post_ids = get_posts( array(
			'post_type'      => $this->post_type,
			'fields'         => 'ids',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'   => '_created_by_dummy_content',
					'value' => 1
				)
			)
		) );

		if ( ! $post_ids ) {
			return new WP_Error( 'failed', 'No posts found.' );
		}

		foreach ( $post_ids as $id ) {
			$metadata = $this->get_random();

			foreach ( $metadata as $key => $value ) {

				delete_post_meta( $id, $key );
				$meta_id = update_post_meta( $id, $key, $value );

				if ( $id ) {
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