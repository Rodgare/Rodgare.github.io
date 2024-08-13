<?php
namespace App;

class Validator
{
    public function validate($user)
    {
        $errors = [];
        if ($user['name'] === '') {
            $errors['name'] = "Ім'я не може бути пустим";
        } else if (mb_strlen($user['name']) < 1) {
            $errors['name'] = "Ім'я має буде довше одного символа";
        }
        if ($user['email'] === '') {
            $errors['email'] = "Email не може бути пустим";
        }

        return $errors;
    }
}
?>