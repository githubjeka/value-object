<?php
namespace ValueObject;

/**
 * Represent the class for an immutable object value.
 * @see http://martinfowler.com/eaaCatalog/valueObject.html
 */
trait ImmutableValueObject
{
    /**
     * An immutable value of the Value Object.
     * @var mixed
     */
    private $significance;

    /**
     * Populates the Value Object from the $significance.
     * It will be called once inside __construct.
     * @param mixed $significance
     * @throws \InvalidArgumentException if value isn't valid.
     */
    abstract protected function setAttributes($significance);

    /**
     * It may be implemented in one of two variants:
     *
     * 1)   Returns true if $this equals $valueObject, otherwise false
     *
     * 2)   Returns 0 when $this equals $valueObject
     *      Returns 1 when $this greater than $valueObject
     *      Returns -1 when $this less than $valueObject
     *
     * @param static $valueObject
     * @return integer|boolean
     */
    abstract protected function compare($valueObject);

    /**
     * ImmutableValueObject constructor.
     * @param $significance
     */
    final public function __construct($significance)
    {
        if ($this->significance !== null) { //if passed $this->__construct($significance)
            throw new \LogicException(
                'You can not change your immutable value object.'
            );
        }
        $this->significance = $significance;

        $this->setAttributes($significance);
    }

    /**
     * Represents the Value Object as a string.
     * It can be rewritten, if necessary.
     * @return string
     */
    public function __toString()
    {
        return (string)$this->significance;
    }

    /**
     * Compares $this Value Object with a value which can be transmitted as a link to Value Object or $significance of Value Object
     * @param mixed $value
     * @return integer
     */
    final public function compareTo($value)
    {
        if ($value instanceof $this) {
            return $this->compare($value);
        }

        return $this->compare(new $this($value));
    }

    /**
     * Returns a cloned Value Object
     * @param $newValue
     * @return static
     */
    final public function changeTo($newValue)
    {
        return new static($newValue);
    }
}
