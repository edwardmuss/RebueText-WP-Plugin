=== RebueText ===
Contributors: rebuetext
Tags: SMS, WhatsApp, WooCommerce, notifications, contact form 7, gravity forms
Requires at least: 5.0  
Tested up to: 7.0  
Requires PHP: 7.4  
Stable tag: 1.1  
License: GPLv2 or later  
License URI: https://www.gnu.org/licenses/gpl-2.0.html

RebueText sends Omnichannel (SMS & WhatsApp) notifications for WooCommerce order updates and form submissions (CF7, Gravity Forms).

== Description ==

**Omnichannel SMS & WhatsApp Notifications for WordPress**

RebueText is a powerful notification routing engine that sends real-time SMS and WhatsApp alerts for:
- WooCommerce order status changes
- Contact Form 7 submissions
- Gravity Forms entries
- WPForms submissions _(coming soon)_

**Core Features:**
- **Omnichannel Routing:** Toggle between SMS, WhatsApp, or both for Admin and Customer notifications.
- **WhatsApp Template Sync:** Securely syncs approved Meta WhatsApp templates directly to your WordPress dashboard.
- **Smart Variable Mapping:** Click-to-insert dynamic merge tags seamlessly into SMS bodies and WhatsApp template variables.
- **Environment Switcher:** Easily toggle between Local Development and Production API environments.
- **Comprehensive Logging:** Built-in SMS logs to track message statuses, timestamps, and API responses.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/rebuetext`
2. Activate it through the **Plugins** screen in WordPress
3. Go to **Settings > RebueText** to configure your API Token and Sender configurations.

== Frequently Asked Questions ==

= Which forms are supported? =  
Contact Form 7, Gravity Forms, and WPForms (coming soon).

= Can I send automated WhatsApp messages? =  
Yes! RebueText pulls your approved Meta WhatsApp templates and allows you to map dynamic WordPress data (like order totals or customer names) directly into the template variables.

= Can I customize the SMS message? =  
Yes, you can use our built-in smart tag dropdown to insert dynamic merge tags directly into your SMS templates.

== Screenshots ==

1. SMS Logs
2. RebueText API & Sender Settings
3. WooCommerce Omnichannel Configuration
4. Contact Form 7 Integration & WhatsApp Mapping
5. Gravity Forms Integration

== Changelog ==

= 1.1 =
* Feature: Added full WhatsApp Business API integration.
* Feature: Omnichannel routing (Toggle SMS, WhatsApp, or both per notification).
* Feature: Dynamic WhatsApp Template synchronization and UI variable mapping.
* Feature: Click-to-insert smart merge tags for WooCommerce and CF7.
* Feature: API Environment switcher (Local Development vs Production).
* Fix: Resolved checkbox state retention for multi-dimensional array settings.

= 1.0 =
* Initial release: Core SMS engine for WooCommerce, CF7, and Gravity Forms.

== Upgrade Notice ==

= 1.1 =
Massive omnichannel update! Adds full support for automated WhatsApp template messaging and a new smart-tag insertion UI.

= 1.0 =
First stable release.

== Usage ==

- Setup your API Token, SMS Sender ID, and WhatsApp Sender Phone in the main settings.
- Navigate to the WooCommerce tab to enable Omnichannel notifications for specific order statuses.
- Use the "Insert Tag" dropdown to quickly map dynamic data to your SMS or WhatsApp fields.
- Configure individual CF7 or Gravity Forms from their respective plugin settings pages.

== Compatibility ==

* **WordPress:** 5.0+
* **WooCommerce:** 4.0+
* **Gravity Forms:** 2.5+
* **Contact Form 7:** ✓
* **WPForms:** ✖ Coming Soon

== Merge Tags ==

**WooCommerce:**
- `{billing_first_name}`, `{billing_last_name}`, `{billing_phone}`, `{billing_email}`, `{status}`, `{order_id}`, `{order_total}`

**Gravity Forms:**
- `{Name:1}`, `{First:1.3}`, `{Last:1.6}`, `{Phone:4}`, `{Email:2}`, `{Message:3}`

**Contact Form 7:**
- `[your-name]`, `[your-email]`, `[your-phone]`, `[your-subject]`, `[your-message]`

== Sample Message ==

**Visitor SMS Example:**

    Hi [your-name],
    
    Thank you for reaching out. We’ve received your message and will respond shortly.
    
    Summary:
    - Name: [your-name]
    - Email: [your-email]
    - Phone: [your-phone]
    - Subject: [your-subject]
    - Message: [your-message]
    
    Best regards,
    The Support Team
    [https://rebuetext.com](https://rebuetext.com)

**Admin SMS Example:**

    New form submission received:
    
    - Name: [your-name]
    - Email: [your-email]
    - Phone: [your-phone]
    - Subject: [your-subject]
    - Message: [your-message]

== Author ==

Developed by [RebueText](https://rebuetext.com)  
Project URL: [https://rebuetext.com/plugins](https://rebuetext.com/plugins)

== Privacy Policy ==

Your privacy is important. This plugin collects minimal data necessary for its operation. Any data collected is used solely for providing the plugin's functionality and is not shared with third parties. For more details, please refer to our full Privacy Policy at: [https://rebuetext.com/privacy-policy].

== License ==

This plugin is licensed under the GPLv2 License.