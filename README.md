# moodle-local_activationlink

Provide an "Activation Link" feature for Moodle allowing new users to set their own password via a secure link sent to their email.

## Features

- **Automatic Notification**: Automatically sends an activation link when creating a new user account.
- **Secure Tokens**: Uses cryptographically secure, hashed tokens with expiration.
- **Password Policy Compliance**: Ensures new passwords meet the site's password policy.
- **Auto-Login**: Automatically logs the user in after setting a password.
- **Management**: Allows administrators to configure the token expiration time.
- **Rate Limiting**: Protects the activation page against brute-force attacks.

## Installation

1. Copy the `activationlink` folder to your Moodle's `local/` directory.
2. Log in to your Moodle site as an administrator.
3. Moodle will detect the new plugin and prompt you to upgrade the database.
4. Follow the on-screen instructions to complete the installation.

## Configuration

You can configure *Token Expiry* and *Rate Limit Threshold* by going to:
`Site administration > Plugins > Local plugins > Activation Link`

The default token expiry is 24 hours, and the default rate limit threshold is 10 attempts per hour.

## How it Works

1. When a new user is created, the plugin intercepts the event.
2. The plugin generates a secure token and sends a custom email to the user with an activation link, provided the "Force password change" or "Generate password and notify user" options were not selected.
3. If "Generate password and notify user" is selected, Moodle's default password generation and notification process is allowed to proceed, and no activation link is sent.
4. If "Force password change" is selected, the plugin defers to Moodle's standard password reset flow upon the user's first login.
5. For cases where an activation link is sent, the user clicks the link, sets a password, and is automatically logged in.

## Security Considerations

- Tokens are stored as SHA-256 hashes in the database.
- Tokens have a configurable expiration time.
- The activation page has built-in rate limiting to prevent token guessing.
- All password changes are validated against the site's password policy.

## Compatibility

Compatible with Moodle 5.0 and later.  Note: this plugin may work with earlier versions of Moodle but has not been tested with any versions prior to 5.0.
