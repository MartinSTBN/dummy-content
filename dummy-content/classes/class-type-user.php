<?php

class CPDC_Type_User extends CPDC_Type {

	private $user_ids = null;

	public function __construct() {

		$this->id = 'user';
		$this->meta_type = 'user';
		$this->label = 'User';
	}

	public function get_label() {
		return 'Users';
	}

	public function get_random() {
		return get_userdata( $this->id() );
	}

	public function id() {
		if ( null === $this->user_ids ) {
			$this->user_ids = get_users( array( 'fields' => 'ids' ) );
		}

		return $this->random( $this->user_ids );
	}

	public function ids( $min = 1, $max = 10 ) {
		$ids = array();
		for ( $i = 1; $i <= mt_rand( (int) $min, (int) $max ); $i++ ) {
			$ids[] = $this->id();
		}

		return $ids;
	}

	/**
	 * Add a random ID to the stack
	 *
	 * @since 1.0
	 */
	private function add_random_id( $user_id ) {
		$this->user_ids[] = $user_id;
	}

	/**
	 * @since 1.0
	 */
	public function get_email() {
		$user = $this->get_user_name();

		return strtolower( sanitize_user( $user[0], true ) ) . '@' . strtolower( sanitize_user( $user[1], true ) ) . '.com';
	}

	public function get_first_names() {
		$first_names = array();
		foreach ( $this->get_user_names() as $fullname ) {
			$first_names[] = $fullname[0];
		}

		return $first_names;
	}

	public function get_last_names() {
		$last_names = array();
		foreach ( $this->get_user_names() as $fullname ) {
			$last_names[] = $fullname[0];
		}

		return $last_names;
	}

	public function get_user_name() {
		return array( $this->get_first_name(), $this->get_last_name() );
	}

	public function get_user_names() {
		$user_names = array(
			array( 'Florence', 'Packard' ),
			array( 'Wilfredo', 'Berenbaum' ),
			array( 'Tammy', 'Turberville' ),
			array( 'Joe', 'Voliva' ),
			array( 'Carey', 'Atwood' ),
			array( 'Lyndia', 'Hildreth' ),
			array( 'Harrison', 'Montiel' ),
			array( 'Jude', 'Gorley' ),
			array( 'Anna', 'Sarinana' ),
			array( 'Shiloh', 'Melville' ),
			array( 'Thurman', 'Brasier' ),
			array( 'Nam', 'Crownover' ),
			array( 'Columbus', 'Fulgham' ),
			array( 'Russel', 'Piper' ),
			array( 'Dewey', 'Benshoof' ),
			array( 'Tarsha', 'Ruch' ),
			array( 'Leonia', 'Donnell' ),
			array( 'Rubie', 'Mcclenny' ),
			array( 'Andera', 'Mansir' ),
			array( 'Nidia', 'Joubert' ),
			array( 'Mayme', 'Driskill' ),
			array( 'Roderick', 'Burrill' ),
			array( 'Mason', 'Songer' ),
			array( 'Long', 'Auer' ),
			array( 'Del', 'Gasca' ),
			array( 'Jade', 'Gruver' ),
			array( 'Latanya', 'Bamburg' ),
			array( 'Carlos', 'Rosell' ),
			array( 'Ivey', 'Kempker' ),
			array( 'Cathey', 'Kernan' ),
			array( 'Talisha', 'Folkers' ),
			array( 'Dewey', 'Kinsella' ),
			array( 'Arlena', 'Ervin' ),
			array( 'Francesca', 'Nygaard' ),
			array( 'Harley', 'Ice' ),
			array( 'Angella', 'Ikard' ),
			array( 'Annetta', 'July' ),
			array( 'Bambi', 'Coplan' ),
			array( 'Jene', 'Sneed' ),
			array( 'Darwin', 'Friday' ),
			array( 'Shanon', 'Soper' ),
			array( 'Roxanne', 'Verdi' ),
			array( 'Gilberto', 'Wannamaker' ),
			array( 'Priscilla', 'Brakefield' ),
			array( 'Mallory', 'Crooms' ),
			array( 'Rico', 'Bhatti' ),
			array( 'Stefanie', 'Burgin' ),
		);

		return $user_names;
	}

	/**
	 * @since 1.0
	 */
	public function get_first_name() {
		return $this->random( $this->get_first_names() );
	}

	public function get_last_name() {
		return $this->random( $this->get_last_names() );
	}

	/**
	 * @since 1.0
	 */
	public function get_role() {
		return $this->random( $this->get_roles() );
	}

	public function get_roles() {
		return array_keys( wp_roles()->roles );
	}

	/**
	 * @since 1.0
	 */
	public function get_created_ids() {
		return get_users( array(
			'fields'     => 'ID',
			'meta_query' => array(
				array(
					'key'   => '_created_by_dummy_content',
					'value' => 1
				)
			)
		) );
	}

	public function delete() {
		$deleted_ids = array();

		if ( ! $users = get_users() ) {
			return false;
		}

		require_once( ABSPATH . 'wp-admin/includes/user.php' );

		foreach ( $users as $user ) {
			if ( get_current_user_id() == $user->ID ) {
				continue; // do not delete yourself ;)
			}
			if ( '1' === get_user_meta( $user->ID, '_created_by_dummy_content', true ) ) {
				$deleted = wp_delete_user( $user->ID, true );
				if ( $deleted ) {
					$deleted_ids[] = $user->ID;
				}
			}
		}

		$this->delete_cache( 'count' );
		$this->delete_ids();

		return $deleted_ids;
	}

	public function create( $amount = 50 ) {

		$added_ids = array();

		for ( $i = 1; $i <= $amount; $i++ ) {

			$firstname = $this->get_first_name();
			$lastname = $this->get_last_name();

			$firstname_sanitize = sanitize_key( strtolower( sanitize_user( $firstname, true ) ) );
			$lastname_sanitize = sanitize_key( strtolower( sanitize_user( $lastname, true ) ) );

			$random_string = substr( uniqid(), 5 );

			$username = $firstname_sanitize . '_' . $lastname_sanitize . '_' . $random_string;
			$password = $username;
			$useremail = $firstname_sanitize . '@' . $lastname_sanitize . '_' . $random_string . '.com';
			$userwebsite = 'http://www.' . $firstname_sanitize . '-example.com';

			// Create user
			$created_user_id = wp_create_user( $username, $password, $useremail );

			if ( is_wp_error( $created_user_id ) ) {
				$this->add_notice( $username . '. ' . $created_user_id->get_error_message(), 'error' );
				continue;
			}

			wp_insert_user( array(
				'ID'           => $created_user_id,
				'user_login'   => $username,
				'user_pass'    => $password,
				'user_email'   => $useremail,
				'user_url'     => $userwebsite,
				'nickname'     => $firstname,
				'first_name'   => $firstname,
				'last_name'    => $lastname,
				'display_name' => $firstname . ' ' . $lastname,
				'description'  => CPDC_Data_Generator::sentence(),
				'role'         => $this->get_role(),
				'user_registered' => CPDC_Data_Generator::date( '-2 year', 'now' )
			) );

			$this->add_random_id( $created_user_id );

			update_user_meta( $created_user_id, '_created_by_dummy_content', 1 );

			$added_ids[] = $created_user_id;
		}

		if ( empty( $added_ids ) ) {
			return new WP_Error( 'failed', 'No users added.' );
		}

		$this->delete_cache( 'count' );
		$this->store_ids( $added_ids );

		return $added_ids;
	}
}