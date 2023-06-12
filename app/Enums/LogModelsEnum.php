<?php

namespace App\Enums;

enum LogModelsEnum: string
{
    case LOGIN = 'Login';
    case REGISTER = 'Register';
    case  FORGOT_PASSWORD = 'ForgotPass';
    case  RESET_PASSWORD = 'ResetPass';
    case  VERIFY = 'Verify';
    case  LOGOUT = 'Logout';
    case USER = 'User';
    case VEHICLE = 'Vehicle';
    case STORE = 'Store';
}
