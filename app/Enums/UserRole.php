<?php

namespace App\Enums;

enum UserRole: string
{
    case User = 'user';
    case Coach = 'coach';
    case Admin = 'admin';
}