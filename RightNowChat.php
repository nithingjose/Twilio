<?php 

require_once 'cache.class.php';

class RightNowChat {
    private $config;

    public function __construct($options) {
        // TODO - Sanitation
        /*
        We are expecting the following as part of options array
        siteName,, siteInterface, agentUser, Password
        CacheAdapter


        */
        $this->config = $options;

        // Load Chat Configurations
        $this->loadChatConfig();
    }

    function getConfig( $item ) {
        if( array_key_exists( $iten, $this->config ) )
            return $this->config[ $item ];
        else
            return NULL;
    }

    
    /**
    * Generates chat token for a particular chat session
    */
    function loadChatConfig(){

        try{

            $dest = 'https://'. $this->getConfig( 'siteURL' ) .'/cgi-bin/'. $this->getConfig( 'siteInterface' ) .'.cfg/services/chat_soap';
            $envel = utf8_encode('<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v1="urn:messages.chat.ws.rightnow.com/v1_2">
                                    <soapenv:Header>
                                        <v1:ClientRequestHeader xmlns="urn:messages.chat.ws.rightnow.com/v1_2">
                                            <v1:AppID>Sample Custom Api Application</v1:AppID>
                                        </v1:ClientRequestHeader>
                                        <wsse:Security mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
                                            <wsse:UsernameToken>
                                                <wsse:Username>'. $this->getConfig( 'agentUser' ) .'</wsse:Username>
                                                <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText ">'. $this->getConfig( 'agentPassword' ) .'</wsse:Password>
                                            </wsse:UsernameToken>
                                        </wsse:Security>
                                    </soapenv:Header>
                                    <soapenv:Body>
                                        <v1:GetChatUrl xmlns="urn:messages.chat.ws.rightnow.com/v1_2">
                                            <v1:UrlType>ENDUSER</v1:UrlType>
                                            <v1:Version>1.0</v1:Version>
                                        </v1:GetChatUrl>
                                    </soapenv:Body>
                                </soapenv:Envelope>');

            $ch = curl_init();
            $headers = array(
                                'Content-Type: text/xml; charset="utf-8"',
                                'Content-Length: '.strlen($envel),
                                'Accept: text/xml',
                                'Cache-Control: no-cache',
                                'SOAPAction: "GetChatUrl"',
                                'Pragma: no-cache'
                                );

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, $dest);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $envel);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $result = curl_exec($ch);
            $parser = xml_parser_create();
            xml_parse_into_struct($parser, $result, $values, $tags);
            xml_parser_free($parser);

            $this->chatToken = $values[$tags["N0:CHATTOKEN"][0]]['value'];
            $this->chatUrl = $values[$tags["N0:CHATURL"][0]]['value'];
            $this->siteName = $values[$tags["N0:SITENAME"][0]]['value'];

        }

