<?php
/*
Plugin Name: wp_media_export
Plugin URI : https://github.com/akrmd/wp-media-csv-export
Description: wp media data export csv
Version:1
Author: akrmd
Author URI : http://wp.akirumade.com/wp-media-attachment-bluk-csv-export/
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

new wp_media_export_csv;
class wp_media_export_csv
{

    public function __construct()
    {
        add_action('admin_init', array($this, 'dl_csv'), 1);
    }

    public function dl_csv()
    {
        if (!isset($_POST['dl'])) {
            return;
        }

        $date      = date_i18n("Ymd-His");
        $file_name = "export-media-" . $date . ".csv";

        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=" . $file_name);
        $fp = fopen('php://output', 'w');


        $convert = $_POST['convert'];
        if ($convert == 'shiftjis') {
            stream_filter_prepend($fp, 'convert.iconv.utf-8/cp932');
        }

        $the_query = get_posts(array('post_type' => 'attachment', 'posts_per_page' => -1));
        foreach ($the_query[0] as $key => $value) {
            $head[] = $key;
        }
        fputcsv($fp, $head, ',', '"');
        foreach ($the_query as $key => $value) {
            $results[] = (array) $value;
        }

        foreach ($results as $row) {
            if (!$row) {
                continue;
            }
            fputcsv($fp, $row, ',', '"');
        }

        fclose($fp);
        exit();
    }
}


class wp_media_export_admin
{
    private $wp_media_export_options;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'wp_media_export_add_plugin_page'));
    }

    public function wp_media_export_add_plugin_page()
    {
        add_management_page(
            __('メディアCSVエクスポート', 'wp_media_export'), // page_title
            __('メディアエクスポート', 'wp_media_export'), // menu_title
            'manage_options', // capability
            'wp-media-export', // menu_slug
            array($this, 'wp_media_export_create_admin_page') // function
        );
    }

    public function wp_media_export_create_admin_page()
    {
        ?>
        <div class="wrap">
          <h2><?php _e('メディアCSVエクスポート', 'wp_media_export');?></h2>
          <form method="post">
            <table class="setting_table">
              <tr>
              <th><span><?php _e('文字コード', 'wp_media_export');?></span></th>
              <td>
                <ul class="setting_list">
                <li><label><input type="radio" name="convert" value="utf8" checked="checked">UTF-8</label></li>
                <li><label><input type="radio" name="convert" value="shiftjis">SHIFT-JIS</label></li>
                </ul>
              </td>
              </tr>
            </table>
            <p><button name="dl" class="button-primary"><?php _e('CSVエクスポート', 'wp_media_export');?></button></p>
          </form>
        </div>
      <?php
    }

}
if (is_admin()) {
    $wp_media_export = new wp_media_export_admin();
}
