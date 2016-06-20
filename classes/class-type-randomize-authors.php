<?php

class CPDC_Type_Randomize_Authors extends CPDC_Type {

	public $post_type;

	/**
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->id = 'randomize-authors';
		$this->meta_type = 'randomize-authors';
		$this->label = 'Randomize Post Authors';
	}

	/**
	 * @since 1.0.0
	 */
	public function set_post_type( $post_type ) {
		$this->post_type = $post_type;
	}

	public function display_create_link() {
		$this->display_randomize_link();
	}


	public function display_delete_link() {
		return;
	}

	/**
	 * @since 1.0.0
	 */
	public function display_randomize_link() {
		$link = $this->get_randomize_link();

		echo "<a href='{$link}'>Randomize authors</a><br/>";
	}

	/**
	 * @since 1.0.0
	 */
	public function get_randomize_link() {
		$create_args = array(
			'action' => 'randomize',
			'meta_type' => $this->meta_type,
			'id' => $this->id
		);

		if ( ! empty( $this->post_type ) ) {
			$create_args['_post_type'] = $this->post_type;
		}

		return add_query_arg( $create_args, admin_url( 'admin.php?page=dummy-content' ) );
	}

	/**
	 * @since 1.0.0
	 */
	public function randomize() {

		$updated_posts = array();

		if ( $posts = get_posts( array( 'post_type' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'post_status' => 'any' ) ) ) {
			foreach ( $posts as $id ) {
				$updated = wp_update_post( array(
					'ID' => $id,
					'post_author' => $this->get_random_user_id()
				) );

				if ( ! is_wp_error( $updated ) && $updated ) {
					$updated_posts[] = $id;
				}
			}
		}

		return $updated_posts;
	}
}