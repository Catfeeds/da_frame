<?php
$str = "echo 'a';";
eval($str);
var_dump(function_exists("eval"));