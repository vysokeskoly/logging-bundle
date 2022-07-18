<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\Fixtures\CircularReference;

class Bar
{
    public function __construct(private Foo $foo)
    {
    }

    public function getFoo(): Foo
    {
        return $this->foo;
    }
}
