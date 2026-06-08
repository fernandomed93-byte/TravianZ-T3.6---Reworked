<?php

global $autoprefix;

$autoloader_found = false;

for ($i = 0; $i < 5; $i++) {
    $autoprefix = str_repeat('../', $i);
    if (file_exists($autoprefix.'autoloader.php')) {
        $autoloader_found = true;
        include_once $autoprefix.'autoloader.php';
        break;
    }
}

if (!$autoloader_found) {
    die('Could not find autoloading class.');
}

	include_once($autoprefix."GameEngine/config.php");
	include_once($autoprefix."GameEngine/database.php");
	//include_once($autoprefix."GameEngine/Session.php");
  

$user = $_POST['user'];
$senha = $_POST['pw'];

// Verificar no banco de dados

		list($username, $password) = $database->escape_input($user, $senha);
		$q = "SELECT * FROM " . TB_PREFIX . "users where username = '$username'";
		$result = mysqli_query($database->dblink,$q);
		$dbarray = mysqli_fetch_array($result);
		
$pwOk = password_verify($password, $dbarray['password']);

if ($pwOk) {
    // Usuário encontrado
    echo json_encode(["userId" => $dbarray['id'], "tribe" => $dbarray['tribe'], "vilId" => $dbarray['village_select']]);
} else {
    // Credenciais inválidas
    echo json_encode(["userId" => "0", "mensagem" => "Usuário ou senha inválidos"]);
}

?>
