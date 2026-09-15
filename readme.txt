=== Aystat Honeypot Admin ===
Contributors: stonemanhero
Tags: honeypot, security, login, anti-spam, bot protection
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://paypal.me/mkamenovic

A secure honeypot to trap bots and malicious actors by faking an admin login page.

== Description ==

This plugin acts as a decoy to protect your website. It creates a fake login page at a path attackers frequently target (such as /admin) to trick automated bots and hackers.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/aystat-honeypot-admin` directory or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to Settings -> Aystat Honeypot Admin to configure your decoy settings.

== Frequently Asked Questions ==

= What happens when a bot tries to log in? =
The bot's IP address, user agent, and attempted credentials are logged in the database. If you enable the attempt limitation feature, the attacker's IP will be blocked after 3 failed attempts.

= Can I use /wp-admin as my fake path? =
No. For safety and compatibility, the plugin prevents you from overriding reserved core WordPress paths like `/wp-admin` or `/wp-login.php`.

== Screenshots ==

1. The main settings and configuration screen.
2. Live preview of the customizable fake login page.
3. The real-time activity logs and statistics dashboard.

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
* First release.