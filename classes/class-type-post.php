<?php

/**
 * @since 1.0
 */
class CPDC_Type_Post extends CPDC_Type {

	public $post_type;

	private $ids = null;

	public function __construct() {

		$this->meta_type = 'post';
	}

	public function set_post_type( $post_type ) {

		$this->id = $post_type;
		$this->post_type = $post_type;
		$this->label = ucfirst( $post_type );

		return $this;
	}

	public function get_create_link() {
		return add_query_arg( array( 'post_type' => $this->post_type ), parent::get_create_link() );
	}

	public function get_delete_link() {
		return add_query_arg( array( 'post_type' => $this->post_type ), parent::get_delete_link() );
	}

	public function get_random( $post_type = '' ) {
		return get_post( $this->id( $post_type ) );
	}

	public function get_post_type( $args = array() ) {
		$args = wp_parse_args( $args, array( 'public' => true ) );

		$post_types = get_post_types( $args );
		if ( isset( $post_types['attachment'] ) ) {
			unset( $post_types['attachment'] );
		}

		return $this->random( $post_types );
	}

	public function get_title( $post_type = '' ) {
		if ( ! $post = $this->get_random( $post_type ) ) {
			return false;
		}

		return $post->post_title;
	}

	public function id( $post_type = '' ) {
		if ( ! $post_type ) {
			$post_type = $this->get_post_type();
		}
		if ( ! post_type_exists( $post_type ) ) {
			return false;
		}
		$id = get_posts( array(
			'post_type'      => $post_type,
			'fields'         => 'ids',
			'posts_per_page' => 1,
			'orderby'        => 'rand'
		) );

		return isset( $id[0] ) ? $id[0] : false;
	}

	public function ids( $min = 1, $max = 10, $post_type = '' ) {
		$ids = array();
		for ( $i = 1; $i <= mt_rand( (int) $min, (int) $max ); $i++ ) {
			$ids[] = $this->id( $post_type );
		}

		return $ids;
	}

	private function add_random_id( $post_type = 'any', $post_id ) {
		$this->ids[ $this->post_type ][] = $post_id;
	}

	public function get_created_ids() {
		return get_posts( array(
			'post_type'      => $this->post_type,
			'fields'         => 'ids',
			'posts_per_page' => -1,
			'post_status'    => array( 'any', 'trash' ),
			'meta_query'     => array(
				array(
					'key'   => '_created_by_dummy_content',
					'value' => 1
				)
			)
		) );
	}

	public function delete() {
		$this->delete_cache( 'count' );

		$ids = $this->get_created_ids();

		if ( ! $ids ) {
			return new WP_Error( 'empty', "No stored {$this->post_type} found." );
		}

		$deleted_ids = array();
		foreach ( $ids as $id ) {
			$deleted = wp_delete_post( $id, true );
			if ( $deleted ) {
				$deleted_ids[] = $id;
			}
		}

		if ( ! $deleted_ids ) {
			return new WP_Error( 'failed_delete', "No {$this->post_type} deleted." );
		}

		$this->delete_ids();

		return $deleted_ids;
	}

	public function create( $amount = 30 ) {

		if ( ! post_type_exists( $this->post_type ) ) {
			return new WP_Error( 'not_exists', "Posttype {$this->post_type} does not exists." );
		}

		$added_ids = array();

		$post_status = array( 'draft', 'publish', 'pending', 'private' );
		$ping_status = array( 'open', 'closed' );

		$post_type = $this->post_type;
		$taxonomies = get_object_taxonomies( $post_type, 'names' );

		// Load generator classes
		$_user = dc()->get_generator( 'user' );
		$_media = dc()->get_generator( 'media' );
		$_taxonomy = dc()->get_generator( 'taxonomy' );

		for ( $i = 1; $i <= $amount; $i++ ) {

			//$more_tag = $i %4 ==0 && mt_rand( 0, 1 ) ? '<!--more-->' : '';

			$image_id = $_media->id( 'image' );
			$image = $image_id ? wp_get_attachment_image( $image_id, 'thumbnail', '', array( 'class' => 'wp-image-' . $image_id ) ) : '';

			$contents = CPDC_Data_Generator::contents();
			$article = $this->random( $contents );

			$content = isset( $article['content'] ) ? $article['content'] : '';
			$title = isset( $article['title'] ) ? $article['title'] : '';

			$args = array(
				'post_title'     => $title,
				'post_type'      => $post_type,
				'post_author'    => $_user->id(),
				'post_status'    => CPDC_Data_Generator::true( 80 ) ? 'publish' : $this->random( $post_status ),
				'menu_order'     => mt_rand( 1, 300 ),
				'post_content'   => CPDC_Data_Generator::true( 90 ) ? $content . $image : '',
				'post_excerpt'   => CPDC_Data_Generator::true( 40 ) ? $content : '',
				'ping_status'    => $this->random( $ping_status ),
				'comment_status' => $this->random( $ping_status ),
			);

			// 20% chance to set post_parent
			if ( is_post_type_hierarchical( $post_type ) && CPDC_Data_Generator::true( 20 ) ) {
				$args['post_parent'] = $this->id( $post_type );
			}

			$id = wp_insert_post( $args );

			if ( is_wp_error( $id ) || ! $id ) {
				continue;
			}

			if ( 'post' == $post_type ) {
				set_post_format( $id, $this->random( get_post_format_strings() ) );
			}

			add_post_meta( $id, '_thumbnail_id', $_media->id( 'image' ) );

			// add terms
			if ( $taxonomies ) {
				foreach ( $taxonomies as $taxonomy ) {
					if ( in_array( $taxonomy, array( 'post_format' ) ) ) {
						continue;
					}
					wp_set_post_terms( $id, array( $_taxonomy->term_id( $taxonomy ), $_taxonomy->term_id( $taxonomy ) ), $taxonomy );
				}
			}

			// add to the stack of random ID's
			$this->add_random_id( $post_type, $id );

			update_post_meta( $id, '_created_by_dummy_content', 1 );

			$added_ids[] = $id;
		}

		if ( empty( $added_ids ) ) {
			return new WP_Error( 'failed', 'No posts added.' );
		}

		$this->delete_cache( 'count' );
		$this->store_ids( $added_ids );

		return $added_ids;
	}
}