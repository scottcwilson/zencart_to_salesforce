Zen Cart to Salesforce
======================

  
If you'd like to use Salesforce with your Zen Cart based store, these integrations can help. I have created three modules:

*   [Export Customers](https://www.thatsoftwareguy.com/zencart_to_salesforce_customers.html)
*   [Export Products](https://www.thatsoftwareguy.com/zencart_to_salesforce_products.html)
*   [Export Prices](https://www.thatsoftwareguy.com/zencart_to_salesforce_prices.html)

If you're using Salesforce Essentials, you only need to import customers. (You can't create quotes, so you don't need to worry about products or prices; you have just the CRM portion of Salesforce.)  
  

Customers Import
----------------

Salesforce Export Customers builds a CSV of your customers from Zen Cart for import into Salesforce. The import is done using the Data Import Wizard, which creates and updates [Contact](https://help.salesforce.com/articleView?id=contacts_fields.htm&type=5) and [Account](https://help.salesforce.com/articleView?id=account_fields.htm&type=5) objects.  
  
Here is the mapping I used. The address record selected had its address\_book\_id field value match the customers\_default\_address\_id field from the customers table. (Table aliases: c = customers; ab = address\_book.)  
  
|Salesforce|Zen Cart|
|--- |--- |
|Legacy ID (custom field)|c.customers_id|
|First Name|c.customers_firstname|
|Last Name|c.customers_lastname|
|Email|c.email_address|
|Phone|c.telephone|
|Account Name|c.email_address|
|Account Type|Customer|
|Company (custom field)|ab.entry_company|
|Shipping City|ab.entry_city|
|Shipping Country|zen_get_country_name(ab.entry_country_id)|
|Shipping State/Province|ab.entry_state|
|Shipping Street|ab.entry_street_address|
|Shipping Zip/Postal Code|ab.entry_postcode|
|Billing City|ab.entry_city|
|Billing Country|zen_get_country_name(ab.entry_country_id)|
|Billing State/Province|ab.entry_state|
|Billing Street|ab.entry_street_address|
|Billing Zip/Postal Code|ab.entry_postcode|
|Mailing City|ab.entry_city|
|Mailing Country|zen_get_country_name(ab.entry_country_id)|
|Mailing State/Province|ab.entry_state|
|Mailing Street|ab.entry_street_address|
|Mailing Zip/Postal Code|ab.entry_postcode|


The choice to use the Zen Cart email address instead of the company name as the Salesforce Account name was made because email address is a required field which is validated for uniqueness. Doing it this way, you can easily move from a customer you have created a simple record for in Salesforce (left), perhaps based on a Web to lead form, or manual data entry, to the real customer record after they have created an account on your store (right).  
  

You can see screenshots of how all this looks on my website on the [Zen Cart to Salesforce](https://www.thatsoftwareguy.com/zencart_to_salesforce_customers.html) page.
  
Export is incremental, so once you have done an initial export, only new or changed records are exported. No CSV is produced if there are no new or changed records.  

Criteria for inclusion in a subsequent export are as follows:

*   New customer accounts created after the last export was done.
*   Customer changed account data in My Account->Change Account Information.
*   Customer changed their primary address in My Account->Change Entries in my Address Book.
*   Admin changed a customer record using Admin->Customers->Customers.

<h3>Installation</h3>

Prior to doing the import, you will need to create the custom fields shown in the table above.  In Salesforce, use these steps: 
- Click Gear, then choose Setup
- Object Manager-Contact-Fields and Relationships->New->
the value is a Number whose name is Legacy ID. Check box that says it's an
external ID.
- Same thing for Company.  Length is 64.
- You may also optionally add Company to the Account object.

Now go to your Zen Cart admin, and in Tools->Install SQL Patches, run the .sql file in zen_customers.  Then upload the files in the admin folder to your admin directory. 

<h3>Running the Import</h3>

- From your Zen Cart admin, click Tools->Salesforce Export Customers.
- From Salesforce, go to Setup, then to Data->Data Import Wizard.
- Choose Accounts and Contacts, then Add New and Update Existing.
- Match contact by Email, Account by Name & Site, then check Update existing Account information.


Products Import
---------------
Products Import is more complicated because you can't use the Salesforce Data Import Wizard.  Instead, you'll want to use [dataloader.io](dataloader.io), which creates and updates [Product](https://help.salesforce.com/articleView?id=products_fields.htm&type=5) objects. You can [read about importing using dataloader.io](https://dataloader.io/importing-data-salesforce) if you're new to it.  
  
Here is the mapping I used. (Table aliases: p = products; pd = products_description.)

|Salesforce|Zen Cart|
|--- |--- |
|Store Product ID (A custom field defined as an External ID)|p.products_id|
|Product Name|pd.products_name|
|Product Code|p.products_model|
|Active|p.products_status|
|Product Family|User Defined|
|Product Description|pd.products_description|
|Marketing URL|pd.products_url|

<h3>Installation</h3>

Prior to doing the import, you will need to create the custom fields shown in the table above.  In Salesforce, use these steps: 
- Click Gear, then choose Setup
- Object Manager-Product-Fields and Relationships->New->
the value is a Number whose name is Store Product ID. Check box that says it's an
external ID.


Now go to your Zen Cart admin, and in Tools->Install SQL Patches, run the .sql file in zen_products.  Then upload the files in the admin folder to your admin directory. 

<h3>Running the Import</h3>

- From your Zen Cart admin, click Tools->Salesforce Export Products.
- From dataloader.io, click New Task, then Import, and select the Product Object,
then click Next.
- Do an <i>upsert</i>.io so new records will be inserted but
existing records will be updated, so there's no duplication.
- Since you don't have the Salesforce ID for your product records,
you want to match on the Store Product ID.
- You can see images of the dataloader.io screens and settings on the 
[Zen Cart Products to Salesforce](https://www.thatsoftwareguy.com/zencart_to_salesforce_products.html) page.

Prices Import
---------------
Prices Import is even more complicated.  We will again use use [dataloader.io](dataloader.io), this time to create and update [Price Book Entry](https://help.salesforce.com/articleView?id=products_fields.htm&type=5) objects. 

However, but the steps are different for creating vs. updating. 

Here's the mapping I used:
  

|Salesforce|Zen Cart|
|--- |--- |
|Price Book ID|18 character Salesforce ID of your Standard price book|
|Price Book Entry ID|p.products_model on insert|
|Product2Id|p.products_model on update|
|List Price|p.products_price|
|Active|Y|

(Note that since this is the Standard Price Book, the Use Standard Price field is omitted and left at the default value of false.)

There are two things to know about the Price Book Entries:

*   We can't automatically map all of our fields. Instead, we have to use the trick that was mentioned on the products page of using the Product Code as the identifier.

*   Price Book Entries cannot be upserted. This is a restriction of Salesforce. So this means you have to Import entries for new products, which do not yet have standard prices, and Update entries for existing products when it's time to update. This makes the process of setting prices a bit more confusing than creating customers and products.

  
The way most people create a standard price is to press the "Add Standard Price" button on the Related screen for their new product. 
  
But you wouldn't want to do this for hundreds of products. So use my procedure to Insert Standard Prices (for product that don't have them) and Update Standard Prices (for products that do).

<h3>Installation</h3>

Go to your Zen Cart admin, and in Tools->Install SQL Patches, run the .sql file in zen_prices.  Then upload the files in the admin folder to your admin directory. 

<h3>Running the Import</h3>

- From your Zen Cart admin, click Tools->Salesforce Export Prices.

- From dataloader.io, click New Task, then Import, and select the Price Book Entry Object,
then click Next.

- To set standard prices for new products which do not yet have prices, select Insert, and look up the Product2Id field from the model number. 

- To update standard prices for products which already have prices, select Update, and look up the Price Book Entry ID field from the model number.

- You can see images of the dataloader.io screens and settings on the [Zen Cart Prices to Salesforce](https://www.thatsoftwareguy.com/zencart_to_salesforce_prices.html) page.

Re-running an Import
--------------------
If you need to re-run an import for some reason (say you are changing code to add data to the csv file), you will need to reset the last export record.  Just update the table `sfdc_config` in phpMyAdmin, and make the appropriate change. 

Accounts with Multiple Payers
----------------------------- 
If some of your customers have multiple email addresses they use to order,
you may wish to consolidate these using a single account for the customer.
In this case, the mapping of email address to account won't work, and 
you'll need something more sophisticated.  A solution to this problem is
provided by my [Zen Cart 360 Sales Reporting](https://github.com/scottcwilson/zencart_360_sales_reporting) modification.

