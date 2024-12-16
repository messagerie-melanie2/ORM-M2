#!/bin/bash

MY_PATH="`dirname \"$0\"`"              # relative
MY_PATH="`( cd \"$MY_PATH/..\" && pwd )`"  # absolutized and normalized

if [ -z "$MY_PATH" ] ; then
  # error; for some reason, the path is not accessible
  # to the script (e.g. permissions re-evaled after suid)
  exit 1  # fail
fi

rm -rf "$MY_PATH"/docs/*
./bin/phpdoc -d "$MY_PATH"/src -t "$MY_PATH"/docs

cd "$MY_PATH"

exit 0