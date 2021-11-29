#!/usr/bin/env bash
SCRIPT=`realpath $0`
SCRIPTPATH=`dirname $SCRIPT`
cd ${SCRIPTPATH}

chmod +x ./runTests.sh
chmod +x ./test.sh

rm -rf ../../../../../test-t3ba.sh
ln -s ${SCRIPTPATH}/test.sh ../../../../../test-t3ba.sh