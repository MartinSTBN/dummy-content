<?php

class CPDC_Type_Post_Meta_ACF extends CPDC_Type_Post_Meta {

	protected $post_type;

	private $groups;

	public function __construct() {
		$this->meta_type = 'post-meta-acf';
	}

	public function set_post_type( $post_type ) {

		$this->post_type = $post_type;
		$this->id = $post_type . '-meta-acf';
		$this->label = 'ACF Meta ' . ucfirst( $post_type );

		return $this;
	}

	public function get_terms() {
		$taxonomy = new CPDC_Type_Taxonomy();
		$tax = $taxonomy->random_taxonomy( $this->post_type );

		$terms = array();
		if ( $term_ids = $taxonomy->term_ids( 1, 6, $tax ) ) {
			foreach ( $term_ids as $term_id ) {
				$terms[] = array( 'term_id' => $term_id, 'taxonomy' => $tax );
			}
		}

		return $terms;
	}

	private function get_groups() {

		if ( ! $this->groups ) {

			// Applies to all
			add_filter( 'acf/location/rule_match/user_type', '__return_true', 16 );

			// Applies to pages
			if ( 'page' == $this->post_type ) {
				add_filter( 'acf/location/rule_match/page', '__return_true', 16 );
				add_filter( 'acf/location/rule_match/page_type', '__return_true', 16 );
				add_filter( 'acf/location/rule_match/page_parent', '__return_true', 16 );
				add_filter( 'acf/location/rule_match/page_template', '__return_true', 16 );
			}

			// Applies to posts only
			if ( 'post' == $this->post_type ) {
				add_filter( 'acf/location/rule_match/post_format', '__return_true', 16 );
			}

			if ( 'attachment' == $this->post_type ) {
				add_filter( 'acf/location/rule_match/post_type', '__return_true', 16 );
				add_filter( 'acf/location/rule_match/post', '__return_true', 16 );
				add_filter( 'acf/location/rule_match/post_category', '__return_true', 16 );
				add_filter( 'acf/location/rule_match/post_status', '__return_true', 16 );
				add_filter( 'acf/location/rule_match/post_taxonomy', '__return_true', 16 );

				add_filter( 'acf/location/rule_match/attachment', '__return_true', 16 );
			}

			// any post type
			add_filter( 'acf/location/rule_match/post_type', '__return_true', 16 );
			add_filter( 'acf/location/rule_match/post', '__return_true', 16 );
			add_filter( 'acf/location/rule_match/post_category', '__return_true', 16 );
			add_filter( 'acf/location/rule_match/post_status', '__return_true', 16 );
			add_filter( 'acf/location/rule_match/post_taxonomy', '__return_true', 16 );

			$this->groups = acf_get_field_groups( true );

			// Remove all location filters for the next storage_model
			remove_filter( 'acf/location/rule_match/page', '__return_true', 16 );
			remove_filter( 'acf/location/rule_match/page_type', '__return_true', 16 );
			remove_filter( 'acf/location/rule_match/page_parent', '__return_true', 16 );
			remove_filter( 'acf/location/rule_match/page_template', '__return_true', 16 );

			remove_filter( 'acf/location/rule_match/post_format', '__return_true', 16 );

			remove_filter( 'acf/location/rule_match/post_type', '__return_true', 16 );
			remove_filter( 'acf/location/rule_match/post', '__return_true', 16 );
			remove_filter( 'acf/location/rule_match/post_category', '__return_true', 16 );
			remove_filter( 'acf/location/rule_match/post_status', '__return_true', 16 );
			remove_filter( 'acf/location/rule_match/post_taxonomy', '__return_true', 16 );

			remove_filter( 'acf/location/rule_match/attachment', '__return_true', 16 );
		}

		return $this->groups;
	}

	private function get_data_by_type( $type, $multiple = false, $args = array() ) {

		$user = new CPDC_Type_User();
		$post = new CPDC_Type_Post();
		$media = new CPDC_Type_Media();
		$taxonomy = new CPDC_Type_Taxonomy();

		$data = null;
		switch ( $type ) {
			case 'text' :
				$data = $post->get_title();
				break;
			case 'textarea' :
				$data = CPDC_Data_Generator::content( true, 50 );
				break;
			case 'number' :
				$data = mt_rand( 1, 300 );
				break;
			case 'email' :
				$data = $user->get_email();
				break;
			case 'url' :
				$data = CPDC_Data_Generator::url();
				break;
			case 'password' :
				$data = CPDC_Data_Generator::password( 14 );
				break;
			case 'wysiwyg' :
				$data = CPDC_Data_Generator::content( false, 100 );
				break;
			case 'oembed' :
				$data = CPDC_Data_Generator::oembed();
				break;
			case 'image' :
				$data = $media->id( 'image' );
				break;
			case 'file' :
				$data = $media->id( 'application/pdf' );
				break;
			case 'gallery' :
				$data = $media->ids();
				break;
			case 'select' :
				if ( $multiple ) {
					$data = array( mt_rand( 1, 5 ), mt_rand( 1, 5 ) );
				}
				else {
					$data = mt_rand( 1, 5 );
				}
				break;
			case 'checkbox' :
				$data = array( mt_rand( 1, 5 ), mt_rand( 1, 5 ) );
				break;
			case 'radio' :
				$data = mt_rand( 1, 5 );
				break;
			case 'true_false' :
				$data = CPDC_Data_Generator::boolean();
				break;
			case 'page_link' :
				$data = $post->id();
				break;
			case 'post_object' :
				if ( $multiple ) {
					$data = $post->ids( 1, 6 );
				}
				else {
					$data = $post->id();
				}
				break;
			case 'relationship' :
				$data = $post->ids( 1, 6, $post->get_post_type() );
				break;
			case 'taxonomy' :
				if ( in_array( $args['field_type'], array( 'multi_select', 'checkbox' ) ) ) {
					$data = $taxonomy->term_ids( 1, 6, $args['taxonomy'] );
				}
				else {
					$data = $taxonomy->term_id( $args['taxonomy'] );
				}
				break;
			case 'user' :
				$data = $user->id();
				break;
			case 'google_map' :
				$data = CPDC_Data_Generator::coordinate();
				break;
			case 'date_picker' :
				$data = date( 'Ymd', strtotime( CPDC_Data_Generator::date() ) );
				break;
			case 'color_picker' :
				$data = CPDC_Data_Generator::color();
				break;
			case 'message' :
				break;
			case 'date_time_picker' :
				break;
			case 'tab' :
				break;
			case 'repeater' :
				break;
			case 'flexible_content' :
				break;
		}

		return $data;
	}

	public function get_random() {
		$metadata = array();
		foreach ( $this->get_groups() as $group ) {
			$fields = acf_get_fields( $group );
			foreach ( $fields as $field ) {
				$metadata[ $field['name'] ] = $this->get_data_by_type( $field['type'], ! empty( $field['multiple'] ), $field );
			}
		}

		return $metadata;
	}
}