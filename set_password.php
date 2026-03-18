<?php

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/moodlelib.php');

use local_activationlink\token_manager;
use local_activationlink\form\set_password_form;

// Get the token from the URL.
$token = optional_param('token', '', PARAM_ALPHANUM);

// Basic setup.
$PAGE->set_url(new moodle_url('/local/activationlink/set_password.php', ['token' => $token]));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('login');
$PAGE->set_title(get_string('set_password', 'local_activationlink'));
$PAGE->set_heading(get_string('set_password', 'local_activationlink'));

// 1. Validate the token.
if (empty($token)) {
    throw new moodle_exception('invalid_token', 'local_activationlink');
}

// Rate limiting prevents brute-force token guessing attacks by:
// - tracking failed validation attempts per IP address using Moodle's cache system;
// - enforcing a limit for failed attempts (default is 10);
// - blocking further attempts from an offending IP address until the cache expires (typically 1 hour);
// The threshold can be configured in Site administration > Plugins > Local plugins > Activation Link.
// Successful validation resets the counter for that IP.
$cache = cache::make('local_activationlink', 'failed_attempts');
$ip = getremoteaddr();
$attempts = $cache->get($ip) ?: 0;
$threshold = get_config('local_activationlink', 'failed_attempts_threshold') ?: 10;
if ($attempts > $threshold) {
	throw new moodle_exception('too_many_attempts', 'local_activationlink');
}$cache = cache::make('local_activationlink', 'failed_attempts');
$ip = getremoteaddr();
$attempts = $cache->get($ip) ?: 0;
$threshold = get_config('local_activationlink', 'failed_attempts_threshold') ?: 10;
if ($attempts > $threshold) {
    throw new moodle_exception('too_many_attempts', 'local_activationlink');
}

$record = token_manager::validate_token($token);

if (!$record || isset($record->error)) {
    $cache->set($ip, $attempts + 1);
    $error = $record->error ?? 'invalid_token';
    throw new moodle_exception($error, 'local_activationlink');
}

// Reset attempts since the token was validated successfully.
$cache->delete($ip);

$user = core_user::get_user($record->userid, '*', MUST_EXIST);

// 2. Display the set password form.
$mform = new set_password_form(null, ['token' => $token]);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/'));
} else if ($data = $mform->get_data()) {
    // 3. Update Password.
    $user->password = $data->password;

    // Passwords must be hashed before database storage.
    // - Older Moodle versions require manual hashing.
    // - Newer versions may auto-handle it in functions like update_user().
    // To ensure compatibility and security, we use the standard Moodle API:
    // update_internal_user_password().
    update_internal_user_password($user, $data->password);

    // Invalidate the token.
    token_manager::mark_as_used($record->id);

    // Log the user in.
    complete_user_login($user);
    
    // Redirect to the home page.
    redirect(new moodle_url('/'), get_string('password_set_success', 'local_activationlink'), null, \core\output\notification::NOTIFY_SUCCESS);
}

// 4. Render the form.
echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
