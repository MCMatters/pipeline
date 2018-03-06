<?php

declare(strict_types = 1);

namespace McMatters\Tests;

use InvalidArgumentException;
use McMatters\Pipeline\Pipeline;
use PHPUnit\Framework\TestCase;
use TypeError;

/**
 * Class PipelineTest
 *
 * @package McMatters\Tests
 */
class PipelineTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     * @throws \LogicException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testStrReplace()
    {
        $string = (new Pipeline('FooBar', 2))
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

    /**
     * @throws TypeError
     */
    public function testStrReplaceWithException()
    {
        $this->expectException(TypeError::class);

        (new Pipeline('FooBar', 2))
            ->pipe(['Foo', 'str_replace'], null, null)
            ->process();
    }

    /**
     * @throws InvalidArgumentException
     * @throws \LogicException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
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
