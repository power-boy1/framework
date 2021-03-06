<?php
/**
 * Bluz Framework Component
 *
 * @copyright Bluz PHP Team
 * @link https://github.com/bluzphp/framework
 */

/**
 * @namespace
 */
namespace Bluz\Validator\Rule;

use Bluz\Validator\Exception\ComponentException;

/**
 * Class Ip
 * @package Bluz\Validator\Rule
 */
class Ip extends AbstractRule
{
    /**
     * @var int
     */
    protected $options;

    /**
     * @var array
     */
    protected $networkRange;

    /**
     * @param null $options
     * @throws \Bluz\Validator\Exception\ComponentException
     */
    public function __construct($options = null)
    {
        if (is_int($options)) {
            $this->options = $options;
            return;
        }

        $this->networkRange = $this->parseRange($options);
    }

    /**
     * @param string $input
     * @return array
     * @throws \Bluz\Validator\Exception\ComponentException
     */
    protected function parseRange($input)
    {
        if ($input === null || $input == '*' || $input == '*.*.*.*'
            || $input == '0.0.0.0-255.255.255.255') {
            return null;
        }

        $range = array('min' => null, 'max' => null, 'mask' => null);

        if (strpos($input, '-') !== false) {
            list($range['min'], $range['max']) = explode('-', $input);
        } elseif (strpos($input, '*') !== false) {
            $this->parseRangeUsingWildcards($input, $range);
        } elseif (strpos($input, '/') !== false) {
            $this->parseRangeUsingCidr($input, $range);
        } else {
            throw new ComponentException('Invalid network range');
        }

        if (!$this->verifyAddress($range['min'])) {
            throw new ComponentException('Invalid network range');
        }

        if (isset($range['max']) && !$this->verifyAddress($range['max'])) {
            throw new ComponentException('Invalid network range');
        }

        return $range;
    }

    /**
     * @param string $input
     * @param string $char
     */
    protected function fillAddress(&$input, $char = '*')
    {
        while (substr_count($input, '.') < 3) {
            $input .= '.' . $char;
        }
    }

    /**
     * @param string $input
     * @param array $range
     */
    protected function parseRangeUsingWildcards($input, &$range)
    {
        $this->fillAddress($input);

        $range['min'] = strtr($input, '*', '0');
        $range['max'] = str_replace('*', '255', $input);
    }

    /**
     * @param string $input
     * @param array $range
     * @throws \Bluz\Validator\Exception\ComponentException
     */
    protected function parseRangeUsingCidr($input, &$range)
    {
        $input = explode('/', $input);
        $this->fillAddress($input[0], '0');

        $range['min'] = $input[0];
        $isAddressMask = strpos($input[1], '.') !== false;

        if ($isAddressMask && $this->verifyAddress($input[1])) {
            $range['mask'] = sprintf('%032b', ip2long($input[1]));

            return ;
        }

        if ($isAddressMask || $input[1] < 8 || $input[1] > 30) {
            throw new ComponentException('Invalid network mask');
        }

        $range['mask'] = sprintf('%032b', ip2long(long2ip(~(pow(2, (32 - $input[1])) - 1))));
    }

    /**
     * @param string $input
     * @return bool
     */
    public function validate($input)
    {
        return $this->verifyAddress($input) && $this->verifyNetwork($input);
    }

    /**
     * @param string $address
     * @return bool
     */
    protected function verifyAddress($address)
    {
        return (boolean) filter_var(
            $address,
            FILTER_VALIDATE_IP,
            array(
                'flags' => $this->options
            )
        );
    }

    /**
     * @param string $input
     * @return bool
     */
    protected function verifyNetwork($input)
    {
        if ($this->networkRange === null) {
            return true;
        }

        if (isset($this->networkRange['mask'])) {
            return $this->belongsToSubnet($input);
        }

        $input = sprintf('%u', ip2long($input));

        $min = sprintf('%u', ip2long($this->networkRange['min']));
        $max = sprintf('%u', ip2long($this->networkRange['max']));

        return ($input >= $min) && ($input <= $max);
    }

    /**
     * @param string $input
     * @return bool
     */
    protected function belongsToSubnet($input)
    {
        $range = $this->networkRange;
        $min = sprintf('%032b', ip2long($range['min']));
        $input = sprintf('%032b', ip2long($input));

        return ($input & $range['mask']) === ($min & $range['mask']);
    }

    /**
     * Get error template
     *
     * @return string
     */
    public function getTemplate()
    {
        if ($this->networkRange) {
            $message = $this->networkRange['min'];
            if (isset($this->networkRange['max'])) {
                $message .= '-' . $this->networkRange['max'];
            } else {
                $message .= '/' . long2ip($this->networkRange['mask']);
            }
            return __('{{name}} must be an IP address in the "%s" range', $message);
        } else {
            return __('{{name}} must be an IP address');
        }
    }
}
