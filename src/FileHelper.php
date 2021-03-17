<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Support;

/**
 * 文件助手
 * @author Tongle Xu <xutongle@gmail.com>
 */
class FileHelper
{
    /**
     * 获取文件名
     *
     * @param string $path
     * @return string
     */
    public function basename(string $path): string
    {
        return pathinfo($path, PATHINFO_BASENAME);
    }

    /**
     * 获取父目录
     *
     * @param string $path
     * @return string
     */
    public function dirname(string $path): string
    {
        return pathinfo($path, PATHINFO_DIRNAME);
    }

    /**
     * 获取文件后缀
     *
     * @param string $path
     * @return string
     */
    public function extension(string $path): string
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Get the file type of a given file.
     *
     * @param string $path
     * @return string
     */
    public function type(string $path): string
    {
        return filetype($path);
    }

    /**
     * Get the mime-type of a given file.
     *
     * @param string $path
     * @return string|false
     */
    public function mimeType(string $path)
    {
        return finfo_file(finfo_open(\FILEINFO_MIME_TYPE), $path);
    }

    /**
     * 返回指定文件的大小
     *
     * @param string $path
     * @return int 返回文件大小的字节数
     */
    public function size(string $path): int
    {
        return filesize($path);
    }

    /**
     * 获取格式化后的文件大小
     * @param string $path
     * @return string
     */
    public function sizeFormat(string $path): string
    {
        $size = static::size($path);
        $sizes = [" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB"];
        if ($size == 0) {
            return 'N/A';
        } else {
            return round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizes[$i];
        }
    }

    /**
     * 获取文件最后修改的时间戳
     *
     * @param string $path
     * @return int
     */
    public function lastModified(string $path): int
    {
        return filemtime($path);
    }
}
