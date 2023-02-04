<?php

class BadNameException extends RuntimeException
{
}

class StupidPasswordException extends Exception
{
}

class BadLastNameException extends LogicException
{
}


class BadMarksException extends OutOfRangeException
{
}
class User
{
    private $age;
    private $name;
    private $password;
    private $birthday;
    private $lastName;
    private $mark;
    public function __construct($age, $name, $lastName, $birthday, $mark)
    {
        $this->age = $age;
        $this->birthday = $birthday;
        $this->setName($name);
        $this->setLastName($lastName);
        $this->setMark($mark);
    }

    public function setLastName($lastName)
    {
        if (is_numeric($lastName))
            throw new BadLastNameException("Last names cannot be numeric");
        $this->lastName = $lastName;
    }

    public function setMark($mark)
    {
        if ($mark <= 0 || $mark > 5)
            throw new BadMarksException("Ocena je nepostojeca");
        if ($mark <= 2)
            throw new BadMarksException("Ocena nije zadovoljavajuca ");
        else  if ($mark == 3)
            throw new BadMarksException("Ocena je prihvatljiva");
        // else if ($mark >= 4)
        //     throw new BadMarksException("Ocena je odlicna");
        $this->mark = $mark;
    }

    public function setName($name)
    {
        if (strlen($name) > 20)
            throw new BadNameException('Large names are not allowed');
        $this->name = $name;
    }
    public function setPassword($password)
    {
        if ($password == 'pass123' || $password == $this->birthday) {
            throw new StupidPasswordException("That is a very stupid password!");
        }
        $this->password = $password;
    }


    public function checkAge()
    {
        if (!is_numeric($this->age) || $this->age < 0 || $this->age > 150)
            throw new UnexpectedValueException('Age must be numeric.');
        return "Age is valid";
    }
}


try {
    $user1 = new User(-25, 'adasd', 'sadsad', new DateTime('2022-10-10'), 5);
    $user1->checkAge();
} catch (BadMarksException $e) {
    echo "BadMarksException: " . $e->getMessage();
} catch (StupidPasswordException $e) {
    echo "StupidPasswordException: " . $e->getMessage();
} catch (BadLastNameException $e) {
    echo "BadLastNameException: " . $e->getMessage();
} catch (RuntimeException $e) {
    echo "RuntimeException: " . $e->getMessage();
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
} finally {
    echo "<br>finally";
}
