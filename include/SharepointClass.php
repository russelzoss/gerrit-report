<?php

/*
 * SharePoint SOAP Client
 *
 * @author Ruslan Oprits
 */

use PhpEws\Ntlm\ExchangeSoapClient;

class SharePoint {
    private $soapClient;
    private $params;
    
    public function __construct(){
        //Creating the SOAP client and initializing the GetListItems method parameters
        $wsdl = get_include_path() .DIRECTORY_SEPARATOR. SHAREPOINT_WSDL;
        $this->soapClient = new ExchangeSoapClient($wsdl,
            array(
                'user' => SHAREPOINT_USER,
                'password' => SHAREPOINT_PASSWORD,
                'version' => 'Exchange2010_SP2',
                'location' => 'https://'.SHAREPOINT_SERVER.'/EWS/Exchange.asmx'
            )
        );

        $this->params = array('listName' => SHAREPOINT_LIST_NAME,'rowLimit' => SHAREPOINT_ROW_LIMIT);
    }
     
    function get_people() {
        //Calling the GetListItems Web Service
        $rawXMLresponse = null;

        try{
                $rawXMLresponse = $this->soapClient->GetListItems($this->params)->GetListItemsResult->any;
        }
    
        catch(SoapFault $fault){
                echo 'Fault code: '.$fault->faultcode;
                echo 'Fault string: '.$fault->faultstring;
        }

        //Loading the XML result into parsable DOM elements
        $dom = new DOMDocument();
        $dom->loadXML($rawXMLresponse);
        $results = $dom->getElementsByTagNameNS("#RowsetSchema", "*");

        //Fetching the elements values. Specify more attributes as necessary
        foreach($results as $result){
                $lastname = $result->getAttribute("ows_Title");
                $firstname = $result->getAttribute("ows_First_x0020_name");
                $list[] = $firstname .' '. $lastname;
        }
        //unset($soapClient);
        return $list;
    }
}

?>
