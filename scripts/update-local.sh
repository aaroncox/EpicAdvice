#!/bin/bash
cd $( dirname $0 )/..
basedir=$(pwd)/library
git submodule init
git submodule update
for i in Shanty Zend
do 
	case $i in
		Shanty )
			repo=http://svn.momentumworkshop.com/svn/lib_mw/trunk/library/Shanty
			;;
		Zend )
			repo=http://framework.zend.com/svn/framework/standard/tags/release-1.11.4/library/Zend
			;;
	esac
	echo "Updating $i from $repo"
	if [ -d $basedir/$i ]
	then
		url=$(svn info $basedir/$i | grep URL: | cut -f2 -d" ")
		if [ "$url" = "$repo" ]
		then 
			svn up $basedir/$i
		else
			echo "Removing old version of $i"
			rm -rf $basedir/$i
			svn co $repo $basedir/$i
		fi
	else
		svn co $repo $basedir/$i 
	fi
done