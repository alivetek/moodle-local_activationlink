<?php

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_activationlink', get_string('pluginname', 'local_activationlink'));

    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_heading('local_activationlink/config_header',
        get_string('config_header', 'local_activationlink'), ''));

    // Token Expiry
    $settings->add(new admin_setting_configtext('local_activationlink/expiry',
        get_string('expiry_setting', 'local_activationlink'),
        get_string('expiry_setting_desc', 'local_activationlink'),
        24, PARAM_INT));

    // Failed Login Threshold
    $settings->add(new admin_setting_configtext('local_activationlink/failed_attempts_threshold',
        get_string('failed_attempts_threshold', 'local_activationlink'),
        get_string('failed_attempts_threshold_desc', 'local_activationlink'),
        10, PARAM_INT));
}
