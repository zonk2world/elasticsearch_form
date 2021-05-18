<?php
  require_once 'app/init.php';

  $auction_result = [];
  $price_results = [];

  if ( isset($_GET['pop']) && $_GET['pop'] == 'true' ) {
    $sku = $_GET['sku'];

    $price_query = $es->search([
      'index' => 'card_auctions',
      'body'  => [
        'query' => [
          'bool' => [
            'must' => [
              [
                'term' => [
                  'sku.keyword' => $sku
                ]
              ],
              [
                'regexp' => [ 'sku' => '.+' ]
              ],
              [
                'exists' => [ 'field' => 'sold_date' ]
              ]
            ]
          ]
        ],
        'size' => 1,
        'aggs' => [
          'sales_over_time' => [
            'date_histogram' => [
              'field' => 'sold_date',
              'calendar_interval' => 'week',
              'min_doc_count' => 2
            ],
            'aggs' => [
              'price_avg' => [ 'avg' => [ 'field' => 'total_price' ] ],
              'grades' => [
                'terms' => [ 'field' => 'card_grade.keyword' ],
                'aggs' => [
                  'price_avg' => [ 'avg' => [ 'field' => 'total_price' ] ]
                ]
              ]
            ]
          ]
        ]
      ]
    ]);

    if($price_query['hits']['total']['value'] >=1 ) {
      $auction_result = $price_query['hits']['hits'][0]['_source'];
    }

    $price_results = $price_query['aggregations']['sales_over_time']['buckets'];

    $results = ["auction_result"=>$auction_result, "price_results"=>$price_results];

    echo json_encode($results);
  }

?>
