<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\Fixtures\CircularReference;

class Foo
{
    private Bar $bar;

    public function __construct()
    {
        $this->bar = new Bar($this);
    }

    public function getBar(): Bar
    {
        return $this->bar;
    }
}
