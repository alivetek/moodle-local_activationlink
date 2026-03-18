<?php

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Activation Link';
$string['activationlink'] = 'Activation Link';
$string['email_subject'] = 'Activate your account at {$a->sitename}';
$string['email_body'] = 'Hello {$a->firstname},

A new account has been created for you at {$a->sitename}.
To complete your account setup and set your password, please click on the link below:

{$a->link}

This link will expire in {$a->expiry} hours.
If you did not expect this, you can ignore this email.';

$string['email_body_html'] = '<p>Hello {$a->firstname},</p>
<p>A new account has been created for you at {$a->sitename}.</p>
<p>To complete your account setup and set your password, please click on the link below:</p>
<p><a href="{$a->link}">{$a->link}</a></p>
<p>This link will expire in {$a->expiry} hours.</p>
<p>If you did not expect this, you can ignore this email.</p>';

$string['set_password'] = 'Set Password';
$string['password_set_success'] = 'Password has been set successfully. You are now logged in.';
$string['invalid_token'] = 'The activation link is invalid or has expired.';
$string['token_expired'] = 'This activation link has expired.';
$string['token_used'] = 'This activation link has already been used.';
$string['too_many_attempts'] = 'Too many failed attempts. Please try again later.';
$string['expiry_setting'] = 'Token Expiry (hours)';
$string['expiry_setting_desc'] = 'The number of hours the activation link is valid for.';
$string['failed_attempts_threshold'] = 'Rate Limit Threshold';
$string['failed_attempts_threshold_desc'] = 'The number of failed attempts allowed per IP before a rate limit is applied.';
$string['config_header'] = 'Activation Link Settings';
