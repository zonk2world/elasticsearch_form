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
                  <td><a class="modal_buttons" href="server.php?sku=<?php echo $r['_source']['sku']; ?>&pop=true"><?php echo $r['_source']['sku']; ?></a></td>
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

  
    <!-- Modal -->
    <div id="auction_modal" class="modal fade" role="dialog">
      <div class="modal-dialog modal-lg">

        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"></h4>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-4">
                <img src="" class="w-100 card_image"></img>
              </div>
              <div class="col-md-8">
                <div class="row">
                  <div class="col-sm-3">
                    <div><strong>Player</strong></div>
                    <div class="specs_player"></div>
                  </div>
                  <div class="col-sm-3">
                    <div><strong>Team</strong></div>
                    <div class="specs_team"></div>
                  </div>
                  <div class="col-sm-3">
                    <div><strong>Attributes</strong></div>
                    <div class="specs_card_attribs"></div>
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

        function dateformat(date) {
          return date.split('T')[0];
        }

        $('.modal_buttons').click(function(e){

          e.preventDefault();
          link = $(this).attr('href');
          $.ajax({
            url: link,
            type: 'GET',
            success: function(data) {
              console.log(data);

              var auction_result = JSON.parse(data)['auction_result'];
              var price_results = JSON.parse(data)['price_results'];


              $('.modal-title').text(auction_result['sku']);
              $('.specs_player').text(auction_result['specs_player']);
              $('.specs_team').text(auction_result['specs_team']);
              $('.specs_card_attribs').text(auction_result['specs_card_attribs']);
              $('.card_image').attr('src', auction_result['picture_url']);


              var colors = ['4d2ae7', 'b525cf', '3e8977', 'ffff00', '31708f', '8a6d3b', '1490fc', '45f7d0', '7fa17f', 'd90012', 'f2a800'];

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

              price_results.sort(function(a,b){
                return Date.parse(a['key_as_string']) > Date.parse(b['key_as_string']);
              });
              var grade_buckets_values = {};
              var buckets_index = 0;
              var date_array = [];
              $.each(price_results, function( index, result ) {
                var grade_buckets = result['grades']['buckets'];

                $.each(grade_buckets, function( index1, grade_bucket ){
                  if(grade_bucket['key'] && grade_bucket['key'] != 'None'){
                    if(!grade_buckets_values[grade_bucket['key']])
                      grade_buckets_values[grade_bucket['key']] = {};
                    grade_buckets_values[grade_bucket['key']][dateformat(result['key_as_string'])] = grade_bucket['price_avg']['value'];
                  }
                });
                date_array.push(dateformat(result['key_as_string']));
                chartData.labels.push(dateformat(result['key_as_string']));
                chartData.datasets[0].data.push(result['price_avg']['value']);

              });

              index = 1;

              $.each(grade_buckets_values, function(key, grade_buckets_value){
                var print_values = [];
                var total_value = 0;
                var total_count = 0;

                $.each(date_array, function( id, date ){
                  if(!grade_buckets_value[date])
                    print_values.push(null);
                  else{
                    print_values.push(grade_buckets_value[date]);
                    total_value += grade_buckets_value[date];
                    total_count ++;
                  }
                });
                var average_value = total_value / total_count;
                var average_values = [];
                for(i = 0; i < print_values.length; i ++){
                  if(i == 0 || i == print_values.length - 1)
                    average_values.push(average_value);
                  else
                    average_values.push(null);
                }

                chartData.datasets[index] = {};
                chartData.datasets[index].label = "Grade - " + key;
                chartData.datasets[index].data = print_values;

                var color = colors[index] || Math.floor(Math.random()*16777215).toString(16);
                chartData.datasets[index].backgroundColor = '#' + color;
                chartData.datasets[index].borderColor = '#' + color;

                chartData.datasets[index + 1] = {};
                chartData.datasets[index + 1].label = "Average Grade - " + key;
                chartData.datasets[index + 1].data = average_values;
                chartData.datasets[index + 1].backgroundColor = '#' + color;
                chartData.datasets[index + 1].borderColor = '#' + color;
                chartData.datasets[index + 1].borderWidth = 1;

                index += 2;
              });

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

              $('#auction_modal').modal('show');
            }
          })

          
        });

        
      });
    </script>

</body>
</html>
