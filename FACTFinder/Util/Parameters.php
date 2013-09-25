<?php
namespace FACTFinder\Util;

class Parameters implements \ArrayAccess, \Countable
{
    protected $parameters = array();

    /**
     * Optionally takes a URL query string or an array of initial parameters to
     * populate the object with.
     * In the case of an array this is just a convenience over creating an empty
     * object and setting the parameters manually with ->setAll($parameters).
     *
     * @param mixed $parameters Either a URL query string or an array of
     *        parameters to initialize the object with.
     */
    public function __construct($parameters = null)
    {
        if (is_string($parameters))
            $this->parseFromQueryString($parameters);
        else if (is_array($parameters))
            $this->setAll($parameters);
        else if (!is_null($parameters))
            throw new \InvalidArgumentException('Can only construct Parameters from string or array.');
    }

    private function parseFromQueryString($query)
    {
        if (strpos($query, '?') !== false)
        {
            $parts = explode('?', $query, 2);
            $query = $parts[1];
        }

        $pairs = explode('&', $query);
        foreach ($pairs AS $pair){
            $pair = explode('=', $pair);
            // Make sure that the parameter name actually contains an identifier
            if (preg_match('/^(?:$|\[[^\]]*\])/', $pair[0]))
                continue;
            if (count($pair) == 1)
                $pair[1] = '';

            // Use rawurldecode(), because encoding spaces as '+' is only for
            // legacy compatibility.
            $k = rawurldecode($pair[0]);
            $v = rawurldecode($pair[1]);

            if (preg_match('/^[^\]]+(?=\[[^\]]*\])/', $k, $matches))
                $this->add($matches[0], $v);
            else
                $this[$k] = $v;
        }
    }

    /**
     * Sets a parameter with one or multiple values.
     *
     * @param string $name  The parameter name.
     * @param mixed  $value A string or an array of strings. Nested arrays are
     *        not allowed. All other types are cast to strings.
     *
     * @throws InvalidArgumentException if no name is given or the value is a
     *         nested array.
     */
    public function offsetSet($name, $value)
    {
        if (is_null($name) || empty($name))
            throw new \InvalidArgumentException('No parameter name given.');

        $value = $this->sanitizeValue($value);

        $this->parameters[$name] = $value;
    }

    /**
     * Checks if a parameter has any values set.
     *
     * @param string $name The parameter name.
     *
     * @return bool
     */
    public function offsetExists($name)
    {
        return isset($this->parameters[$name]);
    }

    /**
     * Gets a parameter's value(s).
     *
     * @param string $name The parameter name.
     *
     * @return mixed The parameter's value(s). Either a single string or an
     *         array of multiple strings.
     *
     * @throws InvalidArgumentException if the name has no values set.
     */
	public function offsetGet($name)
    {
        if (!isset($this->parameters[$name]))
            throw new \InvalidArgumentException('Requested parameter has no value set.');

        return $this->parameters[$name];
    }

    /**
     * Clears all values for the given parameter.
     *
     * @param string $name The parameter name.
     */
    public function offsetUnset($name)
    {
        unset($this->parameters[$name]);
    }

    /**
     * Returns the number of values (not parameter names) stored in this object.
     *
     * @return int
     */
    public function count()
    {
        $count = 0;
        foreach ($this->parameters as $value)
            if (is_string($value))
                ++$count;
            else
                $count += count($value);
        return $count;
    }

    /**
     * Makes sure that the given value is not a nested array. It also converts
     * all non-strings to strings. Lastly, single-element arrays are converted
     * to string values.
     *
     * @param mixed $value The value to be sanitized.
     *
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

    /**
     * Returns a copy of the object's internal array used to store all
     * parameters.
     *
     * @return mixed[] An array of all parameters. All values are either strings
     *         or arrays of strings.
     */
    public function getArray()
    {
        return $this->parameters;
    }

    /**
     * Deletes all parameters.
     */
    public function clear()
    {
        $this->parameters = array();
    }

    /**
     * Set all given parameters. Previous values of the given parameters will be
     * replaced. Unmentioned parameters will retain their values.
     *
     * @param mixed[] $parameters An array of parameters. The keys are parameter
     *        names, the values are like those you would pass to offsetSet().
     */
    public function setAll($parameters)
    {
        foreach ($parameters as $k => $v)
            $this[$k] = $v;
    }

