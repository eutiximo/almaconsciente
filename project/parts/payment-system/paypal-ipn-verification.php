<?php

//Permite crear un archivo LOG cada vez que se ejecuta este script, 0 para desactivarlo
define("DEBUG", 0);
// 1 para usarlo con el simulador de paypal (https://developer.paypal.com/developer/ipnSimulator/), 0 para usarlo con pagos reales
define("USE_SANDBOX", 1);
// este es el archivo LOG no importa si no existe este lo crea automaticamente
define("LOG_FILE", get_template_directory()."/logs/resultadopaypal.txt");

// permite verificar si los datos son recibidos desde paypal y no otra pagina diferente
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();

foreach ($raw_post_array as $keyval) {
	$keyval = explode ('=', $keyval);
	if (count($keyval) == 2)
		$myPost[$keyval[0]] = urldecode($keyval[1]);
}

$req = 'cmd=_notify-validate';

if(function_exists('get_magic_quotes_gpc')) {
	$get_magic_quotes_exists = true;
}

foreach ($myPost as $key => $value) {
	if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
		$value = urlencode(stripslashes($value));
	} else {
		$value = urlencode($value);
	}
	$req .= "&$key=$value";
}

if(USE_SANDBOX == true) {
	$paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
} else {
	$paypal_url = "https://www.paypal.com/cgi-bin/webscr";
}

$ch = curl_init($paypal_url);

if ($ch == FALSE) {
	return FALSE;
}

curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);

if(DEBUG == true) {
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
}

//si depronto se usa proxy eliminar los //
//curl_setopt($ch, CURLOPT_PROXY, $proxy);
//curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);

//se coloca el tiempo maximo que se demora este script
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

$res = curl_exec($ch);

if (curl_errno($ch) != 0) // cURL error
	{
	if(DEBUG == true) {	
		error_log(date('[Y-m-d H:i e] '). "No se puede conectar a PayPal para validar el IPN: " . curl_error($ch) . PHP_EOL, 3, LOG_FILE);
	}
	curl_close($ch);
	exit;
} else {
		// Log son las respuestas de error que se escribiran en el LOG
		if(DEBUG == true) {
			error_log(date('[Y-m-d H:i e] '). "HTTP respuesta:". curl_getinfo($ch, CURLINFO_HEADER_OUT) ." para IPN: $req" . PHP_EOL, 3, LOG_FILE);
			error_log(date('[Y-m-d H:i e] '). "HTTP respuesta a la validacion: $res" . PHP_EOL, 3, LOG_FILE);
		}
		curl_close($ch);
}

$tokens = explode("\r\n\r\n", trim($res));
$res = trim(end($tokens));

if (strcmp ($res, "VERIFIED") == 0) {
	
	//aqui se colocan las instrucciones php si el pago fue hecho es decir COMPLETED
	//estas son las variables recibidas desde paypal que se pueden usar
	
    $_POST["is_trustworthy"] = true;
	
	//Crea un archivo TXT cada vez que se recibe un pago desde paypal
	//con todos los datos que nos envia
	$txt = fopen(get_template_directory()."/logs/paypalpagos.txt", "a+");
	
	ob_start();
	var_dump($_POST);
	$result = ob_get_clean();
	
	fputs($txt, $result);
	@fclose($fp);
	//fin
	
	// crea el LOG
	if(DEBUG == true) {
		error_log(date('[Y-m-d H:i e] '). "IPN Verificado: $req ". PHP_EOL, 3, LOG_FILE);
	}
    
    return true;
    
} else if (strcmp ($res, "INVALID") == 0) {
	if(DEBUG == true) {
		error_log(date('[Y-m-d H:i e] '). "IPN Invalido: $req" . PHP_EOL, 3, LOG_FILE);
	}
    
    return false;
}