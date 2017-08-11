=== Extranet ===
Contributors: ionutlupu
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7XNVWRMYGALQ2
Tags: downloads, client area, extranet, files, download files, private area
Requires at least: 4.0.0
Tested up to: 4.7.2
Stable tag: 2.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create a secure download area / extranet / client area.

== Description ==

Extranet is a plugin that creates a special area with a folder based structure, providing a safe solution to sharing files with website's users. The concept is simple. The website admin creates folders, adds files into folders and grant different permissions to selected users. Once a user is allowed to access this area, he can then download, upload, delete files, depending on what permissions he has. 

Create folders and upload files. Grant your users individual permissions to download, upload or delete files in your folders.

Share and work together with your users and allow them to get their files from anywhere, on any device.

= EXTRANET FEATURES =

= Create folders, add files, grant permissions =

Each file is located inside a folder, just as in your operating system. As the website administrator, you determine the level of access that the user has to your folders. Any restricted folder will automatically be removed from the user view.

= Easy to add files = 

To add new files you must use a simple drag-and-drop interface. You can upload multiple files in a single step. You can also upload the new files directly on server using a ftp client.

= Complete control over user permissions =

As the person granting permission, you determine the level of access that each user has to folders. You can control if the user can:
* list files in folder
* download files in folder
* upload files in folder
* delete files from folder
* create new folder inside a folder
* delete an existing folder

= More Information =

Visit the [Extranet homepage](https://ionutlupu.me/wp-plugins/13-extranet-wordpress.html) for documentation and support.

== Installation ==
1. Visit 'Plugins > Add New'
2. Click 'Upload Plugin' and select the zip archive
3. Click 'Install now'
4. Activate the plugin

In case something goes wrong, you can try to install it manually.
1. Unzip the archive.
2. Upload `extranet` to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. After activation a new page named 'Extranet' is created
5. Add newly created page to your menu, so that your users can access the plugin


== Upgrade Notice ==
No special notes.

== Frequently Asked Questions ==

= Does Extranet protects against direct access to a file? =

No, not if someone knows the full path to a file on your server. At activation time, Extranet generates a random folder name to avoid this issue, but this method is not 100% safe. If you want to be safe, make sure you use as a storage folder a directory outside your public_html folder.

= How do I add users? =

You don't add users, in Extranet you must enable the users you want to allow access to this plugin. This is possible by going to Extranet -> Users

= Where shoud my users access this plugin? =

After activation, a new page `Extranet` is created. Add this page to your menu. Please note that your users must be logged in before accessing this page.

= What if I need support? =

All my paid customers receive priority support. As free or Premium member can visit [this area](https://ionutlupu.me/your-desk/knowledgebase.html), where you will find out knowledgebase. If you're a Premium member you can also open a [support ticket](https://ionutlupu.me/your-desk/tickets.html).

== Screenshots ==

1. The administration area where you can manage all folders activites (create, delete, upload files, rename).
2. Creating a new folder in the admin area.
3. Uploading files into an existing folder.  
4. The list of users and their status. Allowed or Blocked from accessing the plugin.
5. Permissions view for a folder.
6. Plugin settings.
7. Front-end plugin page, where your users can access the plugin.

== Changelog ==

= 2.0.1 =
* fixed missing file

= 2.0 =
* option to email users when new file is added
* option to restrict file types that a user can upload from the front-end
* option to share a file with anyone (being a user is not a requirement) 
* edit user permissions from user page

= 1.1.1 =
* fixed bug. Forced download on apache server.
* new. Added option to order files and folders.

= 1.1.0 =
* + implemented a login form in the front-end. This will avoid using the default WP login which will redirect the user to the back-end area.
* + added a logout item in the plugin menu

= 1.0.1 =
* ~ modified activation to generate a random folder to use as a storage folder.
* ~ modified storage path to support full path to storage folder. This will allow you to use a folder outside public_html.
* ~ fixed a bug in incorrect handling of exceptions in the admin folders view.

= 1.0.0 =
* ~ initial release 
