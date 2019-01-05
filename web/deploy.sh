#!/bin/bash


echo "Sauvegarde";
rsync -uav --exclude='tmp/*'  /var/www/tac-tac-city.fr/web/ /var/www/tac-tac-city_last.mydde.fr/web/

echo "Mise en production";
rsync -uav --exclude='images_base' --exclude='tmp/*' /var/www/tac-tac.shop.mydde.fr/web/ /var/www/tac-tac-city.fr/web/

echo "suppresion cache cible"
rm -R /var/www/tac-tac-city.fr/web/tmp/tmp_tpl/*

echo "relance node"
forever restartall

echo "OK TERMINE"