<?php
/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use app\widgets\Alert;
use app\assets\AppAsset;

$this->registerLinkTag(['rel' => 'shortcut icon', 'href' => Url::to(Yii::$app->params['settings']['img-ico']), 'type' => "image/x-icon"]);
$this->registerLinkTag(['rel' => 'icon', 'href' => Url::to(Yii::$app->params['settings']['img-ico']), 'type' => "image/x-icon"]);

AppAsset::register($this);
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&amp;subset=latin" rel="stylesheet">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode(strtoupper($this->title . ($this->title? ' | ': '') . Yii::$app->name)) ?></title>
        <?php $this->head() ?>
    </head>
    <body data-url-root="<?= Url::home(true) ?>">
        <?php $this->beginBody() ?>

        <div id="wrapper">
            <nav class="navbar-default navbar-static-side" role="navigation">
                <div class="sidebar-collapse">
                    <ul class="nav metismenu" id="side-menu">
                        <?= Yii::$app->nifty->get_profile_widget() ?>
                        <?= Yii::$app->nifty->get_menu() ?>
                        <?= Yii::$app->nifty->get_widget() ?>
                    </ul>

                </div>
            </nav>

            <div id="page-wrapper" class="gray-bg">
                <div class="row border-bottom">
                    <nav class="navbar navbar-static-top  " role="navigation" >
                        <?= Yii::$app->nifty->get_notification_dropdown() ?>
                    </nav>
                </div>

                <div class="row wrapper border-bottom white-bg page-heading" style="margin-bottom: 1%;">
                    <div class="col-sm-4" id = "page-title">
                        <h2><?=$this->title?></h2>
                         <?= Breadcrumbs::widget([
                            'homeLink' => [
                                'label' => Yii::t('yii', 'Inicio'),
                                'url'   => Yii::$app->homeUrl,
                            ],
                            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                            'itemTemplate' => '<li class="breadcrumb-item">{link}</li>',
                            'activeItemTemplate' => "<li class=\"breadcrumb-item \"><strong>{link}</strong></li>",
                            'tag' => 'ol',
                        ]) ?>
                    </div>
                </div>
                <div id="page-content" class=" wrapper wrapper-content animated fadeInRight">
                    <?= Alert::widget(); ?>
                    <?= $content ?>
                </div>
                <div id="footer" class="footer">
                    <div class="float-right">Versión <strong><?=Yii::$app->version?></strong></div>
                    <div>
                        <p class="text-sm">Powered by <strong><a target='_blank' href="http://lerco.mx">Lerco solutions</a></strong>
                            &#0169; <?= date('Y') . ' ' . Yii::$app->name?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>


<!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script> -->

