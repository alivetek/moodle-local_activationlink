<?php

namespace local_activationlink\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

use moodleform;
use stdClass;

class set_password_form extends moodleform {

    public function definition() {
        $mform = $this->_form;
        $mform->setDisableShortforms(true);
        $token = $this->_customdata['token'] ?? '';

        $mform->addElement('hidden', 'token');
        $mform->setType('token', PARAM_ALPHANUM);
        $mform->setDefault('token', $token);

        $mform->addElement('header', 'passwordheader', get_string('set_password', 'local_activationlink'));

        $mform->addElement('password', 'password', get_string('newpassword'));
        $mform->setType('password', PARAM_RAW);
        $mform->addRule('password', get_string('required'), 'required', null, 'client');

        $mform->addElement('password', 'passwordconfirm', get_string('newpassword') . ' (' . get_string('again') . ')');
        $mform->setType('passwordconfirm', PARAM_RAW);
        $mform->addRule('passwordconfirm', get_string('required'), 'required', null, 'client');

        $mform->addRule(['password', 'passwordconfirm'], get_string('passwordsdiffer'), 'compare', 'eq', 'client');

        $this->add_action_buttons(false, get_string('set_password', 'local_activationlink'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['password'] !== $data['passwordconfirm']) {
            $errors['passwordconfirm'] = get_string('passwordsdiffer');
        }

        // Check password policy.
        $errmsg = '';
        if (!check_password_policy($data['password'], $errmsg)) {
            $errors['password'] = $errmsg;
        }

        return $errors;
    }
}
