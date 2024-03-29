<?php

namespace XPBot\System\Utils;

class Params extends \ArrayObject implements \Countable
{
    protected $_pure;
    protected $_prepared;

    /**
     * @param array|null|object $command Command string.
     */
    public function __construct($command)
    {
        $this->_pure = str_getcsv($command, ' ', '"', "\\");

        for ($i = 0, $count = count($this->_pure); $i < $count; $i++) {
            $match = '';
            $res   = (preg_match('/^--([a-zA-Z][a-zA-Z0-9\_]*)$/si', $this->_pure[$i], $match) || preg_match('/^-([a-zA-Z][a-zA-Z0-9\_]*)$/si', $this->_pure[$i], $match));

            if (
                $res &&
                (isset($this->_pure[$i + 1]) &&
                !preg_match('/^-{1,2}([a-zA-Z][a-zA-Z0-9\_]*)$/si', $this->_pure[$i + 1]))
            ) {
                $this->_prepared[$match[1]] = $this->_pure[$i + 1];
                $i++;
            } elseif (
                $res &&
                (!isset($this->_pure[$i + 1]) || preg_match('/^-{1,2}([a-zA-Z][a-zA-Z0-9\_]*)$/si', $this->_pure[$i + 1]))
            ) {
                $this->_prepared[$match[1]] = true;
            } else {
                $this->_prepared[] = $this->_pure[$i];
            }
        }
    }

    public function __isset($name)
    {
        return (isset($this->_prepared[$name]) && !empty($this->_prepared[$name]) && $this->_prepared[$name] != "\0");
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->_prepared);
    }

    public function offsetGet($index)
    {

        return isset($this->_prepared[$index]) ?
            $this->_prepared[$index] :
            null;
    }

    public function append($value)
    {
        array_push($this->_prepared, $value);
    }

    public function offsetSet($index, $newval)
    {
        $this->_prepared[$index] = $newval;
    }

    public function offsetExists($index)
    {
        return isset($this->_prepared[$index]);
    }

    public function offsetUnset($index)
    {
        unset($this->_prepared[$index]);
    }

    /**
     * Gets all arguments in array.
     * @return mixed Array of arguments.
     */
    public function asArray()
    {
        return $this->_prepared;
    }

    public function count()
    {
        $count = 0;
        foreach($this->_prepared as $key => $value)
            if(is_int($key)) $count++;

        return $count;
    }
}

?>
