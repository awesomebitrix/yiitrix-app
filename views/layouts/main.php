<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/**
 * @var CMain $APPLICATION
 */
global $USER;
global $APPLICATION;


?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title><?$APPLICATION->ShowTitle()?><?=(!EHelper::isMain()) ? ' &mdash; ' . EHelper::get('NAME') : ''?></title>

    <?= EHelper::jsApp() ?>

    <?$APPLICATION->ShowHead();?>
</head>

<body class="<?$APPLICATION->ShowProperty('body_class', '')?>">

<?$APPLICATION->ShowPanel();?>



<?= $content?>


</body>
</html>















