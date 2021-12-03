#!/usr/bin/env bash
SCRIPT=`realpath $0`
SCRIPTPATH=`dirname $SCRIPT`
cd ${SCRIPTPATH}

if [ "$1" = "install" ]; then
	./runTests.sh -s composerInstall
	exit
fi

if [ "$1" = "install-min" ]; then
	./runTests.sh -s composerInstallMin
	exit
fi

if [ "$1" = "unit" ]; then
	./runTests.sh -s unit
	exit
fi

if [ "$1" = "unit-73" ]; then
	./runTests.sh -s unit -p 7.3
	exit
fi

if [ "$1" = "unit-cover" ]; then
	./runTests.sh -s unitCoverage -e "--coverage-html Tests/Coverage-Unit"
	exit
fi

if [ "$1" = "functional" ]; then
	./runTests.sh -s functional
	exit
fi

read -r -d '' HELP <<EOF
Simplified test runner for a TYPO3 extension.

  Commands:

    install         prepares the composer installation
    install-min     prepares the composer installation in the minimal version
    unit            executes the unit tests
    unit-73         executes the unit tests in PHP73
    unit-cover      executes the unit tests and dumps the coverage report
    functional      executes the functional tests

EOF

echo "${HELP}"
exit 0