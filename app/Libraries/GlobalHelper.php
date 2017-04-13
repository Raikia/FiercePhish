<?php
namespace App\Libraries;

class GlobalHelper
{
    public static function makeUnclickableLink($str)
    {
        return str_replace(['http://', 'https://', '.'], ['hxxp://', 'hxxps://', '[.]'], $str);
    }
}
