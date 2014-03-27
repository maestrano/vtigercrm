<?php

/**
 * Mno Entity Interface
 */
class MnoSoaBaseEntity
{                   
    const STATUS_ERROR = 1;
    const STATUS_NEW_ID = 2;
    const STATUS_EXISTING_ID = 3;
    const STATUS_DELETED_ID = 4;
    
    protected $_local_entity;
    protected $_local_entity_name;
    
    protected $_mno_entity_name;
    
    protected $_create_rest_entity_name;
    protected $_create_http_operation;
    protected $_update_rest_entity_name;
    protected $_update_http_operation;
    protected $_receive_rest_entity_name;
    protected $_receive_http_operation;
    protected $_delete_rest_entity_name;
    protected $_delete_http_operation;  
    
    protected $_db;
    protected $_log;
    
    protected $_enable_delete_notifications=false;
    
    protected $_mno_soa_db_interface;
    
    /**
    * Constructor to initialize transformation resources
    *
    * @return null
    */
    public function __construct($db, $log)
    {
	$this->_db = $db;
        $this->_log = $log;
        $this->_mno_soa_db_interface = new MnoSoaDB($db, $log);
    }
    
    /**
    * Build a Maestrano entity message
    * 
    * @return MaestranoEntity the maestrano entity json object
    */
    protected function build() {
		throw new Exception('Function '. __FUNCTION__ . ' must be overriden in Entity class!');
    }
    
    protected function persist($mno_entity) {
		throw new Exception('Function '. __FUNCTION__ . ' must be overriden in Entity class!');
    }
    
    public function getLocalEntityIdentifier() {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in Entity class!');
    }
        
    public function send($local_entity) {
		$this->_log->debug(__FUNCTION__ . " start");
        
		$this->_local_entity = $local_entity;
		$message = $this->build();
        $mno_had_no_id = empty($this->_id);
        
		if ($mno_had_no_id) {
            $this->_log->debug(__FUNCTION__ . " $this->_id = ".$this->_id);
            $response = $this->callMaestrano($this->_create_http_operation, $this->_create_rest_entity_name, $message);
        } else {
            $response = $this->callMaestrano($this->_update_http_operation, $this->_update_rest_entity_name . '/' . $this->_id, $message);
        }
	
        $local_entity_id = $this->getLocalEntityIdentifier();
        $local_entity_now_has_id = !empty($local_entity_id);
        
		$mno_response_id = $response->id;
        $mno_response_has_id = !empty($mno_response_id);
	
        if ($mno_had_no_id && $local_entity_now_has_id && $mno_response_has_id) {
	    	$this->addIdMapEntry($local_entity_id,$mno_response_id);
		}
    }
    
    public function receive($mno_entity) {
        $this->persist($mno_entity);
    }
    
    public function receiveNotification($notification) {
		$mno_entity = $this->callMaestrano($this->_receive_http_operation, $this->_receive_rest_entity_name . '/' . $notification->id);
        if (!empty($mno_entity)) {
            $this->receive($mno_entity);
        }
    }
    
    public function sendDeleteNotification($local_id) {
        $this->_log->debug(__FUNCTION__ .  " start local_id = " . $local_id);
        $mno_id =  $this->getMnoIdByLocalId($local_id);
	
        if ($this->isValidIdentifier($mno_id)) {
            $this->_log->debug(__FUNCTION__ . " corresponding mno_id = " . $mno_id->_id);
            
            if ($this->_enable_delete_notifications) {
                $this->callMaestrano($this->_delete_http_operation, $this->_delete_rest_entity_name . '/' . $mno_id->_id);
            }
            
            $this->deleteIdMapEntry($local_id);
            $this->_log->debug(__FUNCTION__ .  " after deleting ID entry");
        }
    }
    
    public function getUpdates($timestamp) {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in Entity class!');
    }
    
