<?php

namespace local_activationlink\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Set password form tests.
 *
 * @package    local_activationlink
 * @category   test
 * @copyright  2026 Robert Rienzi (https://alivetek.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class set_password_form_test extends \advanced_testcase {

    /**
     * Test form validation.
     */
    public function test_validation(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        // We need to provide dummy data for the form.
        $form = new set_password_form(null, ['token' => 'testtoken'], 'post', '', null, true);
        
        // Passwords match and follow policy (default admin policy usually requires complex password).
        $data = [
            'password' => 'moodle!123A',
            'passwordconfirm' => 'moodle!123A',
        ];
        $errors = $form->validation($data, []);
        $this->assertEmpty($errors);

        // Passwords differ.
        $data = [
            'password' => 'moodle!123A',
            'passwordconfirm' => 'moodle!123B',
        ];
        $errors = $form->validation($data, []);
        $this->assertArrayHasKey('passwordconfirm', $errors);
        $this->assertEquals(get_string('passwordsdiffer'), $errors['passwordconfirm']);

        // Password policy failure (too simple).
        // By default Moodle has some policy. 
        $data = [
            'password' => 'simple',
            'passwordconfirm' => 'simple',
        ];
        $errors = $form->validation($data, []);
        $this->assertArrayHasKey('password', $errors);
    }
}
