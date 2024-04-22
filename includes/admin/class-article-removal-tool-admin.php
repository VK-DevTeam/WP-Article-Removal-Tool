<?php
class Article_Removal_Tool_Admin {
    public function __construct() {
        // Add hooks for admin initialization
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
    }

    // Add plugin page to the admin menu
    public function add_plugin_page() {
        add_menu_page(
            'Article Removal Tool',
            'Article Removal Tool',
            'manage_options',
            'article_removal_tool',
            array($this, 'create_admin_page'),
            'dashicons-admin-tools',
            99
        );
    }

    // Create the plugin settings page
    public function create_admin_page() {
        ?>
        <div class="wrap">
            <h2>Article Removal Tool</h2>
            <form method="post" action="options.php">
                <?php
                settings_fields('article_removal_tool_settings');
                do_settings_sections('article_removal_tool_settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    // Initialize plugin settings
    public function page_init() {
        register_setting(
            'article_removal_tool_settings',
            'article_removal_tool_settings',
            array($this, 'sanitize')
        );

        add_settings_section(
            'setting_section_id',
            'Settings',
            array($this, 'print_section_info'),
            'article_removal_tool_settings'
        );

        add_settings_field(
            'selected_author',
            'Select User',
            array($this, 'author_callback'),
            'article_removal_tool_settings',
            'setting_section_id'
        );

        add_settings_field(
            'selected_age',
            'Select Age',
            array($this, 'age_callback'),
            'article_removal_tool_settings',
            'setting_section_id'
        );

        // Check if settings have been saved and trigger task execution
        if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
            $this->run_functionality();
        }
    }

    // Run the plugin functionality
    public function run_functionality() {
        // Run the task
        do_action('remove_old_articles');

        // Display success message
        add_settings_error(
            'article_removal_tool_settings',
            'functionality_executed',
            'Functionality executed successfully.',
            'updated'
        );
    }

    // Sanitize input
    public function sanitize($input) {
        $sanitized_input = array();
        $sanitized_input['selected_user'] = isset($input['selected_user']) ? intval($input['selected_user']) : 0;
        $sanitized_input['selected_age'] = isset($input['selected_age']) ? intval($input['selected_age']) : 8;
        return $sanitized_input;
    }

    // Print section info
    public function print_section_info() {
        echo 'Select user and age criteria for article removal:';
    }

    // Render author dropdown
    public function user_callback() {
        $users = get_users();
        ?>
        <select name="article_removal_tool_settings[selected_user]">
            <option value="0">Select User</option>
            <?php foreach ($users as $user) : ?>
                <option value="<?php echo esc_attr($user->ID); ?>"><?php echo esc_html($user->display_name); ?></option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    // Render age dropdown
    public function age_callback() {
        $selected_age = get_option('article_removal_tool_settings')['selected_age'];
        ?>
        <select name="article_removal_tool_settings[selected_age]">
            <option value="6" <?php selected($selected_age, 6); ?>>6 Months</option>
            <option value="8" <?php selected($selected_age, 8); ?>>8 Months</option>
            <option value="10" <?php selected($selected_age, 10); ?>>10 Months</option>
            <option value="12" <?php selected($selected_age, 12); ?>>12 Months</option>
        </select>
        <?php
    }
}
