=== RebueText ===
Contributors: edwardmuss  
Tags: SMS, WooCommerce, notifications, contact form 7, gravity forms, WPForms  
Requires at least: 5.0  
Tested up to: 6.8  
Requires PHP: 7.4  
Stable tag: 1.0  
License: GPLv2 or later  
License URI: https://www.gnu.org/licenses/gpl-2.0.html

RebueText sends SMS notifications for WooCommerce order updates and form submissions (CF7, Gravity Forms, WPForms).

== Description ==

**SMS Notifications Plugin for WordPress & WooCommerce**

RebueText sends real-time SMS alerts for:
- WooCommerce order status changes
- Contact Form 7 submissions
- Gravity Forms entries
- WPForms submissions _(coming soon)_

**Features:**
- WooCommerce SMS alerts for order statuses (Processing, Completed, etc.)
- Form SMS support (CF7, Gravity Forms, WPForms)
- Merge tag support for dynamic data
- Admin and customer SMS notifications
- SMS logs to track all activity

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/rebuetext`
2. Activate it through the **Plugins** screen in WordPress
3. Go to **Settings > RebueText** to configure

== Frequently Asked Questions ==

= Which forms are supported? =  
Contact Form 7, Gravity Forms, and WPForms (coming soon)

= Can I customize the SMS message? =  
Yes, you can use merge tags to customize your SMS templates.

== Screenshots ==

1. **SMS Logs**  
   ![SMS Logs](https://ressirli.sirv.com/Cloud%20Rebue/sms_log.png)

2. **WooCommerce SMS Settings**  
   ![WooCommerce SMS Settings](https://ressirli.sirv.com/Cloud%20Rebue/woo-sms-settings.png)

3. **Contact Form 7 Integration**  
   ![CF7 Settings](https://ressirli.sirv.com/Cloud%20Rebue/CF7%20settings.png)

4. **Gravity Forms Integration**  
   ![Gravity Forms](https://ressirli.sirv.com/Cloud%20Rebue/gravity-forms.png)

== Changelog ==

= 1.0 =
* Initial release

== Upgrade Notice ==

= 1.0 =
First stable release

== Usage ==

- Enable SMS notifications for selected WooCommerce order statuses
- Setup SMS templates using merge tags
- Enable SMS for specific forms

== Compatibility ==

| Plugin           | Status       |
| ---------------- | ------------ |
| WordPress        | 5.0+         |
| WooCommerce      | 4.0+         |
| Gravity Forms    | 2.5+         |
| Contact Form 7   | ✓            |
| WPForms          | ✖ Coming Soon |

== Merge Tags ==

**WooCommerce:**
- `{billing_first_name}`, `{billing_last_name}`, `{billing_phone}`, `{billing_email}`, `{status}`, `{order_id}`

**Gravity Forms:**
- `{Name:1}`, `{First:1.3}`, `{Last:1.6}`, `{Phone:4}`, `{Email:2}`, `{Message:3}`

**Contact Form 7:**
- `[your-name]`, `[your-email]`, `[your-phone]`, `[your-subject]`, `[your-message]`

== Sample Message ==

**Visitor SMS Example:**
```
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
https://rebuetext.com
```

**Admin SMS Example:**
```
New form submission received:

- Name: [your-name]
- Email: [your-email]
- Phone: [your-phone]
- Subject: [your-subject]
- Message: [your-message]
```

== Author ==

Developed by [Edward Muss](https://edwardmuss.tech)  
Project URL: [https://rebuetext.com/plugins](https://rebuetext.com/plugins)

== License ==

This plugin is licensed under the GPLv2 License.