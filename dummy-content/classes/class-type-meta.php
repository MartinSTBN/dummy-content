<?php

class CPDC_Type_Meta extends CPDC_Type {

	/**
	 * Get random
	 *
	 */
	public function get_random() {

		$user = new CPDC_Type_User();
		$post = new CPDC_Type_Post();
		$media = new CPDC_Type_Media();

		$meta = array(
			'color' 	=> CPDC_Data_Generator::color(),
			'image' 	=> $media->id('image'),
			'excerpt' 	=> CPDC_Data_Generator::string(),
			'array' 	=> array( CPDC_Data_Generator::string(6), CPDC_Data_Generator::string(6) ),
			'numeric' 	=> mt_rand( 1, 99 ),
			'date' 		=> CPDC_Data_Generator::date(),
			'post_id' 	=> $post->id(),
			'user_id' 	=> $user->id(),
			'checkmark' => CPDC_Data_Generator::boolean(),
			'_thumbnail_id' => $media->id('image'), // featured image
		);
		return $meta;
	}
}