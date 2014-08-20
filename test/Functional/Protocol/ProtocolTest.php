<?php
namespace Functional\Protocol;

use ArrayObject;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;

class ProtocolTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Sequence::extend("array", "Functional\Protocol\ArraySequence");
        Sequence::extend("NULL", "Functional\Protocol\NullSequence");
        Sequence::extend("ArrayObject", "Functional\Protocol\ArrayObjectSequence");
    }
    
    /**
     * @expectedException \RuntimeException
     */
    public function testBadProtocol()
    {
        BadProtocol::extend("ArrayObject", "Functional\Protocol\BadSequence");
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testBadSequence()
    {
        Sequence::extend("ArrayObject", "Functional\Protocol\BadSequence");
    }
    
    public function testArray()
    {
        $array = [1, 2, 3, 4];
        $this->assertEquals(1, Sequence::first($array));
        $this->assertEquals([2, 3, 4], Sequence::rest($array));
    }
    
    public function testNull()
    {
        $this->assertEquals("nil", Sequence::first(null));
        $this->assertEquals("also nil", Sequence::rest(null));
    }
    
    public function testArrayObject()
    {
        $array = new ArrayObject([1, 2, 3, 4]);
        $this->assertEquals(1, Sequence::first($array));
        $this->assertEquals([2, 3, 4], Sequence::rest($array)->getArrayCopy());
    }
}

interface SequenceInterface
{
    public static function first($coll);
    
    public static function rest($coll);
}

class Sequence implements SequenceInterface 
{
    use Protocol;

    public static function first($coll)
    {
        return self::invoke(__FUNCTION__, func_get_args());
    }
    
    public static function rest($coll)
    {
        return self::invoke(__FUNCTION__, func_get_args());
    }
}

class ArraySequence implements SequenceInterface
{
    public static function first($coll)
    {
        if (!count($coll)) {
            return null;
        }

        return $coll[0];
    }

    public static function rest($coll)
    {
        return array_slice($coll, 1);
    }

}

class ArrayObjectSequence extends ArraySequence
{
    public static function rest($coll)
    {
        return new ArrayObject(Sequence::rest($coll->getArrayCopy()));
    }
}

class NullSequence implements SequenceInterface
{
    public static function first($coll)
    {
        return "nil";
    }

    public static function rest($coll)
    {
        return "also nil";
    }

}

class BadProtocol
{
    use Protocol;
}

class BadSequence
{
    
}