        catch (Exception $exception){
                print($exception->getMessage());
        }

    }

   
    public function initializeChat( $contact ){
        $this->contact = $contact;
        try{

            $dest = $this->chatUrl;
            $envel = utf8_encode('<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v1="urn:messages.common.chat.ws.rightnow.com/v1" xmlns:v11="urn:messages.enduser.chat.ws.rightnow.com/v1">
                                        <soapenv:Header>
                                            <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" mustUnderstand="1">
                                                    <wsse:UsernameToken>
                                                        <wsse:Username>'. $this->getConfig( 'agentUser' ) .'</wsse:Username>
                                                        <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText ">'. $this->getConfig( 'agentPassword' ) .'</wsse:Password>
                                                    </wsse:UsernameToken>
                                            </wsse:Security>
                                            <v1:ChatClientInfoHeader>
                                                <v1:AppID>Test End User</v1:AppID>
                                            </v1:ChatClientInfoHeader>
                                        </soapenv:Header>
                                        <soapenv:Body>
                                            <v11:RequestChat>
                                                <v11:TransactionRequestData>
                                                    <v11:ClientRequestTime>2008-09-28T21:49:45</v11:ClientRequestTime>
                                                    <v11:ClientTransactionID>11</v11:ClientTransactionID>
                                                    <v11:SiteName>'.$this->siteName.'</v11:SiteName>
                                                </v11:TransactionRequestData>
                                                <!--Zero or more repetitions:-->
                                                <v11:CustomerInformation>
                                                    <v1:EMailAddress></v1:EMailAddress>
                                                    <v1:FirstName>'. $contact->Name->First .'</v1:FirstName>
                                                    <v1:LastName>'. $contact->Name->Last .'</v1:LastName>
                                                    <v1:ContactId id="'. $contact->ID . '"/>
                                                    <v1:InterfaceID>
                                                        <v1:ID id="1"/>
                                                    </v1:InterfaceID>
                                                </v11:CustomerInformation>
                                                <!--Optional:-->
                                                <v11:ResumeType>RESUME</v11:ResumeType>
                                                <v11:ChatSessionToken>'.$this->chatToken.'</v11:ChatSessionToken>
                                                <!--Optional:-->
                                                <!--<v11:IncidentID id="999"/>-->
                                            </v11:RequestChat>
                                        </soapenv:Body>
                                </soapenv:Envelope>');

            $ch = curl_init();
            $headers = array(
                                'Content-Type: text/xml; charset="utf-8"',
                                'Content-Length: '.strlen($envel),
                                'Accept: text/xml',
                                'Cache-Control: no-cache',
                                'SOAPAction: "RequestChat"',
                                'Pragma: no-cache'
                                );

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, $dest);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $envel);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $result = curl_exec($ch);
            $parser = xml_parser_create();
            xml_parse_into_struct($parser, $result, $values, $tags);
            xml_parser_free($parser);

            $this->sessionID = $values[$tags["NS3:SESSIONID"][0]]['value'];
            $this->engagementID = $values[$tags["NS3:ENGAGEMENTID"][0]]['attributes']['ID'];

            return array( 
                "sessionID"     => $this->sessionID,
                "engagementID"  => $this->engagementID
            );
            
        }

        catch (Exception $exception){
                print($exception->getMessage());
        }

    }

    function postMessage( $chat_session, $message, $retry = false ){

        try{

            $dest = $this->chatUrl;
            $envel = utf8_encode('<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v1="urn:messages.common.chat.ws.rightnow.com/v1" xmlns:v11="urn:messages.enduser.chat.ws.rightnow.com/v1">
                                    <soapenv:Header>
                                        <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" mustUnderstand="1">
                                                <wsse:UsernameToken>
                                                    <wsse:Username>'. $this->getConfig( 'agentUser' ) .'</wsse:Username>
                                                    <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText ">'. $this->getConfig( 'agentPass' ) .'</wsse:Password>
                                                </wsse:UsernameToken>
                                        </wsse:Security>
                                        <v1:ChatClientInfoHeader>
                                            <v1:AppID>test</v1:AppID>
                                            <!--Optional:-->
                                            <v1:SessionID>'.$chat_session["sessionID"].'</v1:SessionID>
                                        </v1:ChatClientInfoHeader>
                                    </soapenv:Header>
                                    <soapenv:Body>
                                        <v11:PostChatMessage>
                                            <v11:TransactionRequestData>
                                                <v11:ClientRequestTime>2016-09-28T21:49:45</v11:ClientRequestTime>
                                                <v11:ClientTransactionID>11</v11:ClientTransactionID>
                                                <v11:SiteName>'.$this->siteName.'</v11:SiteName>
                                            </v11:TransactionRequestData>
                                            <v11:Body>'.$message.'</v11:Body>
                                            <!--Optional:-->
                                            <v11:OffTheRecord>false</v11:OffTheRecord>
                                        </v11:PostChatMessage>
                                    </soapenv:Body>
                                </soapenv:Envelope>');

            $ch = curl_init();
            $headers = array(
                                'Content-Type: text/xml; charset="utf-8"',
                                'Content-Length: '.strlen($envel),
                                'Accept: text/xml',
                                'Cache-Control: no-cache',
                                'SOAPAction: "PostChatMessage"',
                                'Pragma: no-cache'
                                );

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, $dest);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $envel);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $result = curl_exec($ch);
            $parser = xml_parser_create();
            xml_parse_into_struct($parser, $result, $values, $tags);
            xml_parser_free($parser);


            if ($values[$tags["FAULTSTRING"][0]]!=null){
                return false;
            }

            return true;
        }
        catch (Exception $exception){
                print($exception->getMessage());
        }
    }

}