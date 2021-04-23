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

namespace Tygh\Addons\CommerceML\Xml;

use SimpleXMLElement as BaseSimpleXmlElement;

/**
 * Class SimpleXmlElement
 *
 * @package Tygh\Addons\CommerceML
 *
 * phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint
 */
class SimpleXmlElement extends BaseSimpleXmlElement
{
    /**
     * @var array<string, string> Xml nodes/attributes aliases
     */
    public static $aliases;

    /**
     * Finds value alias
     *
     * @param string $value Xml node name or attrbute name
     *
     * @return string
     */
    public static function findAlias($value)
    {
        if (self::$aliases === null) {
            self::$aliases = fn_get_schema('cml', 'aliases');
        }

        return isset(self::$aliases[$value]) ? self::$aliases[$value] : $value;
    }

    /**
     * Parses node path to nodes
     *
     * @param string $node_path Node path (example: catalog.product.id)
     *
     * @return array{0: list<string>, 1: null|string}
     */
    public static function parsePath($node_path)
    {
        $attribute = null;
        $nodes = explode('/', $node_path);
        $last_node = array_pop($nodes);

        if (strpos($last_node, '@') !== false) {
            list($last_node, $attribute) = explode('@', $last_node);
            $attribute = self::findAlias($attribute);
        }

        if ($last_node) {
            $nodes[] = $last_node;
        }

        foreach ($nodes as &$node_name) {
            $node_name = self::findAlias($node_name);
        }
        unset($node_name);

        return [$nodes, $attribute];
    }

    /**
     * Builds path
     *
     * @param array<string> $nodes     Nodes
     * @param null|string   $attribute Attribute
     *
     * @return string
     */
    public static function buildPath(array $nodes, $attribute = null)
    {
        $path = implode('/', $nodes);

        if ($attribute) {
            $path = sprintf('%s@%s', $path, $attribute);
        }

        return $path;
    }

    /**
     * Normailizes path
     *
     * @param string $node_path Node path
     *
     * @return string
     */
    public static function normalizePath($node_path)
    {
        list($nodes, $attribute) = self::parsePath($node_path);

        return self::buildPath($nodes, $attribute);
    }

    /**
     * Normalize xml value as boolean
     *
     * @param string $value Value
     *
     * @return bool
     */
    public static function normalizeBool($value)
    {
        return in_array(strtolower((string) $value), [
            'true',
            '1',
            'required',
            'yes',
            self::findAlias('yes'),
            self::findAlias('true')
        ], true);
    }

    /**
     * Gets xml node by path
     *
     * @param string     $node_path     Node path (example: catalog.product.id)
     * @param mixed|null $default_value Default value if node not found
     *
     * @return \Tygh\Addons\CommerceML\Xml\SimpleXmlElement|array<\Tygh\Addons\CommerceML\Xml\SimpleXmlElement>|\Tygh\Addons\CommerceML\Xml\SimpleXmlElement[]|null|mixed
     */
    public function get($node_path, $default_value = null)
    {
        $xml = $this;
        list($nodes, $attribute) = self::parsePath($node_path);

        foreach ($nodes as $node_name) {
            if (!isset($xml->{$node_name})) {
                return $default_value;
            }

            $xml = $xml->{$node_name};
        }

        if ($attribute) {
            return isset($xml[$attribute]) ? $xml[$attribute] : $default_value;
        }

        return $xml;
    }

    /**
     * Checks if node exsits
     *
     * @param string $node_path Node path (example: catalog.product.id)
     *
     * @return bool
     */
    public function has($node_path)
    {
        return $this->get($node_path) !== null;
    }

    /**
     * Checks if node exsits and not empty
     *
     * @param string $node_path Node path (example: catalog.product.id)
     *
     * @return bool
     */
    public function hasAndNotEmpty($node_path)
    {
        return !empty($this->getAsString($node_path));
    }

    /**
     * Gets node value as string
     *
     * @param string $node_path     Node path (example: catalog.product.id)
     * @param string $default_value Default value if node not found
     *
     * @return string
     */
    public function getAsString($node_path, $default_value = '')
    {
        $element = $this->get($node_path);

        /**
         * @psalm-suppress PossiblyInvalidCast
         */
        return $element ? (string) $element : (string) $default_value;
    }

    /**
     * Gets node value as string in enum
     *
     * @param string        $node_path     Node path (example: catalog.product.id)
     * @param array<string> $list          List of available items
     * @param string        $default_value Default value if node not found
     *
     * @return string
     */
    public function getAsEnumItem($node_path, array $list, $default_value = '')
    {
        $element = $this->getAsString($node_path);

        if (!$element) {
            return $default_value;
        }

        $element = mb_strtolower($element);

        foreach ($list as $item) {
            $alias = self::findAlias($item);

            if ($element === mb_strtolower($alias) || $element === mb_strtolower($item)) {
                return $item;
            }
        }

        return (string) $default_value;
    }

    /**
     * Gets node value as bool
     *
     * @param string $node_path     Node path (example: catalog.product.id)
     * @param bool   $default_value Default value if node not found
     *
     * @return bool
     */
    public function getAsBool($node_path, $default_value = false)
    {
        $element = $this->get($node_path);

        /**
         * @psalm-suppress PossiblyInvalidCast
         */
        return $element ? self::normalizeBool((string) $element) : (bool) $default_value;
    }

    /**
     * Gets node value as float
     *
     * @param string $node_path     Node path (example: catalog.product.id)
     * @param float  $default_value Default value if node not found
     *
     * @return float
     */
    public function getAsFloat($node_path, $default_value = 0.0)
    {
        $element = $this->get($node_path);

        /**
         * @psalm-suppress PossiblyInvalidCast
         */
        return $element ? (float) $element : (float) $default_value;
    }

    /**
     * Gets node value as int
     *
     * @param string $node_path     Node path (example: catalog.product.id)
     * @param int    $default_value Default value if node not found
     *
     * @return int
     */
    public function getAsInt($node_path, $default_value = 0)
    {
        $element = $this->get($node_path);

        /**
         * @psalm-suppress PossiblyInvalidCast
         */
        return $element ? (int) $element : (int) $default_value;
    }

    /**
     * Gets node value as string list
     *
     * @param string                   $node_path     Node path (example: catalog.product.id)
     * @param array<array-key, string> $default_value Default value if node not found
     *
     * @return array<array-key, string>
     */
    public function getAsStringList($node_path, array $default_value = [])
    {
        $element = $this->get($node_path);

        if ($element === null) {
            return $default_value;
        }

        $list = [];

        foreach ($element as $item) {
            $list[] = (string) $item;
        }

        return $list;
    }
}
