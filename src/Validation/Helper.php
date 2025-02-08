<?php
namespace App\Validation;

use Symfony\Component\PropertyAccess\PropertyAccess;

class Helper
{
    public static function parseMessage($violations, $translation = null)
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $errorMessages = [];
        foreach ($violations as $violation) {

            $accessor->setValue($errorMessages,
                $violation->getPropertyPath(),
                [$violation->getMessage()]);
        }
        return $errorMessages;
    }

    public static function parse(array $data, array $validators)
    {
        $result = $data;
        foreach ($validators as $path => $validator) {
            $keys = explode('.', $path);
            if (count($keys) > 0) {
                $value = $data;
                $found = false;
                foreach ($keys as $key) {
                    if (isset($value[$key])) {
                        $found = true;
                        $value = $value[$key];
                    } else {
                        $found = false;
                        break;
                    }
                }

                if ($found) {
                    $result[$path] = $value;
                }
                else {
                    $result[$path] = null;
                }
            }
        }
        return $result;
    }

    public static function evaluateValidator(array $options, $conditions)
    {
        $andConditions = [];
        foreach ($conditions as $andCondition) {
            if (isset($andCondition['operator'])) {
                $andConditions[] = self::evaluateCondition($options, $andCondition);
            } else {
                $orConditions = [];

                foreach ($andCondition as $orCondition) {
                    if (isset($orCondition['operator'])) {
                        $orConditions[] = self::evaluateCondition($options, $orCondition);
                    } else {
                        $andLogic = null;

                        foreach ($orCondition as $andCombined) {
                            if (isset($andCombined['operator'])) {
                                $evaluation = self::evaluateCondition($options, $andCombined);
                                $andLogic = $andLogic === null ? $evaluation : $andLogic && $evaluation;
                            }
                        }

                        if ($andLogic !== null) {
                            $orConditions[] = $andLogic;
                        }
                    }
                }

                $andConditions[] = $orConditions;
            }
        }

        $andConditions = array_map(
            function ($condition) {
                if (is_bool($condition)) {
                    return $condition;
                } else {
                    return array_reduce(
                        $condition,
                        function ($carry, $next) {
                            return $carry || $next;
                        },
                        false
                    );
                }
            },
            $andConditions
        );

        return array_reduce(
            $andConditions,
            function ($carry, $next) {
                return $carry && $next;
            },
            true
        );
    }

    public static function evaluateCondition(array $options, $condition)
    {
        if (!empty($condition['rightField'])) {
            $rightValue = self::getValueFromStringPath($options, $condition['rightField']);
            if(@$condition['path']) {
                $rightValue = array_map(function($v) use ($condition) {return $v[$condition['path']];}, $rightValue);
            }
        } else {
            $rightValue = $condition['rightValue'];
        }

        if (!empty(@$condition['leftField'])) {
            $leftValue = self::getValueFromStringPath($options, $condition['leftField']);
        } else {
            $leftValue = @$condition['leftValue'];
        }

        switch ($condition['operator']) {
            case 'notEmpty':
                return !empty($rightValue);
            case 'empty':
                return empty($rightValue);
            case 'equals':
                return $rightValue === $leftValue;
            case 'notEquals':
                return $rightValue !== $leftValue;
            case 'greaterThan':
                return $rightValue > $leftValue;
            case 'greaterThanOrEqual':
                return $rightValue >= $leftValue;
            case 'lessThan':
                return $rightValue < $leftValue;
            case 'in':
                return (is_array($leftValue)) ? in_array($rightValue, $leftValue) : in_array($leftValue, $rightValue);
            case 'notIn':
                return (is_array($leftValue)) ? !in_array($rightValue, $leftValue) : !in_array($leftValue, $rightValue);
            case 'anyIn':
                if (!is_array($leftValue) && !is_array($rightValue)) {
                    return false;
                } else {
                    return count(array_intersect($rightValue, $leftValue)) > 0;
                }
                break;
            case 'noneIn':
                if (!is_array($leftValue) && !is_array($rightValue)) {
                    return true;
                } else {
                    return !(count(array_intersect($rightValue, $leftValue)) > 0);
                }
                break;
            case 'always':
                return true;
            default:
                return false;
        }
    }

    public static function getValueFromStringPath(array $options, string $path)
    {
        $routes = self::getPathFromString($path);
        if (!count($routes)) {
            return null;
        }
        $firstPath = array_shift($routes);
        $value = self::getValue($options['data'], [$firstPath]);
        if (!count($routes)) {
            return $value;
        } else {
            return self::getValueFromPath($value, ...$routes);
        }
    }

    public static function getPathFromString(string $path)
    {
        $routes = [];
        $pointer = 0;
        $route = '';
        while ($pointer < strlen($path)) {
            if (in_array($path[$pointer], ['[', ']', '.', '"', "'"])) {
                if (strlen($route)) {
                    $routes[] = $route;
                }
                $route = '';
            } else {
                $route .= $path[$pointer];
            }
            $pointer++;
        }
        if (strlen($route)) {
            $routes[] = $route;
        }
        return $routes;
    }

    public static function getValue($data, array $path) {
        $result = '';
        if (count($path) > 0) {
            $value = $data;
            $found = false;
            foreach ($path as $key) {
                if (isset($value[$key])) {
                    $found = true;
                    $value = $value[$key];
                } else {
                    $found = false;
                    break;
                }
            }

            if ($found) {
                $result = $value;
            }
            else {
                $result = null;
            }
        }
        return $result;
    }

    public static function getValueFromPath($target, ...$indices)
    {
        $movingTarget = $target;

        foreach ($indices as $index) {
            $isArray = is_array($movingTarget) || $movingTarget instanceof \ArrayAccess;
            if (!$isArray || !isset($movingTarget[$index])) {
                return NULL;
            }

            $movingTarget = $movingTarget[$index];
        }

        return $movingTarget;
    }
}
