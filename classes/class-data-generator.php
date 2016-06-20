<?php

class CPDC_Data_Generator {

	/**
	 * @since 1.0
	 */
	static function random( $array ) {
		if ( empty( $array ) || ! is_array( $array ) ) {
			return;
		}

		return $array[ array_rand( $array ) ];
	}

	static function random_values( $array, $num_req = null ) {
		return array_intersect_key( $array, array_flip( array_rand( $array, $num_req ) ) );
	}

	/**
	 * @since 1.0
	 */
	static function string( $length = 99, $space = false, $random_length = true ) {
		$random_string = "";
		$valid_chars = 'abcdefghijklmnopqrstuvwxyz';
		$random_string = "";
		$num_valid_chars = strlen( $valid_chars );
		if( $random_length ){
			$length = mt_rand( $length, $length + 10 );
		}
		for ( $i = 0; $i < $length; $i++ ) {
			$random_pick = mt_rand( 1, $num_valid_chars );
			$random_char = $valid_chars[ $random_pick - 1 ];

			// add spatial
			if ( $space && $i % 5 == 0 ) {
				$random_string .= ' ';
			}

			$random_string .= $random_char;
		}

		return $random_string;
	}

	/**
	 * @since 1.0
	 */
	static function oembed() {
		$vimeo = array( 'http://vimeo.com/92830752', 'http://vimeo.com/93974038', 'http://vimeo.com/93715681' ); // admin columns video
		return self::random( $vimeo );
	}

	/**
	 * @since 1.0
	 */
	static function ip() {
		return mt_rand( 1, 255 ) . '.' . mt_rand( 1, 255 ) . '.' . mt_rand( 1, 255 ) . '.' . mt_rand( 1, 125 );
	}

	/**
	 * @since 1.0
	 */
	static function password() {
		return self::string( mt_rand( 8, 20 ) );
	}

	/**
	 * @since 1.0
	 */
	static function color() {
		mt_srand( (double) microtime() * 1000000 );
		$c = '';
		while ( strlen( $c ) < 6 ) {
			$c .= sprintf( "%02X", mt_rand( 0, 255 ) );
		}

		return '#' . $c;
	}

	/**
	 * @since 1.0
	 */
	static function browser_agent() {
		return 'Mozilla/' . mt_rand( 5, 13 ) . '.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.10) Gecko/2009042316 Firefox/3.0.10 (.NET CLR 3.5.30729)';
	}

	/**
	 * @since 1.0
	 */
	static function boolean() {
		return mt_rand( 0, 1 );
	}

	/**
	 * @since 1.0
	 */
	static function true( $percentage = 100 ) {
		return mt_rand( 1, 100 ) <= $percentage;
	}

	/**
	 * @since 1.0
	 */
	static function date( $start = "-2 month", $end = "+2 month" ) {
		return date( "Y-m-d H:i:s", mt_rand( strtotime( $start ), strtotime( $end ) ) );
	}

	/**
	 * @since 1.0
	 */
	static function url() {
		return 'http://www.' . self::string( 10 ) . '.com';
	}

	/**
	 * @since 1.0
	 */
	static function title() {
		$content = self::random( self::contents() );

		return $content['title'];
	}

	/**
	 * @since 1.0
	 */
	static function content( $strip_tags = false, $max_words = null ) {
		$content = self::random( self::contents() );
		$content = $content['content'];
		if ( $strip_tags ) {
			$content = strip_tags( $content );
		}

		if ( $max_words && is_numeric( $max_words ) ) {
			$content = wp_trim_words( $content, absint( $max_words ), '' );
		}

		return $content;
	}

	static function randomize_string( $string ) {
		if ( preg_match_all( '/(?<={)[^}]*(?=})/', $string, $matches ) ) {
			$matches = reset( $matches );
			foreach ( $matches as $i => $match ) {
				if ( preg_match_all( '/(?<=\[)[^\]]*(?=\])/', $match, $sub_matches ) ) {
					$sub_matches = reset( $sub_matches );
					foreach ( $sub_matches as $sub_match ) {
						$pieces = explode( '|', $sub_match );
						$count = count( $pieces );

						$random_word = $pieces[ rand( 0, ( $count - 1 ) ) ];
						$matches[ $i ] = str_replace( '[' . $sub_match . ']', $random_word, $matches[ $i ] );
					}
				}

				$pieces = explode( '|', $matches[ $i ] );
				$count = count( $pieces );

				$random_word = $pieces[ rand( 0, ( $count - 1 ) ) ];
				$string = str_replace( '{' . $match . '}', $random_word, $string );
			}
		}

		return $string;
	}

	static function sentence() {
		return self::randomize_string( '{Please|Just|Let\'s} make this {cool|awesome|random|sweet|nice|awesome|great|superb|lovely} test sentence {rotate [quickly|fast] and random|spin and be random}.' );
	}

	/**
	 * Contents
	 *
	 * @since 1.0
	 *
	 * @param $type string
	 */
	static function contents( $type = 'admin-columns' ) {
		if ( ! file_exists( CPDC_DIR . '/classes/content/' . $type . '.php' ) ) {
			return false;
		}

		return require CPDC_DIR . '/classes/content/' . $type . '.php';
	}

	static function coordinate() {
		$coordinates = array(
			array( 'address' => 'Amersfoort, Netherlands', 'lat' => '52.161260299999995', 'lng' => '5.378353199999999' ),
			array( 'address' => 'New York, US', 'lat' => '40.7127837', 'lng' => '-74.00594130000002' ),
			array( 'address' => 'Amsterdam, Netherlands', 'lat' => '52.3702157', 'lng' => '4.895167899999933' ),
			array( 'address' => 'London, United Kingdom', 'lat' => '51.5073509', 'lng' => '-0.12775829999998223' ),
			array( 'address' => 'Berlin, Germany', 'lat' => '52.52000659999999', 'lng' => '13.404953999999975' ),
			array( 'address' => 'Tokyo, Japan', 'lat' => '35.6894875', 'lng' => '139.69170639999993' ),
			array( 'address' => 'Paris, France', 'lat' => '48.856614', 'lng' => '2.3522219000000177' ),
			array( 'address' => 'Rome, Italy', 'lat' => '41.90278349999999', 'lng' => '12.496365500000024' ),
			array( 'address' => 'Oslo, Norway', 'lat' => '59.9138688', 'lng' => '10.752245399999993' ),
			array( 'address' => 'Stockholm, Sweden', 'lat' => '59.32932349999999', 'lng' => '18.068580800000063' ),
			//array( 'address' => '', 'lat' => '', 'lng' => '' ),
		);

		return self::random( $coordinates );
	}

	static function price( $min, $max, $decimals = true ) {
		return rand( $min, $max );
	}
}