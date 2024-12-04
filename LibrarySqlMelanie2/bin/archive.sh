#!/bin/bash

if [ "$#" != "0" ]; then
  echo "Usage $0"
  exit 1
fi

MY_PATH="`dirname \"$0\"`"              # relative
MY_PATH="`( cd \"$MY_PATH/..\" && pwd )`"  # absolutized and normalized
VERSION="`./bin/read_version.php`" # read version from php
if [ -z "$MY_PATH" ] ; then
  # error; for some reason, the path is not accessible
  # to the script (e.g. permissions re-evaled after suid)
  exit 1  # fail
fi

mkdir "$MY_PATH"/archives/libm2_"$VERSION"
cp -r "$MY_PATH"/src "$MY_PATH"/archives/libm2_"$VERSION"/libm2
cd "$MY_PATH"/archives/libm2_"$VERSION"/
rm -rf libm2/config/default/
tar zcvf "$MY_PATH"/archives/libm2_"$VERSION".tar.gz libm2/
rm -rf "$MY_PATH"/archives/libm2_"$VERSION"
cd "$MY_PATH"
git tag -a "$VERSION" -m "Archive LibM2 Version $VERSION"

exit 0