<?php

declare(strict_types = 1);

namespace McMatters\Pipeline;

use InvalidArgumentException;
use LogicException;

use function array_slice, call_user_func_array, count, is_callable;

use const false, null, true;

/**
 * Class Pipeline
 *
 * @package McMatters\Pipeline
 */
class Pipeline
{
    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var array
     */
    protected $stack = [];

    /**
     * @var int
     */
    protected $defaultPosition;

    /**
     * @var mixed
     */
    protected $defaultValue;

    /**
     * Pipeline constructor.
     *
     * @param mixed $data
     * @param int $defaultDataPosition
     * @param mixed $defaultValue
     */
    public function __construct(
        $data = null,
        int $defaultDataPosition = 0,
        $defaultValue = null
    ) {
        $this->data = $data;
        $this->defaultPosition = $defaultDataPosition;
        $this->defaultValue = $defaultValue;
    }

    /**
     * @param array|callable|string $callable
     * @param array $args
     *
     * @return \McMatters\Pipeline\Pipeline
     */
    public function pipe(callable $callable, ...$args): self
    {
        $this->stack[] = [
            'callable' => $callable,
            'args' => $args,
            'default' => $this->defaultValue,
            'position' => $this->defaultPosition,
            'referencable' => false,
        ];

        return $this;
    }

    /**
     * @param mixed $value
     *
     * @return \McMatters\Pipeline\Pipeline
     *
     * @throws \LogicException
     */
    public function default($value): self
    {
        $this->checkStackLength();
        $this->stack[$this->getLastStackKey()]['default'] = $value;

        return $this;
    }

    /**
     * @param int $position
     *
     * @return \McMatters\Pipeline\Pipeline
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function dataPosition(int $position): self
    {
        if ($position < 0) {
            throw new InvalidArgumentException('Position cannot be less than zero');
        }

        $this->checkStackLength();

        $this->stack[$this->getLastStackKey()]['position'] = $position;

        return $this;
    }

    /**
     * @return \McMatters\Pipeline\Pipeline
     *
     * @throws \LogicException
     */
    public function referencable(): self
    {
        $this->checkStackLength();

        $this->stack[$this->getLastStackKey()]['referencable'] = true;

        return $this;
    }

    /**
     * @return mixed
     */
    public function process()
    {
        foreach ($this->stack as $key => $item) {
            $args = $this->getArgs($item);

            $data = call_user_func_array($item['callable'], $args);

            $this->data = $this->getValue($data, $item['default']);

            unset($this->stack[$key]);
        }

        return $this->data;
    }

    /**
     * @return int
     */
    protected function getLastStackKey(): int
    {
        return count($this->stack) - 1;
    }

    /**
     * @param mixed $value
     * @param mixed $default
     *
     * @return mixed
     */
    protected function getValue($value, $default = null)
    {
        if (null !== $value) {
            return $value;
        }

        return is_callable($default) ? $default() : $default;
    }

    /**
     * @param array $item
     *
     * @return array
     */
    protected function getArgs(array $item): array
    {
        $data = $this->data;
        $args = array_slice($item['args'], 0, $item['position'], true);
        $end = array_slice($item['args'], $item['position'], count($item['args']) - $item['position']);

        if ($item['referencable']) {
            $args[] = &$data;
        } else {
            $args[] = $data;
        }

        foreach ($end as $value) {
            $args[] = $value;
        }

        return $args;
    }

    /**
     * @return void
     *
     * @throws \LogicException
     */
    protected function checkStackLength()
    {
        if (empty($this->stack)) {
            throw new LogicException('Stack can not be empty');
        }
    }
}
