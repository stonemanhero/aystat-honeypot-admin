# Aystat Honeypot Admin

**Aystat Honeypot Admin** is a lightweight, highly customizable WordPress security plugin designed to trap malicious bots, automated scripts, and hackers. It creates a decoy login page at a custom path (such as `/admin` or `/portal`) to intercept attacks before they ever reach your actual WordPress login.

## How It Works
Hackers and bots constantly scan WordPress sites for common login URLs to launch brute-force attacks. Aystat Honeypot Admin lets you set up a fake, fully functional-looking login page at these highly targeted paths. When a bot attempts to log in to the decoy page, their IP address, user agent, and attempted credentials are secretly logged in the database, and they are shown a fake error message.

## Key Features

### Security & Trapping
*   **Custom Decoy Path:** Set your honeypot to any non-reserved URL (e.g., `/admin`, `/login`, `/portal`).
*   **Attempt Limitation (Lockdown):** Automatically block an attacker's IP address with a `403 Forbidden` response after 3 failed attempts.
*   **Custom Error Messages:** Define exactly what the attacker sees when they fail a login or get locked out to maintain the illusion.

### Advanced Logging & Analytics
*   **Real-Time Dashboard:** View a Chart.js-powered visual overview of attack frequencies.
*   **Deep Statistics:** See the Top 20 IP Addresses, Usernames, and Passwords used by attackers.
*   **CSV Export:** Export your logs with a single click for analysis in Excel, external firewalls, or fail2ban.
*   **Database Maintenance:** Built-in cleanup tools to automatically or manually purge logs older than 30, 60, or 90 days.

### Design & Customization
*   **10 Pre-built Themes:** Apply styles like "Dark Mode Hacker", "Cyberpunk Neon", "Modern Minimalist", or "Corporate Blue".
*   **Live Preview:** See exactly what your decoy login page looks like right from the WordPress dashboard as you edit.
*   **Full UI Control:** Customize background colors, box shadows, border radiuses, button colors, typography, and add custom logos to make the decoy perfectly match your brand.

## Installation

1. Download the `aystat-honeypot-admin.zip` file.
2. Log in to your WordPress dashboard.
3. Navigate to **Plugins > Add New > Upload Plugin**.
4. Choose the zip file and click **Install Now**.
5. Click **Activate Plugin**.

## Configuration

Once activated, navigate to **Settings > Aystat Honeypot Admin** in your WordPress dashboard to configure the plugin:

1. **Settings Tab:** Check "Enable Honeypot", set your decoy path (e.g., `/admin`), and configure your simulated error messages.
2. **Design Tab:** Select a theme preset or manually build your decoy login page. Use the Live Preview to ensure it looks convincing.
3. **Logs Tab:** Monitor real-time attacks, filter by date ranges, or export to CSV.
4. **Statistics Tab:** Identify brute-force trends and common passwords used against your site.

## Security & Code Standards
Aystat Honeypot Admin has been built strictly adhering to the latest **WordPress Coding Standards (WPCS)**. It utilizes:
*   Strict late-escaping for all output variables.
*   Prepared SQL statements for all database interactions.
*   Proper nonce verification for administrative actions.
*   Independent, lightweight database tables to prevent bloat in `wp_options`.

## Changelog

### 1.0.0
* Initial Release.
* Added customizable decoy login generation.
* Added attempt limitation and IP logging.
* Added Chart.js analytics dashboard and CSV exporting.
* Passed full WordPress Plugin Check security guidelines.

---
*Created by Aystat.*