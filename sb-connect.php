<?php
/**
* Plugin Name: SB Connect
* Plugin URI: https://github.com/majsan/sb-connect
* Description: Show saldo in Systembolaget on your site
* Version: 1.0
* Author: Majsan
* Author URI:
**/

if (! class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class SBConnect_Overview_Table extends WP_List_Table
{
  
  /** Class constructor */
    public function __construct()
    {
        parent::__construct([
    'singular' => __('Artikel', 'sp'), // singular name of the listed records
    'plural'   => __('Artiklar', 'sp'), // plural name of the listed records
    'ajax'     => false // should this table support ajax?

  ]);
    }
  
    /**
     * Retrieve customer’s data from the database
     *
     * @param int $per_page
     * @param int $page_number
     *
     * @return mixed
     */
    public static function get_articles($per_page = 25, $page_number = 1)
    {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}sb_connect_articles";

        if (! empty($_REQUEST['orderby'])) {
            $sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
            $sql .= ! empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
        }

        $sql .= " LIMIT $per_page";

        $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;


        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result;
    }
  
    /**
 * Prepare the items for the table to process
 *
 * @return Void
 */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $perPage = 25;
        $currentPage = $this->get_pagenum();

        $items = self::get_articles($perPage, $currentPage);
        $totalItems = count($items);

        $this->set_pagination_args(array(
          'total_items' => $totalItems,
          'per_page'    => $perPage
      ));

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $items;
    }
  
    /**
     *  Associative array of columns
     *
     * @return array
     */
    public function get_columns()
    {
        $columns = [
      'cb'      => '<input type="checkbox" />',
      'article_name'    => 'Artikelnamn',
      'article_id' => 'Artikelnummer'
    ];

        return $columns;
    }
  
    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        return array('article_name' => array('article_name', false));
    }

    /**
      * Define what data to show on each column of the table
      *
      * @param  Array $item        Data
      * @param  String $column_name - Current column name
      *
      * @return Mixed
      */
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
           case 'id':
           case 'article_name':
           case 'article_id':
               return $item[ $column_name ];
           default:
               return print_r($item, true);
       }
    }
}

global $sb_connect_db_version;
$sb_connect_db_version = '1.0';

function sb_connect_install()
{
    global $wpdb;
    global $sb_connect_db_version;

    $charset_collate = $wpdb->get_charset_collate();

    $articles_table_name = $wpdb->prefix . "sb_connect_articles";
    $sites_table_name = $wpdb->prefix . "sb_connect_sites";

    $sql1 = "CREATE TABLE $articles_table_name (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      #time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      article_name text NOT NULL,
      article_id tinytext NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;";
    
    $sql2 = "CREATE TABLE $sites_table_name (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      #time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      site_name text NOT NULL,
      site_id tinytext NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql1);
    dbDelta($sql2);
}


add_action('admin_menu', 'plugin_setup_menu');
register_activation_hook(__FILE__, 'sb_connect_install');
#register_deactivation_hook(__FILE__, 'sb_something');
register_uninstall_hook(__FILE__, 'remove_db_tables');

