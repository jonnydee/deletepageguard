# Delete Guard Plugin for DokuWiki

The **Delete Guard** plugin prevents accidental or intentional deletion of
wikipages in your DokuWiki installation by blocking the “empty save”
operation when certain pages are protected. A page is considered for
protection when its ID or relative file path matches one or more
regular expressions configured via the DokuWiki Configuration Manager.

## Features

* Prevents deletion via “empty save” on protected pages.
* Supports any number of protection patterns using PCRE syntax.
* Choose whether to match against the colon‑separated page ID or the
  relative file system path.
* Allow administrators and optional additional groups to bypass the
  protection.
* Optionally treat whitespace‑only content as empty.

## Installation

1. Copy the contents of this plugin directory into
   `<dokuwiki>/lib/plugins/deleteguard/`.
2. Ensure that the directory name matches the `base` value in
   `plugin.info.txt` (here: `deleteguard`).
3. Visit the **Configuration Manager** in your DokuWiki and adjust the
   plugin settings under the “Delete Guard” section.

## Configuration

The following options are available in the Configuration Manager:

| Setting | Description |
|---|---|
| **Protected page patterns** | List of PCRE regular expressions. Each line defines a pattern. When a page matches any pattern, non‑admin users cannot delete it by empty save. |
| **Match against** | Choose whether the patterns should match against the page ID (e.g. `users:john:home`) or the relative file path (e.g. `users/john/home.txt`). |
| **Extra groups allowed to delete** | Comma separated list of user groups that are allowed to delete protected pages, in addition to administrators. Leave empty to restrict deletion to admins only. |
| **Treat whitespace‑only pages as empty** | If enabled, pages containing only whitespace will be treated as empty and deletion will be blocked on protected pages. |

### Pattern examples

* `^users:` – protect all pages in the `users` namespace.
* `^users:[^:]+:start$` – protect every user’s landing page named `start` under `users:<username>`. |
* `^projects:.*$` – protect everything in the `projects` namespace. |
* `^private/.*\.txt$` – when matching against file paths, protect any `.txt` file in the `private` directory. |

## How it works

When a page is saved, DokuWiki triggers the
`COMMON_WIKIPAGE_SAVE` event just before writing to disk. For normal
edits, the plugin does nothing. However, when the new content is
empty (after optional trimming) the plugin checks the configured
patterns against the chosen target (ID or file path). If a match
occurs and the current user is not an administrator and not in one of
the exempt groups, the plugin prevents the deletion by calling
`$event->preventDefault()` and `$event->stopPropagation()` as
documented in DokuWiki’s event system【147016890842581†L304-L335】. An error message is displayed to
the user informing them that deletion is not allowed.

## Compatibility

This plugin hooks into the `COMMON_WIKIPAGE_SAVE` event, which was
introduced in DokuWiki release **“Detritus” (2016‑02‑24)** and is
marked as preventable【699486104488352†L111-L139】. It has been tested for compatibility with
current releases such as **Kaos 2024‑02‑06b** and **Librarian 2025‑05‑14**.
The plugin uses only public APIs and the documented event system,
so it should continue to work with future versions as long as these
events remain available.

## License

This plugin is released under the terms of the
[GNU General Public License v2](https://www.gnu.org/licenses/gpl-2.0.html).