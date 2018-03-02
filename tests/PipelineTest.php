<?php

declare(strict_types = 1);

namespace McMatters\Tests;

use McMatters\Pipeline\Pipeline;
use PHPUnit\Framework\TestCase;
use Throwable;

class PipelineTest extends TestCase
{
    public function testStrReplace()
    {
        $string = (new Pipeline('FooBar', null, 2))
            ->pipe('str_replace', 'Foo', 'Baz')
            ->pipe('str_replace', 'Bar', 'Test')
            ->process();

        $this->assertEquals('BazTest', $string);

        $string = (new Pipeline('Hello World'))
            ->pipe('str_replace', 'Hello', 'Goodbye')
            ->dataPosition(2)
            ->pipe('str_replace', 'World', 'Narnia')
            ->dataPosition(2)
            ->process();

        $this->assertEquals('Goodbye Narnia', $string);
    }

    public function testStrReplaceWithException()
    {
        $this->expectException(Throwable::class);

        (new Pipeline('FooBar', 'Foo', 2))
            ->pipe('str_replace', null, null, null)
            ->process();
    }

    public function testArray()
    {
        $data = [
            [
                'foo' => 'bar',
            ],
            [
                'baz' => 'test',
            ],
        ];

        $this->assertEquals(
            'foo',
            (new Pipeline($data))
                ->pipe('reset')
                ->referencable()
                ->pipe('key')
                ->referencable()
                ->process()
        );

        $this->assertEquals(
            ['foo' => 'bar'],
            (new Pipeline($data))
                ->pipe('reset')
                ->referencable()
                ->process()
        );

        $this->assertEquals(
            'baz',
            (new Pipeline($data))
                ->pipe('end')
                ->referencable()
                ->pipe('key')
                ->referencable()
                ->process()
        );
    }
}
