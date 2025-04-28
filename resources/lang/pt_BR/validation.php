<?php

return [

    'accepted'             => 'O campo :attribute deve ser aceito.',
    'active_url'           => 'O campo :attribute não é uma URL válida.',
    'after'                => 'O campo :attribute deve ser uma data posterior a :date.',
    'after_or_equal'       => 'O campo :attribute deve ser uma data posterior ou igual a :date.',
    'alpha'                => 'O campo :attribute deve conter apenas letras.',
    'alpha_dash'           => 'O campo :attribute deve conter apenas letras, números e traços.',
    'alpha_num'            => 'O campo :attribute deve conter apenas letras e números.',
    'array'                => 'O campo :attribute deve ser uma matriz.',
    'before'               => 'O campo :attribute deve ser uma data anterior a :date.',
    'before_or_equal'      => 'O campo :attribute deve ser uma data anterior ou igual a :date.',
    'between'              => [
        'numeric' => 'O campo :attribute deve estar entre :min e :max.',
        'file'    => 'O arquivo :attribute deve ter entre :min e :max kilobytes.',
        'string'  => 'O campo :attribute deve ter entre :min e :max caracteres.',
        'array'   => 'O campo :attribute deve ter entre :min e :max itens.',
    ],
    'boolean'              => 'O campo :attribute deve ser verdadeiro ou falso.',
    'confirmed'            => 'A confirmação de :attribute não confere.',
    'email'                => 'O campo :attribute deve ser um endereço de e-mail válido.',
    'required'             => 'O campo :attribute é obrigatório.',
    'max'                  => [
        'string' => 'O campo :attribute não pode ter mais que :max caracteres.',
    ],
    'min'                  => [
        'string' => 'O campo :attribute deve ter no mínimo :min caracteres.',
    ],
    'unique'               => 'O campo :attribute já está em uso.',

    'attributes' => [
        'name'     => 'nome',
        'lastname' => 'sobrenome',
        'phone'    => 'telefone',
        'email'    => 'e-mail',
        'password' => 'senha',
    ],
];
