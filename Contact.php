<?php 

require_once 'config.php';

function createContact( $first, $last ){

    try{
            $contact = new RNCPHP\Contact();
            $contact->Name = new RNCPHP\PersonName();
            $contact->Name->First = $first;
            $contact->Name->Last = $last;
            //$contact->Login = "LOGIN_".date("Y-m-d-h-i-s");
            $contact->save();
            return $contact;
        }

    catch (Exception $err ){
        echo $err->getMessage();
    }

}

function fetchContact( $first, $last ){

    try{
            $contacts = RNCPHP\Contact::find( "Name.First = '$first' and Name.Last = '$last'" );
            if( 0 >= count($contacts))
                return false;
            
            return $contacts[0];
        }

    catch (Exception $err ){
        echo $err->getMessage();
        return false;
    }

}