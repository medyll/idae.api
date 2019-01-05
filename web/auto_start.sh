#!/bin/bash


forever start -a --uid tac_tac_shop_mydde /var/www/tac-tac.shop.mydde.fr/web/bin/node/app_https/app_https_main.js;
forever list;
cat   /root/.forever/tac_tac_shop_mydde.log;
