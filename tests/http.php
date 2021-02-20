<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */
include '../vendor/autoload.php';

$http = new \Larva\Support\HttpClient();

$http->withOnlyIPv4()->withoutRedirecting()->withoutVerifying()->acceptJson();
$response = $http->get('https://api.myip.la/en?json');

print_r($response->transferStats);
print_r($http->options);
