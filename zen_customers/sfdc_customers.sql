DROP TABLE sfdc_config;
CREATE TABLE sfdc_config (
  export_version_id tinyint(3) NOT NULL auto_increment,
  last_customers_export datetime NOT NULL default '0001-01-01 00:00:00',
  last_products_export datetime NOT NULL default '0001-01-01 00:00:00',
  PRIMARY KEY  (export_version_id)
) ENGINE=MyISAM;

INSERT INTO sfdc_config VALUES ('1', '0001-01-01 00:00:00', '0001-01-01 00:00:00');

INSERT INTO admin_pages (page_key, language_key, main_page, page_params, menu_key, display_on_menu, sort_order) VALUES
       ('sfdcExportCustomers', 'BOX_SFDC_EXPORT_CUSTOMERS', 'FILENAME_SFDC_EXPORT_CUSTOMERS', '', 'tools', 'Y', 500);
