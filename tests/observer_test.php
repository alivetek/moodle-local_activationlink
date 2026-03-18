<?php

namespace local_activationlink;

defined('MOODLE_INTERNAL') || die();

/**
 * Observer tests.
 *
 * @package    local_activationlink
 * @category   test
 * @copyright  2026 Robert Rienzi (https://alivetek.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer_test extends \advanced_testcase {

    /**
     * Test that the user_created event triggers token creation and email.
     */
    public function test_user_created_triggers_activation(): void {
        global $DB;
        $this->resetAfterTest();
        $sink = $this->redirectEmails();

        // Create a manual user.
        $user = $this->getDataGenerator()->create_user([
            'auth' => 'manual',
        ]);

        // Check if a token was created.
        $this->assertTrue($DB->record_exists('local_activationlink_tokens', ['userid' => $user->id]));

        // Check if an email was sent.
        $emails = $sink->get_messages();
        $this->assertCount(1, $emails);
        $email = reset($emails);
        $this->assertEquals($user->email, $email->to);
        $this->assertStringContainsString('Activate', $email->subject);

        $token_record = $DB->get_record('local_activationlink_tokens', ['userid' => $user->id]);
        $this->assertNotFalse($token_record);

        // The raw token is random and only its hash is stored,
        // so assert the email contains the activation path and a token-shaped query value.
        // We normalize the body to account for line breaks inserted by the mailer.
		// Note: Depending on the mailer configuration, the body may contain soft line breaks (=\r\n)
		// or hard line breaks (\r\n or \n).
		$body = quoted_printable_decode($email->body);
        $body = str_replace(["\r\n", "=\r", "\r", "\n"], '', $body);
        $this->assertStringContainsString('/local/activationlink/set_password.php', $body);
        $this->assertMatchesRegularExpression('/[?&]token=[a-f0-9]{64}\b/', $body);
    }

    /**
     * Test user_created with "Force password change" does NOT trigger activation.
     */
    public function test_user_created_with_force_password_change(): void {
        global $DB;
        $this->resetAfterTest();
        $sink = $this->redirectEmails();

		// Moodle's data generator doesn't accept `forcepasswordchange` directly,
		// so we simulate the relevant `user_create_user()` behavior for this test.

        $user = $this->getDataGenerator()->create_user(['auth' => 'manual']);
        // The event was already triggered by create_user. 
        // To test this properly, we should trigger it ourselves or use a mock.
        
        // Let's try to trigger it manually.
        $DB->delete_records('local_activationlink_tokens');
        $sink->clear();
        
        set_user_preference('auth_forcepasswordchange', 1, $user->id);
        
        \core\event\user_created::create_from_userid($user->id)->trigger();

        $this->assertFalse($DB->record_exists('local_activationlink_tokens', ['userid' => $user->id]));
        $this->assertCount(0, $sink->get_messages());
    }

    public function test_user_created_with_generate_password(): void {
        global $DB;
        $this->resetAfterTest();
        $sink = $this->redirectEmails();

        // We simulate the 'not set' password and the 'createpassword' flag in the event, 
        // which is used when "Generate password and notify user" is checked.
        // The generator doesn't simulate this perfectly, so we trigger the event manually.
        $user = $this->getDataGenerator()->create_user(['auth' => 'manual']);
        
        // Clear anything created by the generator's internal event.
        $DB->delete_records('local_activationlink_tokens');
        $sink->clear();

        $event = \core\event\user_created::create([
            'objectid' => $user->id,
            'relateduserid' => $user->id,
            'context' => \context_user::instance($user->id),
            'other' => ['createpassword' => 1]
        ]);
        $event->trigger();

        $this->assertFalse($DB->record_exists('local_activationlink_tokens', ['userid' => $user->id]));
        // Note: Moodle might send its own email here, but we are checking if OUR activation email was sent.
        // Actually, sink will catch ALL emails. Moodle's standard email has a different subject.
        $emails = $sink->get_messages();
        foreach ($emails as $email) {
            $this->assertStringNotContainsString('Activate', $email->subject);
        }
    }

    /**
     * Test user_created with non-manual auth does NOT trigger activation.
     */
    public function test_user_created_non_manual_auth(): void {
        global $DB;
        $this->resetAfterTest();
        $sink = $this->redirectEmails();

        $user = $this->getDataGenerator()->create_user([
            'auth' => 'nologin',
        ]);

        $this->assertFalse($DB->record_exists('local_activationlink_tokens', ['userid' => $user->id]));
        $this->assertCount(0, $sink->get_messages());
    }
}
