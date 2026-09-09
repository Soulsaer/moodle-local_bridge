Bootstrap Icons
================

Library: Bootstrap Icons
Version: 1.13.1
License: MIT
Project: https://github.com/twbs/icons
Release: https://github.com/twbs/icons/releases/tag/v1.13.1

Imported files
--------------
The Bridge plugin uses the distributable Bootstrap Icons stylesheet with its
embedded WOFF2 font. The bundled stylesheet is bootstrap-icons.min.css.

Import/update procedure
-----------------------
1. Download the Bootstrap Icons 1.13.1 release from the project release page.
2. Use the distributable font/css files from that release.
3. Replace the contents of this directory while keeping the library directory
   name as bootstrap-icons.
4. Verify that the MIT license is retained as LICENSE.txt.
5. Update the version in ../thirdpartylibs.xml and this file when upgrading.
6. Record the library update in the plugin upgrade.txt/CHANGES.

Moodle note
-----------
Bootstrap Icons is bundled by Bridge for its administration UI. It is not
loaded from a remote CDN.
