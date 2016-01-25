<?php
namespace ValueObjectTest;

use ValueObject\ImmutableValueObject;

class ValueObjectTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateValueObjects()
    {
        $emailObject = new Email('e.test@mail.org');

        $this->assertEquals('e.test@mail.org', $emailObject);

        $cloneEmailObject = $emailObject->changeTo('j.test@mail.org');
        $this->assertEquals('e.test@mail.org', $emailObject);
        $this->assertEquals('j.test@mail.org', $cloneEmailObject);
        $this->assertTrue($cloneEmailObject->compareTo('j.test@mail.org'));
    }

    public function testChangeAttribute()
    {
        try {
            $email = new Email('e.test@mail.org');
            $email->__construct('test@mail.org');
        } catch (\LogicException $e) {
            $error = 'You can not change your immutable value object.';
            $this->assertEquals($error, $e->getMessage());
            return;
        }

        $this->fail();
    }

    public function tesUnitQ()
    {
        $unitQ = new UnitQ([1, 'Gcal.']);
        $this->assertEquals(1, $unitQ->getQ());
        $this->assertEquals('1', $unitQ);
        $this->assertEquals('Gcal.', $unitQ->getLabel());

        $newUnitQ = $unitQ->toGigaJoule();
        $this->assertEquals('GJ.', $newUnitQ->getLabel());
        $this->assertEquals(0, $newUnitQ->compareTo(new UnitQ([0.239 * 1,'GJ.'])));
        $this->assertEquals(-1, $newUnitQ->compareTo(new UnitQ([0.239 * 1 + .1,'GJ.'])));
        $this->assertEquals(1, $newUnitQ->compareTo(new UnitQ([0.239 * 1 - .1,'GJ.'])));

        $this->assertEquals(0, $newUnitQ->compareTo(new UnitQ([1,'Gcal.'])));
        $this->assertEquals(-1, $newUnitQ->compareTo(new UnitQ([1.1,'Gcal.'])));
        $this->assertEquals(1, $newUnitQ->compareTo(new UnitQ([0.9,'Gcal.'])));

        $this->assertEquals(0, $unitQ->compareTo([4.1841,'GJ.']));
    }
}

final class Email
{
    use ImmutableValueObject;

    private $email;

    /**
     * @param static $valueObject
     * @return boolean
     */
    protected function compare($valueObject)
    {
        return $this->email === $valueObject->email;
    }

    /**
     * @param $value
     * @return mixed
     * @throws \LogicException if value isn't valid.
     */
    protected function setAttributes($value)
    {
        $this->email = $value;
    }
}

/**
 * Class UnitQ represent units of energy.
 * @package ValueObjectTest
 */
final class UnitQ
{
    use ImmutableValueObject;

    private $q;
    private $label;

    public function getQ()
    {
        return $this->q;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function toGigaCalories()
    {
        if ($this->label === 'Gcal.') {
            return $this->changeTo([$this->q, 'Gcal.']);
        }

        $k = $this->getMapValues()[$this->label];
        $q = $this->getQ();
        return $this->changeTo([$q * $k, 'Gcal.']);
    }

    public function toGigaJoule()
    {
        return $this->changeTo([$this->toGigaCalories()->getQ() / 0.239, 'GJ.']);
    }

    /**
     * Rewrite parent::__toString
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getQ();
    }

    /**
     * Coefficients are given as gigacalories.
     * @return array
     */
    private function getMapValues()
    {
        return [
            'Gcal.' => 1,
            'GJ.' => 0.239,
            //...
        ];
    }

    /**
     * @param $value
     */
    protected function setAttributes($value)
    {
        $this->label = $value[1];
        $this->q = $value[0];
    }

    /**
     * Returns -1,0,1
     * @param UnitQ $value
     * @return integer
     */
    protected function compare($value)
    {
        $thisQ = $this->toGigaCalories()->getQ();
        $comparedQ = $value->toGigaCalories()->getQ();

        if (function_exists('bccomp')) {
            return bccomp($thisQ, $comparedQ);
        }

        if (function_exists('gmp_cmp')) {
            return gmp_cmp($thisQ, $comparedQ);
        }

        if (abs($thisQ - $comparedQ) < 0.00001) {
            return 0;
        }

        if ($thisQ < $comparedQ) {
            return -1;
        }

        return 1;
    }
}