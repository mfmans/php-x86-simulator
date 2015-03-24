<?php

require_once 'plato/inc.base.php';

plato_require('test.dll');


echo test_with_variable_argument(3, "abc", "xxxxx", "defg");
