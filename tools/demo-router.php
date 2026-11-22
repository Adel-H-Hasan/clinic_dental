<?php
if(!in_array($_SERVER['REMOTE_ADDR']??'', ['127.0.0.1','::1'],true)){http_response_code(403);exit;}
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
$public=realpath(__DIR__.'/../public');
$file=realpath($public.rawurldecode($path));
if($file&&str_starts_with($file,$public.DIRECTORY_SEPARATOR)&&is_file($file)&&pathinfo($file,PATHINFO_EXTENSION)!=='php')return false;
$app=require __DIR__.'/demo-app.php';
$app->handleRequest(Illuminate\Http\Request::capture());
