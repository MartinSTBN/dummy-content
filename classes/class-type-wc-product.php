<?php

/**
 * @since 1.0
 */
class CPDC_Type_Wc_Product extends CPDC_Type_Post {

	public function __construct() {
		parent::__construct();

		$this->meta_type = 'wc-product';
		$this->id = 'wc-product';
		$this->post_type = 'product';
		$this->label = ucfirst( $this->post_type );
		$this->product = null;
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

	public function create( $amount = 10 ) {

		if ( ! post_type_exists( $this->post_type ) ) {
			return new WP_Error( 'not_exists', "Posttype {$this->post_type} does not exists." );
		}

		$added_ids = array();

		// Load generator classes
		$_user = dc()->get_generator( 'user' );
		$_media = dc()->get_generator( 'media' );
		$_taxonomy = dc()->get_generator( 'taxonomy' );

		for ( $i = 1; $i <= $amount; $i++ ) {

			$product_type = CPDC_Data_Generator::random( array( 'simple', 'variable' ) );
			$contents = CPDC_Data_Generator::contents();
			$article = $this->random( $contents );

			$content = isset( $article['content'] ) ? $article['content'] : '';

			$args = array(
				'post_title'   => $this->get_product_title(),
				'post_type'    => $this->post_type,
				'post_content' => CPDC_Data_Generator::true( 90 ) ? $content . $image : '',
				'post_excerpt' => CPDC_Data_Generator::true( 40 ) ? $content : '',
			);
			$id = wp_insert_post( $args );
			$this->product = wc_get_product( $id );

			if ( ! $this->product ) {
				continue;
			}

			switch ( $product_type ) {
				case 'simple':
					update_post_meta( $id, '_virtual', CPDC_Data_Generator::random( array( 'yes', 'no' ) ) );
					update_post_meta( $id, '_downloadable', CPDC_Data_Generator::random( array( 'yes', 'no' ) ) );

					break;
			}

			$this->set_prices( $id );
			$this->set_sku( $id );
			$this->set_stock( $id );
			$this->set_dimensions( $id );
			$this->set_images( $id, $_media );


			if ( is_wp_error( $id ) || ! $id ) {
				continue;
			}
			wp_set_object_terms( $id, $product_type, 'product_type' );


			$added_ids[] = $id;
		}

		if ( empty( $added_ids ) ) {
			return new WP_Error( 'failed', 'No posts added.' );
		}

		$this->delete_cache( 'count' );
		$this->store_ids( $added_ids );

		return $added_ids;
	}

	public function get_product_title( $brand = true ) {

		$title = $this->random( $this->get_product_titles() );
		if ( $brand ) {
			$title .= ' - ' . $this->random( $this->get_brands() );
		}

		return $title;
	}

	public function get_product_titles() {
		$titles = array(
			'Watch',
			'Personal computer',
			'Cactus',
			'Lego robot',
			'Pencil',
			'Coffee cup',
			'Keyboard',
			'Paint',
			'Xbox one',
			'Playstation 6',
			'Lavalamp',
			'Beer opener',
			'Touch mouse',
			'Docking station',
			'Webdesigner',
			'Admin Columns Pro',
			'Scooter',
			'Racing bike',
		);

		return $titles;
	}

	public function get_brands() {
		$brands = array(
			'Philips',
			'Apple',
			'Microsoft',
			'Samsung',
			'Bodum',
			'Mediamarkt',
			'Takamine',
			'HTC',
			'Pioneer',
			'DeLonghi',
		);

		return $brands;
	}

	// TODO make media accessible trough class
	public function set_images( $id, $media ) {
		add_post_meta( $id, '_thumbnail_id', $media->id( 'image' ) );

		$number_of_images = mt_rand( 0, 5 );
		$images = array();
		for ( $i = 0; $i < $number_of_images; $i++ ) {
			$images[] = $media->id( 'image' );
		}

		add_post_meta( $id, '_product_image_gallery', implode( ',', array_unique( $images ) ) );
	}

	public function set_stock( $id ) {

		if ( CPDC_Data_Generator::boolean() ) {
			update_post_meta( $id, '_manage_stock', 'yes' );
			update_post_meta( $id, '_backorders', CPDC_Data_Generator::random( array( 'yes', 'no' ) ) );
			update_post_meta( $id, '_stock', rand( 0, 100 ) );
		}

		update_post_meta( $id, '_stock_status', CPDC_Data_Generator::random( array( 'instock', 'outofstock' ) ) );
		update_post_meta( $id, '_sold_individually', CPDC_Data_Generator::random( array( 'yes', 'no' ) ) );
	}

	public function set_dimensions( $id ) {
		if ( $this->product->is_virtual() ) {
			return;
		}

		update_post_meta( $id, '_weight', mt_rand( 1 * 10, 150 * 10 ) / 10 );
		update_post_meta( $id, '_length', mt_rand( 1, 150 ) );
		update_post_meta( $id, '_width', mt_rand( 1, 150 ) );
		update_post_meta( $id, '_height', mt_rand( 1, 150 ) );
	}

	public function set_sku( $id ) {
		if ( $this->product->is_type( 'external' ) ) {
			return;
		}
		update_post_meta( $id, '_sku', mt_rand( 100000, 999999 ) . strtoupper( CPDC_Data_Generator::string( 2, false, false ) ) );
	}

	public function set_prices( $id ) {

		if ( $this->product->is_type( array( 'variable', 'grouped' ) ) ) {
			return;
		}

		$regular_price = CPDC_Data_Generator::price( 0, 200 );
		$sale_price = CPDC_Data_Generator::price( 0, 200 );

		if ( $sale_price > $regular_price ) {
			return;
		}
		update_post_meta( $id, '_sale_price', wc_format_decimal( $sale_price ) );
		update_post_meta( $id, '_regular_price', wc_format_decimal( $regular_price ) );
		update_post_meta( $id, '_price', wc_format_decimal( $regular_price ) );

		if ( CPDC_Data_Generator::true( 30 ) ) {

			update_post_meta( $id, '_sale_price', wc_format_decimal( CPDC_Data_Generator::price( 0, $regular_price - 10 ) ) );
		}

	}
}