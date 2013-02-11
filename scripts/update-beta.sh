#!/bin/sh

branch="layout"

ssh greymass.com " cd /var/www/com_epicadvice_beta ; git pull ; scripts/update-local.sh "
