<?php
require_once("mbApi.php");

class MBClientService extends MBAPIService 
{
	function __construct($debug = false)
	{
		$serviceUrl = "https://" . GetApiHostname() . "/api/0_5/ClientService.asmx?wsdl";
	
		$this->debug = $debug;
		$option = array('connection_timeout' => 3,
                    'cache_wsdl' => WSDL_CACHE_NONE,
                    'soap_version'=> SOAP_1_1,
                    'exceptions'=> true
                    );
		if ($debug)
		{
			$option = array('trace'=>1);
		}

    // Use a local copy of the WSDL
    $this->client = new SoapClient(dirname(__FILE__) . "/ClientService.wsdl", $option);
	}
	
	public function GetClients(array $clientIDs, $PageSize = null, $CurrentPage = null, $XMLDetail = XMLDetail::Full, $Fields = null, SourceCredentials $credentials = null)
	{
		$additions['ClientIDs'] = $clientIDs;
		$params = $this->GetMindbodyParams($additions, $this->GetCredentials($credentials), $XMLDetail, $PageSize, $CurrentPage, $Fields);
		
		try
		{
			$result = $this->client->GetClients($params);
		}
		catch (SoapFault $fault)
		{
			DebugResponse($result);
			echo '</xmp><br/><br/> Error Message : <br/>', $fault->getMessage(); 
		}
		
		if ($this->debug)
		{
			DebugRequest($this->client);
			DebugResponse($this->client, $result);
		}
		
		return $result;
	}
	
	public function AddArrival($client, $location, SourceCredentials $credentials = null, $XMLDetail = XMLDetail::Full, $PageSize = NULL, $CurrentPage = NULL, $Fields = NULL)
	{
		$additions['ClientID'] = $client;
		$additions['LocationID'] = $location;
		$params = $this->GetMindbodyParams($additions, $this->GetCredentials($credentials), $XMLDetail, $PageSize, $CurrentPage, $Fields);
		
		try
		{
			$result = $this->client->AddArrival($params);
		}
		catch (SoapFault $fault)
		{
			DebugResponse($result);
			echo '</xmp><br/><br/> Error Message : <br/>', $fault->getMessage(); 
		}
		
		if ($this->debug)
		{
			DebugRequest($this->client);
			DebugResponse($this->client, $result);
		}
		
		return $result;
	}
	
	public function GetClientServices($clientID, array $programIDs = array(), array $sessionTypeIDs = array(), array $locationIDs = array(), $visitCount = null, $startDate = null, $endDate = null, $ShowActiveOnly = true, SourceCredentials $credentials = null, $XMLDetail = XMLDetail::Full, $PageSize = NULL, $CurrentPage = NULL, $Fields = NULL)
	{
		$additions['ClientID'] = $clientID;
		if (isset($programIDs))
		{
			$additions['ProgramIDs'] = $programIDs;
		}
		if (isset($sessionTypeIDs))
		{
			$additions['SessionTypeIDs'] = $sessionTypeIDs;
		}
		if (isset($locationIDs))
		{
			$additions['LocationIDs'] = $locationIDs;
		}
		if (isset($visitCount))
		{
			$additions['VisitCount'] = $visitCount;
		}
		if (isset($startDate))
		{
			$additions['StartDate'] = $startDate;
		}
		if (isset($endDate))
		{
			$additions['EndDate'] = $endDate;
		}
		if (isset($ShowActiveOnly))
		{
			$additions['ShowActiveOnly'] = $ShowActiveOnly;
		}
		
		$params = $this->GetMindbodyParams($additions, $this->GetCredentials($credentials), $XMLDetail, $PageSize, $CurrentPage, $Fields);
		
		try
		{
			$result = $this->client->GetClientServices($params);
		}
		catch (SoapFault $fault)
		{
			DebugResponse($result);
			echo '</xmp><br/><br/> Error Message : <br/>', $fault->getMessage(); 
		}
		
		if ($this->debug)
		{
			DebugRequest($this->client);
			DebugResponse($this->client, $result);
		}
		
		return $result;
	}
	
	/**
	 * AddOrUpdateClientsRaw is identical to AddOrUpdateClients, but returns the raw result of the SOAP call,
	 * which may have additional information but will not be nicely formatted.
	 * @param $clients An array of Client objects
	 * @param $test If true, the call is made and validated but no changes are made.
	 * @param SourceCredentials $credentials A source credentials object to use with this call
	 * @param string $XMLDetail
	 * @param int $PageSize
	 * @param int $CurrentPage
	 * @param string $Fields
	 * @return bool Returns true if the arrival was successfully added.
	 */
	public function AddOrUpdateClients($clients, $test = false, SourceCredentials $credentials = null, $XMLDetail = XMLDetail::Full, $PageSize = NULL, $CurrentPage = NULL, $Fields = NULL)
	{	
		$additions['Test'] = $test;
		$additions['Clients'] = $clients;
		$params = $this->GetMindbodyParams($additions, $this->GetCredentials($credentials), $XMLDetail, $PageSize, $CurrentPage, $Fields);
		
		try {
			$result = $this->client->AddOrUpdateClients($params);
		} catch (SoapFault $fault) {
			// <xmp> tag displays xml output in html
			echo 'Request : <br/><xmp>',
			$this->client->__getLastRequest(),
			'</xmp><br/><br/> Error Message : <br/>',
			$fault->getMessage(); 
		}
		
		if ($this->debug) {
			echo 'Request : <br/><xmp>', $this->client->__getLastRequest(), '</xmp><br/><br/>';
			echo('<h2>AddOrUpdateClientsRaw Result</h2><pre>');
			print_r($result);
			echo("</pre>");
		}
		
		return $result;
	}
	
	public function ValidateLogin($username, $password, $PageSize = null, $CurrentPage = null, $XMLDetail = XMLDetail::Full, $Fields = null, SourceCredentials $credentials = null)
	{
		$additions['Username'] = $username;
		$additions['Password'] = $password;
		$params = $this->GetMindbodyParams($additions, $this->GetCredentials($credentials), $XMLDetail, $PageSize, $CurrentPage, $Fields);


    
		try
		{
			$result = $this->client->ValidateLogin($params);
		}
		catch (SoapFault $fault)
		{
			DebugResponse($this->client, $result);
			echo '</xmp><br/><br/> Error Message : <br/>', $fault->getMessage(); 
		}
		
		if ($this->debug)
		{
			DebugRequest($this->client);
			DebugResponse($this->client, $result);
		}
		
		return $result;
	}
}
