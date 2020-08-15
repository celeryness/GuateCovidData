export GOOGLE_APPLICATION_CREDENTIALS=~/.google/VVTrial-2afe54fb2ed0.json
php ../bq-upload/bigquery/api/bigquery.php --project="vv-trial" import covid_guatemala.casos_integrados for_upload/casos_integrados.json  --overwrite=true
##php bigquery/api/bigquery.php --project="vv-trial" import viaventure_sales.sac_resource_type_current_year %~dp0SalesAndConversions\sac_rt_current_year.json  --overwrite=true