    /**
     * Send/retrieve data from Maestrano integration service
     *
     * @param HTTPOperation {"POST", "PUT", "GET", "DELETE"}
     * @param String EntityName
     * @param JSON Request payload
     * @return JSON Response payload
     */
    protected function callMaestrano($operation, $entity, $msg='')
    {            
      $this->_log->debug(__FUNCTION__ .  " start");
      $maestrano = MaestranoService::getInstance();
      $curl = curl_init($maestrano->getSoaUrl() . $entity);
      $this->_log->debug(__FUNCTION__ . " path = " . $maestrano->getSoaUrl() . $entity);
      $this->_log->debug(__FUNCTION__ . " maestrano msg = ".$msg);
      curl_setopt($curl, CURLOPT_HEADER, false);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
      curl_setopt($curl, CURLOPT_TIMEOUT, '60');
      
      $this->_log->debug(__FUNCTION__ . " before switch");
      switch ($operation) {
	  case "POST":
	      curl_setopt($curl, CURLOPT_POST, true);
	      curl_setopt($curl, CURLOPT_POSTFIELDS, $msg);
	      break;
	  case "PUT":
	      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
	      curl_setopt($curl, CURLOPT_POSTFIELDS, $msg);
	      break;
	  case "GET":
	      break;
          case "DELETE":
              curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
              break;
      }

      $this->_log->debug(__FUNCTION__ . " before curl_exec");
      $response = trim(curl_exec($curl));
      $this->_log->debug(__FUNCTION__ . " after curl_exec");
      $this->_log->debug(__FUNCTION__ . " response = ". $response);
      $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      
      $this->_log->debug(__FUNCTION__ . " status = ". $status);
      
      if ( $status != 200 ) {
		    $this->_log->debug(__FUNCTION__ . " Error: call to URL $url failed with status $status, response $response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl), 0);
            curl_close($curl);
            return null;
      }

      curl_close($curl);

      $response = json_decode($response, false);
      
      return $response;
    }
    
    protected function addIdMapEntry($local_id, $mno_id) {
        return $this->_mno_soa_db_interface->addIdMapEntry($local_id, $this->_local_entity_name, $mno_id, $this->_mno_entity_name);
    }
    
    protected function getMnoIdByLocalId($localId)
    {
        $this->_log->debug(__FUNCTION__ . " reached getMnoIdByLocalId = " . $localId);
        return $this->getMnoIdByLocalIdName($localId, $this->_local_entity_name);
    }
    
    /**
    * Get Maestrano ID by local ID
    *
    * @return 	object->id 		a Maestrano ID if found, null otherwise
    * 	     	object->entity  	entity name related to ID
    */
    protected function getMnoIdByLocalIdName($localId, $localEntityName)
    {
        return $this->_mno_soa_db_interface->getMnoIdByLocalIdName($localId, $localEntityName);
    }
    
    protected function getLocalIdByMnoId($mnoId)
    {
        return $this->getLocalIdByMnoIdName($mnoId, $this->_mno_entity_name);
    }
    
    /**
    * Get local ID by Maestrano ID
    *
    * @return 	object->id 		a local ID if found, null otherwise
    * 	     	object->entity  	entity name related to ID
    */
    protected function getLocalIdByMnoIdName($mnoId, $mnoEntityName)
    {
        return $this->_mno_soa_db_interface->getLocalIdByMnoIdName($mnoId, $mnoEntityName);
    }
    
    protected function deleteIdMapEntry($localId)
    {
        return $this->deleteIdMapEntryName($localId, $this->_local_entity_name);
    }
    
    protected function deleteIdMapEntryName($localId, $localEntityName) 
    {
        return $this->_mno_soa_db_interface->deleteIdMapEntry($localId, $localEntityName);
    }
    
    protected function isValidIdentifier($id_obj) {
        $this->_log->debug(__FUNCTION__ . " in is valid identifier");
        return !empty($id_obj) && (!empty($id_obj->_id) || (array_key_exists('_id',$id_obj) && $id_obj->_id == 0)) && array_key_exists('_deleted_flag',$id_obj) && $id_obj->_deleted_flag == 0;
    }
    
    protected function isDeletedIdentifier($id_obj) {
        $this->_log->debug(__FUNCTION__ . " in is deleted identifier");
        return !empty($id_obj) && (!empty($id_obj->_id) || (array_key_exists('_id',$id_obj) && $id_obj->_id == 0)) && array_key_exists('_deleted_flag',$id_obj) && $id_obj->_deleted_flag == 1;
    }

    /**
    *	Helper functions
    *
    */
    protected function getNumeric($str) {
	$result = preg_replace("/[^0-9.]/","",$str);
	if (empty($result) || !is_numeric($result)) return 0;
	return intval($result);
    }
    
    protected function array_key_has_value($key, $array)
    {
        return array_key_exists($key, $array) && $array->$key != null;
    }
    
    protected function set_if_array_key_has_value(&$target, $key, &$array)
    {
        if ($this->array_key_has_value($key, $array)) {
            $target = $array->$key;
        }
    }
    
    protected function push_set_or_delete_value(&$source, $empty_value="")
    {
        if (!empty($source)) { return $source; }
        else { return $empty_value; }
    }
    
    protected function pull_set_or_delete_value(&$source, $empty_value="")
    {
        if ($source == null) { $this->_log->debug('source==null'); return null; }
        else if (!empty($source)) { $this->_log->debug('!empty(source)'); return $source; }
        else { $this->_log->debug('empty(source)'); return $empty_value; }
    }
    
    protected function mapCountryToISO3166($country) {
	switch ($country) {
	      case "Afghanistan": return "AF";
	      case "Aland Islands": return "AX";
	      case "Albania": return "AL";
	      case "Algeria": return "DZ";
	      case "American Samoa": return "AS";
	      case "Andorra": return "AD";
	      case "Angola": return "AO";
	      case "Anguilla": return "AI";
	      case "Antarctica": return "AQ";
	      case "Antigua and Barbuda": return "AG";
	      case "Argentina": return "AR";
	      case "Armenia": return "AM";
	      case "Aruba": return "AW";
	      case "Australia": return "AU";
	      case "Austria": return "AT";
	      case "Azerbaijan": return "AZ";
	      case "Bahamas": return "BS";
	      case "Bahrain": return "BH";
	      case "Bangladesh": return "BD";
	      case "Barbados": return "BB";
	      case "Belarus": return "BY";
	      case "Belgium": return "BE";
	      case "Belize": return "BZ";
	      case "Benin": return "BJ";
	      case "Bermuda": return "BM";
	      case "Bhutan": return "BT";
	      case "Bolivia, Plurinational State of": return "BO";
	      case "Bonaire, Saint Eustatius and Saba": return "BQ";
	      case "Bosnia and Herzegovina": return "BA";
	      case "Botswana": return "BW";
	      case "Bouvet Island": return "BV";
	      case "Brazil": return "BR";
	      case "British Indian Ocean Territory": return "IO";
	      case "Brunei Darussalam": return "BN";
	      case "Bulgaria": return "BG";
	      case "Burkina Faso": return "BF";
	      case "Burundi": return "BI";
	      case "Cambodia": return "KH";
	      case "Cameroon": return "CM";
	      case "Canada": return "CA";
	      case "Cape Verde": return "CV";
	      case "Cayman Islands": return "KY";
	      case "Central African Republic": return "CF";
	      case "Chad": return "TD";
	      case "Chile": return "CL";
	      case "China": return "CN";
	      case "Christmas Island": return "CX";
	      case "Cocos (Keeling) Islands": return "CC";
	      case "Colombia": return "CO";
	      case "Comoros": return "KM";
	      case "Congo": return "CG";
	      case "Congo, The Democratic Republic of the": return "CD";
	      case "Cook Islands": return "CK";
	      case "Costa Rica": return "CR";
	      case "Cote d'Ivoire": return "CI";
	      case "Croatia": return "HR";
	      case "Cuba": return "CU";
	      case "Curacao": return "CW";
	      case "Cyprus": return "CY";
	      case "Czech Republic": return "CZ";
	      case "Denmark": return "DK";
	      case "Djibouti": return "DJ";
	      case "Dominica": return "DM";
	      case "Dominican Republic": return "DO";
	      case "Ecuador": return "EC";
	      case "Egypt": return "EG";
	      case "El Salvador": return "SV";
	      case "Equatorial Guinea": return "GQ";
	      case "Eritrea": return "ER";
	      case "Estonia": return "EE";
	      case "Ethiopia": return "ET";
	      case "Falkland Islands (Malvinas)": return "FK";
	      case "Faroe Islands": return "FO";
	      case "Fiji": return "FJ";
	      case "Finland": return "FI";
	      case "France": return "FR";
	      case "French Guiana": return "GF";
	      case "French Polynesia": return "PF";
	      case "French Southern Territories": return "TF";
	      case "Gabon": return "GA";
	      case "Gambia": return "GM";
	      case "Georgia": return "GE";
	      case "Germany": return "DE";
	      case "Ghana": return "GH";
	      case "Gibraltar": return "GI";
	      case "Greece": return "GR";
	      case "Greenland": return "GL";
	      case "Grenada": return "GD";
	      case "Guadeloupe": return "GP";
	      case "Guam": return "GU";
	      case "Guatemala": return "GT";
	      case "Guernsey": return "GG";
	      case "Guinea": return "GN";
	      case "Guinea-Bissau": return "GW";
	      case "Guyana": return "GY";
	      case "Haiti": return "HT";
	      case "Heard Island and McDonald Islands": return "HM";
	      case "Holy See (Vatican City State)": return "VA";
	      case "Honduras": return "HN";
	      case "Hong Kong": return "HK";
	      case "Hungary": return "HU";
	      case "Iceland": return "IS";
	      case "India": return "IN";
	      case "Indonesia": return "ID";
	      case "Iran, Islamic Republic of": return "IR";
	      case "Iraq": return "IQ";
	      case "Ireland": return "IE";
	      case "Isle of Man": return "IM";
	      case "Israel": return "IL";
	      case "Italy": return "IT";
	      case "Jamaica": return "JM";
	      case "Japan": return "JP";
	      case "Jersey": return "JE";
	      case "Jordan": return "JO";
	      case "Kazakhstan": return "KZ";
	      case "Kenya": return "KE";
	      case "Kiribati": return "KI";
	      case "Korea, Democratic People's Republic of": return "KP";
	      case "Korea, Republic of": return "KR";
	      case "Kuwait": return "KW";
	      case "Kyrgyzstan": return "KG";
	      case "Lao People's Democratic Republic": return "LA";
	      case "Latvia": return "LV";
	      case "Lebanon": return "LB";
	      case "Lesotho": return "LS";
	      case "Liberia": return "LR";
	      case "Libyan Arab Jamahiriya": return "LY";
	      case "Liechtenstein": return "LI";
	      case "Lithuania": return "LT";
	      case "Luxembourg": return "LU";
	      case "Macao": return "MO";
	      case "Macedonia, The Former Yugoslav Republic of": return "MK";
	      case "Madagascar": return "MG";
	      case "Malawi": return "MW";
	      case "Malaysia": return "MY";
	      case "Maldives": return "MV";
	      case "Mali": return "ML";
	      case "Malta": return "MT";
	      case "Marshall Islands": return "MH";
	      case "Martinique": return "MQ";
	      case "Mauritania": return "MR";
	      case "Mauritius": return "MU";
	      case "Mayotte": return "YT";
	      case "Mexico": return "MX";
	      case "Micronesia, Federated States of": return "FM";
	      case "Moldova, Republic of": return "MD";
	      case "Monaco": return "MC";
	      case "Mongolia": return "MN";
	      case "Montenegro": return "ME";
	      case "Montserrat": return "MS";
	      case "Morocco": return "MA";
	      case "Mozambique": return "MZ";
	      case "Myanmar": return "MM";
	      case "Namibia": return "NA";
	      case "Nauru": return "NR";
	      case "Nepal": return "NP";
	      case "Netherlands": return "NL";
	      case "New Caledonia": return "NC";
	      case "New Zealand": return "NZ";
	      case "Nicaragua": return "NI";
	      case "Niger": return "NE";
	      case "Nigeria": return "NG";
	      case "Niue": return "NU";
	      case "Norfolk Island": return "NF";
	      case "Northern Mariana Islands": return "MP";
	      case "Norway": return "NO";
	      case "Occupied Palestinian Territory": return "PS";
	      case "Oman": return "OM";
	      case "Pakistan": return "PK";
	      case "Palau": return "PW";
	      case "Panama": return "PA";
	      case "Papua New Guinea": return "PG";
	      case "Paraguay": return "PY";
	      case "Peru": return "PE";
	      case "Philippines": return "PH";
	      case "Pitcairn": return "PN";
	      case "Poland": return "PL";
	      case "Portugal": return "PT";
	      case "Puerto Rico": return "PR";
	      case "Qatar": return "QA";
	      case "Reunion": return "RE";
	      case "Romania": return "RO";
	      case "Russian Federation": return "RU";
	      case "Rwanda": return "RW";
	      case "Saint Barthelemy": return "BL";
	      case "Saint Helena, Ascension and Tristan da Cunha": return "SH";
	      case "Saint Kitts and Nevis": return "KN";
	      case "Saint Lucia": return "LC";
	      case "Saint Martin (French part)": return "MF";
	      case "Saint Pierre and Miquelon": return "PM";
	      case "Saint Vincent and The Grenadines": return "VC";
	      case "Samoa": return "WS";
	      case "San Marino": return "SM";
	      case "Sao Tome and Principe": return "ST";
	      case "Saudi Arabia": return "SA";
	      case "Senegal": return "SN";
	      case "Serbia": return "RS";
	      case "Seychelles": return "SC";
	      case "Sierra Leone": return "SL";
	      case "Singapore": return "SG";
	      case "Sint Maarten (Dutch part)": return "SX";
	      case "Slovakia": return "SK";
	      case "Slovenia": return "SI";
	      case "Solomon Islands": return "SB";
	      case "Somalia": return "SO";
	      case "South Africa": return "ZA";
	      case "South Georgia and the South Sandwich Islands": return "GS";
	      case "Spain": return "ES";
	      case "Sri Lanka": return "LK";
	      case "Sudan": return "SD";
	      case "Suriname": return "SR";
	      case "Svalbard and Jan Mayen": return "SJ";
	      case "Swaziland": return "SZ";
	      case "Sweden": return "SE";
	      case "Switzerland": return "CH";
	      case "Syrian Arab Republic": return "SY";
	      case "Taiwan, Province of China": return "TW";
	      case "Tajikistan": return "TJ";
	      case "Tanzania, United Republic of": return "TZ";
	      case "Thailand": return "TH";
	      case "Timor-Leste": return "TL";
	      case "Togo": return "TG";
	      case "Tokelau": return "TK";
	      case "Tonga": return "TO";
	      case "Trinidad and Tobago": return "TT";
	      case "Tunisia": return "TN";
	      case "Turkey": return "TR";
	      case "Turkmenistan": return "TM";
	      case "Turks and Caicos Islands": return "TC";
	      case "Tuvalu": return "TV";
	      case "Uganda": return "UG";
	      case "Ukraine": return "UA";
	      case "United Arab Emirates": return "AE";
	      case "United Kingdom": return "GB";
	      case "United States": return "US";
	      case "United States Minor Outlying Islands": return "UM";
	      case "Uruguay": return "UY";
	      case "Uzbekistan": return "UZ";
	      case "Vanuatu": return "VU";
	      case "Venezuela, Bolivarian Republic of": return "VE";
	      case "Viet Nam": return "VN";
	      case "Virgin Islands, British": return "VG";
	      case "Virgin Islands, U.S.": return "VI";
	      case "Wallis and Futuna": return "WF";
	      case "Western Sahara": return "EH";
	      case "Yemen": return "YE";
	      case "Zambia": return "ZM";
	      case "Zimbabwe": return "ZW";
	}
	return null;
    }
    
    protected function mapISO3166ToCountry($country) {
	switch($country) {
	      case "AF": return "Afghanistan";
	      case "AX": return "Aland Islands";
	      case "AL": return "Albania";
	      case "DZ": return "Algeria";
	      case "AS": return "American Samoa";
	      case "AD": return "Andorra";
	      case "AO": return "Angola";
	      case "AI": return "Anguilla";
	      case "AQ": return "Antarctica";
	      case "AG": return "Antigua and Barbuda";
	      case "AR": return "Argentina";
	      case "AM": return "Armenia";
	      case "AW": return "Aruba";
	      case "AU": return "Australia";
	      case "AT": return "Austria";
	      case "AZ": return "Azerbaijan";
	      case "BS": return "Bahamas";
	      case "BH": return "Bahrain";
	      case "BD": return "Bangladesh";
	      case "BB": return "Barbados";
	      case "BY": return "Belarus";
	      case "BE": return "Belgium";
	      case "BZ": return "Belize";
	      case "BJ": return "Benin";
	      case "BM": return "Bermuda";
	      case "BT": return "Bhutan";
	      case "BO": return "Bolivia, Plurinational State of";
	      case "BQ": return "Bonaire, Saint Eustatius and Saba";
	      case "BA": return "Bosnia and Herzegovina";
	      case "BW": return "Botswana";
	      case "BV": return "Bouvet Island";
	      case "BR": return "Brazil";
	      case "IO": return "British Indian Ocean Territory";
	      case "BN": return "Brunei Darussalam";
	      case "BG": return "Bulgaria";
	      case "BF": return "Burkina Faso";
	      case "BI": return "Burundi";
	      case "KH": return "Cambodia";
	      case "CM": return "Cameroon";
	      case "CA": return "Canada";
	      case "CV": return "Cape Verde";
	      case "KY": return "Cayman Islands";
	      case "CF": return "Central African Republic";
	      case "TD": return "Chad";
	      case "CL": return "Chile";
	      case "CN": return "China";
	      case "CX": return "Christmas Island";
	      case "CC": return "Cocos (Keeling) Islands";
	      case "CO": return "Colombia";
	      case "KM": return "Comoros";
	      case "CG": return "Congo";
	      case "CD": return "Congo, The Democratic Republic of the";
	      case "CK": return "Cook Islands";
	      case "CR": return "Costa Rica";
	      case "CI": return "Cote d'Ivoire";
	      case "HR": return "Croatia";
	      case "CU": return "Cuba";
	      case "CW": return "Curacao";
	      case "CY": return "Cyprus";
	      case "CZ": return "Czech Republic";
	      case "DK": return "Denmark";
	      case "DJ": return "Djibouti";
	      case "DM": return "Dominica";
	      case "DO": return "Dominican Republic";
	      case "EC": return "Ecuador";
	      case "EG": return "Egypt";
	      case "SV": return "El Salvador";
	      case "GQ": return "Equatorial Guinea";
	      case "ER": return "Eritrea";
	      case "EE": return "Estonia";
	      case "ET": return "Ethiopia";
	      case "FK": return "Falkland Islands (Malvinas)";
	      case "FO": return "Faroe Islands";
	      case "FJ": return "Fiji";
	      case "FI": return "Finland";
	      case "FR": return "France";
	      case "GF": return "French Guiana";
	      case "PF": return "French Polynesia";
	      case "TF": return "French Southern Territories";
	      case "GA": return "Gabon";
	      case "GM": return "Gambia";
	      case "GE": return "Georgia";
	      case "DE": return "Germany";
	      case "GH": return "Ghana";
	      case "GI": return "Gibraltar";
	      case "GR": return "Greece";
	      case "GL": return "Greenland";
	      case "GD": return "Grenada";
	      case "GP": return "Guadeloupe";
	      case "GU": return "Guam";
	      case "GT": return "Guatemala";
	      case "GG": return "Guernsey";
	      case "GN": return "Guinea";
	      case "GW": return "Guinea-Bissau";
	      case "GY": return "Guyana";
	      case "HT": return "Haiti";
	      case "HM": return "Heard Island and McDonald Islands";
	      case "VA": return "Holy See (Vatican City State)";
	      case "HN": return "Honduras";
	      case "HK": return "Hong Kong";
	      case "HU": return "Hungary";
	      case "IS": return "Iceland";
	      case "IN": return "India";
	      case "ID": return "Indonesia";
	      case "IR": return "Iran, Islamic Republic of";
	      case "IQ": return "Iraq";
	      case "IE": return "Ireland";
	      case "IM": return "Isle of Man";
	      case "IL": return "Israel";
	      case "IT": return "Italy";
	      case "JM": return "Jamaica";
	      case "JP": return "Japan";
	      case "JE": return "Jersey";
	      case "JO": return "Jordan";
	      case "KZ": return "Kazakhstan";
	      case "KE": return "Kenya";
	      case "KI": return "Kiribati";
	      case "KP": return "Korea, Democratic People's Republic of";
	      case "KR": return "Korea, Republic of";
	      case "KW": return "Kuwait";
	      case "KG": return "Kyrgyzstan";
	      case "LA": return "Lao People's Democratic Republic";
	      case "LV": return "Latvia";
	      case "LB": return "Lebanon";
	      case "LS": return "Lesotho";
	      case "LR": return "Liberia";
	      case "LY": return "Libyan Arab Jamahiriya";
	      case "LI": return "Liechtenstein";
	      case "LT": return "Lithuania";
	      case "LU": return "Luxembourg";
	      case "MO": return "Macao";
	      case "MK": return "Macedonia, The Former Yugoslav Republic of";
	      case "MG": return "Madagascar";
	      case "MW": return "Malawi";
	      case "MY": return "Malaysia";
	      case "MV": return "Maldives";
	      case "ML": return "Mali";
	      case "MT": return "Malta";
	      case "MH": return "Marshall Islands";
	      case "MQ": return "Martinique";
	      case "MR": return "Mauritania";
	      case "MU": return "Mauritius";
	      case "YT": return "Mayotte";
	      case "MX": return "Mexico";
	      case "FM": return "Micronesia, Federated States of";
	      case "MD": return "Moldova, Republic of";
	      case "MC": return "Monaco";
	      case "MN": return "Mongolia";
	      case "ME": return "Montenegro";
	      case "MS": return "Montserrat";
	      case "MA": return "Morocco";
	      case "MZ": return "Mozambique";
	      case "MM": return "Myanmar";
	      case "NA": return "Namibia";
	      case "NR": return "Nauru";
	      case "NP": return "Nepal";
	      case "NL": return "Netherlands";
	      case "NC": return "New Caledonia";
	      case "NZ": return "New Zealand";
	      case "NI": return "Nicaragua";
	      case "NE": return "Niger";
	      case "NG": return "Nigeria";
	      case "NU": return "Niue";
	      case "NF": return "Norfolk Island";
	      case "MP": return "Northern Mariana Islands";
	      case "NO": return "Norway";
	      case "PS": return "Occupied Palestinian Territory";
	      case "OM": return "Oman";
	      case "PK": return "Pakistan";
	      case "PW": return "Palau";
	      case "PA": return "Panama";
	      case "PG": return "Papua New Guinea";
	      case "PY": return "Paraguay";
	      case "PE": return "Peru";
	      case "PH": return "Philippines";
	      case "PN": return "Pitcairn";
	      case "PL": return "Poland";
	      case "PT": return "Portugal";
	      case "PR": return "Puerto Rico";
	      case "QA": return "Qatar";
	      case "RE": return "Reunion";
	      case "RO": return "Romania";
	      case "RU": return "Russian Federation";
	      case "RW": return "Rwanda";
	      case "BL": return "Saint Barthelemy";
	      case "SH": return "Saint Helena, Ascension and Tristan da Cunha";
	      case "KN": return "Saint Kitts and Nevis";
	      case "LC": return "Saint Lucia";
	      case "MF": return "Saint Martin (French part)";
	      case "PM": return "Saint Pierre and Miquelon";
	      case "VC": return "Saint Vincent and The Grenadines";
	      case "WS": return "Samoa";
	      case "SM": return "San Marino";
	      case "ST": return "Sao Tome and Principe";
	      case "SA": return "Saudi Arabia";
	      case "SN": return "Senegal";
	      case "RS": return "Serbia";
	      case "SC": return "Seychelles";
	      case "SL": return "Sierra Leone";
	      case "SG": return "Singapore";
	      case "SX": return "Sint Maarten (Dutch part)";
	      case "SK": return "Slovakia";
	      case "SI": return "Slovenia";
	      case "SB": return "Solomon Islands";
	      case "SO": return "Somalia";
	      case "ZA": return "South Africa";
	      case "GS": return "South Georgia and the South Sandwich Islands";
	      case "ES": return "Spain";
	      case "LK": return "Sri Lanka";
	      case "SD": return "Sudan";
	      case "SR": return "Suriname";
	      case "SJ": return "Svalbard and Jan Mayen";
	      case "SZ": return "Swaziland";
	      case "SE": return "Sweden";
	      case "CH": return "Switzerland";
	      case "SY": return "Syrian Arab Republic";
	      case "TW": return "Taiwan, Province of China";
	      case "TJ": return "Tajikistan";
	      case "TZ": return "Tanzania, United Republic of";
	      case "TH": return "Thailand";
	      case "TL": return "Timor-Leste";
	      case "TG": return "Togo";
	      case "TK": return "Tokelau";
	      case "TO": return "Tonga";
	      case "TT": return "Trinidad and Tobago";
	      case "TN": return "Tunisia";
	      case "TR": return "Turkey";
	      case "TM": return "Turkmenistan";
	      case "TC": return "Turks and Caicos Islands";
	      case "TV": return "Tuvalu";
	      case "UG": return "Uganda";
	      case "UA": return "Ukraine";
	      case "AE": return "United Arab Emirates";
	      case "GB": return "United Kingdom";
	      case "US": return "United States";
	      case "UM": return "United States Minor Outlying Islands";
	      case "UY": return "Uruguay";
	      case "UZ": return "Uzbekistan";
	      case "VU": return "Vanuatu";
	      case "VE": return "Venezuela, Bolivarian Republic of";
	      case "VN": return "Viet Nam";
	      case "VG": return "Virgin Islands, British";
	      case "VI": return "Virgin Islands, U.S.";
	      case "WF": return "Wallis and Futuna";
	      case "EH": return "Western Sahara";
	      case "YE": return "Yemen";
	      case "ZM": return "Zambia";
	      case "ZW": return "Zimbabwe";
	}
	return null;
    }
}
?>