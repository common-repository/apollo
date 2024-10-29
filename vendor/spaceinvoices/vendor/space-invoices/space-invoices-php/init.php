<?php
include(dirname(__FILE__) . '/lib/ApiResource.php');

// Space invoices singleton
include(dirname(__FILE__) . '/lib/Spaceinvoices.php');

// API operations
require(dirname(__FILE__) . '/lib/ApiOperations/Find.php');
require(dirname(__FILE__) . '/lib/ApiOperations/Create.php');
require(dirname(__FILE__) . '/lib/ApiOperations/Delete.php');
require(dirname(__FILE__) . '/lib/ApiOperations/Edit.php');
require(dirname(__FILE__) . '/lib/ApiOperations/GetById.php');

// Space invoices API Resources
include(dirname(__FILE__) . '/lib/Accounts.php');
include(dirname(__FILE__) . '/lib/Clients.php');
include(dirname(__FILE__) . '/lib/Companies.php');
include(dirname(__FILE__) . '/lib/Currencies.php');
include(dirname(__FILE__) . '/lib/Documents.php');
include(dirname(__FILE__) . '/lib/Items.php');
include(dirname(__FILE__) . '/lib/Organizations.php');
include(dirname(__FILE__) . '/lib/Payments.php');
include(dirname(__FILE__) . '/lib/Recurrences.php');
include(dirname(__FILE__) . '/lib/Taxes.php');
?>