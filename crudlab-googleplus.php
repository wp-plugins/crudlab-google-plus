<?php

/*
  Plugin Name: CRUDLab Google Plus
  Description: CRUDLab Google Plus button allows you to add Google Plus button to your wordpress blog.
  Author: <a href="http://crudlab.com/">CRUDLab</a>
  Version: 1.0.0
 */
$CLGPPath = plugin_dir_path(__FILE__);

require_once $CLGPPath . 'CLGPSettings.php';

class CLGooglePlusBtn {

    private $CLGPSettings = null;
    private $table_name = null;
    public static $table_name_s = 'clgp';
    private $db_version = '1.0.0';
    private $menuSlug = "clgplus-button";
    private $settingsData = null;

    public function __construct() {

        register_activation_hook(__FILE__, array($this, 'clgp_install'));
        register_uninstall_hook(__FILE__, array('CLGooglePlusBtn', 'clgp_uninstall'));

        add_action('admin_menu', array($this, 'setup_menu'));
        $this->CLGPSettings = new CLGPSettings($this);
        $this->menuSlug = 'clgplus-button';
        global $wpdb;
        $this->table_name = $wpdb->prefix . self::$table_name_s;
        $this->settingsData = $wpdb->get_row("SELECT * FROM $this->table_name WHERE id = 1");

        $plugin = plugin_basename(__FILE__);
        add_filter("plugin_action_links_$plugin", array($this, 'settingsLink'));

        add_filter('wp_head', array($this, 'gplusScript'));
        add_filter('the_content', array($this, 'gplusButton'));
        add_shortcode('clgplus', array($this, 'gplusButton'));
    }

    function settingsLink($links) {
        $settings_link = '<a href="admin.php?page=' . $this->menuSlug . '">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    public static function getTableName() {
        global $wpdb;
        return $wpdb->prefix . self::$table_name_s;
    }

    public function getTable_name() {
        return $this->table_name;
    }

    public function getDb_version() {
        return $this->db_version;
    }

    public function setTable_name($table_name) {
        $this->table_name = $table_name;
    }

    public function setDb_version($db_version) {
        $this->db_version = $db_version;
    }

    public function getMenuSlug() {
        return $this->menuSlug;
    }

    public function setMenuSlug($menuSlug) {
        $this->menuSlug = $menuSlug;
    }

    public function getSettingsData() {
        return $this->settingsData;
    }

    public function reloadDBData() {
        global $wpdb;
        return $this->settingsData = $wpdb->get_row("SELECT * FROM $this->table_name WHERE id = 1");
    }

    public function gplusScript() {
        if ($this->getSettingsData()->status != 0) {
            echo '<script src="https://apis.google.com/js/platform.js" async defer>{lang: \'' . $this->getSettingsData()->language . '\'}</script>';
        }
    }

    public function setup_menu() {
        if ($this->getSettingsData()->status == 0) {
            add_menu_page('CRUDLab Google Plus Button', 'CL G+ Button<span  class="update-plugins count-1" id="clgp_circ" style="background:#F00"><span class="plugin-count">&nbsp&nbsp</span></span>', 'manage_options', $this->menuSlug, array($this, 'admin_settings'),plugins_url('img/gplus.png', __FILE__));
        } else {
            add_menu_page('CRUDLab Google Plus Button', 'CL G+ Button<span class="update-plugins count-1" id="clgp_circ" style="background:#0F0"><span class="plugin-count">&nbsp&nbsp</span></span>', 'manage_options', $this->menuSlug, array($this, 'admin_settings'),plugins_url('img/gplus.png', __FILE__));
        }
    }

    function admin_settings() {
        $this->CLGPSettings->registerJSCSS();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->CLGPSettings->validateData()) {
                $this->CLGPSettings->saveData();
            }
        }
        $this->CLGPSettings->renderPage();
    }

    function clgp_install() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

