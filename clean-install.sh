# ! /bin/bash

CURR_DIR=$(cd `dirname $0`; pwd)

rm  -f  ${CURR_DIR}/wwwroot/Application/Common/Conf/config.php
rm  -f  ${CURR_DIR}/wwwroot/Application/User/Conf/config.php
rm  -f  ${CURR_DIR}/wwwroot/Data/install.lock
