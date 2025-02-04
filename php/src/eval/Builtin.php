<?php

readonly class BuiltinFunctions {
    /**
     * @var BuiltinFunctionValue[] $functions
     */
    public array $functions;

    public function __construct() {
        $this->functions = [
            "len" => new BuiltinFunctionValue(function (array $arguments): IntegerValue | ErrorValue {
                if (count($arguments) !== 1) {
                    return new ErrorValue("wrong number of arguments. got=" . count($arguments) . ", want=1");
                }

                /** @var Value $argument */
                $argument = $arguments[0];

                if ($argument instanceof StringValue) {
                    return new IntegerValue(strlen($argument->value));
                }

                if ($argument instanceof ArrayValue) {
                    return new IntegerValue(count($argument->elements));
                }

                return new ErrorValue("argument to `len` not supported, got " . $argument->type());
            }),

            "first" => new BuiltinFunctionValue(function (array $arguments): Value | ErrorValue {
                if (count($arguments) !== 1) {
                    return new ErrorValue("wrong number of arguments. got=" . count($arguments) . ", want=1");
                }

                /** @var Value $argument */
                $argument = $arguments[0];

                if ($argument instanceof ArrayValue) {
                    if (count($argument->elements) > 0) {
                        return $argument->elements[0];
                    }

                    return NullValue::$NULL;
                }

                return new ErrorValue("argument to `first` must be ARRAY, got " . $argument->type());
            }),

            "last" => new BuiltinFunctionValue(function (array $arguments): Value | ErrorValue {
                if (count($arguments) !== 1) {
                    return new ErrorValue("wrong number of arguments. got=" . count($arguments) . ", want=1");
                }

                /** @var Value $argument */
                $argument = $arguments[0];

                if ($argument instanceof ArrayValue) {
                    $length = count($argument->elements);

                    if ($length > 0) {
                        return $argument->elements[$length - 1];
                    }

                    return NullValue::$NULL;
                }

                return new ErrorValue("argument to `last` must be ARRAY, got " . $argument->type());
            }),

            "rest" => new BuiltinFunctionValue(function (array $arguments): Value | ErrorValue {
                if (count($arguments) !== 1) {
                    return new ErrorValue("wrong number of arguments. got=" . count($arguments) . ", want=1");
                }

                /** @var Value $argument */
                $argument = $arguments[0];

                if ($argument instanceof ArrayValue) {
                    $length = count($argument->elements);

                    if ($length > 0) {
                        $newElements = array_slice($argument->elements, 1);
                        return new ArrayValue($newElements);
                    }

                    return NullValue::$NULL;
                }

                return new ErrorValue("argument to `rest` must be ARRAY, got " . $argument->type());
            }),

            "push" => new BuiltinFunctionValue(function (array $arguments): ArrayValue | ErrorValue {
                if (count($arguments) !== 2) {
                    return new ErrorValue("wrong number of arguments. got=" . count($arguments) . ", want=2");
                }

                /** @var Value $argument */
                $argument = $arguments[0];

                if ($argument instanceof ArrayValue) {
                    $length = count($argument->elements);
                    $newElements = array_slice($argument->elements, 0, $length);
                    $newElements[] = $arguments[1];
                    return new ArrayValue($newElements);
                }

                return new ErrorValue("argument to `push` must be ARRAY, got " . $argument->type());
            }),

            "puts" => new BuiltinFunctionValue(function (array $arguments): NullValue {
                foreach ($arguments as $argument) {
                    echo $argument->inspect() . "\n";
                }

                return NullValue::$NULL;
            }),

            // Added to make the language more expressive without extending the syntax

            "is_null" => new BuiltinFunctionValue(function (array $arguments): BooleanValue | ErrorValue {
                if (count($arguments) !== 1) {
                    return new ErrorValue("wrong number of arguments. got=" . count($arguments) . ", want=1");
                }

                /** @var Value $argument */
                $argument = $arguments[0];

                return BooleanValue::fromBool($argument === NullValue::$NULL);
            }),

            "stdin_read" => new BuiltinFunctionValue(function (array $arguments): Value | ErrorValue {

                if (count($arguments) >= 1 && !$arguments[0] instanceof StringValue) {
                    return new ErrorValue("wrong type of arguments. got=" . $arguments[0]->type() . ", want=STRING");
                }

                $line = readline($arguments[0]->value ?? null);

                if ($line === false) {
                    return new ErrorValue("failed to read stdin");
                }

                return new StringValue($line);
            }),

            "object_set" => new BuiltinFunctionValue(function (array $arguments): Value | ErrorValue {
                if (count($arguments) !== 3) {
                    return new ErrorValue("wrong number of arguments. got=" . count($arguments) . ", want=3");
                }

                /** @var Value $argument */
                $argument = $arguments[0];

                if ($argument instanceof HashValue) {

                    if ($arguments[2] instanceof ErrorValue) {
                        return $arguments[2];
                    }

                    if ($argument->set($arguments[1], $arguments[2])) {
                        return $arguments[2];
                    }

                    return new ErrorValue("Invalid key");
                }

                return new ErrorValue("argument to `object_set` must be HASH, got " . $argument->type());
            }),

            "char_at" => new BuiltinFunctionValue(function (array $arguments): StringValue | ErrorValue {
                if (count($arguments) !== 2) {
                    return new ErrorValue("wrong number of arguments. got=" . count($arguments) . ", want=2");
                }

                /** @var Value $argument */
                $argument = $arguments[0];

                if ($argument instanceof StringValue) {
                    $index = $arguments[1];

                    if ($index instanceof IntegerValue) {
                        $length = strlen($argument->value);

                        if ($index->value >= 0 && $index->value < $length) {
                            return new StringValue($argument->value[$index->value]);
                        }

                        return new ErrorValue("Index out of bounds");
                    }

                    return new ErrorValue("argument to `char_at` must be INTEGER, got " . $index->type());
                }

                return new ErrorValue("argument to `char_at` must be STRING, got " . $argument->type());
            }),
        ];
    }
}
