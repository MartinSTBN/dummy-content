<?php

class CPDC_Type_Comment extends CPDC_Type {

	public $post_type;

	private $comment_ids = null;

	/**
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->meta_type = 'comment';
	}

	/**
	 * @since 1.0
	 */
	public function set_post_type( $post_type ) {

		$this->post_type = $post_type;
		$this->id = $post_type . '-comment';
		$this->label = ucfirst( $post_type ) . ' comments';

		return $this;
	}

	/**
	 * @since 1.0
	 */
	public function get_create_link() {
		return add_query_arg( array( 'post_type' => $this->post_type ), parent::get_create_link() );
	}

	/**
	 * @since 1.0
	 */
	public function get_delete_link() {
		return add_query_arg( array( 'post_type' => $this->post_type ), parent::get_delete_link() );
	}

	/**
	 * @since 1.0.0
	 */
	public function get_random() {
		return get_comment( $this->id() );
	}

	/**
	 * @since 1.0.0
	 */
	public function id() {
		if ( null === $this->comment_ids ) {
			$this->comment_ids = get_comments( array( 'fields' => 'ids', 'status' => 'approve' ) );
		}

		return $this->random( $this->comment_ids );
	}

	/**
	 * Add a random ID to the stack
	 *
	 * @since 1.0
	 */
	private function add_random_id( $comment_id ) {
		$this->comment_ids[] = $comment_id;
	}

	/**
	 * @since 1.0
	 */
	public function get_created_ids() {
		return get_comments( array(
			'fields'    => 'ids',
			'key'       => '_created_by_dummy_content',
			'value'     => 1,
			'post_type' => $this->post_type
		) );
	}

	/**
	 * @since 1.0.0
	 */

	public function delete() {
		if ( ! $comment_ids = $this->get_created_ids() ) {
			return new WP_Error( 'empty', 'No stored comments found.' );
		}

		$deleted_ids = array();
		foreach ( $comment_ids as $id ) {
			$deleted = wp_delete_comment( $id, true );
			if ( $deleted ) {
				$deleted_ids[] = $id;
			}
		}

		if ( ! $deleted_ids ) {
			return new WP_Error( 'failed_delete', 'No comments deleted.' );
		}

		// reset
		$this->delete_cache( 'count' );
		$this->delete_ids();

		return $deleted_ids;
	}

	/**
	 * @since 1.0.0
	 */
	public function create( $amount = 30 ) {
		$added_ids = array();

		$user = dc()->get_generator( 'user' );
		$post = dc()->get_generator( 'post' );

		for ( $i = 1; $i <= $amount; $i ++ ) {

			$random_user = $user->get_random();

			$args = array(
				'comment_post_ID'      => $post->id( $this->post_type ),
				'comment_content'      => CPDC_Data_Generator::content( true, 100 ),
				'user_id'              => $random_user->ID,
				'comment_author'       => $random_user->data->user_login,
				'comment_author_email' => $random_user->data->user_email,
				'comment_author_url'   => $random_user->data->user_url,
				'comment_author_IP'    => CPDC_Data_Generator::ip(),
				'comment_date'         => CPDC_Data_Generator::date(),
				'comment_approved'     => CPDC_Data_Generator::true( 88 ),
				'comment_agent'        => CPDC_Data_Generator::browser_agent(),
				'comment_parent'       => CPDC_Data_Generator::true( 18 ) ? $this->id() : 0,
			);

			$comment_id = wp_insert_comment( $args );

			if ( $comment_id ) {
				$this->add_random_id( $comment_id );

				update_comment_meta( $comment_id, '_created_by_dummy_content', 1 );

				$added_ids[] = $comment_id;
			}
		}

		if ( empty( $added_ids ) ) {
			return new WP_Error( 'failed', 'No comments added.' );
		}

		$this->delete_cache( 'count' );
		$this->store_ids( $added_ids );

		return $added_ids;
	}
}