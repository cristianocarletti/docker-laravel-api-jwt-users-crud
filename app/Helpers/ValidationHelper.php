<?php

namespace App\Helpers;

class ValidationHelper
{
    /**
     * Verifica se o email é válido.
     *
     * @param  string  $email
     * @return bool
     */
    public static function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    // Você pode adicionar outros métodos conforme necessário
}
