# My Custom Table Plugin (Table My Posts)

A WordPress plugin that creates customizable, sortable, and responsive tables from your posts and custom post types with Advanced Custom Fields (ACF) integration.

## Features

- 📱 Fully responsive table design with mobile-friendly card view
- 🔄 Client-side sorting for all columns
- 🎯 Support for multiple column types:
  - Post Title
  - Post Content
  - Publication Date
  - ACF Fields
- 🔗 Hyperlink support for any column
- ↔️ Column alignment options (left, center, right)
- ⚙️ Easy-to-use admin interface
- 📦 Works with any post type

## Installation

1. Download the plugin files
2. Upload the plugin folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure your table settings under the 'Table Settings' menu item

## Usage

### Basic Setup

1. Navigate to 'Table Settings' in your WordPress admin menu
2. Select your desired post type
3. Configure your table columns:
   - Add/remove columns using the buttons
   - Set column headers
   - Choose column types
   - Configure alignment
   - Set up ACF field connections
   - Enable hyperlinks if needed

### Displaying the Table

Use the shortcode `[custom_post_table]` in any post, page, or widget area to display your table.

### Column Types

- **Title**: Displays the post title
- **Content**: Shows a trimmed version of the post content
- **Date**: Shows the post publication date
- **ACF Field**: Displays the value of any Advanced Custom Fields field

### Hyperlink Feature

For any column, you can:
1. Check the "Is Hyperlink?" option
2. Specify an ACF field that contains the URL
3. The column content will then link to the specified URL

## Mobile Responsiveness

- Tables are horizontally scrollable on tablets
- Transforms into a card view on mobile devices (under 480px)
- Column headers become labels in mobile view

## Requirements

- WordPress 5.0 or higher
- Advanced Custom Fields plugin (for ACF field functionality)
- PHP 7.0 or higher

## Styling

The plugin includes default styling that matches most WordPress themes. The table design is clean and modern with:

- Alternating row colors
- Hover effects
- Sort indicators
- Responsive breakpoints
- Clean typography

## Support

For support or feature requests, please open an issue in the plugin's repository.

## License

This plugin is licensed under the GPL v2 or later.

## Changelog

### Version 1.4
- Added sorting functionality
- Improved mobile responsiveness
- Added column alignment options
- Added hyperlink support for columns

---

*Note: This plugin works best with Advanced Custom Fields (ACF) plugin installed if you plan to use custom field functionality.*
