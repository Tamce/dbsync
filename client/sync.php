<?php
require __DIR__.'/../vendor/autoload.php';
use Illuminate\Database\Capsule\Manager as Capsule;

define ('KEY', '');
define ('IV', '');
define ('METHOD', 'aes-256-cfb');

define ('URL', 'localhost:8000');
$data = [
    'db' => 'apiv2',
    'username' => 'root',
    'password' => 'root',
    'table' => 'lostitem'
];

$target = [
    'db' => 'test',
    'username' => 'root',
    'password' => 'root',
    'table' => 'test'
];


echo '['.date('Y-m-d H:i:s')."]Begin syncing...\n";
$data = json_encode($data);
$data = openssl_encrypt($data, METHOD, KEY, 0, IV);

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, URL);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($curl);
if (false === $result || curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200) {
    exit('Failed to sync data! '.curl_error($curl));
}
$result = json_decode(openssl_decrypt($result, METHOD, KEY, 0, IV), true);

$capsule = new Capsule;
$table = $target['table'];
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => $target['db'],
    'username'  => $target['username'],
    'password'  => $target['password'],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);
$capsule->setAsGlobal();

Capsule::table($table)->truncate();
Capsule::table($table)->insert($result);
$count = count($result);
echo '['.date('Y-m-d H:i:s')."]Sync complete! $count record(s) transferred.\n";