//        $wpdb->query("DROP TABLE IF EXISTS " . $this->table_name);
        $sql = "CREATE TABLE IF NOT EXISTS $this->table_name (
		`id` int(11) NOT NULL,
                `display` int(11) DEFAULT NULL,
                `except_ids` varchar(255) DEFAULT NULL,
                `share_type` int(2) DEFAULT NULL,
                share_type_url varchar(500) DEFAULT NULL,
                 beforeafter varchar (25) DEFAULT NULL,
                `position` varchar(50) DEFAULT NULL,
                `language` varchar(50) DEFAULT NULL,
                `size` varchar(20) DEFAULT NULL,
                `annotation` varchar(50) DEFAULT NULL,
                `width` int(11) DEFAULT NULL,
                `status` int(1) NOT NULL DEFAULT '1',
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		UNIQUE KEY id (id)
	) $charset_collate;";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($sql);

        add_option('CLGooglePlusBtn_db_version', $this->db_version);
        $myrows = $wpdb->get_results("SELECT * FROM $this->table_name WHERE id = 1");
        if ($myrows == NULL) {
            $wpdb->insert($this->table_name, array(
                'id' => 1,
                'display' => 7,
                'except_ids' => '',
                'share_type' => 1,
                'share_type_url' => NULL,
                'beforeafter' => 'before',
                'position' => 'center',
                'language' => 'en-US',
                'size' => 'standard',
                'annotation' => 'inline',
                'width' => 250,
                'status' => 1,
            ));
        }
    }

    public static function clgp_uninstall() {
        global $wpdb;
        $tbl = self::getTableName();
        $wpdb->query("DROP TABLE IF EXISTS $tbl");
    }

    public function gplusButton($content = NULL) {
        $settings = $this->getSettingsData();
        $str = $content;

        if ($settings->share_type == 1) {
            $actual_link = get_the_permalink();
        } else if ($settings->share_type == 2) {
            $actual_link = get_site_url();
        } else {
            if (filter_var($settings->share_type_url, FILTER_VALIDATE_URL) === FALSE) {
                $actual_link = get_the_permalink();
            } else {
                $actual_link = $settings->share_type_url;
            }
        }

        $fb = '<div style="width:100%; text-align:' . $settings->position . '"><div class="g-plusone" data-size="' . $settings->size . '" data-annotation="' . $settings->annotation . '"  ' . ((intval($settings->width) > 0) ? 'data-width="' . $settings->width . '"' : '') . '  data-href="' . $actual_link . '"></div></div>';
        if ($settings->status == 0) {
            $str = $content;
        } else {
            if ($content == NULL) {
                $str = $fb;
            }
            if ($settings->display & 2) {
                if (is_page() && !is_front_page()) {
                    if ($settings->beforeafter == 'before') {
                        $str = $fb . $content;
                    } else {
                        $str = $content . $fb;
                    }
                }
            }
            if ($settings->display & 1) {
                if (is_front_page()) {
                    if ($settings->beforeafter == 'before') {
                        $str = $fb . $content;
                    } else {
                        $str = $content . $fb;
                    }
                }
            }
            if ($settings->display & 4) {
                if (is_single()) {
                    if ($settings->beforeafter == 'before') {
                        $str = $fb . $content;
                    } else {
                        $str = $content . $fb;
                    }
                }
            }
        }

        $except_check = true;
        if ($settings->display & 8) {
            $post_id = get_the_ID();
            @$expect_ids_arrays = split(',', $settings->except_ids);
            foreach ($expect_ids_arrays as $id) {
                if (trim($id) == $post_id) {
                    $except_check = false;
                }
            }
        }
        if ($except_check) {
            return $str;
        } else {
            return $content;
        }
    }

}

global $wpgpbtn;
$wpgpbtn = new CLGooglePlusBtn();

function clgplusButton() {
    global $wpgpbtn;
    echo $wpgpbtn->gplusButton();
}
