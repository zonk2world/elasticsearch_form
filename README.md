##Example result record

```
{
   "specs_condition":"",
   "picture_url":"https://cardsite-auction-images.s3-us-west-2.amazonaws.com/5045eae56f9f119f43e931e3be2d1d40.jpg",
   "thumb_url":"https://cardsite-auction-images.s3-us-west-2.amazonaws.com/5045eae56f9f119f43e931e3be2d1d40.jpg",
   "card_grade":"9",
   "auction_total_bids":0,
   "auction_shipping_cost":"0.0",
   "auction_id":"110268337945",
   "specs_team":"See Description",
   "specs_card_attribs":"",
   "auction_hits":0,
   "date_stamp":"04/19/2021",
   "specs_graded":"Graded",
   "record_phase":"r3-initial",
   "specs_manufacturer":"Topps",
   "total_price":16.9,
   "set_sport":"basketball",
   "picture_md5":"5045eae56f9f119f43e931e3be2d1d40",
   "specs_product":"Single",
   "id":"None",
   "auction_url":"https://www.ebay.com/itm/1999-00-Topps-Gold-Label-Steve-Francis-87-PSA-9-Rookie/110268337945?hash=item19ac814f19:g:eEIAAOSwpEdbV4QB&orig_cvip=true",
   "specs_year":"1999",
   "auction_sold_date":"04/19/2021",
   "psa_key":"None",
   "auction_text":"1999-00 Topps Gold Label Steve Francis #87 PSA 9 Rookie",
   "auction_description":"Nice card! Identification # is 04323481.",
   "specs_player":"Steve Francis",
   "auction_seller":"konksplatt",
   "specs_league":"",
   "reviewed_status":"None",
   "auction_source":"ebay",
   "thumb_md5":"5045eae56f9f119f43e931e3be2d1d40",
   "specs_era":"",
   "full_price":16.9,
   "specs_year_season":"",
   "card_title":"None",
   "grade_source":"PSA",
   "specs_original_reprint":"Original"
}
```

##Example elastic search query

```
{
  "query": {
    "bool": {
      "must": [
        {
          "term": {
            "sku.keyword": $sku
          }
        },
        {
        "regexp": {
          "card_grade": ".+"
        }
      }
      ]
    }
  },
  "size": 0,
  "aggs": {
    "price_avg" : { "avg" : { "field" : "total_price" } },
    "price_count" : { "value_count": { "field" : "total_price" } },
    "price_max" : { "max": { "field" : "total_price" } },
    "price_min" : { "min": { "field" : "total_price" } },
    "price_sum" : { "sum" : { "field" : "total_price" } },
    "price_hist" : { "histogram": { "field" : "total_price", "interval": 10, "min_doc_count": 2 } },
    "grade_distro" : { "terms": { "field" : "card_grade.keyword"} }
  }
}
```
