<?php
/**
* Plugin Name: SB Connect
* Plugin URI: https://github.com/majsan/sb-connect
* Description: Show saldo in Systembolaget on your site
* Version: 1.0
* Author: Majsan
* Author URI:
**/

//if (!defined('SBCONNECT_PLUGIN_URL')) {
    // TODO fix this
    //define('SBCONNECT_PLUGIN_URL', plugin_dir_url(__FILE__));
    define('SBCONNECT_PLUGIN_URL', 'wp-content/plugins/sb-connect/');
//}

function get_articles($table)
{
    global $wpdb;

    $sql = "SELECT * FROM {$wpdb->prefix}{$table}";

    if (! empty($_REQUEST['orderby'])) {
        $sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
        $sql .= ! empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
    }

    $result = $wpdb->get_results($sql, 'ARRAY_A');

    return $result;
}

global $sbconnect_db_version;
$sbconnect_db_version = '1.0';

function sbconnect_install()
{
    global $wpdb;
    global $sbconnect_db_version;

    $charset_collate = $wpdb->get_charset_collate();

    $articles_table_name = $wpdb->prefix . "sbconnect_articles";
    $sites_table_name = $wpdb->prefix . "sbconnect_sites";

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
register_activation_hook(__FILE__, 'sbconnect_install');
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

function display_rows($rows, $col1, $col2)
{
    ?>
  <table>
    <thead>
        <tr>
            <th><?php echo "{$col1}"; ?></th>
            <th><?php echo "{$col2}"; ?></th>
            <th>Ta bort</th>
        </tr>
    </thead>
    <tbody>
       <?php foreach ($rows as $row) {
        echo "<tr>";
        echo "<td>{$row[$col1]}</td>";
        echo "<td>{$row[$col2]}</td>";
        echo "<td><img src='/" . constant("SBCONNECT_PLUGIN_URL") . "admin/images/icons8-trash-can.svg' alt='Delete icon'/></td>";
        echo "</tr>";
    } ?>
    </tbody>
  </table>
    <?php
}

function show_table($table, $col1, $col2)
{
    $rows = get_articles($table);
    display_rows($rows, $col1, $col2);
}

function sbconnect_init()
{
    ?>
   <h1>SBConnect</h1>
   <h2>Artiklar</h2>
   <p>Pluginet kan visa lagersaldo för följande artiklar</p>
   <div style="margin-bottom: 15px;"><a href="http://localhost/wp-admin/admin.php?page=sb-connect-new-article" class="page-title-action">Lägg till</a></div>
   <?php show_table("sbconnect_articles", "article_name", "article_id"); ?>
    <h2>Butiker</h2>
    <p>Pluginet kommer för varje artikel att visa lagersaldo på följande butiker.</p>
    <div style="margin-bottom: 15px;"><a href="http://localhost/wp-admin/admin.php?page=sb-connect-new-site" class="page-title-action">Lägg till</a></div>
    <?php show_table("sbconnect_sites", "site_name", "site_id"); ?>
    <?php
}

function sbconnect_add_new_article()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'sbconnect_articles',
            array(
              'article_name' => htmlspecialchars($_POST["article_name"]),
              'article_id' => htmlspecialchars($_POST["article_id"])
            )
        );
        echo 'Added ' . htmlspecialchars($_POST["article_name"]) . '!';
    // TODO redirect instead of echo
    } else {
        ?>
       <h1>Lägg till ny artikel</h1>
       
       <form method="post">
         <label style="display: block;">
           Artikelnamn:
           <input type="text" name="article_name" placeholder="Artikelnamn" />
         </label>
         <label style="display: block;">
           Artikelnummer:
           <input type="text" name="article_id" placeholder="Artikelnummer" />
         </label>
         <input type="submit" value="Lägg till" />
       </form>
       
       <?php
    }
}

function sbconnect_add_new_site()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'sbconnect_sites',
            array(
              'site_name' => htmlspecialchars($_POST["site_name"]),
              'site_id' => htmlspecialchars($_POST["site_id"])
            )
        );
        echo 'Added ' . htmlspecialchars($_POST["site_name"]) . '!';
    // TODO redirect instead of echo
    } else {
        ?>
       <h1>Lägg till ny butik</h1>
       
       <form method="post">
         <label style="display: block;">
           Butiksnamn:
           <input type="text" name="site_name" placeholder="Butiksnamn" />
         </label>
         <label style="display: block;">
           Butikens ID:
           <input type="text" name="site_id" placeholder="Butikens ID" />
         </label>
         <input type="submit" value="Lägg till" />
       </form>
       
       <?php
    }
}
