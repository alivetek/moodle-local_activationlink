<?php

namespace local_activationlink;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use moodle_url;
use core_user;

class token_manager {

    /**
     * Create a new activation token for a user.
     *
     * @param int $userid
     * @return string The raw token.
     */
    public static function create_token(int $userid): string {
        global $DB, $CFG;

        // Generate a random token.
        $token = bin2hex(random_bytes(32));
        $tokenhash = hash('sha256', $token);

        // Calculate expiry. Default to 24 hours.
        $expiryhours = get_config('local_activationlink', 'expiry') ?: 24;
        $expiry = time() + ($expiryhours * 3600);

        $record = new stdClass();
        $record->userid = $userid;
        $record->tokenhash = $tokenhash;
        $record->expiry = $expiry;
        $record->used = 0;
        $record->timecreated = time();

        $DB->insert_record('local_activationlink_tokens', $record);

        return $token;
    }

    /**
     * Validate a token and return the associated record if valid.
     *
     * @param string $token
     * @return stdClass|null
     */
    public static function validate_token(string $token): ?stdClass {
        global $DB;

        $tokenhash = hash('sha256', $token);
        $record = $DB->get_record('local_activationlink_tokens', ['tokenhash' => $tokenhash]);

        if (!$record) {
            return null;
        }

        if ($record->used) {
            return (object)['error' => 'token_used'];
        }

        if ($record->expiry < time()) {
            return (object)['error' => 'token_expired'];
        }

        return $record;
    }

    /**
     * Mark a token as used.
     *
     * @param int $tokenid
     */
    public static function mark_as_used(int $tokenid): void {
        global $DB;
        $DB->set_field('local_activationlink_tokens', 'used', 1, ['id' => $tokenid]);
    }

    /**
     * Get the activation URL for a token.
     *
     * @param string $token
     * @return moodle_url
     */
    public static function get_activation_url(string $token): moodle_url {
        return new moodle_url('/local/activationlink/set_password.php', ['token' => $token]);
    }

    /**
     * Send the activation email to the user.
     *
     * @param stdClass $user
     * @param string $token
     */
    public static function send_activation_email(stdClass $user, string $token): void {
        global $SITE;

        $link = self::get_activation_url($token);
        $expiryhours = get_config('local_activationlink', 'expiry') ?: 24;

        $data = new stdClass();
        $data->firstname = $user->firstname;
        $data->sitename = format_string($SITE->fullname);
        $data->link = $link->out(false);
        $data->expiry = $expiryhours;

        $subject = get_string('email_subject', 'local_activationlink', $data);
        $body = get_string('email_body', 'local_activationlink', $data);
        $bodyhtml = get_string('email_body_html', 'local_activationlink', $data);

        email_to_user($user, core_user::get_noreply_user(), $subject, $body, $bodyhtml);
    }
}
