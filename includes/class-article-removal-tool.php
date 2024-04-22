<?php
class Article_Removal_Tool {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_run_functionality', array($this, 'run_functionality_ajax'));
        add_action('init', array($this, 'schedule_task'));
        add_action('remove_old_articles', array($this, 'remove_articles_task'));
        // Hook into the 'transition_post_status' action to detect post status changes
        add_action('transition_post_status', 'add_post_slug_to_htaccess', 10, 3);
    }

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

    public function create_admin_page() {
        ?>
        <div class="wrap">
            <h2>Article Removal Tool</h2>
            <div id="article-removal-feedback"></div>
            <label for="user-select">Select Author:</label>
            <select id="user-select">
                <option value="0">All Authors</option>
                <?php
                $users = get_users();
                foreach ($users as $user) {
                    printf('<option value="%d">%s</option>', $user->ID, $user->display_name);
                }
                ?>
            </select>
            <label for="month-select">Select Month:</label>
            <select id="month-select">
                <option value="6">6 Months</option>
                <option value="8">8 Months</option>
                <option value="10">10 Months</option>
                <option value="12">12 Months</option>
            </select>
            <button id="run-functionality" class="button-primary">Run Functionality</button>
        </div>
        <?php
    }
    

    public function enqueue_scripts($hook) {
        if ('toplevel_page_article_removal_tool' === $hook) {
            wp_enqueue_script('article-removal-script', plugins_url('assets/js/article-removal-script.js', __FILE__), array('jquery'), '', true);
            wp_localize_script('article-removal-script', 'article_removal_tool', array('ajaxurl' => admin_url('admin-ajax.php')));
        }
    }
    

    public function schedule_task() {
        if (!wp_next_scheduled('remove_old_articles')) {
            wp_schedule_event(time(), 'daily', 'remove_old_articles');
        }
    }

    public function remove_articles_task() {
        // Functionality moved to JavaScript
    }

    public function run_functionality_ajax() {
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $month = isset($_POST['month']) ? intval($_POST['month']) : 8;
    
        // Get articles based on user ID and date criteria
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'author' => $user_id,
            'date_query' => array(
                array(
                    'before' => date('Y-m-d', strtotime("-$month months")),
                    'inclusive' => true,
                ),
            ),
            'fields' => 'ids', // Only retrieve post IDs for performance
            'posts_per_page' => -1, // Retrieve all posts
        );
    
        $articles = get_posts($args);
    
        // Remove each article
        foreach ($articles as $article_id) {
            // Set post status to draft (unpublish)
            wp_update_post(array(
                'ID' => $article_id,
                'post_status' => 'draft',
            ));
    
            // Optionally, remove media associated with the article
            // This depends on your specific requirements
            // For example: wp_delete_attachment(get_post_thumbnail_id($article_id), true);
        }
    
        // Send response
        wp_send_json_success('Articles removed successfully.');
    }


    function add_post_slug_to_htaccess($new_status, $old_status, $post) {
        // Check if the new status of the post is 'draft' and the old status is not 'draft'
        if ($new_status === 'draft' && $old_status !== 'draft') {
            // Get the post slug
            $slug = $post->post_name;
    
            // Define the .htaccess file path
            $htaccessFile = ABSPATH . '.htaccess';
    
            // Check if the .htaccess file is writable
            if (is_writable($htaccessFile)) {
                // Define the redirect rule
                $rule = "\nRedirect 301 /" . $slug . " /";
    
                // Append the redirect rule to the .htaccess file
                file_put_contents($htaccessFile, $rule, FILE_APPEND | LOCK_EX);
            }
        }
    }

    
}
