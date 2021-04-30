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
  <link href="https://fonts.googleapis.com/css?family=Pattaya|Slabo+27px" rel="stylesheet"> 
  <link rel="image_src" type="image/jpeg" href="http://search.ruanbekker.com/search/images/header-logo.png" />
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-social/4.10.1/bootstrap-social.css" rel="stylesheet" >
  <link rel="icon" type="image/png" href="images/favicon.png">
  <link href="//fonts.googleapis.com/css?family=Pattaya|Slabo+27px|Raleway:400,300,600" rel="stylesheet" type="text/css">
  <link href="css/bootstrap.min.css" rel="stylesheet">

  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="author" content="Ruan Bekker">
  <meta name="viewport" content="width=device-width, initial-scale=1">

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
    <li role="presentation" class="active"><a href="index.php">Home</a></li>
  </ul>

  <form action="results.php" method="get" autocomplete="on">
    <div class="row">
      <div class="col-lg-4 col-lg-offset-4">
        <div class="input-group">
          <input type="text" name="q" placeholder="Search ..." class="form-control" /> 
          <span class="input-group-btn">
            <button type="submit" class="btn btn-primary">Search</button>
          </span>
        </div>
      </div>
    </div>
  </form>

</body>
</html>
