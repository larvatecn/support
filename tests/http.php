<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */
include '../vendor/autoload.php';

use Larva\Support\HttpClient;

$http = HttpClient::make();

$http->withOnlyIPv4()->withoutRedirecting()->withoutVerifying()->acceptJson();
$http->baseUrl('https://api.myip.la/');
$response = $http->post('/en', ['json' => '']);

print_r($response->json());

print_r($http);
