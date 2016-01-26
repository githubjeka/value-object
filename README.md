# Trait Value Object
Help to create immutable Value Objects

[![Build Status](https://travis-ci.org/githubjeka/value-object.svg?branch=master)](https://travis-ci.org/githubjeka/value-object)

## Default API

Two methods [`compareTo`](https://github.com/githubjeka/value-object/blob/master/src/ImmutableValueObject.php#L64-L86) 
and [`changeTo`](https://github.com/githubjeka/value-object/blob/master/src/ImmutableValueObject.php#L64-L86):

```php
// Value Object is a Metre(['1', 'centimeter'])
$metre = new Metre(['1', 'cm']);
$metre->compareTo([2,'cm']); // returns -1
$metre->compareTo([.1,'m']); // returns 0
$metre->compareTo([1,'mm']); // returns 1
$metre->changeTo([1,'m']) //returns new  Metre(['1', 'm']), $metre is  Metre(['1', 'centimeter'])

//user API via changeTo
$metre->toMillimeter(); // new Metre(['10', 'mm']), $metre is  Metre(['1', 'centimeter'])
$metre->toMillimeter()->add([1,'mm'])->getAmount();  //returns 11

// rewrite __toString()
echo $metre // returns '1'
```

### a little more

```php
final class Money
{
    use ImmutableValueObject;
    
    private $amount;
    private $currency;
        
    protected function compare($valueObject)
    {
        return bccomp($this->toUsd()->amount, $valueObject->toUsd()->amount);
    }
    
    protected function setAttributes($value)
    {
        // ... validate value
        
        $this->currency = $value[1];
        $this->amount = $value[0];
    }
    
    private function getCurrencies()
    {
        return [
            'usd' => 1,
            'rub' => 60,
            //...
        ];
    }
    
    public function toUsd()
    {
        if ($this->currency === 'usd') {
            return $this->changeTo([$this->amount, 'usd']);
        }        
        $k = $this->getCurrencies()[$this->currency];
        return $this->changeTo([$this->amount * $k, 'usd']);
    }
    
    public function toRub()
    {            
        $amount = $this->toUsd()->amount;
        $k = $this->getCurrencies()['rub'];
        return $this->changeTo([$amount / $k, 'rub']);
    }
}
```
