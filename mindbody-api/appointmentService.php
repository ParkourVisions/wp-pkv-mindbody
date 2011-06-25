<?php
require_once("mbapi.php");

class MBAppointmentService extends MBAPIService
{
	function __construct($debug = false)
	{
		$serviceUrl = "https://" . GetApiHostname() . "/api/0_5/AppointmentService.asmx?wsdl";
	
		$this->debug = $debug;
		$option = array();
		if ($debug)
		{
			$option = array('trace'=>1);
		}
		$this->client = new soapclient($serviceUrl, $option);
	}
	
	/**
	 * Returns the raw result of the MINDBODY SOAP call.
	 * @param int $PageSize
	 * @param int $CurrentPage
	 * @param string $XMLDetail
	 * @param string $Fields
	 * @param SourceCredentials $credentials A source credentials object to use with this call
	 * @return object The raw result of the SOAP call
	 */
	public function GetBookableItems(array $sessionTypeIDs, array $locationIDs, array $staffIDs, $startDate, $endDate, SourceCredentials $credentials = null, $XMLDetail = XMLDetail::Full, $PageSize = NULL, $CurrentPage = NULL, $Fields = NULL) {
		$additions = array();
		$additions['SessionTypeIDs'] = $sessionTypeIDs;
		if (isset($locationIDs))
		{
			$additions['LocationIDs'] = $locationIDs;
		}
		if (isset($staffIDs))
		{
			$additions['StaffIDs'] = $staffIDs;
		}
		if (isset($startDate))
		{
			$additions['StartDate'] = $startDate;
		}
		if (isset($endDate))
		{
			$additions['EndDate'] = $endDate;
		}
		
		$params = $this->GetMindbodyParams($additions, $this->GetCredentials($credentials), $XMLDetail, $PageSize, $CurrentPage, $Fields);
		
		try {
			$result = $this->client->GetBookableItems($params);
		} 
		catch (SoapFault $fault)
		{
			debugResponse($this->client, $fault->getMessage());
			// <xmp> tag displays xml output in html
			echo '</xmp><br/><br/> Error Message : <br/>', $fault->getMessage(); 
		}
		
		if ($this->debug)
		{
			debugRequest($this->client);
			debugResponse($this->client, $result);
		}
		
		return $result;
	}
	
	/**
	 * Returns the raw result of the MINDBODY SOAP call.
	 * @param int $PageSize
	 * @param int $CurrentPage
	 * @param string $XMLDetail
	 * @param string $Fields
	 * @param SourceCredentials $credentials A source credentials object to use with this call
	 * @return object The raw result of the SOAP call
	 */
	public function AddOrUpdateAppointments(array $appointments, $updateAction = 'AddNew', $test = false, SourceCredentials $credentials = null, $XMLDetail = XMLDetail::Full, $PageSize = NULL, $CurrentPage = NULL, $Fields = NULL) {
		$additions = array();
		$additions['Appointments'] = $appointments;
		if (isset($updateAction))
		{
			$additions['UpdateAction'] = $updateAction;
		}
		if (isset($Test))
		{
			$additions['test'] = $test;
		}
		
		$params = $this->GetMindbodyParams($additions, $this->GetCredentials($credentials), $XMLDetail, $PageSize, $CurrentPage, $Fields);
		
		try {
			$result = $this->client->AddOrUpdateAppointments($params);
		} 
		catch (SoapFault $fault)
		{
			debugResponse($this->client, $fault->getMessage());
			// <xmp> tag displays xml output in html
			echo '</xmp><br/><br/> Error Message : <br/>', $fault->getMessage(); 
		}
		
		if ($this->debug)
		{
			debugRequest($this->client);
			debugResponse($this->client, $result);
		}
		
		return $result;
	}
	
}
