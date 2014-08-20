<?php
namespace BahulNeel\Functional;

use ArrayObject;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;

class ProtocolTest extends PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        if (version_compare(PHP_VERSION, '5.5.0', '<')) {
            Sequence::extend(gettype([]), "BahulNeel\Functional\ArraySequence");
            Sequence::extend(gettype(null), "BahulNeel\Functional\NullSequence");
            Sequence::extend("ArrayObject", "BahulNeel\Functional\ArrayObjectSequence");
        } else {
            Sequence::extend(gettype([]), ArraySequence::class);
            Sequence::extend(gettype(null), NullSequence::class);
            Sequence::extend(ArrayObject::class, ArrayObjectSequence::class);
        }
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testBadProtocol()
    {
        BadProtocol::extend("ArrayObject", "BahulNeel\Functional\BadSequence");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testBadSequence()
    {
        Sequence::extend("ArrayObject", "BahulNeel\Functional\BadSequence");
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
