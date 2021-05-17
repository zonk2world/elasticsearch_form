<?php
require_once 'app/init.php';

$results = [];
$main_result = [];

if ( isset($_GET['q']) ) {

  $q = $_GET['q'];

  $query = $es->search([
    'index' => 'skulib',
    'body'  => [
      'query' => [
        'match' => [
          'sku' => $q
        ]
      ],
      'from' => 0,
      'size' => 30
    ]
  ]);

  if($query['hits']['total']['value'] >=1 ) {
    $results = $query['hits']['hits'];
    $main_result = $results[0]['_source'];
  }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>

  <meta charset="utf-8">
  <title>Search | Document Search</title>
  <meta name="description" content="search-results">
  <meta name="author" content="Ruan Bekker">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="//fonts.googleapis.com/css?family=Pattaya|Slabo+27px|Raleway:400,300,600" rel="stylesheet" type="text/css">
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="css/starter-template.css" rel="stylesheet">

  <link rel="icon" type="image/png" href="images/favicon.png">

  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.2.0/chart.min.js"></script>

</head>

<body>

  <ul class="nav nav-tabs">
    <li role="presentation"><a href="index.php">Home</a></li>
  </ul>

  <form action="results.php" method="get" autocomplete="on" class="pt-4">
    <div class="row">
      <div class="col-md-4 col-md-offset-4">
        <div class="input-group">
          <input type="text" name="q" placeholder="Search ..." class="form-control" />
          <span class="input-group-btn">
            <button type="submit" class="btn btn-primary">Search</button>
            <a class="btn btn-danger" href="index.php">Back</a>
          </span>
        </div>
      </div>
    </div>
  </form>

  <?php
    if ( isset($results) ) {
  ?>
    <div class="row pt-4">
      <div class="container">
        <div class="table-responsive">
          <table class="table">
            <tbody>

              <?php
              foreach ( $results as $r ) {
              ?>
                <tr>
                  <td><a href="results.php?q=<?php echo $r['_source']['sku']; ?>&pop=true"><?php echo $r['_source']['sku']; ?></a></td>
                </tr>
              <?php
              }
              ?>

            </tbody>
          </table>
        </div>
      </div>
    </div>

      <?php
    } else {
      ?>

        <div class="row text-center pt-4">
          <div class="container">
            <div class="panel panel-success p-4">
              There are no search results.
            </div>
          </div>
        </div>

      <?php
    }
  ?>

  <?php

  $auction_result = [];
  $price_results = [];

  if ( isset($_GET['pop']) && $_GET['pop'] == 'true' ) {
    $q = $_GET['q'];

    $price_query = $es->search([
      'index' => 'card_auctions',
      'body'  => [
        'query' => [
          'bool' => [
            'must' => [
              [
                'term' => [
                  'sku.keyword' => 'Major League Bowman 2018 Ronald Acuna Jr. Atlanta Braves Rookie'
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


  ?>
    <!-- Modal -->
    <div id="auction_modal" class="modal fade" role="dialog">
      <div class="modal-dialog modal-lg">

        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><?php echo $auction_result['sku']; ?></h4>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-4">
                <img src="<?php echo $auction_result['picture_url']; ?>" class="w-100"></img>
              </div>
              <div class="col-md-8">
                <div class="row">
                  <div class="col-sm-3">
                    <div><strong>Player</strong></div>
                    <div><?php echo $auction_result['specs_player']; ?></div>
                  </div>
                  <div class="col-sm-3">
                    <div><strong>Team</strong></div>
                    <div><?php echo $auction_result['specs_team']; ?></div>
                  </div>
                  <div class="col-sm-3">
                    <div><strong>Attributes</strong></div>
                    <div><?php echo $auction_result['specs_card_attribs']; ?></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <canvas id="chart_area" width="600" height="400"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script type="text/javascript">
      $(document).ready( function() {
        $('#auction_modal').modal('show');

        var chartCanvas = document.getElementById("chart_area");

        var chartData = {
          labels: [],
          datasets: [{
            label: "Price History",
            data: [],
            backgroundColor: ['#ff6384'],
            borderColor: ['#ff6384']
          }],
        };

        <?php

          function cmp($a, $b) {
            return strtotime($a['key_as_string']) > strtotime($b['key_as_string']);
          }
          usort($price_results, "cmp");
          $grade_buckets_values = [];
          $buckets_index = 0;
          $date_array = [];
          foreach($price_results as $result) {
            $grade_buckets = $result['grades']['buckets'];

            foreach ($grade_buckets as $grade_bucket) {
              if(isset($grade_bucket['key']) && $grade_bucket['key'] != '' && $grade_bucket['key'] != 'None'){
                if(!isset($grade_buckets_values[$grade_bucket['key']]))
                  $grade_buckets_values[$grade_bucket['key']] = [];
                $grade_buckets_values[$grade_bucket['key']][$result['key_as_string']] = $grade_bucket['price_avg']['value'];
              }
            }
            $date_array[] = $result['key_as_string'];

        ?>
          chartData.labels.push("<?php echo date('Y-m-d', strtotime($result['key_as_string'])) ?>");
          chartData.datasets[0].data.push("<?php echo $result['price_avg']['value'] ?>");
        <?php } ?>

        <?php
          
          $index = 1;
          foreach ($grade_buckets_values as $key => $grade_buckets_value) {
              $print_values = [];
              $total_value = 0;
              $total_count = 0;
              foreach($date_array as $date){
                if(!isset($grade_buckets_value[$date]))
                  $print_values[] = null;
                else{
                  $print_values[] = $grade_buckets_value[$date];
                  $total_value += $grade_buckets_value[$date];
                  $total_count ++;
                }
              }
              $average_value = $total_value / $total_count;
              $average_values = [];
              for($i = 0; $i < count($print_values); $i ++){
                if($i == 0 || $i == count($print_values) - 1)
                  $average_values[] = $average_value;
                else
                  $average_values[] = null;
              }
          ?>
            chartData.datasets[<?php echo $index ?>] = {};
            chartData.datasets[<?php echo $index ?>].label = "Grade - <?php echo $key ?>";
            chartData.datasets[<?php echo $index ?>].data = JSON.parse('<?php echo json_encode($print_values) ?>');
            var color = Math.floor(Math.random()*16777215).toString(16);
            chartData.datasets[<?php echo $index ?>].backgroundColor = '#' + color;
            chartData.datasets[<?php echo $index ?>].borderColor = '#' + color;

            chartData.datasets[<?php echo $index+1 ?>] = {};
            chartData.datasets[<?php echo $index+1 ?>].label = "Average Grade - <?php echo $key ?>";
            chartData.datasets[<?php echo $index+1 ?>].data = JSON.parse('<?php echo json_encode($average_values) ?>');
            chartData.datasets[<?php echo $index+1 ?>].backgroundColor = '#' + color;
            chartData.datasets[<?php echo $index+1 ?>].borderColor = '#' + color;
          <?php
          $index += 2;
          } ?>

        var chartOptions = {
          legend: {
            display: true,
            position: 'top',
            labels: {
              boxWidth: 80,
              fontColor: 'black'
            }
          },
          spanGaps: true
        };

        var lineChart = new Chart(chartCanvas, {
          type: 'line',
          data: chartData,
          options: chartOptions
        });
      });
    </script>

  <?php
  }
  ?>

</body>
</html>
