<?php

require_once 'RightNowChat.php';
require_once 'config.php';
require_once 'twilio.php';

require_once 'Contact.php';
// ToDo -  Sanity check

$to = $_REQUEST["To"]; // Our Page ID
$from = $_REQUEST["From"]; 
$message = $_REQUEST["Body"];

$chat = new RightNowChat(array(
    'siteURL'       => SITEURL, 
    'siteInterface' => SITE_INTERFACE,
    'agentUser'     => AGENT_USER,
    'agentPass'     => AGENT_PASS
));

// Todo - Use Facebook Graph API to get user's name and email (currently profile id is available through Twilio API)

// Step 1 - Check the contact, and create the contact if does not exist
$contact = fetchContact( "Facebook", $from );

if( false === $contact ) {
    $contact = createContact( "Facebook", $from );
}

$cache = new Cache();
$chat_session = NULL;

if( $cache->isCached( $from ) ) {
    $chat_session = $cache->retrieve( $from );
} else {
    notify_user( $to, $from, "Please hold. Our next available agent will assist you shortly!");
    $chat_session = $chat->initializeChat( $contact );
    $cache->store( $from, $chat_session );
}

if( !$chat->postMessage( $chat_session, $message )) {
    $chat_session = $chat->initializeChat( $contact );
    $cache->store( $from, $chat_session );
    $chat->postMessage( $chat_session, $message );
}


function notify_user( $from, $user, $message ) {
    postMessage( $from, $user, $message );
}