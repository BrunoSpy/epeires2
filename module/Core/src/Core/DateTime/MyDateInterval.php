<?php

namespace Core\DateTime;

class MyDateInterval extends \DateInterval {
    
    public
        $pluralCheck = '()',
            // Must be exactly 2 characters long
            // The first character is the opening brace, the second the closing brace
            // Text between these braces will be used if > 1, or replaced with $this->singularReplacement if = 1
        $singularReplacement = '',
            // Replaces $this->pluralCheck if = 1
            // hour(s) -> hour
        $separator = ', ',
            // Delimiter between units
            // 3 hours, 2 minutes
        $finalSeparator = ' et ',
            // Delimeter between next-to-last unit and last unit
            // 3 hours, 2 minutes, and 1 second
        $finalSeparator2 = ' et ';
            // Delimeter between units if there are only 2 units
            // 3 hours and 2 minutes
    
    public static function createFromDateInterval (\DateInterval $interval) {
        $obj = new self('PT0S');
        foreach ($interval as $property => $value) {
            $obj->$property = $value;
        }
        return $obj;
    }
    
    public function formatWithoutZeroes () {
        // Each argument may have only one % parameter
        // Result does not handle %R or %r -- but you can retrieve that information using $this->format('%R') and using your own logic
        $parts = array ();
        foreach (func_get_args() as $arg) {
            $pre = mb_substr($arg, 0, mb_strpos($arg, '%'));
            $param = mb_substr($arg, mb_strpos($arg, '%'), 2);
            $post = mb_substr($arg, mb_strpos($arg, $param)+mb_strlen($param));
            $num = intval(parent::format($param));

            $open = preg_quote($this->pluralCheck[0], '/');
            $close = preg_quote($this->pluralCheck[1], '/');
            $pattern = "/$open(.*)$close/";
            list ($pre, $post) = preg_replace($pattern, $num == 1 ? $this->singularReplacement : '$1', array ($pre, $post));

            if ($num != 0) {
                $parts[] = $pre.$num.$post;
            }
        }

        $output = '';
        $l = count($parts);
        foreach ($parts as $i => $part) {
            $output .= $part.($i < $l-2 ? $this->separator : ($l == 2 ? $this->finalSeparator2 : ($i == $l-2 ? $this->finalSeparator : '')));
        }
        return $output;
    }

}
