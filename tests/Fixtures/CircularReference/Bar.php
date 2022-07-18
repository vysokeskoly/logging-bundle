<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\Fixtures\CircularReference;

class Bar
{
    private Foo $foo;

    public function __construct(Foo $foo)
    {
        $this->foo = $foo;
    }

    public function getFoo(): Foo
    {
        return $this->foo;
    }
}
