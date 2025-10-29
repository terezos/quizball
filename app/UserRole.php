<?php

namespace App;

enum UserRole: string
{
    case User = 'user';
    case Editor = 'editor';
    case Admin = 'admin';
}
