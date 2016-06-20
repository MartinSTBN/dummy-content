<?php

class CPDC_Type_Taxonomy extends CPDC_Type {

	private $taxonomy;

	private $term_ids = null;

	public function __construct() {
		$this->meta_type = 'taxonomy';
	}

	public function set_taxonomy( $taxonomy = 'any' ) {
		$this->taxonomy = $taxonomy;
		$this->id = 'taxonomy-' . $taxonomy;
		$this->label = $this->get_label();

		return $this;
	}

	public function get_create_link() {
		return add_query_arg( array( 'taxonomy' => $this->taxonomy ), parent::get_create_link() );
	}

	public function get_delete_link() {
		return add_query_arg( array( 'taxonomy' => $this->taxonomy ), parent::get_delete_link() );
	}

	public function term( $taxonomy = '' ) {
		if ( ! $taxonomy ) {
			$taxonomy = $this->random_taxonomy();
		}
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return false;
		}

		return get_term( $this->term_id( $taxonomy ), $taxonomy );
	}

	public function term_id( $taxonomy = '' ) {
		if ( ! $taxonomy ) {
			$taxonomy = $this->random_taxonomy();
		}
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return false;
		}
		if ( ! isset( $this->term_ids[ $taxonomy ] ) ) {
			$this->term_ids[ $taxonomy ] = get_terms( $taxonomy, array( 'fields' => 'ids', 'hide_empty' => false ) );
		}

		return $this->random( $this->term_ids[ $taxonomy ] );
	}

	public function term_ids( $min = 1, $max = 10, $taxonomy = '' ) {
		$ids = array();
		for ( $i = 1; $i <= mt_rand( (int) $min, (int) $max ); $i++ ) {
			$ids[] = $this->term_id( $taxonomy );
		}

		return $ids;
	}

	private function add_random_id( $taxonomy = 'category', $term_id ) {
		$this->term_ids[ $taxonomy ][] = $term_id;
	}

	public function get_label() {
		$tax = get_taxonomy( $this->taxonomy );
		$label = $tax ? $tax->label : false;

		return esc_html( $label );
	}

	public function get_terms_names() {
		$term_names = array(
			'CSS',
			'HTML',
			'JavaScript',
			'Techniques',
			'Design',
			'Web Design',
			'Typography',
			'Inspiration',
			'Business',
			'Mobile',
			'Responsive',
			'iPhone & iPad',
			'Android',
			'Design Patterns',
			'Graphics',
			'Photoshop',
			'Fireworks',
			'Wallpapers',
			'Freebies',
			'UX design',
			'Usability',
			'User Experience',
			'UI Design',
			'E-Commerce',
			'WordPress',
			'Essentials',
			'Techniques',
			'Plugins',
			'Themes',
			'Documentation',
			'Website',
			'HTML5',
			'SASS',
			'LESS',
			'Node',
			'Writing',
			'Pixels',
			'Code Poetry',
			'Downloads',
			'Useful Snippets',
			'Manual',
			'Upcoming events',
			'SEO',
			'Past events',
			'Outdoor',
			'Photography',
			'Frameworks',
			'Markup',
			'Chat',
			'GIT',
			'Music',
			'Movies',
			'Browser',
			'Mobile experience',
			'Newsletters',
			'Analytics',
			'Software Development',
			'Plugin Development'
		);

		return $term_names;
	}

	/**
	 * Get term names
	 *
	 * @since 1.0.0
	 */
	public function get_term_name() {
		return $this->random( $this->get_term_names() );
	}

	public function random_taxonomy( $post_type = '' ) {

		if ( $post_type ) {
			$taxonomies = get_object_taxonomies( $post_type, 'names' );
		}
		else {
			$taxonomies = get_taxonomies( '', 'names' );
		}

		$exclude = array(
			'nav_menu',
			'link_category',
			'post_format',
			'product_type', // woocommerce
			'product_shipping_class', // woocommerce
			'shop_order_status' // woocommerce
		);
		foreach ( $taxonomies as $k => $taxonomy ) {
			if ( in_array( $taxonomy, $exclude ) ) {
				unset( $taxonomies[ $k ] );
			}
		}

		return $this->random( $taxonomies );
	}

	public function delete() {
		$this->delete_cache( 'count' );

		$term_ids = $this->get_stored_ids();

		if ( ! $term_ids ) {
			return new WP_Error( 'empty', 'No stored terms found.' );
		}

		$deleted_ids = array();
		foreach ( $term_ids as $term_id ) {
			$deleted = wp_delete_term( $term_id, $this->taxonomy );
			if ( $deleted ) {
				$deleted_ids[] = $term_id;
			}
		}
		$this->delete_ids();

		return $deleted_ids;
	}

	public function create( $amount = 28 ) {
		$added_ids = array();

		$term_names = CPDC_Data_Generator::random_values( $this->get_terms_names(), $amount );

		foreach ( $term_names as $name ) {

			$created_term = wp_insert_term(
				$name,
				$this->taxonomy,
				array(
					'description' => wp_trim_words( strip_tags( CPDC_Data_Generator::content() ), 10, '' ),
					'parent'      => CPDC_Data_Generator::true( 30 ) ? $this->term_id( $this->taxonomy ) : ''
				)
			);

			if ( is_wp_error( $created_term ) ) {
				$this->add_notice( $name . '. ' . $created_term->get_error_message(), 'error' );
				continue;
			}

			$term_id = $created_term['term_id'];

			$this->add_random_id( $this->taxonomy, $term_id );

			$added_ids[] = $term_id;

			$this->delete_cache( 'count' );
		}

		if ( empty( $added_ids ) ) {
			return new WP_Error( 'failed', 'No terms added.' );
		}

		$this->delete_cache( 'count' );
		$this->store_ids( $added_ids );

		return $added_ids;
	}
}