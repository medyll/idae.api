#!/bin/bash

echo "suppresion cache cible"
rm -R /var/www/tac-tac.shop.mydde.fr/web/tmp/tmp_tpl/*
echo "vidage cache node"
forever cleanlogs
echo "vidage cache node OK"
echo "relance node"
forever restartall
echo "relance node OK"

echo "OK TERMINE"