<?php
$cache_path = '/var/run/nginx-cache/';
//$url = parse_url($_POST['url']);
$url = $_POST['url'];
//error_log($_POST['url']);
if(!$url)
{
    echo 'Invalid URL entered';
    die();
}
//$scheme = $url['scheme'];
//$host = $url['host'];
//$requesturi = $url['path'];
//$hash = md5($scheme.'GET'.$host.$requesturi);
//$hash = md5($url);
//error_log(var_dump(unlink($cache_path . substr($hash, -1) . '/' . substr($hash,-3,2) . '/' . $hash)));
//array_map('unlink', glob($cache_path."*"));

function recursiveRemoveDirectory($directory)
{
    foreach(glob("{$directory}/*") as $file)
    {
        if(is_dir($file)) { 
            recursiveRemoveDirectory($file);
        } else {
            unlink($file);
        }
    }
   //rmdir($directory);
}

recursiveRemoveDirectory($cache_path);

?>
