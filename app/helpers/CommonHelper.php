<?php
if(!function_exists('ms')){

    function ms($array){

        print_r(json_encode($array));

        exit(0);

    }

}

if (!function_exists("rando")) {

    function rando($length = 14, $pre =""){
        $str = "";
        $characters = array_merge(range('A','Z'), range('a','z'), range('0','9'));
        $max = count($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }
        return $pre.$str;
    }
}

if (!function_exists("file_upload")) {

    function file_upload($path_with_name, $base64_file, $file_name = ""){

        if(empty($file_name)){
            $file_name = rando();
        }

        $image_parts = explode(";base64,", $base64_file);
        // dd($image_parts);
        $image_type_aux = explode("/", $image_parts[0]);
        $image_type = $image_type_aux[1]; //image extension
        $image_base64 = base64_decode($image_parts[1]);
        $file = $file_name.".".$image_type;
        file_put_contents($path_with_name.$file , $image_base64);
        if(file_exists($path_with_name)){
            return $file;
        }
        return 0;
    }
}
?>
