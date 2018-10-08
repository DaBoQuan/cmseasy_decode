<?php 
$path = $argv[1];
function read_decode($filename){
  $content = file_get_contents($filename);
  if(strstr($content,'@Zend')){
    $content = gzinflate(base64_decode(substr(substr($content,2054),0,-2)));
    file_put_contents($filename,'<?php '.$content);
  }
}

function scan($filename){
    foreach(glob($filename."/*") as $filename){
      if(is_dir($filename)){
        scan($filename);
      }else{
      read_decode($filename);
      }
    }
}
scan($path);