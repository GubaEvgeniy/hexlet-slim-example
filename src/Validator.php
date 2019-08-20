<?php


namespace App;


class Validator implements ValidatorInterface
{
    public function validate(array $course)
    {
        $errors = [];

        if (empty($course['title'])) {
            $errors['title'] = "Can't be blank";
        }
        if (empty($course['paid'])) {
            $errors['title'] = "Can't be blank";
        }

        return $errors;
    }
}
