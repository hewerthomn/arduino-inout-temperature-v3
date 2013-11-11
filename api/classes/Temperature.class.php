<?php
/**
 * Temperature class
 */
class Temperature
{
	/**
	 * Usa a API da wunderground para obter temperatura e humidade da localização
	 * @return array
	 */
	public static function get()
	{
	    /**
	     * create a account and get your API key at
	     * http://www.wunderground.com/
	     */
	    $APIKEY = API_KEY;

	    //$json_string = file_get_contents("http://api.wunderground.com/api/{$APIKEY}/conditions/q/BR/Porto_Velho.json");
	    $json_string = file_get_contents("http://api.wunderground.com/api/{$APIKEY}/conditions/q/SBPV.json");
	    $parsed_json = json_decode($json_string);

	    // d($parsed_json);die

	    $location    = $parsed_json->{'current_observation'}->{'display_location'}->{'city'};
	    $location   .= '/' . $parsed_json->{'current_observation'}->{'display_location'}->{'country_iso3166'};

	    $temperature = $parsed_json->{'current_observation'}->{'temp_c'};
	    $humidity    = $parsed_json->{'current_observation'}->{'relative_humidity'};

	    $location     = str_pad($location, 16);
	    
	    return [
	        'city' 				=> $location,
	        'temperature' => $temperature,
	        'humidity'    => $humidity,
	        'dew_point'   => self::dew_point($temperature, (float) $humidity),
	        'time'        => date('h:i')
	    ];
	}

	public static function dew_point($temp, $humidity)
	{
		$RATIO = 373.15 / (273.15 + $temp); // $RATIO wa originally named A0, possibly confusing in Arduino context
		$SUM = -7.90298 * ($RATIO - 1);
		$SUM += 5.02808 * log10($RATIO);
		$SUM += -1.3816e-7 * (pow(10, (11.344 * (1 - 1/$RATIO ))) - 1);
		$SUM += 8.1328e-3 * (pow(10, (-3.49149 * ($RATIO - 1))) - 1);
		$SUM += log10(1013.246);
		$VP = pow(10, $SUM - 3) * $humidity;
		$T = log($VP / 0.61078); // temp var

		return number_format( (241.88 * $T) / (17.558 - $T), 2);
	}
}