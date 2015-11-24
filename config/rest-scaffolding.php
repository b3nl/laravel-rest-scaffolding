<?php
    return [
        'tags' => [
            'model' => 'Avatar\\Tag',
            'validators' => [
                'store' => [
                    'tag' => 'required|string|unique:tags,tag', // TODO Add language_id
                    'user_id' => 'required|exists:users,id'
                ],
                'update' => [
                    'tag' => 'string|unique:tags,tag', // TODO Add language_id
                    'user_id' => 'exists:users,id'
                ]
            ]
        ]
    ];
