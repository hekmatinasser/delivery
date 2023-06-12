<?php

namespace App\Enums;

enum LogActionsEnum: string
{
    case ADD = '0';
    case VIEW_DETAILS = '1';
    case  EDIT = '2';
    case  DELETE = '3';
    case  SHOW_ALL = '4';
    case  REQUEST = '5';
    case SUCCESS = '6';
    case FAILED = '7';
    case REGISTER = '9';
    case VIEW_PROFILE = '10';
    case SHOW_PROFILE = '11';
}
