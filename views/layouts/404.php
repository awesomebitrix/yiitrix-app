<?
global $USER;
global $APPLICATION;
?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="netlab-com.ru">

    <title>404 Not found<?=(!EHelper::isMain()) ? ' &mdash; ' . EHelper::get('NAME') : ''?></title>


    <link href='https://fonts.googleapis.com/css?family=Fira+Sans&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="<?=CUtil::GetAdditionalFileURL(P_CSS . 'all.css')?>">

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>

<body class="page-404">

<div class="container main-container">
    <div class="row">
        <div class="col-xs-6 page-404-container">
            <img src="<?=P_IMAGES?>404.png" width="590" height="265">
            <div>
                Страница, которую вы запросили, не существует<br>
                перейти на <a href="/">главную страницу</a>
            </div>
        </div>
    </div>
</div>



</body>
</html>
