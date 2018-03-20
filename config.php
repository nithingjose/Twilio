<?php

define( 'SITEURL', 'demo-twilio.rightnowdemo.com' );
define( 'SITE_INTERFACE', 'demo_twilio' );
define( 'AGENT_USER', 'ramesh' );
define( 'AGENT_PASS', 'Welcome@1' );

define( 'TWILIO_SID', 'AC53e1bef416030118237d6676e42ed271');
define( 'TWILIO_TOKEN', 'c193dbd56d9fe1a4bbe167a9dd5a6433');




/********************* Including dependencies ***************************/
require_once('include/init.phph');
require_once(get_cfg_var("doc_root") . "/include/ConnectPHP/Connect_init.phph" );
use RightNow\Connect\v1_3 as RNCPHP;
initConnectAPI( AGENT_USER, AGENT_PASS );
if (!extension_loaded('curl'))
load_curl();
/********************* End Including dependencies ***************************/
