#!/bin/bash
echo "Stopping Apache"
apache2ctl stop
cd $( dirname $0 )/..
basedir=$(pwd)/
for git in ./ ext/php-openid/ library/MW/ library/EpicDb/
do
  cd $basedir$git
  echo -n "Checking $git... "
  git checkout-index -a -f --prefix=/var/www/com_epicadvice/$git
  echo 'checked!'
done
for svn in library/Shanty/ library/Zend/
do
  cd $basedir$svn
  echo -n "Exporting $svn... "
  svn export ./ /var/www/com_epicadvice/$svn --force
done
echo "Starting Apache"
apache2ctl start
