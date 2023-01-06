<?php

// функция по выводу массива на экран
function print_arr($arr){
    echo '<pre>';
    print_r($arr);
    echo '</pre>';
}



// самописная функция по замене в строках
// работает по принципу .format in Python
if(!function_exists('mb_str_replace')){

    function mb_str_replace($needle, $text_replace, $haystack){
        return implode($text_replace, explode($needle, $haystack));
    }

}
