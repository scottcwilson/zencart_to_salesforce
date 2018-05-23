<?php
/*
Exports product data from Zen Cart into a format which Salesforce can consume.

Product Data:
https://help.salesforce.com/articleView?id=products_fields.htm&type=5

*/
 require_once('includes/application_top.php');
 if (!defined('IS_ADMIN_FLAG')) {
   die('Illegal Access');
 }

 // include product family? 
 define('INCLUDE_FAMILY', '1'); 


 $last_export = $db->Execute("SELECT * FROM " . TABLE_SFDC_CONFIG); 
 $last_products_export = $last_export->fields['last_products_export']; 
 $curr_export = $db->Execute("SELECT now() as newtime"); 
 $newtime = $curr_export->fields['newtime']; 

  $products_query = "SELECT * FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd WHERE p.products_id = pd.products_id AND pd.language_id = " . (int)$_SESSION['languages_id'] . " AND (p.products_date_added > '" . $last_products_export . "' OR p.products_last_modified > '" . $last_products_export . "')"; 
 $products = $db->Execute($products_query); 
 if ($products->EOF) {
    $messageStack->add_session(NO_NEW_PRODUCTS, 'caution'); 
    zen_redirect(zen_href_link(FILENAME_DEFAULT));
 }
 header("Content-type: text/csv");
 header("Content-Disposition: attachment; filename=products.csv");
 header("Pragma: no-cache");
 header("Expires: 0");
$fp = fopen('php://output', 'w');
  $count = 0; 
  $row = array(); 
  // header
  $row[] = "Store Product ID";
  $row[] = "Product Name";
  $row[] = "Product Code";
  $row[] = "Active";
  if (INCLUDE_FAMILY == '1') {
     $row[] = "Product Family";
  }
  $row[] = "Marketing URL";
  $row[] = "Product Description"; 
  fputcsv($fp, $row); 
  while(!$products->EOF) { 
    $row = array(); 
    $row[] = $products->fields['products_id']; 
    $row[] = $products->fields['products_name'];
    $row[] = $products->fields['products_model'];
    if ($products->fields['products_status'] == 1) {
      $row[]= "1"; // ACTIVE 
    } else {
      $row[]= "0"; // NOT ACTIVE
    }
    if (INCLUDE_FAMILY == '1') {
      $family = get_family($products->fields['products_id'], $products->fields['master_categories_id']);
      $row[]= $family; 
    }
    $row[] = $products->fields['products_url'];

    $desc = html_entity_decode(strip_tags($products->fields['products_description']));
    $row[] = htmlspecialchars_decode($desc,ENT_QUOTES); 
    fputcsv($fp, $row); 
    $count++;
    $products->MoveNext(); 
  } 
  $last_export = $db->Execute("UPDATE " . TABLE_SFDC_CONFIG . " SET last_products_export = '" . $newtime  . "'" ); 

function get_family($prid, $mcat) { 
  // Warning - this will differ by store
  // This gets top level category and returns name 
  $catlist = array(); 
  zen_get_parent_categories($catlist, $mcat); 
  if (isset($catlist[0])) {
     $cat_id = $catlist[sizeof($catlist)-1];
     return zen_get_category_name($cat_id, (int)$_SESSION['languages_id']); 
  }
  return "Other"; 
}
