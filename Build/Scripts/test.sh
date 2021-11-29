#!/usr/bin/env bash
SCRIPT=`realpath $0`
SCRIPTPATH=`dirname $SCRIPT`
cd ${SCRIPTPATH}

if [ "$1" = "install" ]; then
	./runTests.sh -s composerInstall
	exit
fi

if [ "$1" = "unit" ]; then
	./runTests.sh -s unit
	exit
fi

if [ "$1" = "unit-cover" ]; then
	./runTests.sh -s unitCoverage -e "--coverage-html Tests/Coverage-Unit"
	exit
fi

read -r -d '' HELP <<EOF
Simplified test runner for a TYPO3 extension.

  Commands:

    install         prepares the composer installation
    unit            executes the unit tests
    unit-cover      executes the unit tests and dumps the coverage report

EOF

echo "${HELP}"
exit 0