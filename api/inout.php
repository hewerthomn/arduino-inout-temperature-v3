<?php
/**
 * InOut Temperature v3
 *
 * @author Everton Inocencio <hewertho.mn>
 *
 * Registra a temperatura e humidade recebida do sensor DTH11 no Arduino
 * e retorna a Data, Hora e Temperaturas da cidade
 * 
 */
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

require 'config.php';

$log    = [];
$logger = new InOut;

if(isset($_GET['T']) && isset($_GET['H']))
{

	$log = [
		'in_placename'   => $_GET['P'],
		'in_temperature' => $_GET['T'],
		'in_humidity'    => $_GET['H'],
		'in_dew_point'   => Temperature::dew_point($_GET['T'], $_GET['H'])
	];

	$data = Temperature::get();

	if($data['temperature'] > 0)
	{
		$log['out_placename']   = $data['city'];
		$log['out_temperature'] = $data['temperature'];
		$log['out_humidity']    = $data['humidity'];
		$log['out_dew_point']   = Temperature::dew_point($log['out_temperature'], $log['out_humidity']);

		$r = $logger->save( $log );
	}

	$date = date('M,d');
	$time = date('h:i');

	$result = "<{$date}|{$time}|{$data['city']}|{$data['temperature']}|{$data['humidity']}|{$data['dew_point']}>";

	die( $result );
}
