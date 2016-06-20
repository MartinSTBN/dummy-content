<?php
/**
 * @since 1.0
 */
class CPDC_Type_Media extends CPDC_Type {

	private $attachment_ids = null;

	/**
	 * @since 1.0
	 */
	public function __construct() {

		$this->id = 'attachment';
		$this->meta_type = 'post';
		$this->label = 'Media';
	}

	public function get_random( $mime_type = 'image' ) {
		return get_post( $this->id( $mime_type ) );
	}

	public function id( $mime_type = 'image' ) {
		if ( null === $this->attachment_ids ) {
			$this->attachment_ids = get_posts( array(
				'post_type' => 'attachment',
				'fields' => 'ids',
				'post_mime_type' => $mime_type,
			));
		}
		return $this->random( $this->attachment_ids );
	}

	public function ids( $min = 1, $max = 10, $mime_type = 'image' ) {
		$ids = array();
		for ( $i=1; $i <= mt_rand( (int)$min, (int)$max ); $i++ ) {
			$ids[] = $this->id( $mime_type );
		}
		return $ids;
	}

	private function add_random_id( $attachment_id ) {
		$this->attachment_ids[] = $attachment_id;
	}

	public function delete() {
		// todo
	}

	public function create( $amount = 100 ) {

		// todo
	}
}