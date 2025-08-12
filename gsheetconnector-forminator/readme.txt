=== GSheetConnector for Forminator Forms===
Contributors: westerndeal, abdullah17, gsheetconnector
Donate link: https://www.paypal.me/WesternDeal
Author URL: https://profiles.wordpress.org/westerndeal/
Tags: forminator, forminator google sheet, forminator forms google sheet, google sheet forminator, wordpress google sheet
Tested up to: 6.8
Requires at least: 5.6
Requires PHP: 7.4
Stable tag: 1.0.15
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Send your Forminator Forms data directly to your Google Contacts spreadsheet. 

== Description ==

**GSheetConnector for Forminator Forms is an addon plugin**, 
A bridge between your [WordPress](https://wordpress.org/) based [Forminator Forms](https://wordpress.org/plugins/forminator/) forms and [Google Sheets](https://www.google.com/sheets/about/). 
✔🚀 **Quick and Simple to use WordPress Plugin.**

If you're using **[Forminator Forms](https://wordpress.org/plugins/forminator/) by WPMU DEV** to collect data from your website visitors , it's important to have a streamlined process for managing that data. One way to do this is to send Forminator form entry data directly to a Google Sheet. This integration can save your time and effort by eliminating the need to manually transfer data from your form submissions to your spreadsheet.

When a visitor submits their information on your website using GSheetConnector for Forminator Forms, the data they provide is automatically sent to Google Sheets upon form submission in real-time.

== Installation ==

1. Upload `GSheetConnector Forminator Forms` to the `/wp-content/plugins/` directory and  Install it.
2. Activate the plugin through the 'Plugins' screen in WordPress.  
3. Use the `Admin Panel > Forms > Google Sheet` screen to connect to `Google Sheets` by clicking on signin with Google button. Allow the Sheets and Drive permissions and Hit Save, you will see code copied into the plugin settings and simply save authentication.
4. Configure with the appropriate sheet and hit save and you are done.
Enjoy!

== Screenshots ==

1. Google Sheet Integration Shown with Authentication along with Permissions. 
2. Create a form.
3. How to create feeds and display the Sheet name and Tab name.
4. Entering the Field Header Names Manually in the Connected Sheet and Submitting the form.
5. System Status.
6. Extensions.

= ⚡️ How to Use this Plugin =

* **Step: 1 - [In Google Sheets](https://sheets.google.com/)** 
➜ Log into your Google Account and visit Google Sheets.  
➜ Create a new Sheet and name it.  
➜ Rename or keep default name of the tab on which you want to capture the data. 
➜ Copy Sheet Name, Sheet ID, Tab Name and Tab ID (Refer Screenshots)

* **Step: 2 - In WordPress Admin**
➜ Navigate to Forminator Forms > Google Sheet > Integration Tab
➜ Authenticate with Google using new "Google Access Code" while clicking on "Get Code"
➜ Make Sure to ALLOW Google Permissions for Google Drive and Google Sheets and then copy the code and paste in Google Access Code field, and Hit Save & Authenticate.
➜ Now fetch the sheet details by clicking "Click here to fetch Sheet details to be set at Forminator Forms settings.

* **Step: 3 - Form integrate with Google Sheet** 
➜ Go to Forminator > Google Sheet > Form Feed tabs 
➜ Here Display add Form List and Click on Connect to Google Sheet.
➜ Click on Add Feed .

* **Step: 3 - Arranging Columns in Sheet**
➜ In the Google sheets tab, provide column names in row 1. The first column should be "date". For each further column, copy paste mail tags from the Forminator Forms form (e.g. "your-name", "your-email", "your-subject", "your-message", etc).  
➜ Test your form submit and verify that the data shows up in your Google Sheet.

= 🔥 Videos to help you get started with Forminator Forms Google Sheets Connector =

🚀How to Install, Authenticate and Integrate Contact Form with your Google Sheet.

**Google Sheet Connector Contact Form Addons** 
✔ [CF7 Google Sheet Connector](https://www.gsheetconnector.com/cf7-google-sheet-connector-pro?utm_source=wordpress.org&utm_medium=referral&utm_campaign=WPGSC&utm_content=plugin+repos+description)
✔ [WPForms Google Sheet Connector](https://www.gsheetconnector.com/wpforms-google-sheet-connector-pro?utm_source=wordpress.org&utm_medium=referral&utm_campaign=WPGSC&utm_content=plugin+repos+description)
✔ [Gravity Forms Google Sheet Connector](https://www.gsheetconnector.com/gravity-forms-google-sheet-connector?utm_source=wordpress.org&utm_medium=referral&utm_campaign=WPGSC&utm_content=plugin+repos+description)
✔ [Ninja Forms Google Sheet Connector](https://www.gsheetconnector.com/ninja-forms-google-sheet-connector-pro?utm_source=wordpress.org&utm_medium=referral&utm_campaign=WPGSC&utm_content=plugin+repos+description)
✔ [Elementor Forms Google Sheet Connector](https://www.gsheetconnector.com/elementor-forms-google-sheet-connector-pro?utm_source=wordpress.org&utm_medium=referral&utm_campaign=WPGSC&utm_content=plugin+repos+description)
✔ [Avada Forms Google Sheet Connector](https://www.gsheetconnector.com/avada-forms-google-sheet-connector-pro?utm_source=wordpress.org&utm_medium=referral&utm_campaign=WPGSC&utm_content=plugin+repos+description)
✔ [DIVI Forms Google Sheet Connector](https://www.gsheetconnector.com/divi-forms-db-google-sheet-connector-pro?utm_source=wordpress.org&utm_medium=referral&utm_campaign=WPGSC&utm_content=plugin+repos+description)


**eCommerce Google Sheet Connector Addons**
✔ [WooCommerce Google Sheet Connector](https://wordpress.org/plugins/wc-gsheetconnector?utm_source=wordpress.org&utm_medium=referral&utm_campaign=WPGSC&utm_content=plugin+repos+description)
✔ [Easy Digital Downloads Google Sheet Connector](https://wordpress.org/plugins/gsheetconnector-easy-digital-downloads/)

[FREE VERSIONS CAN BE DOWNLOADED FROM HERE](https://profiles.wordpress.org/westerndeal/#content-plugins)

== Changelog ==	

= 1.0.15 (12-08-2025) =
Fixed: Warnings and errors reported by Plugin Check.
Fixed: Missing function comments for better code readability.
Fixed: Duplicate feed name check to ensure unique entries with error feedback.
Added: Old extension page replaced with new design.
Added: General Settings tab in plugin settings.
Changed: Moved logs to the uploads folder for better file organization.

= 1.0.14 (22-04-2025) =
Added: Moved saving of credentials to database for Auto API Integration.

= 1.0.13 (20-02-2025) =
Fixed: Vulnerability issues.

= 1.0.12 (05-02-2025) =
Fixed: Minor UI changes.

= 1.0.11 (17-11-2024) =
Fixed: File not found error.

= 1.0.10 (15-11-2024) =
Fixed: Incorrect values saving to sheet for Checkbox, Radio and Dropdown fields.
Fixed: Dashboard Widget.
Fixed: Copy to clipboard not coping the data under System Info tab.
Updated: CSS and JS files.

= 1.0.9 (12-08-2024) =
Added: Display a notification when authentication expires.

= 1.0.8 (02-08-2024) =
- Added: Separate data for address fields.

= 1.0.7 (01-08-2024) =
- Fixed: Google hasn’t verified this app error.

= 1.0.6 (23-07-2024) =
- Added: PRO features display.
- Fixed: Some fields to show in sheet, while Enabling Manual Adding Headers for Fields entering into the Google Sheet.

= 1.0.5 (29-05-2024) =
- Fixed: UI changes.
- Added: Compatible with Forminator GSheetConnector PRO.

= 1.0.4 =
* Fixed : Fixed validate parent plugin exists or not then show alert message display issue.

= 1.0.3 =
* Fixed : Databse prefix issue fixed.
* Fixed : Value starts with += fixed.
* Fixed : Resolved active plugins show issue in system status tab.

= 1.0.2 =
* Redesigned the System status interface.

* UI Changes: The user interface has been revamped for a more intuitive and user-friendly experience.

* Authorization Control And Enhancement : Google Sheet Link .

* Fixed Freemius Activation Issue In Multi_site  Network.

* Fixed : Resolved debugging view, open and close link systematically.

* Added : For user without Google Drive and Google Sheets permissions displayed error message.

* Added : Get Code button has replaced with the Sign in with Google button.

= 1.0.1 =
* Fixed Vulnerability: The plugin's authentication and authorization mechanisms have been enhanced to ensure data security. 

* System Status Tab: It assists in troubleshooting and ensuring smooth operation.

* UI Changes: The user interface has been revamped for a more intuitive and user-friendly experience.

* Authorization Control: ensures that only authorized personnel can access and interact with sensitive data and functionalities within the plugin.

* Freemius Integration.

= 1.0.0 =
* First public release