function plugin_setup_menu()
{
    add_menu_page(
        'SBConnect',
        'SBConnect',
        'manage_options',
        'sb-connect',
        'sbconnect_init',
        'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxOS4yLjEsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4KCjxzdmcKICAgeG1sbnM6ZGM9Imh0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvIgogICB4bWxuczpjYz0iaHR0cDovL2NyZWF0aXZlY29tbW9ucy5vcmcvbnMjIgogICB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiCiAgIHhtbG5zOnN2Zz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciCiAgIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIKICAgeG1sbnM6c29kaXBvZGk9Imh0dHA6Ly9zb2RpcG9kaS5zb3VyY2Vmb3JnZS5uZXQvRFREL3NvZGlwb2RpLTAuZHRkIgogICB4bWxuczppbmtzY2FwZT0iaHR0cDovL3d3dy5pbmtzY2FwZS5vcmcvbmFtZXNwYWNlcy9pbmtzY2FwZSIKICAgdmVyc2lvbj0iMS4xIgogICBpZD0ibGF5ZXIiCiAgIHg9IjBweCIKICAgeT0iMHB4IgogICB2aWV3Qm94PSIwIDAgMzQzLjYyNjM0IDI4Ni43NjIxNSIKICAgeG1sOnNwYWNlPSJwcmVzZXJ2ZSIKICAgc29kaXBvZGk6ZG9jbmFtZT0ic2Jjb25uZWN0LnN2ZyIKICAgaW5rc2NhcGU6dmVyc2lvbj0iMC45Mi40ICg1ZGE2ODljMzEzLCAyMDE5LTAxLTE0KSIKICAgd2lkdGg9IjM0My42MjYzNCIKICAgaGVpZ2h0PSIyODYuNzYyMTUiPjxtZXRhZGF0YQogICBpZD0ibWV0YWRhdGE4NTQiPjxyZGY6UkRGPjxjYzpXb3JrCiAgICAgICByZGY6YWJvdXQ9IiI+PGRjOmZvcm1hdD5pbWFnZS9zdmcreG1sPC9kYzpmb3JtYXQ+PGRjOnR5cGUKICAgICAgICAgcmRmOnJlc291cmNlPSJodHRwOi8vcHVybC5vcmcvZGMvZGNtaXR5cGUvU3RpbGxJbWFnZSIgLz48ZGM6dGl0bGU+PC9kYzp0aXRsZT48L2NjOldvcms+PC9yZGY6UkRGPjwvbWV0YWRhdGE+PGRlZnMKICAgaWQ9ImRlZnM4NTIiPgoJCgkKCQo8L2RlZnM+PHNvZGlwb2RpOm5hbWVkdmlldwogICBwYWdlY29sb3I9IiNmZmZmZmYiCiAgIGJvcmRlcmNvbG9yPSIjNjY2NjY2IgogICBib3JkZXJvcGFjaXR5PSIxIgogICBvYmplY3R0b2xlcmFuY2U9IjEwIgogICBncmlkdG9sZXJhbmNlPSIxMCIKICAgZ3VpZGV0b2xlcmFuY2U9IjEwIgogICBpbmtzY2FwZTpwYWdlb3BhY2l0eT0iMCIKICAgaW5rc2NhcGU6cGFnZXNoYWRvdz0iMiIKICAgaW5rc2NhcGU6d2luZG93LXdpZHRoPSIxOTIwIgogICBpbmtzY2FwZTp3aW5kb3ctaGVpZ2h0PSIxMDAxIgogICBpZD0ibmFtZWR2aWV3ODUwIgogICBzaG93Z3JpZD0iZmFsc2UiCiAgIGlua3NjYXBlOnpvb209IjEuMjM5MjYzOCIKICAgaW5rc2NhcGU6Y3g9IjE2Ni42NjA2NiIKICAgaW5rc2NhcGU6Y3k9Ijk5LjIyMTc5NiIKICAgaW5rc2NhcGU6d2luZG93LXg9Ii05IgogICBpbmtzY2FwZTp3aW5kb3cteT0iLTkiCiAgIGlua3NjYXBlOndpbmRvdy1tYXhpbWl6ZWQ9IjEiCiAgIGlua3NjYXBlOmN1cnJlbnQtbGF5ZXI9ImxheWVyIgogICBzaG93Ym9yZGVyPSJ0cnVlIiAvPgo8c3R5bGUKICAgdHlwZT0idGV4dC9jc3MiCiAgIGlkPSJzdHlsZTgxNSI+Cgkuc3Qwe2ZpbGw6I0ZGQzAwMDt9Cjwvc3R5bGU+CjxnCiAgIGlkPSJnODQ1IgogICB0cmFuc2Zvcm09InRyYW5zbGF0ZSgtODcuODAyMjM5LC03Mi4yOTEzNDEpIj4KCQk8cGF0aAogICBzdHlsZT0iZmlsbDojZmZjMDAwIgogICBpbmtzY2FwZTpjb25uZWN0b3ItY3VydmF0dXJlPSIwIgogICBpZD0icGF0aDg0MyIKICAgZD0ibSAyNDUuMywyMzQuMiAtODMuNCwxMTYuNSBjIC00LjMsNiAtMTEuNSw5LjEgLTE4LjcsOC4yIC04LjMsLTEgLTE5LjcsLTQuMSAtMzEuNCwtMTIuNiAtMTEuOCwtOC41IC0xOC4zLC0xOC4zIC0yMiwtMjUuOCAtMy4yLC02LjYgLTIuNSwtMTQuNCAxLjcsLTIwLjQgbCA4My40LC0xMTYuNSBjIDQsLTUuNSA5LjIsLTEwLjEgMTUuMiwtMTMuMyBsIDE2LjQsLTguNyBjIDMuMywtMS43IDYuMiwtNC4xIDguNywtNi44IDgsLTkgMjkuMywtMzIuNyA2Ny4zLC03MSBsIDAuNywtMyBjIDAuMiwtMC43IDAuNywtMS4zIDEuNCwtMS40IGwgMi4xLC0wLjUgYyAwLjksLTAuMiAxLjUsLTEuMSAxLjQsLTIgbCAtMC4yLC0yLjYgYyAtMC4xLC0xLjEgMC43LC0yIDEuOCwtMiAyLjUsLTAuMSA3LjQsMC43IDE0LjMsNS42IDYuOSw1IDkuMiw5LjQgOS45LDExLjggMC4zLDEgLTAuMywyLjEgLTEuNCwyLjQgbCAtMi41LDAuNiBjIC0wLjksMC4yIC0xLjUsMS4xIC0xLjUsMiBsIDAuMiwyLjIgYyAwLjEsMC43IC0wLjMsMS40IC0wLjksMS44IGwgLTIuNywxLjYgYyAtMjQsNDguMiAtMzkuNiw3Ni4xIC00NS42LDg2LjYgLTEuOSwzLjIgLTMuMSw2LjggLTMuNywxMC41IGwgLTIuOSwxOC40IGMgLTEsNi40IC0zLjYsMTIuOCAtNy42LDE4LjQgeiBNIDQyNy43LDMwMCAzNDQuMywxODMuNSBjIC00LC01LjUgLTkuMiwtMTAuMSAtMTUuMiwtMTMuMyBsIC0xNi40LC04LjcgYyAtMy4zLC0xLjcgLTYuMiwtNC4xIC04LjcsLTYuOCAtMiwtMi4yIC00LjgsLTUuNCAtOC40LC05LjQgLTAuNiwtMC42IC0xLjYsLTAuNSAtMiwwLjMgLTEyLjYsMjQuMyAtMjEuMiwzOS40IC0yNSw0Ni4yIC0xLjIsMi4yIC0yLjEsNC41IC0yLjUsNi45IC0wLjgsNS4yIC0wLjgsMTAuNiAwLDE1LjggbCAwLjEsMC45IGMgMS4xLDYuNyAzLjcsMTMuMSA3LjcsMTguNyBsIDgzLjQsMTE2LjUgYyA0LjIsNS45IDExLjMsOS4xIDE4LjUsOC4yIDguMywtMSAxOS44LC00LjEgMzEuNiwtMTIuNiAxMS44LC04LjUgMTguMywtMTguMyAyMiwtMjUuOCAzLjIsLTYuNiAyLjYsLTE0LjQgLTEuNywtMjAuNCBtIC0yMjEsLTIwOC4xIDIuNSwwLjYgYyAwLjksMC4yIDEuNSwxLjEgMS41LDIgbCAtMC4yLDIuMiBjIC0wLjEsMC43IDAuMywxLjQgMC45LDEuOCBsIDIuNywxLjYgYyA0LjQsOC45IDguNiwxNy4xIDEyLjQsMjQuNiAwLjQsMC43IDEuMywwLjkgMS45LDAuMyA2LjMsLTYuNyAxMy44LC0xNC43IDIyLjcsLTIzLjkgMC43LC0wLjggMC44LC0yIDAsLTIuNyAtNC41LC00LjYgLTkuMiwtOS40IC0xNC40LC0xNC42IGwgLTAuNywtMyBjIC0wLjEsLTAuNyAtMC43LC0xLjIgLTEuNCwtMS40IGwgLTIuMSwtMC41IGMgLTAuOSwtMC4yIC0xLjUsLTEuMSAtMS40LC0yIGwgMC4yLC0yLjYgYyAwLjEsLTEuMSAtMC43LC0yIC0xLjgsLTIgLTIuNSwtMC4xIC03LjQsMC42IC0xNC40LDUuNiAtNi45LDUgLTkuMiw5LjQgLTkuOSwxMS44IC0wLjIsMC45IDAuNSwyIDEuNSwyLjIiCiAgIGNsYXNzPSJzdDAiIC8+Cgk8L2c+Cjwvc3ZnPg==',
        0
    );
    add_submenu_page('sb-connect', 'SBConnect', 'Översikt', 'manage_options', 'sb-connect', 'sbconnect_init');
    add_submenu_page('sb-connect', 'Lägg till artikel', 'Lägg till artikel', 'manage_options', 'sb-connect-new-article', 'sbconnect_add_new_article');
    add_submenu_page('sb-connect', 'Lägg till butik', 'Lägg till butik', 'manage_options', 'sb-connect-new-site', 'sbconnect_add_new_site');
}

function sbconnect_init()
{
    ?>
   <h1>SBConnect</h1>
   <a href="http://localhost/wp-admin/admin.php?page=sb-connect-new-article" class="page-title-action">Lägg till</a>
   <?php
   
    $overview_table = new SBConnect_Overview_Table();
    $overview_table->prepare_items();
    $overview_table->views();
    $overview_table->display();
}

function sbconnect_add_new_article()
{
    ?>
   <h1>Lägg till ny artikel</h1>
   <?php
}

function sbconnect_add_new_site()
{
    ?>
   <h1>Lägg till ny butik</h1>
   <?php
}
