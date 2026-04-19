# MRK Zeeq Branches

A WordPress plugin that registers a custom **Branches** post type and renders them in a filterable, paginated grid — all via a single shortcode.

**Author:** Muhammad Rameez Khalid  
**Version:** 1.0  
**License:** GPL-2.0+  
**Requires:** WordPress 5.0+, PHP 7.4+

---

## Features

- Custom post type `zeeq_branches` with full admin support
- Per-branch custom fields: Manager Name, Address, Phone, Map Link, City
- City-based dropdown filter (auto-populated from saved data)
- Responsive 4-column grid (breaks to 3 → 2 → 1 on smaller screens)
- Pagination (16 branches per page)
- Thumbnail popup viewer on image click

---

## Installation

1. Download or clone this repository
2. Upload the `MRK-Zeeq-Branches` folder to `/wp-content/plugins/`
3. Activate the plugin from **WordPress Admin → Plugins**

---

## Shortcode

Place this shortcode on any page or post:

```
[zeeq_branches]
```

This renders the full branches grid with city filter and pagination.

---

## Adding a Branch

1. Go to **WordPress Admin → Zeeq Branches → Add New Branch**
2. Enter the branch title
3. Set a **Featured Image** (displayed as the branch photo)
4. Fill in the **Branch Information** meta box:
   - **Branch Manager Name**
   - **Branch Address**
   - **Phone Number**
   - **Map Location Link** (Google Maps URL)
   - **City** (used for filtering)
5. Publish

---

## File Structure

```
MRK-Zeeq-Branches/
├── mrk-zeeq-branches.php   # Main plugin file
├── css/
│   └── style.css           # Frontend styles for branch grid
└── README.md
```

---

## Hooks & Functions

| Function | Hook | Description |
|---|---|---|
| `zeeq_enqueue_styles()` | `wp_enqueue_scripts` | Loads `style.css` on the frontend |
| `zeeq_custom_post_type()` | `init` | Registers the `zeeq_branches` CPT |
| `zeeq_add_custom_fields()` | `add_meta_boxes` | Adds the Branch Information meta box |
| `zeeq_branch_info_callback()` | — | Renders the meta box form fields |
| `zeeq_save_branch_info()` | `save_post` | Sanitizes and saves custom field data |
| `zeeq_display_branches()` | — | Shortcode callback for `[zeeq_branches]` |
| `zeeq_change_post_object_label()` | `init` | Updates admin menu labels |

---

## Responsive Breakpoints

| Breakpoint | Columns |
|---|---|
| > 1100px | 4 columns |
| 1001px – 1100px | 3 columns |
| 601px – 1000px | 2 columns |
| ≤ 600px | 1 column |

---

## License

This plugin is licensed under the [GPL-2.0+](https://www.gnu.org/licenses/gpl-2.0.html).
