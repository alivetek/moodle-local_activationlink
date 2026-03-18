<?php

namespace local_activationlink;

defined('MOODLE_INTERNAL') || die();

use core\event\user_created;
use stdClass;

class observer {

    /**
     * Handle user_created event.
     *
     * @param user_created $event
     */
    public static function user_created(user_created $event): void {
        global $DB, $CFG;

        $userid = $event->objectid;
        $user = $event->get_record_snapshot('user', $userid);

        if (!$user) {
            // Fallback to DB if a snapshot is not available.
            $user = $DB->get_record('user', ['id' => $userid]);
        }

        if (!$user) {
            return;
        }

        if ($user->auth === 'manual') {
            // Check for the "Force password change" preference.
            // If it is set, we let Moodle handle the password reset flow.
            if (get_user_preferences('auth_forcepasswordchange', 0, $userid)) {
                return;
            }

            // Check for the "Generate password and notify user" option.
            // When this option is selected during user creation (e.g., via the user_create_user() function),
            // Moodle sets the password field to 'not set' and includes 'createpassword' in the event data.
            // In these cases, we defer to Moodle's standard password generation and notification process.
            if ($user->password === 'not set' || !empty($event->other['createpassword'])) {
                return;
            }

            // Create a token.
            $token = token_manager::create_token($userid);

            // Send an activation email.
            token_manager::send_activation_email($user, $token);
        }
    }
}
