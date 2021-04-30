<?php
require_once 'app/init.php';

$results = [];
$main_result = [];

if(isset($_GET['q']) || isset($_GET['graded'])) {

  $q = $_GET['q'];
  $graded = $_GET['graded'];

  $query = $es->search([
    'index' => 'card_auctions',
    'body'  => [
      'query' => [
        'match' => [
          'sku' => $q
        ]
      ]
    ]
  ]);

  if($query['hits']['total'] >=1 ) {
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

  <div class="row vertical-center-row pt-4">
    <div class="col-lg-4 col-lg-offset-4">
      <div class="input-group">
        <div class="center-block">
          <h3>Input search query</h3>
        </div>
      </div>
    </div>
  </div>

  <form action="results.php" method="get" autocomplete="on">
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

  <div class="container py-4">
    <div class="row" style="text-align: center">
      <h2> Search Results: </h2>
    </div>
  </div>

  <?php
    if ( isset($results) ) {
  ?>
    <div class="row">
      <div class="container">
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th></th>
                <th>SKU</th>
                <th>Card Name</th>
                <th>Full Price</th>
                <th>Total Price</th>
                <th>Grade</th>
                <th>Ebay ID</th>
              </tr>
            </thead>
            <tbody>

              <?php
              foreach ( $results as $r ) {
              ?>
                <tr>
                  <td>
                    <img src="<?php echo $r['_source']['picture_url']; ?>" width="50" />
                  </td>
                  <td><a href="results.php?q=<?php echo $r['_source']['sku']; ?>&pop=true"><?php echo $r['_source']['sku']; ?></a></td>
                  <td><?php echo $r['_source']['card_title']; ?></td>
                  <td>$ <?php echo $r['_source']['full_price']; ?></td>
                  <td>$ <?php echo $r['_source']['total_price']; ?></td>
                  <td><?php echo $r['_source']['card_grade']; ?></td>
                  <td><?php echo $r['_source']['auction_id']; ?></td>
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

        <div class="row text-center">
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

  if ( isset($_GET['pop']) && $_GET['pop'] == 'true' ) {
    $q = $_GET['q'];

  ?>
    <!-- Modal -->
    <div id="auction_modal" class="modal fade" role="dialog">
      <div class="modal-dialog modal-lg">

        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><?php echo $main_result['sku']; ?></h4>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-4">
                <img src="<?php echo $main_result['picture_url']; ?>" class="w-100"></img>
              </div>
              <div class="col-md-8">
                <div class="row">
                  <div class="col-sm-12">
                    <h3><?php echo $main_result['card_title']; ?></h3>
                  </div>
                </div>
                <div class="row pt-4">
                  <div class="col-sm-3">
                    <div><strong>Full Price</strong></div>
                    <div>$ <?php echo $main_result['full_price']; ?></div>
                  </div>
                  <div class="col-sm-3">
                    <div><strong>Total Price</strong></div>
                    <div>$ <?php echo $main_result['total_price']; ?></div>
                  </div>
                  <div class="col-sm-6">
                    <div><strong>Sold Date</strong></div>
                    <div>$ <?php echo $main_result['auction_sold_date']; ?></div>
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
          }]
        };

        <?php
          foreach($results as $result){
        ?>
          chartData.labels.push("<?php echo $result['_source']['auction_sold_date'] ?>");
          chartData.datasets[0].data.push("<?php echo $result['_source']['total_price'] ?>");
        <?php } ?>

        var chartOptions = {
          legend: {
            display: true,
            position: 'top',
            labels: {
              boxWidth: 80,
              fontColor: 'black'
            }
          }
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
