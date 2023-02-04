<?php


function multiply($a, $b)
{
    return $a * $b;
}

class Calculator
{
    public function add($a, $b)
    {
        if ($a + $b > PHP_INT_MAX)
            throw new LengthException("Can't display a number");
        return $a + $b;
    }

    public function add2($a, $b)
    {
        try {
            if ($this->checkNumber($a) && $this->checkNumber($b))
                return $a + $b;
        } catch (LogicException $e) {
            echo "LogicException in add2: " . $e->getMessage();
        }
    }

    public function calculateSum($numbers)
    {
        assert(is_array($numbers), new AssertionError('Input must be an array'));
        return array_sum($numbers);
    }

    private function checkNumber($a)
    {
        if ($a < PHP_INT_MIN || $a > PHP_INT_MAX)
            throw new RangeException('Number out of range');
        return true;
    }

    public function subtract($a, $b)
    {
        if (!is_numeric($a) || !is_numeric($b))
            throw new InvalidArgumentException("Both arguments must be numeric");
        return $a - $b;
    }

    public function shiftNumber($shift, $number)
    {
        try {
            if (!is_numeric($shift))
                throw new TypeError("Shift must be a number");
            return $number >> $shift;
        } catch (ArithmeticError $ex) {
            throw new ArithmeticError($ex->getMessage());
        }
    }

    public function divide($a, $b)
    {
        if ($b == 0)
            throw new DivisionByZeroError('Division by zero.');
        if ($a / $b < 0.01 && $a / $b > 0)
            throw new UnderflowException('Result of division is too small');
        return $a / $b;
    }

    public function multiply($a, $b)
    {
        $p = $a * $b;
        if ($p > PHP_INT_MAX) {
            throw new OverflowException('The product exceeds the maximum value');
        }
        return $p;
    }

    public function performOperation($function, $a, $b)
    {
        if (!is_callable($function)) {
            throw new BadFunctionCallException("Invalid function: $function");
        }
        return call_user_func($function, $a, $b);
    }

    public function performOperation2($method, $a, $b)
    {
        switch ($method) {
            case 'add':
                return $this->add($a, $b);
            case 'subtract':
                return $this->subtract($a, $b);
            default:
                throw new BadMethodCallException("Invalid method: $method");
        }
    }

    public function calculate($expresison)
    {
        return eval($expresison);
    }
}


//try N*catch           N [1,...]
//try N*catch finally   N [0,...]
try {
    $calculator = new Calculator();
    // $result = $calculator->divide(10, 0);
    // $result = $calculator->performOperation('multiply', 5, 10);
    // $result = $calculator->performOperation2('divide', 5, 10);
    // $result = $calculator->divide(2, 0);
    // $result = $calculator->subtract(2, 0);
    // $result = $calculator->add(PHP_INT_MAX, PHP_INT_MAX);
    // $result = $calculator->multiply(2, PHP_INT_MAX);
    // $result = $calculator->add2(2, PHP_INT_MAX + PHP_INT_MAX);
    // echo "Result: $result";
    // $result = $calculator->add(2, 5);
    // $result = $calculator->divide(2, 500);
    // $result = $calculator->divide(-1, 0);
    // $object = json_decode('{"broj": 1}');
    // $result = $calculator->calculateSum([1, 2, 3]);
    // $result = $calculator->calculateSum($object);
    //8 -> 1000
    //1     0     0    0
    //2^3   2^2   2^1   2^0 

    // $result = $calculator->calculate('echo "error"');
    // $result = $calculator->shiftNumber('echo', 3);
    echo $calculator->add(5);
    // echo "Result: $result";
} catch (ArgumentCountError $e) {
    echo "ArgumentCountError: " . $e->getMessage();
} catch (Error $e) {
    echo "Error: " . $e->getMessage();
} finally {
    echo "<br>finally";
}



































// //Exception
// $prva = 10;
// $druga = 0;
// $niz = [1, 2, 3];

// try {
//     echo $prva / $druga;
// } catch (Error $e) {
//     echo $e;
// }
