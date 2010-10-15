<?php
/* アニメーションPNGはIDATチャンクの前にacTLチャンクを持たなければなりません。
 * つまりIDATが出現するところまでseekしてacTLが存在するかどうか確認すればokなはずです
 */

function is_png($binary) {
    return !strncmp($binary, pack("c*", 0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a),8);
}

function chunk_type_compare($binary, $type, $position) {
    return !substr_compare($binary, $type, $position+4, 4);
}

function get_chunk_length($binary, $position) {
    $length_bin = substr($binary, $position, 4);
    $arr = unpack("c*", $length_bin);

    $length = 0;
    for ($i=1; $i <= 4; $i++) {
        $length += pow(256, 4-$i) * $arr[$i];
    }

    return $length + 12;
}

function next_chunk($binary, $chunk_head_position){
    return $chunk_head_position + get_chunk_length($binary, $chunk_head_position);
}

function is_animated($binary, $length) {
    $chunk_head_position=8;
    
    while ($chunk_head_position < $length) {
        if (chunk_type_compare($binary, "IDAT", $chunk_head_position)) return false;

        if (chunk_type_compare($binary, "acTL", $chunk_head_position)) return true;

        $chunk_head_position = next_chunk($binary, $chunk_head_position);
    }

    return false;
}


$filename = $argv[1];

$f = fopen($filename, "rb");
$header = fread($f, 1024);
fclose($f);

var_dump(is_png($header) && is_animated($header, 1024));


?>
