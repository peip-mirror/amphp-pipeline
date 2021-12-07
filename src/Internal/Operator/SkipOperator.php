<?php

namespace Amp\Pipeline\Internal\Operator;

use Amp\Pipeline\AsyncGenerator;
use Amp\Pipeline\PipelineOperator;
use Amp\Pipeline\Pipeline;

/**
 * @template TValue
 * @template-implements PipelineOperator<TValue, TValue>
 *
 * @internal
 */
final class SkipOperator implements PipelineOperator
{
    public function __construct(
        private int $count
    ) {
        if ($count < 0) {
            throw new \Error('Number of items to skip must be a non-negative integer');
        }
    }

    public function pipe(Pipeline $pipeline): Pipeline
    {
        return new AsyncGenerator(function () use ($pipeline): \Generator {
            $skipped = 0;
            foreach ($pipeline as $value) {
                if (++$skipped > $this->count) {
                    yield $value;
                }
            }
        });
    }
}
