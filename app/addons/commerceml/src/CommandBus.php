<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/


namespace Tygh\Addons\CommerceML;


use Tygh\Exceptions\DeveloperException;

/**
 * Class CommandBus
 *
 * @package Tygh\Addons\CommerceML
 */
class CommandBus
{
    /**
     * @var array<string, array{handler: callable, middleware: array<callable>}>
     */
    private $schema = [];

    /**
     * CommandBus constructor.
     *
     * @param array<string, array{handler: callable, middleware: array<callable>}> $schema Commands handlers schema
     */
    public function __construct(array $schema)
    {
        $this->schema = $schema;
    }

    /**
     * Dispatches command
     *
     * @param object $command Command
     *
     * @return \Tygh\Common\OperationResult
     */
    public function dispatch($command)
    {
        if (!is_object($command)) {
            throw new DeveloperException('Command must be object');
        }

        return $this->handleCommand($command);
    }

    /**
     * Gets command schema
     *
     * @param object $command Command
     *
     * @return array{handler: callable, middleware: array<callable>}
     */
    private function getCommandSchema($command)
    {
        $class = $this->getClassName($command);

        if (!isset($this->schema[$class])) {
            throw new DeveloperException(sprintf('Undefined handler for command %s', $class));
        }

        return $this->schema[$class];
    }

    /**
     * Gets command handler
     *
     * @param object $command Command
     *
     * @return callable
     */
    private function getCommandHandler($command)
    {
        $schema = $this->getCommandSchema($command);

        if (!isset($schema['handler']) || !is_callable($schema['handler'])) {
            throw new DeveloperException(sprintf('Undefined handler for command %s', $this->getClassName($command)));
        }

        return $schema['handler'];
    }

    /**
     * Gets command middleware list
     *
     * @param object $command Command
     *
     * @return array<callable>
     */
    private function getCommandMiddlewareList($command)
    {
        $schema = $this->getCommandSchema($command);

        if (empty($schema['middleware'])) {
            return [];
        }

        foreach ($schema['middleware'] as $callback) {
            if (!is_callable($callback)) {
                throw new DeveloperException(sprintf('Unrecognized middleware for command %s', $this->getClassName($command)));
            }
        }

        return $schema['middleware'];
    }

    /**
     * Handles command
     *
     * @param object $command Command
     *
     * @return \Tygh\Common\OperationResult
     */
    private function handleCommand($command)
    {
        $handler = $this->getCommandHandler($command);
        $middleware_list = $this->getCommandMiddlewareList($command);

        if (!$middleware_list) {
            return $handler($command);
        }

        $middleware = array_shift($middleware_list);

        $next_function = static function ($command) use (&$middleware_list, &$next_function, $handler) {
            $middleware = array_shift($middleware_list);

            if ($middleware) {
                return $middleware($command, $next_function);
            }

            return $handler($command);
        };

        return $middleware($command, $next_function);
    }

    /**
     * Gets class name
     *
     * @param object $command Command
     *
     * @return string
     */
    private function getClassName($command)
    {
        return trim(get_class($command), '\\');
    }
}
