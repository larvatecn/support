<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Support;

use Larva\Support\Traits\HasHttpRequest;

/**
 * HTTP Client
 * @author Tongle Xu <xutongle@gmail.com>
 */
class HttpClient
{
    use HasHttpRequest;

    /**
     * HttpClient constructor.
     */
    public function __construct()
    {
        $this->options = [
            'http_errors' => false,
        ];
    }
}
