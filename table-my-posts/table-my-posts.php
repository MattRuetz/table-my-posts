<?php
/*
Plugin Name: My Custom Table Plugin
Description: Displays posts in a customizable table format with selected fields
Version: 1.4
Author: Your Name
*/

// Add admin menu
add_action('admin_menu', 'mct_add_admin_menu');
function mct_add_admin_menu() {
    add_menu_page(
        'Table Settings',
        'Table Settings',
        'manage_options',
        'my-custom-table',
        'mct_settings_page',
        'dashicons-grid-view'
    );
}

// Register settings
add_action('admin_init', 'mct_register_settings');
function mct_register_settings() {
    register_setting('mct_settings', 'mct_post_type');
    register_setting('mct_settings', 'mct_columns');
}

// Create settings page
function mct_settings_page() {
    $post_types = get_post_types(['public' => true], 'objects');
    $saved_columns = get_option('mct_columns', []);
    ?>
    <div class="wrap">
        <h2>Table Settings</h2>
        <form method="post" action="options.php">
            <?php settings_fields('mct_settings'); ?>
            
            <table class="form-table">
                <tr>
                    <th>Select Post Type:</th>
                    <td>
                        <select name="mct_post_type">
                            <?php foreach($post_types as $post_type): ?>
                                <option value="<?php echo esc_attr($post_type->name); ?>"
                                    <?php selected(get_option('mct_post_type'), $post_type->name); ?>>
                                    <?php echo esc_html($post_type->labels->singular_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th>Configure Columns:</th>
                    <td>
                        <div id="column-container">
                            <?php
                            if (!empty($saved_columns)) {
                                foreach ($saved_columns as $index => $column) {
                                    ?>
                                    <div class="column-row">
                                        <input type="text" name="mct_columns[<?php echo $index; ?>][header]" 
                                               value="<?php echo esc_attr($column['header']); ?>" placeholder="Column Header">
                                        <select name="mct_columns[<?php echo $index; ?>][type]">
                                            <option value="title" <?php selected($column['type'], 'title'); ?>>Title</option>
                                            <option value="content" <?php selected($column['type'], 'content'); ?>>Content</option>
                                            <option value="date" <?php selected($column['type'], 'date'); ?>>Date</option>
                                            <option value="acf" <?php selected($column['type'], 'acf'); ?>>ACF Field</option>
                                        </select>
                                        <select name="mct_columns[<?php echo $index; ?>][align]">
                                            <option value="left" <?php selected($column['align'] ?? 'left', 'left'); ?>>Left</option>
                                            <option value="center" <?php selected($column['align'] ?? 'left', 'center'); ?>>Center</option>
                                            <option value="right" <?php selected($column['align'] ?? 'left', 'right'); ?>>Right</option>
                                        </select>
                                        <input type="text" name="mct_columns[<?php echo $index; ?>][acf_field]" 
                                               value="<?php echo esc_attr($column['acf_field'] ?? ''); ?>" placeholder="ACF Field Name">
                                        <label>
                                            <input type="checkbox" name="mct_columns[<?php echo $index; ?>][is_hyperlink]" 
                                                   <?php checked(isset($column['is_hyperlink']) && $column['is_hyperlink']); ?>>
                                            Is Hyperlink?
                                        </label>
                                        <input type="text" name="mct_columns[<?php echo $index; ?>][url_field]" 
                                               value="<?php echo esc_attr($column['url_field'] ?? ''); ?>" 
                                               placeholder="URL ACF Field Name"
                                               class="url-field" <?php echo (!isset($column['is_hyperlink']) || !$column['is_hyperlink']) ? 'style="display:none;"' : ''; ?>>
                                        <button type="button" class="remove-column button">Remove</button>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                        <button type="button" id="add-column" class="button">Add Column</button>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
    </div>

    <script>
    jQuery(document).ready(function($) {
        let columnCount = <?php echo !empty($saved_columns) ? count($saved_columns) : 0; ?>;
        
        $('#add-column').click(function() {
            const newRow = `
                <div class="column-row">
                    <input type="text" name="mct_columns[${columnCount}][header]" placeholder="Column Header">
                    <select name="mct_columns[${columnCount}][type]">
                        <option value="title">Title</option>
                        <option value="content">Content</option>
                        <option value="date">Date</option>
                        <option value="acf">ACF Field</option>
                    </select>
                    <select name="mct_columns[${columnCount}][align]">
                        <option value="left">Left</option>
                        <option value="center">Center</option>
                        <option value="right">Right</option>
                    </select>
                    <input type="text" name="mct_columns[${columnCount}][acf_field]" placeholder="ACF Field Name">
                    <label>
                        <input type="checkbox" name="mct_columns[${columnCount}][is_hyperlink]">
                        Is Hyperlink?
                    </label>
                    <input type="text" name="mct_columns[${columnCount}][url_field]" 
                           placeholder="URL ACF Field Name" class="url-field" style="display:none;">
                    <button type="button" class="remove-column button">Remove</button>
                </div>
            `;
            $('#column-container').append(newRow);
            columnCount++;
        });

        $(document).on('click', '.remove-column', function() {
            $(this).parent().remove();
        });

        $(document).on('change', 'input[name*="[is_hyperlink]"]', function() {
            $(this).closest('.column-row').find('.url-field').toggle(this.checked);
        });
    });
    </script>

    <style>
    .column-row {
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .column-row input[type="text"],
    .column-row select {
        min-width: 150px;
    }
    .column-row input[type="checkbox"] {
        margin: 0;
    }
    .url-field {
        transition: all 0.3s ease;
    }
    </style>
    <?php
}

// Create shortcode
add_shortcode('custom_post_table', 'mct_display_table');
function mct_display_table($atts) {
    $post_type = get_option('mct_post_type', 'post');
    $columns = get_option('mct_columns', []);
    
    $posts = get_posts([
        'post_type' => $post_type,
        'numberposts' => -1,
    ]);

    ob_start();
    ?>
    <style>
    .mct-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    }
    .mct-table th {
        background: #f8f9fa;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
    }
    .mct-table td {
        padding: 12px;
        border-bottom: 1px solid #dee2e6;
    }
    .mct-table tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }
    .mct-table tr:hover {
        background-color: #f2f2f2;
    }
    
    .mct-table th {
        cursor: pointer;
        user-select: none;
        position: relative;
    }
    
    .mct-table th::after {
        content: '↕';
        position: absolute;
        right: 8px;
        opacity: 0.3;
    }
    
    .mct-table th.sort-asc::after {
        content: '↑';
        opacity: 1;
    }
    
    .mct-table th.sort-desc::after {
        content: '↓';
        opacity: 1;
    }
    .mct-table .mct-align-left {
        text-align: left;
    }
    .mct-table .mct-align-center {
        text-align: center;
    }
    .mct-table .mct-align-right {
        text-align: right;
    }
    
    /* Mobile Responsive Styles */
    @media screen and (max-width: 768px) {
        .mct-table {
            display: block;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            -ms-overflow-style: -ms-autohiding-scrollbar;
        }
        
        /* Optional: Card view for very small screens */
        @media screen and (max-width: 480px) {
            .mct-table, .mct-table tbody, .mct-table tr, .mct-table td {
                display: block;
                width: 100%;
            }
            
            .mct-table thead {
                display: none;
            }
            
            .mct-table tr {
                margin-bottom: 1rem;
                border: 1px solid #dee2e6;
                padding: 0.5rem;
            }
            
            .mct-table td {
                text-align: right;
                padding: 8px;
                position: relative;
                border-bottom: 1px solid #eee;
            }
            
            .mct-table td:last-child {
                border-bottom: none;
            }
            
            .mct-table td::before {
                content: attr(data-label);
                float: left;
                font-weight: bold;
                text-transform: uppercase;
                font-size: 0.85em;
            }
            
            /* Override alignment classes for mobile */
            .mct-table .mct-align-left,
            .mct-table .mct-align-center,
            .mct-table .mct-align-right {
                text-align: right;
            }
        }
    }
    </style>

    <table class="mct-table">
        <thead>
            <tr>
                <?php foreach ($columns as $column): ?>
                    <th class="mct-align-<?php echo esc_attr($column['align'] ?? 'left'); ?>">
                        <?php echo esc_html($column['header']); ?>
                    </th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($posts as $post): ?>
                <tr>
                    <?php foreach ($columns as $column): ?>
                        <td class="mct-align-<?php echo esc_attr($column['align'] ?? 'left'); ?>" 
                            data-label="<?php echo esc_attr($column['header']); ?>">
                            <?php
                            switch ($column['type']) {
                                case 'title':
                                    $value = $post->post_title;
                                    break;
                                case 'content':
                                    $value = wp_trim_words($post->post_content, 20);
                                    break;
                                case 'date':
                                    $value = get_the_date('', $post);
                                    break;
                                case 'acf':
                                    if (function_exists('get_field') && !empty($column['acf_field'])) {
                                        $value = get_field($column['acf_field'], $post->ID);
                                    }
                                    break;
                            }

                            // Handle hyperlink display for any field type
                            if (isset($column['is_hyperlink']) && $column['is_hyperlink'] && !empty($column['url_field'])) {
                                $url = function_exists('get_field') ? get_field($column['url_field'], $post->ID) : '';
                                if ($url) {
                                    echo '<a href="' . esc_url($url) . '">' . esc_html($value) . '</a>';
                                } else {
                                    echo esc_html($value);
                                }
                            } else {
                                echo esc_html($value);
                            }
                            ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tables = document.querySelectorAll('.mct-table');
        
        tables.forEach(table => {
            const headers = table.querySelectorAll('th');
            let currentSort = { column: null, direction: 'asc' };
            
            headers.forEach((header, columnIndex) => {
                // Store the column index as a data attribute
                header.dataset.columnIndex = columnIndex;
                
                header.addEventListener('click', () => {
                    const tbody = table.querySelector('tbody');
                    const rows = Array.from(tbody.querySelectorAll('tr'));
                    
                    // Reset all headers
                    headers.forEach(h => {
                        h.classList.remove('sort-asc', 'sort-desc');
                    });
                    
                    // Determine sort direction
                    if (currentSort.column === columnIndex) {
                        currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
                    } else {
                        currentSort.column = columnIndex;
                        currentSort.direction = 'asc';
                    }
                    
                    // Add sort indicator
                    header.classList.add(`sort-${currentSort.direction}`);
                    
                    // Sort rows
                    const sortedRows = rows.sort((a, b) => {
                        // Get cells for this column, handling both desktop and mobile layouts
                        const aCell = a.querySelectorAll('td')[columnIndex];
                        const bCell = b.querySelectorAll('td')[columnIndex];
                        
                        if (!aCell || !bCell) return 0;
                        
                        const aValue = aCell.textContent.trim();
                        const bValue = bCell.textContent.trim();
                        
                        // Check if values are dates
                        const aDate = new Date(aValue);
                        const bDate = new Date(bValue);
                        if (aDate instanceof Date && !isNaN(aDate) && 
                            bDate instanceof Date && !isNaN(bDate)) {
                            return currentSort.direction === 'asc' 
                                ? aDate - bDate 
                                : bDate - aDate;
                        }
                        
                        // Check if values are numbers
                        const aNum = parseFloat(aValue);
                        const bNum = parseFloat(bValue);
                        if (!isNaN(aNum) && !isNaN(bNum)) {
                            return currentSort.direction === 'asc' 
                                ? aNum - bNum 
                                : bNum - aNum;
                        }
                        
                        // Sort as strings
                        return currentSort.direction === 'asc' 
                            ? aValue.localeCompare(bValue)
                            : bValue.localeCompare(aValue);
                    });
                    
                    // Clear and re-append sorted rows
                    tbody.innerHTML = '';
                    sortedRows.forEach(row => tbody.appendChild(row));
                });
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