    /**
     * Alias for $parameters['name'] = 'value' notation, because offset indexing
     * cannot be chained with method calls.
     *
     * @param string $name  The parameter name.
     * @param mixed  $value A string or an array of strings. Nested arrays are
     *        not allowed. All other types are cast to strings.
     *
     * @throws InvalidArgumentException if no name is given or the value is a
     *         nested array.
     */
    public function set($name, $value)
    {
        $this[$name] = $value;
    }

    /**
     * Adds all given parameter values in addition to all existing values.
     *
     * @param mixed[] $parameters An array of parameters. The keys are parameter
     *        names, the values are like those you would pass to offsetSet().
     */
    public function addAll($parameters)
    {
        foreach ($parameters as $k => $v)
            $this->add($k, $v);
    }

    /**
     * Adds a parameter value. As opposed to offsetSet() existing values will
     * not be replaced.
     *
     * @param string $name  The parameter name.
     * @param mixed  $value A string or an array of strings. Nested arrays are
     *        not allowed. All other types are cast to strings.
     *
     * @throws InvalidArgumentException if no name is given or the value is a
     *         nested array.
     */
    public function add($name, $value)
    {
        if (is_null($name) || empty($name))
            throw new \InvalidArgumentException('No parameter name given.');

        $value = $this->sanitizeValue($value);

        if (is_string($value))
            $this->addString($name, $value);
        else
            $this->addArray($name, $value);
    }

    protected function addString($name, $value)
    {
        if (!isset($this[$name]))
            $this->parameters[$name] = $value;
        else if (is_array($this[$name]))
            $this->parameters[$name][] = $value;
        else
            $this->parameters[$name] = array($this[$name], $value);
    }

    protected function addArray($name, $value)
    {
        // Drop the keys, so that array_merge won't overwrite any existing ones.
        $value = array_values($value);
        if (!isset($this[$name]))
            $this->parameters[$name] = $value;
        else
        {
            if (!is_array($this[$name]))
                $oldValue = array($this[$name]);
            else
                $oldValue = $this[$name];

            $this->parameters[$name] = array_merge($oldValue, $value);
        }
    }

    /**
     * Returns a URL query string based on the set parameters for use in a
     * request to a PHP server. That is, PHP's built-in functions will correctly
     * recreate the structure of this object's internal array.
     * Note however, that PHP will replace all non-letter, non-digit characters
     * in parameter names with underscores, when retrieving GET parameters.
     *
     * @return string The query string.
     */
    public function toPhpQueryString()
    {
        // Specify '&' explicitly. Otherwise, some servers use '&amp;'.
        $queryString = http_build_query($this->parameters, '', '&');

        // Prior to PHP 5.4 http_build_query() cannot follow RFC 3986. Hence it
        // encodes spaces as +'s and encodes ~ although it shouldn't. We need to
        // fix this.
        // http://stackoverflow.com/a/9265295/1633117
        $queryString = str_replace(
            array( '+'   , '%7E' ),
            array( '%20' , '~'   ),
            $queryString
        );
        return $queryString;
    }

    /**
     * Returns a URL query string based on the set parameters for use in a
     * request to a Java server. The difference to a PHP query string is that
     * Java does not need and cannot deal with square brackets in parameter
     * names. Hence, parameters with multiple names will simply be repeated.
     *
     * @return string The query string.
     */
    public function toJavaQueryString()
    {
        // Specify '&' explicitly. Otherwise, some servers use '&amp;'.
        $result = $this->toPhpQueryString();
        // The following preg_replace removes all []-indices from array
        // parameter names, because tomcat doesn't need them.
        return preg_replace(
            '/
            %5B       # URL encoded "["
            (?:       # start non-capturing group
              (?!%5D) # make sure the next character does not start "%5D"
              [^=&]   # consume the character if it is no "=" or "&"
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

    /**
     * Returns an array of strings which are valid HTTP Header fields with field
     * name and value. Multiple values are joined with commas as per RFC 2616.
     */
    public function toHttpHeaderFields()
    {
        $result = array();

        foreach ($this->parameters as $name => $value)
        {
            if (is_array($value))
                $value = implode(',', $value);

            // TODO: Warn about invalid field names?
            $result[] = "$name: $value";
        }

        return $result;
    }
}
