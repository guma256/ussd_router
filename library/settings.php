<?php

$conf = parse_ini_file("conf/config.ini",true);
$routes = parse_ini_file("conf/shortcodes.ini",true);

define('SHORT_CODES', $routes);
define('MAINTENANCE', $conf['maintenance']['value']);
if(MAINTENANCE==1){
define('MAINTENANCE_MESSAGE', $conf['maintenance']['message']);
}

//Redis
define('ALLOWED_CHARS'  ,$conf['REDIS']['allowed_input']);
define('REDIS_HOST'  ,$conf['REDIS']['host']);
define('REDIS_PORT'  ,$conf['REDIS']['port']);
define('REDIS_PASSWORD'  ,$conf['REDIS']['password']);
define('SESSION_ID_EXP'  ,$conf['REDIS']['session_id_expiry']);
