<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

declare (strict_types=1);

namespace Larva\Support\Exception;

/**
 * Class UnknownPropertyException
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class UnknownPropertyException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName(): string
    {
        return 'Unknown Property';
    }
}
