<?php
/**
 * @copyright Copyright (c) 2018 Larva Information Technology Co., Ltd.
 * @link http://www.larvacent.com/
 * @license http://www.larvacent.com/license/
 */

namespace Larva\Support;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Serializable;

/**
 * Class Collection
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Collection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable, Serializable
{
    /**
     * The collection data.
     *
     * @var array
     */
    protected $items = [];

    /**
     * set data.
     *
     * @param mixed $items
     */
    public function __construct(array $items = [])
    {
        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * To string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Get a data by key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->get($key);
    }

    /**
     * Assigns a value to the specified data.
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set(string $key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Whether or not an data exists by key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return $this->has($key);
    }

    /**
     * Unsets an data by key.
     *
     * @param string $key
     */
    public function __unset(string $key)
    {
        $this->forget($key);
    }

    /**
     * var_export.
     *
     * @param array $array
     *
     * @return array
     */
    public static function __set_state(array $array = []): array
    {
        return (new static())->all();
    }

    /**
     * Return all items.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Return specific items.
     *
     * @param array $keys
     *
     * @return array
     */
    public function only(array $keys): array
    {
        $return = [];

        foreach ($keys as $key) {
            $value = $this->get($key);

            if (!is_null($value)) {
                $return[$key] = $value;
            }
        }

        return $return;
    }

    /**
     * Get all items except for those with the specified keys.
     *
     * @param mixed $keys
     *
     * @return static
     */
    public function except($keys): Collection
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        return new static(ArrayHelper::except($this->items, $keys));
    }

    /**
     * Merge data.
     *
     * @param Collection|array $items
     *
     * @return array
     */
    public function merge($items): array
    {
        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }

        return $this->all();
    }

    /**
     * To determine Whether the specified element exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return !is_null(ArrayHelper::get($this->items, $key));
    }

    /**
     * Retrieve the first item.
     *
     * @return mixed
     */
    public function first()
    {
        return reset($this->items);
    }

    /**
     * Retrieve the last item.
     *
     * @return bool
     */
    public function last(): bool
    {
        $end = end($this->items);

        reset($this->items);

        return $end;
    }

    /**
     * add the item value.
     *
     * @param string $key
     * @param mixed $value
     */
    public function add(string $key, $value)
    {
        ArrayHelper::set($this->items, $key, $value);
    }

    /**
     * Set the item value.
     *
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, $value)
    {
        ArrayHelper::set($this->items, $key, $value);
    }

    /**
     * Retrieve item from Collection.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        return ArrayHelper::get($this->items, $key, $default);
    }

    /**
     * Remove item form Collection.
     *
     * @param string $key
     */
    public function forget(string $key)
    {
        ArrayHelper::forget($this->items, $key);
    }

    /**
     * Build to array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->all();
    }

    /**
     * Build to json.
     *
     * @param int $option
     *
     * @return string
     */
    public function toJson($option = JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($this->all(), $option);
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON.
     *
     * @see http://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *               which is a value of any type other than a resource
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object.
     *
     * @see http://php.net/manual/en/serializable.serialize.php
     *
     * @return string the string representation of the object or null
     */
    public function serialize(): string
    {
        return serialize($this->items);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator.
     *
     * @see http://php.net/manual/en/iteratoraggregate.getiterator.php
     *
     * @return ArrayIterator An instance of an object implementing <b>Iterator</b> or
     *                       <b>ArrayIterator</b>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object.
     *
     * @see http://php.net/manual/en/countable.count.php
     *
     * @return int The custom count as an integer.
     *             </p>
     *             <p>
     *             The return value is cast to an integer
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object.
     *
     * @see  http://php.net/manual/en/serializable.unserialize.php
     *
     * @param string $serialized <p>
     *                           The string representation of the object.
     *                           </p>
     *
     * @return mixed|void
     */
    public function unserialize($serialized)
    {
        return $this->items = unserialize($serialized);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return bool true on success or false on failure.
     *              The return value will be casted to boolean if non-boolean was returned
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            $this->forget($offset);
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->get($offset) : null;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value <p>
     *                      The value to set.
     *                      </p>
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }
}
