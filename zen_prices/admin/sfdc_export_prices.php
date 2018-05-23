<?php
/*
Exports price data from Zen Cart into a format which Salesforce can consume.

Price Book Entries 
https://help.salesforce.com/articleView?id=products_fields.htm&type=5

*/
 require_once('includes/application_top.php');
 if (!defined('IS_ADMIN_FLAG')) {
   die('Illegal Access');
 }

 // set to your Standard price book ID
 define('PRICE_BOOK_ID', '01sf40000050dt2AAA');

 $products_query = "SELECT * FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd WHERE p.products_id = pd.products_id AND pd.language_id = " . (int)$_SESSION['languages_id']; 
 $products = $db->Execute($products_query); 
 header("Content-type: text/csv");
 header("Content-Disposition: attachment; filename=prices.csv");
 header("Pragma: no-cache");
 header("Expires: 0");
 $fp = fopen('php://output', 'w');

  $count = 0; 
  $row = array(); 
  // header
  $row[] = "Price Book ID";
  $row[] = "Price Book Entry ID";
  $row[] = "Product2Id";  
  $row[] = "List Price";
  $row[] = "IsActive";
  fputcsv($fp, $row); 
  while(!$products->EOF) { 
    $row = array(); 
    $row[] = PRICE_BOOK_ID; 
    $row[] = $products->fields['products_model'];
    $row[] = $products->fields['products_model'];
    $row[] = $products->fields['products_price'];
    $row[] = "Y";

    fputcsv($fp, $row); 
    $count++;
    $products->MoveNext(); 
  } 

