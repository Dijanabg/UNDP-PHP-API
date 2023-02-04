<?php

class MyArray
{
    private $elements;

    public function __construct(array $elements)
    {
        $this->elements = $elements;
    }

    public function getElement($index)
    {
        if ($index < 0 || $index >= count($this->elements))
            throw new OutOfRangeException('Index out of range');
        return $this->elements[$index];
    }

    public function getElement2($index)
    {
        if (!array_key_exists($index, $this->elements))
            throw new OutOfBoundsException('Index out of bounds');
        return $this->elements[$index];
    }

    public function getElement3($index)
    {
        if ($index < 0 || $index >= count($this->elements))
            throw new RangeException("Index out of range");
        return $this->elements[$index];
    }

    public function writeArray()
    {
        for ($i = 0; $i <= count($this->elements); $i++) {
            if ($i >= count($this->elements))
                throw new OutOfBoundsException('Out of bounds');
            echo "{$this->elements[$i]},";
        }
    }

    public function chieldMethod($index)
    {
        if (!array_key_exists($index, $this->elements)) {
            throw new OutOfBoundsException('Out of bounds');
        }
        return $this->elements[$index];
    }

    public function parentMethod($index)
    {
        try {
            return $this->chieldMethod($index);
        } catch (RuntimeException $e) {
            echo "RuntimeException: " . $e->getMessage();
        }
    }
}


$myArray = new MyArray([1, 2, 3]);

try {
    // echo $myArray->getElement(5);
    // echo $myArray->getElement2(5);
    // echo $myArray->writeArray();
    // echo $myArray->getElement3(5);
    echo $myArray->chieldMethod(5);
} catch (RangeException $e) {
    echo 'RangeException: ' . $e->getMessage();
} catch (Exception $e) {
    echo 'Exception: ' . $e->getMessage();
}
