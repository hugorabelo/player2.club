<!doctype html>
<html ng-app="aplicacaoLiga">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<title page-title></title>

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">

    <!-- Main Inspinia CSS files -->
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/flag-icon.min.css">
    <link rel="stylesheet" href="css/angular-ui-tree.min.css">
    <link rel="stylesheet" href="css/plugins/iCheck/custom.css">
    <link rel="stylesheet" href="css/plugins/summernote/summernote.css">

    <!-- Estilos do Campeonato-->
    <link rel="stylesheet" href="css/custom.css">


    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery-ui.min.js"></script>
    <script src="js/metisMenu.min.js"></script>
    <script src="js/pace.min.js"></script>
    <script src="js/jquery.slimscroll.min.js"></script>

    <!-- Verificar este
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.1.47/jquery.form-validator.min.js"></script>
    -->

    <script src="js/angular.min.js"></script>
    <script src="js/angular-ui-router.min.js"></script>
    <script src="js/angular-cookies.min.js"></script>
    <script src="js/angular-animate.min.js"></script>
    <script src="js/ui-bootstrap-tpls.min.js"></script>
    <script src="js/angular-translate.min.js"></script>
    <script src="js/angular-translate-loader-static-files.min.js"></script>
    <base href="/player2/public/">
</head>
<!-- ControllerAs syntax -->
<!-- Main controller with serveral data used in Inspinia theme on diferent view -->
<body>

<!-- Wrapper-->
<div id="wrapper">

    <!-- Navigation
    -->
    <div ng-include="'app/views/comum/navigation.html'"></div>

	<!-- Page wrapper
	-->
	<div ng-include="'app/views/comum/topnavbar.html'"></div>

    <!-- Page wraper -->
    <!-- ng-class with current state name give you the ability to extended customization your view -->
    <div id="page-wrapper" class="gray-bg {{$state.current.name}}">

        <!-- Main view
        -->
        <div class="loading_geral" ng-show="loading">
            <i class="fa fa-spinner fa-5x fa-spin"></i>
        </div>
        <div ui-view></div>


        <!-- Footer
        -->
        <br clear="all" /><br clear="all" />
        <div ng-include="'app/views/comum/footer.html'"></div>

    </div>
    <!-- End page wrapper-->

</div>
<!-- End wrapper-->

    <!-- Custom and plugin javascript -->
    <script src="js/inspinia.js"></script>

    <!-- Anglar App Script -->
    <script src="app/app.js"></script>
<!--
    <script src="app/assets/controllers.js"></script>
    <script src="app/assets/directives.js"></script>
    <script src="app/assets/factorys.js"></script>
    <script src="app/assets/plugins.js"></script>
-->

<?php

$arquivoAplicacao = 'app/app.js';
$caminho = 'app/';

$arquivosJS = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($caminho, FilesystemIterator::SKIP_DOTS));
$files_array = array();

foreach($arquivosJS as $files) {
    $data = $files->getPathname();
    $files_array[] = $data;
}

natsort($files_array);

foreach($files_array as $arquivo){
    if($arquivo == $arquivoAplicacao) {
        continue;
    }
    if(substr($arquivo, -3) == '.js') {
        echo "\n\t<script src='$arquivo'></script>";
    }
}

?>

</body>
</html>
