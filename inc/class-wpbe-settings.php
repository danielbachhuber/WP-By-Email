<?php

class WPBE_Settings extends WP_By_Email {

	private $user_meta_key = 'wpbe_settings';
	public $default_options = array(
				'posts'        => 'all',
				'comments'     => 'all',
				'mentions'     => 'yes',
			);

	public function __construct() {
		add_action( 'wpbe_after_setup_actions', array( $this, 'setup_actions' ) );
	}

	public function setup_actions() {

		add_action( 'edit_user_profile', array( $this, 'user_profile_fields' ) );
		add_action( 'show_user_profile', array( $this, 'user_profile_fields' ) );

		add_action( 'personal_options_update', array( $this, 'save_user_profile_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_user_profile_fields' ) );
	}

	public function user_profile_fields( $user ) {

		$user_options = $this->get_user_notification_options( $user->ID );
?>
<h3>WP By Email</h3>
<?php if ( is_multisite() ) : ?>
	<p class="description">Settings are specific to this site.</p>
<?php endif; ?>
	<table class="form-table">
		<tr>
			<th><label for="wpbe-posts">Posts</label></th>
			<td>
				<select id="wpbe-posts" name="wpbe-posts">
				<?php foreach( array( 'all' => 'Send me an email for every new post', 'none' => "Don't send me new post emails" ) as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $user_options['posts'] ); ?>><?php echo esc_attr( $label ); ?></option>
				<?php endforeach; ?>
				</select>
				<?php if ( WP_By_Email()->extend->email_replies->is_enabled() ) : ?>
				<?php $user_secret_email = apply_filters( 'wpbe_emails_reply_to_email', '', 'user', $user->ID ); ?>
				<p class="description">Tip: Create new posts by emailing this secret address: <a href="<?php echo esc_url( 'mailto:' . $user_secret_email ); ?>"><?php echo esc_html( $user_secret_email ); ?></a>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<th><label for="wpbe-comments">Comments</label></th>
			<td>
				<select id="wpbe-comments" name="wpbe-comments">
				<?php foreach( array( 'all' => 'Send me an email for every new comment', 'none' => "Don't send me new comment emails" ) as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $user_options['comments'] ); ?>><?php echo esc_attr( $label ); ?></option>
				<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="wpbe-mentions">Mentions</label></th>
			<td>
				<select id="wpbe-mentions" name="wpbe-mentions">
				<?php foreach( array( 'yes' => 'Make sure I get an email if someone @mentions my username', 'no' => "Respect my post and comment notification settings" ) as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $user_options['mentions'] ); ?>><?php echo esc_attr( $label ); ?></option>
				<?php endforeach; ?>
				</select>
			</td>
		</tr>
	</table>
<?php
	}

	/**
	 * Build a meta key for use in storing a user's WPBE options. If
	 * multisite, site IDs will be appended to create unique keys.
	 *
	 * @return string Meta key used for a user's WPBE options.
	 */
	private function get_user_meta_key() {
		if ( is_multisite() ) {
			return $this->user_meta_key . '_' . get_current_blog_id();
		}

		return $this->user_meta_key;
	}

	public function save_user_profile_fields( $user_id ) {

		if ( ! current_user_can( 'edit_user', $user_id ) )
			return;

		$user_options = $this->default_options;
		if ( isset( $_POST['wpbe-posts'] ) && 'all' != $_POST['wpbe-posts'] )
			$user_options['posts'] = 'none';

		if ( isset( $_POST['wpbe-comments'] ) && 'all' != $_POST['wpbe-comments'] )
			$user_options['comments'] = 'none';

		if ( isset( $_POST['wpbe-mentions'] ) && 'yes' != $_POST['wpbe-mentions'] )
			$user_options['mentions'] = 'no';

		update_user_meta( $user_id, $this->get_user_meta_key(), $user_options );
		return;
	}

	public function get_user_notification_options( $user_id ) {
		return array_merge( $this->default_options, (array)get_user_meta( $user_id, $this->get_user_meta_key(), true ) );
	}


}

WP_By_Email()->extend->settings = new WPBE_Settings();