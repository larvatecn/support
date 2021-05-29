<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

declare (strict_types = 1);

namespace Larva\Support;

use Larva\Support\Exception\FileException;
use Larva\Support\Exception\FileNotFoundException;
use SplFileInfo;

/**
 * A file in the file system.
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class File extends SplFileInfo
{
    /**
     * 文件 hash
     * @var array
     */
    protected $hash = [];

    /**
     * File constructor.
     * @param string $path
     * @param bool $checkPath
     */
    public function __construct(string $path, bool $checkPath = true)
    {
        if ($checkPath && !is_file($path)) {
            throw new FileNotFoundException($path);
        }
        parent::__construct($path);
    }

    /**
     * 获取文件的哈希散列值
     * @param string $type
     * @return string
     */
    public function hash(string $type = 'sha1'): string
    {
        if (!isset($this->hash[$type])) {
            $this->hash[$type] = hash_file($type, $this->getPathname());
        }
        return $this->hash[$type];
    }

    /**
     * 获取文件的MD5值
     * @access public
     * @return string
     */
    public function md5(): string
    {
        return $this->hash('md5');
    }

    /**
     * 获取文件的SHA1值
     * @access public
     * @return string
     */
    public function sha1(): string
    {
        return $this->hash('sha1');
    }

    /**
     * 获取文件内容
     * @return string
     */
    public function getContent(): string
    {
        $content = file_get_contents($this->getPathname());
        if (false === $content) {
            throw new FileException(sprintf('Could not get the content of the file "%s".', $this->getPathname()));
        }
        return $content;
    }
}