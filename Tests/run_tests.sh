#
# Copyright 2021 LABOR.digital
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
#
# Last modified: 2021.06.27 at 16:27
#

#!/usr/bin/env bash

IP="127.0.0.1";
export TYPO3_PATH_APP="${PWD}/Tests/Build/Web/";
export TYPO3_PATH_ROOT="${PWD}/Tests/Build/Web/";

export typo3DatabaseName="functional";

echo "";
echo "";
echo "### Running unit tests";

php \
    "${PWD}/Tests/Build/bin/phpunit" \
    --colors \
    -c "${PWD}/Tests/UnitTests.xml"
