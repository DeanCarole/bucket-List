<?php

namespace App\Utils;

class Censurator
{



    public function purify(string $text){
        $wordsForbidden = ['prout','debile', 'canard', 'extincteur'];

        $nouveautext = str_ireplace($wordsForbidden,'***', $text);

        return $nouveautext;

    }
}