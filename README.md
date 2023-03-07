# Coco_XH

Coco_XH facilitates to have an arbitrary amount of
so called co-contents on your website.
These are similar to newsboxes,
but instead have different content for every page.
Coco_XH is inspired by the
[Also plugin](http://cmsimplewiki-com.keil-portal.de/doku.php?id=plugins:also),
but allows editing with the chosen editor instead of a
textarea and scripting to be used.

- [Requirements](#requirements)
- [Download](#download)
- [Installation](#installation)
- [Settings](#settings)
- [Usage](#usage)
  - [Searching](#searching)
  - [Administration](#administration)
- [Troubleshooting](#troubleshooting)
- [License](#license)
- [Credits](#credits)

## Requirements

Coco_XH is a plugin for [CMSimple_XH](https://www.cmsimple-xh.org/).
It requires CMSimple_XH ≥ 1.7.0 and PHP ≥ 7.0.0.

## Download

The [lastest release](https://github.com/cmb69/coco_xh/releases/latest)
is available for download on Github.

## Installation

The installation is done as with many other CMSimple_XH plugins.

1. Backup the data on your server.
1. Unzip the distribution on your computer.
1. Upload the whole directory `coco/` to your server into
   the `plugins/` directory of CMSimple_XH.
1. Set write permissions to the subdirectories
   `css/` and `languages/`.
1. Navigate to `Plugins` → `Coco` in the back-end to check if
   all requirements are fulfilled.

## Settings

The configuration of the plugin is done as with many other CMSimple_XH
plugins in the back-end of the Website.
Go to `Plugins` → `Coco`.

Localization is done under `Language`.
You can translate the character strings to your own language,
if there is no appropriate language file available,
or customize them according to your needs.

The look of Coco_XH can be customized under `Stylesheet`.
This is mainly meant for styling the back-end.
The styling of the co-content should be done by
modifying the template and its stylesheet.

## Usage

To have co-content on your site, just insert in your template

    <?php echo coco('my_content');?>

in the place where you want, e.g. instead of a newsbox.
Instead of `my_content` you can choose any name,
as long as it consists of lower-case letters (`a`-`z`),
digits and underscores only.
You can have as many co-contents as you like,
as long as you give them different names.

The screenshot below shows two co-contents:

![View mode](https://github.com/cmb69/coco_xh/raw/master/help/view-mode.png)

If you are in edit mode, you can edit the co-content with the configured editor.
If you want to use another toolbar than the one
you have configured for the main content editor,
just give the name of the toolbar as second parameter, e.g.

    <?php echo coco('small_content', 'sidebar');?>

As toolbars, usually "full", "medium", "minimal" and "sidebar" are available.
How to customize the TinyMCE toolbars is explained in the
[CMSimple_XH Wiki](https://wiki.cmsimple-xh.org/doku.php/tinymce#customization),

The screenshot below shows the co-contents in edit mode:

![Edit mode](https://github.com/cmb69/coco_xh/raw/master/help/edit-mode.png)

The width and height of the textarea and the editor, respectively,
default to 100% of its container.
The height can be changed by a third parameter to the `coco()` call, e.g.

    <?php echo coco('my_content', 'sidebar', '500px');?>

If you pass this parameter, it's mandatory to give the toolbar parameter too.
If you want to stick with the default toolbar, you can write:

    <?php echo coco('my_content', false, '500px');?>

If you want to change the width, you can do so in the stylesheet.
The co-contents are stored in the subfolder `coco/` of the `content/` folder
of CMSimple_XH in an HTML file with the name given as first parameter,
e.g. `my_content.htm`.
The structure is similar to that of the content.htm file of CMSimple_XH 1.7 and up,
but for historic reasons,
the pages are separated by `<h1>`, `<h2>` … `<h9>` according to their menu level,
instead of respective HTML comments.
The names of the page headings are inserted for better readability only;
they are ignored by Coco_XH.
Instead the link to the page is made with the id given for the heading.
You must not alter these ids in any way!
Creating new pages by inserting new headings is not possible;
these will simply be ignored.

Note that a backup of all co-contents is automatically made on logout.
This is done the same way as CMSimple_XH makes backups of the content.
If you want to restore a backup, you have to do it via FTP.

### Searching

If you want the search function of CMSimple_XH to search your co-contents too,
you have to replace cmsimple/search.php with plugins/coco/search.php.
The search will work the same as in CMSimple_XH 1.5.x.

### Administration

You can administrate the co-contents under `Plugins` → `Coco` → `Co-Contents`.
Currently it is only possible to delete co-content files that you
do not need any more.
It is however important to delete those files as they will
automatically being searched by the search function;
besides taking time it might give wrong search results.

## Troubleshooting

Report bugs and ask for support either on
[Github](https://github.com/cmb69/coco_xh/issues)
or in the [CMSimple\_XH Forum](https://cmsimpleforum.com/).

## License

Coco_XH is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Coco_XH is distributed in the hope that it will be useful,
but *without any warranty*; without even the implied warranty of
*merchantibility* or *fitness for a particular purpose*. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Coco_XH.  If not, see <https://www.gnu.org/licenses/>.

Copyright 2012-2023 Christoph M. Becker

Slovak translation © 2012 Dr. Martin Sereday  
Czech translation © 2012 Josef Němec  

## Credits

Coco_XH was inspired by Ricardo Serpell’s
[Also plugin](https://cmsimplewiki-com.keil-portal.de/doku.php?id=plugins:also).

The plugin icon is designed by [Andy Gongea](https://gongea.com/).
Many thanks for publishing this icon as freeware.

Many thanks to the community at the [CMSimple_XH forum](https://www.cmsimpleforum.com/)
for tips, suggestions and testing.
Especially, my thanks go to *snafu* and *Ulrich*, who pointed out the
usefulness of Also and changed my mind on where to store those data,
and to *Gert* and *snafu* who reported the first bugs and suggested the new name.

And last but not least many thanks to
[Peter Harteg](https://www.harteg.dk/), the “father” of CMSimple,
and all developers of [CMSimple_XH](https://www.cmsimple-xh.org/)
without whom this amazing CMS would not exist.
