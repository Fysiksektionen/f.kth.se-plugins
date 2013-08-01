=== Plugin Name ===
Contributors: kevinB
Donate link: http://agapetry.net/news/introducing-role-scoper/#role-scoper-download
Tags: gallery, access, cms, members, user, groups, admin
Requires at least: 3.0
Tested up to: 3.3.1
Stable Tag: 1.0.3

This Role Scoper extension enables convenient and flexible administration of NextGEN Gallery edit permissions via the <a href="http://wordpress.org/extend/plugins/role-scoper/"></a> plugin (which is required).

Control NextGen Gallery editing access on a Gallery-specific or Album-specific basis.  Or, assign desired Users or Groups a blog-wide General Role of "Gallery Author", "Gallery Editor" or "Gallery Administrator".

Custom access can be designated without changing your WordPress role definitions or user role assignments.  Even Subscribers or Contributors can be enabled to create or edit galleries of your choice.

== Installation ==
1. Upload `rs-config-ngg_?.zip` to the `/wp-content/plugins/` directory
2. Extract `rs-config-ngg_?.zip` into the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress


== Screenshots ==

1. Supplemental Blog-wide Gallery Roles
2. Gallery Editor roles for specific galleries


== Changelog ==

= 1.0.3 - 19 Mar 2012 =
* BugFix : On Network installations, only worked on main site
* BugFix : PHP Notice for load_plugin_textdomain

= 1.0.2 - 6 Nov 2010 =
* BugFix : If some NGG albums have no galleries, PHP notice caused unexpected output on plugin activation

= 1.0.1 - 3 Nov 2010 =
* BugFix : Albums were not selectable in TinyMCE for user with sitewide "NextGEN Edit album" capability

= 1.0 - 8 Oct 2010 =
* BugFix : "Add Gallery" menu link was hidden from some Gallery Authors / Gallery Editors while editing another gallery
* BugFix : General Role of Gallery Administrator was not effective
* BugFix : Galleries could not be fully edited based on RS role assignment
* BugFix : Image Meta, Rotation and Thumbnail Edit was not available based on RS role assignment
* BugFix : NGG Album Roles were not applied reliably if Gallery Album relationships were edited
* Compat : Support inclusion of "NextGEN Use TinyMCE" capability in scoped role assignments
* Change : Galleries and Albums in TinyMCE popup are filtered based on editing capability (when used with RS 1.2.9+)

= 0.9.5 - 30 Dec 2009 =
* BugFix : Undefined variable error (did not affect functionality)

= 0.9.4 - 12 Dec 2009 =
* BugFix : Current Gallery Page Link (added with NGG 1.4) was not displayed to non-administrator
* BugFix : Page Link setting was cleared if a non-administrator saved without re-selecting it
* Compat : Compatibility with Role Scoper 1.1

= 0.9.3 - 9 Nov 2009 =
* BugFix : Users with a Gallery Manager role for any gallery could add new galleries

= 0.9.2 - 28 Aug 2009 =
* BugFix : Duplicate gallery listings under some configurations

= 0.9.1 - 27 Feb 2009 =
* BugFix : Filtering ineffective with NextGen Gallery version 1.1.0+ (affected code is now less sensitive to DB query syntax)
* BugFix : Role / Restriction edit for an individual NGG Gallery did not save changes
* Feature : Edit link for NGG Galleries and NGG Albums from Roles / Restrictions Bulk Admin

= 0.9.0 - 7 Feb 2009 =
* Initial beta release
