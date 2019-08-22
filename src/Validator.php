<?php


namespace App;


class Validator implements ValidatorInterface
{
    public function validate(array $fields)
    {
        $errors = [];

        if (empty($fields['title']) && isset($fields['title'])) {
            $errors['title'] = "Can't be blank";
        }
        if (empty($fields['paid']) && isset($fields['paid'])) {
            $errors['paid'] = "Can't be blank";
        }
        if ($fields['name'] == '' && isset($fields['name'])) {
            $errors['name'] = "Can't be blank";
        }
        if (empty($fields['body']) && isset($fields['body'])) {
            $errors['body'] = "Can't be blank";
        }

        return $errors;
    }
}
