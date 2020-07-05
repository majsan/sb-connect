<?php

require '/home/maria/bin-data/wordpress/wp-blog-header.php';status_header(200);nocache_headers();

wp_head();
printf("\n\n\n\n");


function post($url, $data)
{
    $args = array(
      'headers' => array(
        'Content-Type' => 'application/json; charset=utf-8',
        'Accept' => 'application/json',
      ),
      'method' => 'POST',
      'body' => json_encode($data),
      'data_format' => 'body'
    );
    $response = wp_remote_post($url, $args);
    return json_decode(wp_remote_retrieve_body($response));
}

function get_product_id_from_article_ids($article_ids)
{
    $url = 'https://www.systembolaget.se/api/product/GetProductsForAnalytics';
    $data = array('ProductNumbers' => $article_ids);
    $body = post($url, $data);

    $result = array();
    foreach ($body->Products as $product) {
        $result[] = $product->ProductId;
    }
    return $result;
}

function get_site_info($site_id)
{
    $url = "https://www.systembolaget.se/api/site/getsites?siteIds=" . $site_id;
    $response = wp_remote_get($url);
    $tmp = wp_remote_retrieve_body($response);
    return json_decode($tmp)[0];
}



function get_site_infos($site_ids)
{
    $res = (object) [];
    foreach ($site_ids as $site_id) {
        $site = get_site_info($site_id);
        $res->$site_id = $site;
    }
    return $res;
}

function get_balance($site_ids, $product_id)
{
    $url = 'https://www.systembolaget.se/api/product/getstockbalance';
    $data = array(
       'productId' => $product_id,
       'siteIds' => $site_ids
    );
    return post($url, $data);
}

function get_product($site_ids, $product_id)
{
    $balances = get_balance($site_ids, $product_id);
    $site_infos = get_site_infos($site_ids);
    foreach ($balances as $site) {
        $site_id = $site->SiteId;
        $site->siteInfo = $site_infos->$site_id;
    }
    return $balances;
}


function get_str($my_sites, $article_id)
{
    $x = get_product_id_from_article_ids([$article_id]);
    $res = "";
    foreach (get_product($my_sites, $x[0]) as $site) {
        if (isset($site->siteInfo->Alias)) {
            $res .= $site->siteInfo->Alias;
        } else {
            $res .= "Lagret";
        }
        $res .= "\n";
        $res .= "Antal:\t\t" . $site->StockTextLong;
        $res .= "\n";
        if (!empty($site->Section)) {
            $res .= $site->SectionLabel . ":\t" . $site->Section;
            $res .= "\n";
            $res .= $site->ShelfLabel . ":\t" . $site->Shelf;
            $res .= "\n";
            $res .= "==========";
            $res .= "\n";
        }
    }
    return $res;
}
    
function test()
{
    $article_id = '3435603';
    $sites = ["1410", "1414", "1899"];
    echo "Belgian Blonde\n";
    echo get_str($sites, '3435603');
    echo "\n\n";
    echo "Heinkeken\n";
    echo get_str($sites, '153612');
}
    
test();
