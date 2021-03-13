<?php
require_once 'Fields.php';

class Validator
{
    private $fields;

    public function __construct()
    {
        $this->fields = new Fields();
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function foundErrors()
    {
        return $this->fields->hasErrors();
    }

    public function addField($name, $message = '')
    {
        $this->fields->addField($name, $message);
    }

    public function checkRequired(
        $name,
        $value,
        $required = true
    ) {
        $field = $this->fields->getField($name);

        if ($required && empty($value)) {
            $field->setErrorMessage('Required');
            return;
        }

        $field->clearErrorMessage();
    }

    public function checkLength(
        $name,
        $value,
        $min = 1,
        $max = 255
    ) {
        $field = $this->fields->getField($name);

        $length = strlen($value);
        if ($length < $min) {
            $field->setErrorMessage('Too short');
        } else if ($length > $max) {
            $field->setErrorMessage('Too long');
        } else {
            $field->clearErrorMessage();
        }
    }

    public function checkPattern(
        $fieldName,
        $value,
        $pattern,
        $message
    ) {
        $field = $this->fields->getField($fieldName);

        $match = preg_match($pattern, $value);
        if ($match === false) {
            $field->setErrorMessage('Error testing field.');
        } else if ($match != 1) {
            $field->setErrorMessage($message);
        } else {
            $field->clearErrorMessage();
        }
    }

    public function checkUsername($fieldName, $username)
    {
        $this->checkRequired($fieldName, $username);
        $field = $this->fields->getField($fieldName);
        if ($field->hasError()) {
            return;
        }

        $this->checkLength($fieldName, $username, 1, 20);
        $field = $this->fields->getField($fieldName);
        if ($field->hasError()) {
            return;
        }
    }

    public function checkPassword($fieldName, $password)
    {
        $this->checkRequired($fieldName, $password);
        $field = $this->fields->getField($fieldName);
        if ($field->hasError()) {
            return;
        }

        $this->checkLength($fieldName, $password, 8);
        $field = $this->fields->getField($fieldName);
        if ($field->hasError()) {
            return;
        }

        $passwordPattern = '/^'
            . '(?=.*[[:digit:]])'   // number lookahead
            . '(?=.*[[:lower:]])'   // lowercase lookahead
            . '(?=.*[[:upper:]])'   // uppercase lookahead
            . '.*'                  // compared to the entire string
            . '$/';
        $this->checkPattern(
            $fieldName,
            $password,
            $passwordPattern,
            'Must contain a number, a lowercase, and uppercase letter'
        );
    }
}
