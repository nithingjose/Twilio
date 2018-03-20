<?php 

/*
// Find our position in the file tree
if (!defined('DOCROOT')) {
$docroot = get_cfg_var('doc_root');
define('DOCROOT', $docroot);
}
 
 
// Set up and call the AgentAuthenticator
require_once (DOCROOT . '/include/services/AgentAuthenticator.phph');
 
// On failure, this includes the Access Denied page and then exits,
// preventing the rest of the page from running.
if( !isset($_REQUEST['session']))
{
    die( "Access denied!" );
}

$account = AgentAuthenticator::authenticateSessionID( $_REQUEST['session'] );

*/

require_once 'config.php';
require_once 'twilio.php';

$accid = $_REQUEST['accId'];

$account = RNCPHP\Account::fetch( $accid );


$to   = $_REQUEST['fbid'];
$message = "Hi, my name is " . $account->DisplayName . ". How may I help you?";

postMessage( $from, $to, $message );

