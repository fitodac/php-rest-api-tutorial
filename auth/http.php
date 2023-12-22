<?php

header( 'Content-Type: application/json' );

if (!isset($_SERVER['PHP_AUTH_USER'])) {
	header('WWW-Authenticate: Basic realm="Área restringida"');
	header('HTTP/1.0 401 Unauthorized');
	echo 'Autenticación cancelada.';
	die;
} else {
	// Verificar las credenciales (generalmente se haría en una base de datos)
	$user = 'hal';
	$password = '1234';

	if ($_SERVER['PHP_AUTH_USER'] !== $user || $_SERVER['PHP_AUTH_PW'] !== $password) {
		header('HTTP/1.0 401 Unauthorized');
		echo json_encode([ 
			'error' => "Usuario y/o password incorrectos", 
		]);
		die;
	}
}


require_once '../server.php';