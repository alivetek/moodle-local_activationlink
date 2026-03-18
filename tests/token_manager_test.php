<?php

namespace local_activationlink;

defined('MOODLE_INTERNAL') || die();

/**
 * Token manager tests.
 *
 * @package    local_activationlink
 * @category   test
 * @copyright  2026 Robert Rienzi (https://alivetek.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class token_manager_test extends \advanced_testcase {

    /**
     * Test token creation.
     */
    public function test_create_token(): void {
        global $DB;

        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();

        $token = token_manager::create_token($user->id);

        $this->assertNotEmpty($token);
        $this->assertEquals(64, strlen($token));

        $tokenhash = hash('sha256', $token);
        $record = $DB->get_record('local_activationlink_tokens', ['tokenhash' => $tokenhash]);

        $this->assertNotFalse($record);
        $this->assertEquals($user->id, $record->userid);
        $this->assertEquals(0, $record->used);
        $this->assertGreaterThan(time(), $record->expiry);
    }

    /**
     * Test token validation.
     */
    public function test_validate_token(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $token = token_manager::create_token($user->id);

        // Valid token.
        $record = token_manager::validate_token($token);
        $this->assertNotNull($record);
        $this->assertEquals($user->id, $record->userid);

        // Invalid token.
        $record = token_manager::validate_token('invalid-token');
        $this->assertNull($record);

        // Used token.
        $record = token_manager::validate_token($token);
        token_manager::mark_as_used($record->id);
        $record = token_manager::validate_token($token);
        $this->assertNotNull($record);
        $this->assertEquals('token_used', $record->error);

        // Expired token.
        set_config('expiry', -1, 'local_activationlink');
        $expired_token = token_manager::create_token($user->id);
        $record = token_manager::validate_token($expired_token);
        $this->assertNotNull($record);
        $this->assertEquals('token_expired', $record->error);
    }

    /**
     * Test mark as used.
     */
    public function test_mark_as_used(): void {
        global $DB;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $token = token_manager::create_token($user->id);
        $tokenhash = hash('sha256', $token);
        $record = $DB->get_record('local_activationlink_tokens', ['tokenhash' => $tokenhash]);

        $this->assertEquals(0, $record->used);
        token_manager::mark_as_used($record->id);

        $record = $DB->get_record('local_activationlink_tokens', ['id' => $record->id]);
        $this->assertEquals(1, $record->used);
    }

    /**
     * Test get activation URL.
     */
    public function test_get_activation_url(): void {
        $token = 'testtoken';
        $url = token_manager::get_activation_url($token);
        $this->assertInstanceOf('moodle_url', $url);
        $this->assertStringContainsString('/local/activationlink/set_password.php', $url->out());
        $this->assertStringContainsString('token=testtoken', $url->out());
    }
}
