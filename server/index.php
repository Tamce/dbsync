<?php
require __DIR__.'/../vendor/autoload.php';
use Illuminate\Database\Capsule\Manager as Capsule;

// ********************************
//         Configurate Here
// ********************************
// Key used to encrypt and decrypt data
define ('KEY', '');
// 16 bytes IV used to encrypt and decrypt data
define ('IV', '');
// which method will be used to encrypt and decrypt
define ('METHOD', 'aes-256-cfb');
// ********************************


$data = file_get_contents('php://input');
$data = json_decode(openssl_decrypt($data, METHOD, KEY, 0, IV), true);
if (empty($data)) {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

$capsule = new Capsule;
$table = $data['table'];
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => $data['db'],
    'username'  => $data['username'],
    'password'  => $data['password'],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);
$capsule->setAsGlobal();

$result = json_encode(Capsule::table($table)->get());
echo openssl_encrypt($result, METHOD, KEY, 0, IV);
