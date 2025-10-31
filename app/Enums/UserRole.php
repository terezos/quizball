<?php

namespace App\Enums;

enum UserRole: string
{
    case User = 'user';
    case Editor = 'editor';
    case Admin = 'admin';
}
