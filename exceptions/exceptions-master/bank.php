<?php

class Bank
{
    private $balance;

    public function __construct($balance = 0)
    {
        $this->balance = $balance;
    }

    public function deposit($amount)
    {
        if ($amount <= 0)
            throw new LogicException("Amount must be greater then zero");

        $this->balance += $amount;
    }

    public function withdraw($amount)
    {
        if ($amount > $this->balance)
            throw new LogicException('Amount must be less then or equal to balance');
        $this->balance -= $amount;
    }

    public function getBalance()
    {
        return $this->balance;
    }
}


$bank = new Bank(100);

try {
    $bank->withdraw(200);
} catch (Exception $e) {
    echo $e->getMessage();
}
