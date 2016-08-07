<?php defined('ABSPATH') or die('No direct script access.');
/**
 * snippet: contact.php
 *
 * @package miniPops
 * @subpackage Rhythmicon
 * @version 1
 */


if (isset($_POST['contact_nonce']) && mp_verify_nonce($_POST['contact_nonce'], 'contact_nonce')){

	$email   = trim( $_POST['contact_email'] );
	$sujet   = trim( $_POST['contact_sujet'] );
	$message = trim( $_POST['contact_message'] );
	
	$to = get_the_blog('author_email');
	
	$subject = ($sujet === '') ? __('Contact', 'snippets').' '.get_the_blog('title') : $sujet;
	$from = ($email === '') ? $to : $email;

	$contact = email( array('to' => $to, 'subject' => $subject, 'body' => $message, 'from' => $from) );
}
?>
<?php if (!isset($contact)): ?>
<form method="post" class="contact-form">

	<?php mp_nonce_field('contact_nonce', 'contact_nonce'); ?>

	<div class="field">
		<label for="contact_email">
			<?php _e('Email','snippets'); ?>:
		</label>
		<input type="email" id="contact_email" name="contact_email" required />
	</div>
	
	<div class="field">
		<label for="contact_sujet">
			<?php _e('Subject','snippets'); ?>:
		</label>
		<input type="text" id="contact_sujet" name="contact_sujet" />
	</div>
	
	<div class="field">
		<label for="contact_message">
			<?php _e('Message','snippets'); ?>:
		</label>
		<textarea id="contact_message" name="contact_message" required></textarea>
	</div>
	
	<div class="submit">
		<input type="submit" value="<?php _e('Send','snippets'); ?>" />
	</div>
</form>
<?php else: ?>
	<div class="message <?php echo ($contact) ? 'succes' : 'erreur'; ?>">
	<?php
	if ($contact) _e('Succes ! You message was sent.','snippets');
	else _e('An error occured during sending, try again in a few minutes.', 'snippets');
?>
	</div>
<?php endif; ?>
