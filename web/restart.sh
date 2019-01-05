#!/usr/bin/env bash
echo "starting";
cd /data/wwwdocker/tac-tac.myddde.fr/docker/
docker-compose up --build --force-recreate -d
echo "started";