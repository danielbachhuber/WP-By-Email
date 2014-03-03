<?php
/**
 * Interact with WP By Email at the command line
 */

class WPBE_CLI_Command extends WP_CLI_Command {

	/**
	 * Send email notifications for a given post
	 *
	 * @subcommand send-post-notification
	 * @synopsis <post-id> [<user-login>]
	 */
	public function send_post_notifications( $args ) {

		$post_id = ( isset( $args[0] ) ) ? (int)$args[0] : 0;
		$user_login = ( isset( $args[1] ) ) ? sanitize_user( $args[1] ) : '';

		$post = get_post( $post_id );

		if ( ! $post || 'publish' != $post->post_status )
			WP_CLI::error( "Invalid post specified" );

		$user = get_user_by( 'login', $user_login );
		if ( ! $user && $user_login )
			WP_CLI::error( "Invalid user specified" );

		if ( $user_login ) {
			WP_By_Email()->extend->emails->send_post_notifications( $post, $user );
			WP_CLI::success( "Sent email to {$user_login} for post #{$post_id}." );
		} else {
			add_filter( 'wpbe_emails_sent_post', '__return_false' );
			WP_By_Email()->extend->emails->queue_post_notifications( $post );
			WP_CLI::success( "Emails sent to all users subscribed to post #{$post_id}." );
		}
	}

	/**
	 * Send email notifications for a given comment
	 *
	 * @subcommand send-comment-notification
	 * @synopsis <comment-id> [<user-login>]
	 */
	public function send_comment_notification( $args ) {

		$comment_id = ( isset( $args[0] ) ) ? (int)$args[0] : 0;
		$user_login = ( isset( $args[1] ) ) ? sanitize_user( $args[1] ) : '';

		$comment = get_comment( $comment_id );

		if ( ! $comment || 1 != $comment->comment_approved )
			WP_CLI::error( "Invalid comment specified" );

		$user = get_user_by( 'login', $user_login );
		if ( ! $user && $user_login )
			WP_CLI::error( "Invalid user specified" );

		if ( $user_login ) {
			WP_By_Email()->extend->emails->send_comment_notifications( $comment_id, $user );
			WP_CLI::success( "Sent email to {$user_login} for comment #{$comment_id}." );
		} else {
			WP_By_Email()->extend->emails->queue_comment_notifications( $comment_id );
			WP_CLI::success( "Emails sent to all users subscribed to comment #{$comment_id}." );
		}

	}

	/**
	 * Ingest emails from a SMTP account
	 *
	 * @subcommand ingest-emails
	 */
	public function ingest_emails( $args, $assoc_args ) {

		$defaults = array(
				'host'           => '', // '{imap.gmail.com:993/imap/ssl/novalidate-cert}' for Gmail
				'username'       => '', // Full email address for Gmail
				'password'       => '', // Whatever the password is
				'inbox'          => 'INBOX', // Where the new emails will go
				'archive'        => 'WPBE_ARCHIVE', // Where you'd like emails put after they've been processed
			);
		$connection_details = array_merge( $defaults, $assoc_args );

		$connection_details = apply_filters( 'wpbe_imap_connection_details', $connection_details );
		$ret = WP_By_Email()->extend->email_replies->ingest_emails( $connection_details );
		if ( is_wp_error( $ret ) )
			WP_CLI::error( $ret->get_error_message() );
		else
			WP_CLI::success( $ret );
	}

}
WP_CLI::add_command( 'wp-by-email', 'WPBE_CLI_Command' );