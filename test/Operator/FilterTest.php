<?php

namespace Amp\Pipeline\Operator;

use Amp\PHPUnit\AsyncTestCase;
use Amp\PHPUnit\TestException;
use Amp\Pipeline;
use Amp\Pipeline\AsyncGenerator;
use Amp\Pipeline\Emitter;

class FilterTest extends AsyncTestCase
{
    public function testNoValuesEmitted(): void
    {
        $source = new Emitter;

        $pipeline = $source->asPipeline()->pipe(Pipeline\filter($this->createCallback(0)));

        $source->complete();

        Pipeline\discard($pipeline);
    }

    public function testValuesEmitted(): void
    {
        $count = 0;
        $values = [1, 2, 3];
        $expected = [1, 3];
        $generator = new AsyncGenerator(function () use ($values) {
            foreach ($values as $value) {
                yield $value;
            }
        });

        $pipeline = $generator->pipe(Pipeline\filter(function ($value) use (&$count): bool {
            ++$count;
            return (bool) ($value & 1);
        }));

        while (null !== $value = $pipeline->continue()) {
            self::assertSame(\array_shift($expected), $value);
        }

        self::assertSame(3, $count);
    }

    /**
     * @depends testValuesEmitted
     */
    public function testCallbackThrows(): void
    {
        $values = [1, 2, 3];
        $exception = new TestException;
        $generator = new AsyncGenerator(function () use ($values) {
            foreach ($values as $value) {
                yield $value;
            }
        });

        $pipeline = $generator->pipe(Pipeline\filter(fn () => throw $exception));

        $this->expectExceptionObject($exception);

        $pipeline->continue();
    }

    public function testPipelineFails(): void
    {
        $exception = new TestException;
        $source = new Emitter;

        $pipeline = $source->asPipeline()->pipe(Pipeline\filter($this->createCallback(0)));

        $source->error($exception);

        $this->expectExceptionObject($exception);

        $pipeline->continue();
    }
}
