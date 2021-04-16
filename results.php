<?php
require_once 'app/init.php';

if(isset($_GET['q'])) {

  $q = $_GET['q'];

  $query = $es->search([
    'index' => 'card_auctions',
    'body'  => [
      'query' => [
        'match' => [
          'auction_text' => $q
        ]
      ]
    ]
  ]);

  if($query['hits']['total'] >=1 ) {
    $results = $query['hits']['hits'];
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
  <link rel="icon" type="image/png" href="images/favicon.png">

  <script src="js/bootstrap.min.js"></script>
  <script src="js/jquery.min.js"></script>

  <style>
    h1 {
      font-family: 'Pattaya', sans-serif;
      font-size: 59px;
      position: relative;
      right: -10px;
    }

    h3 {
      font-family: 'Pattaya', sans-serif;
      font-size: 20px;
      position: relative;
      right: -90px;
    }

    h4 {
      font-family: 'Slabo', sans-serif;
      font-size: 30px;
    }
  </style>

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

  <form action="results.php" method="get" autocomplete="on">
    <div class="row">
      <div class="col-lg-4 col-lg-offset-4">
        <div class="input-group">
          <input type="text" name="q" placeholder="Search..." class="form-control" /> 
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
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Record Phase</th>
                <th>Card Name</th>
                <th>Full Price</th>
                <th>Grade</th>
                <th>Ebay ID</th>
                <th>Auction Text</th>
              </tr>
            </thead>
            <tbody>

              <?php
              foreach ( $results as $r ) {
              ?>
                <tr>
                  <td><?php echo $r['_source']['record_phase']; ?></td>
                  <td><?php echo $r['_source']['card_title']; ?></td>
                  <td>$ <?php echo $r['_source']['full_price']; ?></td>
                  <td><?php echo $r['_source']['card_grade']; ?></td>
                  <td><?php echo $r['_source']['auction_id']; ?></td>
                  <td><?php echo $r['_source']['auction_text']; ?></td>
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

</body>
</html>
