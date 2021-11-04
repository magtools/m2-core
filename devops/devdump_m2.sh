#!/bin/bash

#-------------------------------------------------------------
# Martin Gonzalez magtec @ gmail com
# Magento large databases dump for development
# version 2.0.1, updated March 21, 2018.
#
#
# USAGE:
#
# This command will export the db without logs and reports data
# $ bash devdump.sh dbname
#
# This command will also ignore the data from some sales and quote tables (quotes and orders)
# $ bash devdump.sh dbname compact
#
# NOTE: use bash, sh will ignore the #!/bin/bash line in some systems and use dash instead,
# use of dash would throw an error related with '('
#
# -------
# If after running this script your db dump still too big you can run the following query
# against your database to check if you have some custom table that you could safely ignore,
# for example a custom log table:
# SELECT
#   table_name AS `Table`,
#   round(((data_length + index_length) / 1024 / 1024), 2) `Size in MB`
# FROM information_schema.TABLES WHERE table_schema = 'magento_db_name'
# ORDER BY (data_length + index_length) DESC LIMIT 30;
#-------------------------------------------------------------


HOST="localhost"
USER="root"
PASSWORD="password"
PORT="3306"

DATABASE=$1
DATE=$(date +"%y%m%d")
DB_FILE="${DATABASE}_${DATE}.sql"


# Enter the db name
if [ -z "$1" ] ; then
  echo
  echo "ERROR: database name parameter missing."
  exit
fi

# This script will exclude log and report tables
# Add 'compact' option to exclude sales tables too
COMPACT=false
if [ $2 = "compact" ] ; then
  COMPACT=true
fi

# Log and report tables
EXCLUDE_TABLES=(
captcha_log
catalog_category_product_index
catalog_category_product_index_replica
catalog_product_bundle_price_index
catalog_product_bundle_stock_index
catalog_product_index_eav_decimal_replica
catalog_product_index_eav_replica
catalog_product_index_price
catalog_product_index_price_bundle_opt_tmp
catalog_product_index_price_bundle_sel_tmp
catalog_product_index_price_bundle_tmp
catalog_product_index_price_cfg_opt_agr_tmp
catalog_product_index_price_cfg_opt_tmp
catalog_product_index_price_downlod_tmp
catalog_product_index_price_final_tmp
catalog_product_index_price_idx
catalog_product_index_price_opt_agr_tmp
catalog_product_index_price_opt_tmp
catalog_product_index_price_replica
catalog_product_index_price_tmp
cataloginventory_stock_status_replica
catalogrule_product_price_replica
catalogrule_product_replica
catalogsearch_fulltext_scope1
catalogsearch_fulltext_scope2
cron_schedule
customer_log
customer_visitor
magento_catalogpermissions_index
magento_catalogpermissions_index_product_replica
magento_catalogpermissions_index_replica
magento_logging_event
magento_logging_event_changes
magento_reminder_rule_log
magento_targetrule_index
oauth_token_request_log
release_notification_viewer_log
report_compared_product_index
report_event
report_viewed_product_aggregated_daily
report_viewed_product_aggregated_monthly
report_viewed_product_index
search_query
sendfriend_log
)

# Sales tables
EXCLUDE_EXTRA=(
quote
quote_address
quote_address_item
quote_item
quote_item_option
quote_payment
quote_preview
quote_shipping_rate
sales_bestsellers_aggregated_daily
sales_bestsellers_aggregated_monthly
sales_bestsellers_aggregated_yearly
sales_creditmemo
sales_creditmemo_comment
sales_creditmemo_grid
sales_creditmemo_item
sales_invoice
sales_invoice_comment
sales_invoice_grid
sales_invoice_item
sales_invoiced_aggregated
sales_invoiced_aggregated_order
sales_order
sales_order_address
sales_order_aggregated_created
sales_order_aggregated_updated
sales_order_grid
sales_order_item
sales_order_payment
sales_order_tax
sales_order_tax_item
sales_payment_transaction
sales_refunded_aggregated
sales_refunded_aggregated_order
sales_shipment
sales_shipment_comment
sales_shipment_grid
sales_shipment_item
sales_shipment_track
sales_shipping_aggregated
sales_shipping_aggregated_order
)

IGNORED_TABLES_STRING=''
for TABLE in "${EXCLUDE_TABLES[@]}"
do :
   IGNORED_TABLES_STRING+=" --ignore-table=${DATABASE}.${TABLE}"
done
echo "Ignore log, index and report tables"

if [ "${COMPACT}" = true ] ; then
  for TABLE in "${EXCLUDE_EXTRA[@]}"
  do :
    IGNORED_TABLES_STRING+=" --ignore-table=${DATABASE}.${TABLE}"
  done
  echo "Ignore extra sales and quote tables"
fi

echo "Dump structure"
mysqldump --host=${HOST} --port=${PORT} --user=${USER} --password=${PASSWORD} --single-transaction --no-data ${DATABASE} > ${DB_FILE}

echo "Dump content"
mysqldump --host=${HOST} --port=${PORT} --user=${USER} --password=${PASSWORD} ${DATABASE} ${IGNORED_TABLES_STRING} >> ${DB_FILE}

echo "Remove definer"
sed -i 's/DEFINER=[^*]*\*/\*/g' ${DB_FILE}

tar -czvf "${DB_FILE}.gz" ${DB_FILE}

#end
