<?php

class CPDC_Type_User_Meta_ACF extends CPDC_Type_User_Meta {

	/**
	 * @since 1.0
	 */
	public function __construct() {

		$this->id = 'user-meta-acf';
		$this->meta_type = 'user-meta-acf';
		$this->label = 'ACF User Meta';
	}

	/**
	 * @since 1.0
	 */
	public function get_label() {
		return 'ACF User Meta';
	}

	/**
	 * @since 1.0
	 */
	public function get_random_email() {
		$users = $this->get_user_names();
		$user = $this->get_random( $users );

		return strtolower( sanitize_user( $user[0], true ) ) . '@' . strtolower( sanitize_user( $user[1], true ) ) . '.com';
	}

	/**
	 * @since 1.0
	 */
	protected function get_random_metadata() {
		$metadata = array(
			'acf_text' 	=> $this->get_random_title(),
			'acf_textarea' 	=> $this->get_random_content( true ),
			'acf_number' 	=> mt_rand( 1, 300 ),
			'acf_email' 	=> $this->get_random_email(),
			'acf_url' 		=> $this->get_random_url(),
			'acf_password' 	=> $this->get_random_string(14),
			'acf_wysiwyg' 	=> $this->get_random_content(),
			'acf_oembed' 	=> $this->get_random_oembed(),
			'acf_image' 	=> $this->get_random_attachment_id('image'),
			'acf_file' 		=> $this->get_random_attachment_id('application/pdf'),
			'acf_gallery' 	=> array( $this->get_random_attachment_id('image'), $this->get_random_attachment_id('image') ),
			'acf_select' 	=> mt_rand( 1, 5 ),
			'acf_multi_select' 	=> array( mt_rand( 1, 5 ), mt_rand( 1, 5 ) ),
			'acf_checkbox' 	=> array( mt_rand( 1, 5 ), mt_rand( 1, 5 ) ),
			'acf_radio_button' 	=> mt_rand( 1, 5 ),
			'acf_true_false' => $this->get_random_boolean(),
			'acf_page_link' => $this->get_random_post( array('post','page') ),
			'acf_post_object' => $this->get_random_post( array('post','page') ),
			'acf_post_objects' => array( $this->get_random_post( array('post','page') ), $this->get_random_post( array('post','page') ) ),
			'acf_relationship' => array( $this->get_random_post( array('post','page') ), $this->get_random_post( array('post','page') ) ),
			'acf_taxonomy' => $this->get_random_term_id( 'category' ),
			'acf_taxonomy_checkbox' => array( $this->get_random_term_id( 'category' ), $this->get_random_term_id( 'category' ) ),
			'acf_taxonomy_multiselect' => array( $this->get_random_term_id( 'category' ), $this->get_random_term_id( 'category' ) ),
			'acf_taxonomy_radio' => $this->get_random_term_id( 'category' ),
			'acf_taxonomy_select' => $this->get_random_term_id( 'category' ),
			'acf_user' => $this->get_random_user_id(),
			'acf_users' => array( $this->get_random_user_id(), $this->get_random_user_id(), $this->get_random_user_id() ),
			'acf_google_map' => array( 'address' => 'Netherlands', 'lat' => '52.132633', 'lng' => '5.2912659999999505' ),
			'acf_date_picker' => date( 'Ymd', strtotime( $this->get_random_date() ) ),
			'acf_color_picker' => $this->get_random_color(),
			//'acf_repeater' => '',
			//'acf_flexible_content' => '',
		);

		return $metadata;
	}
}