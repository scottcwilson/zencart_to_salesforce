<?php
/*
Exports customer data from Zen Cart into a format which Salesforce can consume.

Account Data:
https://help.salesforce.com/articleView?id=account_fields.htm&type=5

Contact Data: 
https://help.salesforce.com/articleView?id=contacts_fields.htm&type=5

*/
require_once('includes/application_top.php');
 if (!defined('IS_ADMIN_FLAG')) {
   die('Illegal Access');
 }
 $last_export = $db->Execute("SELECT * FROM " . TABLE_SFDC_CONFIG); 
 $last_customers_export = $last_export->fields['last_customers_export']; 
 $curr_export = $db->Execute("SELECT now() as newtime"); 
 $newtime = $curr_export->fields['newtime']; 

  $customers_query = "SELECT * FROM " . TABLE_CUSTOMERS . " c, " . TABLE_ADDRESS_BOOK . " a, " . TABLE_CUSTOMERS_INFO . " ci WHERE a.customers_id = c.customers_id AND a.address_book_id = c.customers_default_address_id AND ci.customers_info_id = c.customers_id AND (ci.customers_info_date_account_created > '" . $last_customers_export . "' OR ci.customers_info_date_account_last_modified > '" . $last_customers_export . "')"; 
 $customers = $db->Execute($customers_query); 
 if ($customers->EOF) {
    $messageStack->add_session(NO_NEW_CUSTOMERS, 'caution'); 
    zen_redirect(zen_href_link(FILENAME_DEFAULT));
 }
 header("Content-type: text/csv");
 header("Content-Disposition: attachment; filename=customers.csv");
 header("Pragma: no-cache");
 header("Expires: 0");
$fp = fopen('php://output', 'w');
  $count = 0; 
  $row = array(); 
  // header
  $row[] = "Legacy ID";
  $row[] = "First Name";
  $row[] = "Last Name";
  $row[] = "Email";
  $row[] = "Phone";
  $row[] = "Account Name";
  $row[] = "Account Type";
  $row[] = "Company";
  $row[] = "Shipping City"; 
  $row[] = "Shipping Country"; 
  $row[] = "Shipping State/Province"; 
  $row[] = "Shipping Street"; 
  $row[] = "Shipping Zip/Postal Code"; 
  $row[] = "Billing City"; 
  $row[] = "Billing Country"; 
  $row[] = "Billing State/Province"; 
  $row[] = "Billing Street"; 
  $row[] = "Billing Zip/Postal Code"; 
  $row[] = "Mailing City"; 
  $row[] = "Mailing Country"; 
  $row[] = "Mailing State/Province"; 
  $row[] = "Mailing Street"; 
  $row[] = "Mailing Zip/Postal Code"; 
  fputcsv($fp, $row); 
  while(!$customers->EOF) { 
    $row = array(); 
    $row[] = $customers->fields['customers_id'];
    $row[] = $customers->fields['customers_firstname'];
    $row[] = $customers->fields['customers_lastname'];
    $row[] = $customers->fields['customers_email_address'];
    $row[] = $customers->fields['customers_telephone'];
    // Use Email as account name for now 
    $row[] = $customers->fields['customers_email_address'];
    $row[] = "Customer"; 
    $row[] = $customers->fields['entry_company']; 

    // Shipping (Account) 
    $row[] = $customers->fields['entry_city']; 
    $country = zen_get_country_name($customers->fields['entry_country_id']); 
    $row[] = $country;
    if ($customers->fields['entry_zone_id'] != 0) { 
       $state = zen_get_zone_code($customers->fields['entry_country_id'], $customers->fields['entry_zone_id'], $customers->fields['entry_state']);
    } else { 
       $state = $customers->fields['entry_state']; 
    }
    $row[] = $state; 
    $row[] = $customers->fields['entry_street_address']; 
    $row[] = $customers->fields['entry_postcode']; 

    // Billing (Account) 
    $row[] = $customers->fields['entry_city']; 
    $row[] = $country;
    $row[] = $state; 
    $row[] = $customers->fields['entry_street_address']; 
    $row[] = $customers->fields['entry_postcode']; 

    // Mailing (Contact) 
    $row[] = $customers->fields['entry_city']; 
    $row[] = $country;
    $row[] = $state; 
    $row[] = $customers->fields['entry_street_address']; 
    $row[] = $customers->fields['entry_postcode']; 

    fputcsv($fp, $row); 
    $count++;
    $customers->MoveNext(); 
  } 
  $last_export = $db->Execute("UPDATE " . TABLE_SFDC_CONFIG . " SET last_customers_export = '" . $newtime  . "'" ); 
?>
