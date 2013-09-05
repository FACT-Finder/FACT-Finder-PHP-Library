<?php
namespace FACTFinder\Core;

class Parameters implements \ArrayAccess
{
    protected $parameters = array();

    /**
     * Optionally takes an array of initial parameters to populate the object.
     * This is just a convenience over creating an empty object and setting the
     * parameters manually with ->setAll($parameters).
     * @param mixed[] $parameters Array of parameters to initialize the object
     *        with.
     */
    public function __construct($parameters = null)
    {
        if(!is_null($parameters))
            $this->setAll($parameters);
    }

    public function offsetExists($offset)
    {
        return isset($this->parameters[$offset]);
    }

	public function offsetGet($offset)
    {
        return $this->parameters[$offset];
    }

	public function offsetSet($offset, $value)
    {
        if (is_null($offset))
            throw new \InvalidArgumentException('No parameter name given.');

        $value = $this->sanitizeValue($value);

        $this->parameters[$offset] = $value;
    }

    /**
     * Makes sure that the given value is not a nested array. It also converts
     * all non-strings to strings. Lastly, single-element arrays are converted
     * to string values.
     * @param mixed $value The value to be sanitized.
     * @return mixed The sanitized value.
     */
    protected function sanitizeValue($value)
    {
        if (is_string($value))
            return $value;

        if (!is_array($value))
            return (string)$value;

        foreach ($value as $k => $v)
        {
            if (is_string($v))
                continue;
            else if (is_array($v))
                throw new \InvalidArgumentException(
                    'Value must not be an array of arrays.'
                );
            else
                $value[$k] = (string)$v;
        }

        if (count($value) == 1)
        {
            reset($value);
            $value = current($value);
        }

        return $value;
    }

	public function offsetUnset($offset)
    {
        unset($this->parameters[$offset]);
    }

    public function getArray()
    {
        return $this->parameters;
    }

    public function reset()
    {
        $this->parameters = array();
    }

    public function setAll($parameters)
    {
        foreach ($parameters as $k => $v)
            $this[$k] = $v;
    }

    public function addAll($parameters)
    {
        foreach ($parameters as $k => $v)
            $this->add($k, $v);
    }

    public function add($name, $value)
    {
        $value = $this->sanitizeValue($value);

        if (is_string($value))
            $this->addString($name, $value);
        else
            $this->addArray($name, $value);
    }

    protected function addString($name, $value)
    {
        if (!isset($this[$name]))
            $this[$name] = $value;
        else if (is_array($this[$name]))
            $this->parameters[$name][] = $value;
        else
            $this[$name] = array($this[$name], $value);
    }

    protected function addArray($name, $value)
    {
        // Drop the keys, so that array_merge won't overwrite any existing ones.
        $value = array_values($value);
        if (!isset($this[$name]))
            $this[$name] = $value;
        else
        {
            if (!is_array($this[$name]))
                $oldValue = array($this[$name]);
            else
                $oldValue = $this[$name];

            $this[$name] = array_merge($oldValue, $value);
        }
    }

    public function toPhpQueryString()
    {
        return http_build_query($this->parameters);
    }

    public function toTomcatQueryString()
    {
        $result = http_build_query($this->parameters);
        // The following preg_replace removes all []-indices from array
        // parameter names, because tomcat doesn't need them.
        return preg_replace(
            '/
            %5B       # URL encoded "["
            (?:       # start non-capturing group
              (?!%5D) # make sure the next character does not start "%5D"
              [^=]    # consume the character if it is no "="
            )*        # end of group; repeat
            %5D       # URL encoded "]"
            (?=       # lookahead to ensure the match is inside a parameter name
                      # and not a value
              [^=&]*= # make sure there is a "=" before the next "&"
            )         # end of lookahead
            /xi',
            '',
            $result
        );
    }
}
