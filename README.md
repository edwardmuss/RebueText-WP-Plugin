# RebueText ![Version](https://img.shields.io/badge/version-1.0-blue.svg) ![License](https://img.shields.io/badge/license-GPLv2-blue.svg) ![WordPress](https://img.shields.io/badge/WordPress-5.0+-blue) ![WooCommerce](https://img.shields.io/badge/WooCommerce-4.0+-blueviolet)

🔔 **SMS Notifications Plugin for WordPress & WooCommerce**  
RebueText sends real-time SMS notifications for WooCommerce order updates and form submissions from Contact Form 7, Gravity Forms, and WPForms.

---

## 🌐 Plugin Details

- **Plugin Name:** RebueText
- **Plugin URI:** [https://rebuetext.com/plugins](https://rebuetext.com/plugins)
- **Author:** [Edward Muss](https://edwardmuss.tech)
- **Version:** 1.0
- **License:** GPLv2

---

## 📦 Features

- **WooCommerce SMS Alerts**  
  Get notified via SMS when order statuses change (Processing, Completed, etc.)

- **Form SMS Support**  
  Receive messages from:

  - Contact Form 7
  - Gravity Forms _(with sub-field merge tag support)_
  - WPForms

- **Merge Tags**  
  Auto-generate usable tags like `{Name:1.3}`, `{Email:2}` for your SMS messages.

- **Admin & User Alerts**  
  Choose whether to notify site admins, customers, or both.

---

## 🔧 Installation

1. Upload the plugin folder to `/wp-content/plugins/rebuetext`
2. Activate it via the WordPress Admin under **Plugins**
3. Navigate to **Settings > RebueText** to configure SMS options

---

## 📄 Example Merge Tags

### WooCommerce

- `{billing_first_name}` - First Name
- `{billing_last_name}` - Last Name
- `{billing_phone}` - Phone Number
- `{billing_email}` - Email Address
- `{status}` - Order Status (`Pending`, `Processing`, `Completed`, etc.)
- `{order_id}` - Order ID

### Gravity Forms

- `{Name:1}` - Full Name
- `{First:1.3}` - First Name
- `{Last:1.6}` - Last Name
- `{Phone:4}` - Phone Number
- `{Email:2}` - Email Address
- `{Message:3}` - Submitted Message

### Contact Form 7

- `[your-name]` - Visitor's Name
- `[your-email]` - Visitor's Email
- `[your-phone]` - Visitor's Phone
- `[your-subject]` - Subject
- `[your-message]` - Message

_These merge tags may vary from form to form and they will be listed on the specific settings page for you to select or copy/paste into your SMS template._

---

## ✉️ Sample Message

### Visitors

```
Hi [your-name],

Thank you for reaching out to us. We’ve received your message and will get back to you shortly.

Here’s a summary of your submission:

- Name: [your-name]
- Email: [your-email]
- Phone: [your-phone]
- Subject: [your-subject]
- Message:
  [your-message]

Best regards,
The Support Team
https://rebuetext.com
```

### Admin

```
New contact form submission received.

Details:

- Name: [your-name]
- Email: [your-email]
- Phone: [your-phone]
- Subject: [your-subject]
- Message:
  [your-message]

Please respond as soon as possible.
```

---

## 💡 Usage

- Set up SMS templates using merge tags
- Enable for selected WooCommerce order statuses
- Enable SMS notifications for specific forms

---

## 📋 Compatibility

| Feature        | Version          |
| -------------- | ---------------- |
| WordPress      | 5.0+             |
| WooCommerce    | 4.0+             |
| Gravity Forms  | 2.5+             |
| Contact Form 7 | ✓                |
| WPForms        | ✖ 🚧 Coming Soon |

---

## 🙋‍♂️ Author

**Edward Muss**  
🔗 [https://edwardmuss.tech](https://edwardmuss.tech)  
📬 [https://rebuetext.com/plugins](https://rebuetext.com/plugins)

---

## 📸 Screenshots

1. **SMS Logs**  
   View and monitor all SMS activity in one place.  
   ![SMS Logs](https://ressirli.sirv.com/Cloud%20Rebue/sms_log.png)

2. **WooCommerce SMS Settings**  
   Customize SMS notifications for different order statuses.  
   ![WooCommerce SMS Settings](https://ressirli.sirv.com/Cloud%20Rebue/woo-sms-settings.png)

3. **Contact Form 7 Integration**  
   Easily configure SMS messages triggered by Contact Form 7 submissions.  
   ![CF7 Settings](https://ressirli.sirv.com/Cloud%20Rebue/CF7%20settings.png)

4. **Gravity Forms Integration**  
   Send SMS alerts based on Gravity Forms submissions.  
   ![Gravity Forms](https://ressirli.sirv.com/Cloud%20Rebue/gravity-forms.png)

---

## 📜 License

This plugin is licensed under the [GPLv2 License](https://www.gnu.org/licenses/gpl-2.0.html).

---

> 💬 For issues or contributions, feel free to open an [Issue](#) or submit a [Pull Request](#).
