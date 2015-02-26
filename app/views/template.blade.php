<!doctype html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Liga de Jogos Virtuais</title>
    {{ HTML::style('/css/bootstrap.css')}}
    {{ HTML::script('/js/jquery.js') }}
    {{ HTML::script('/js/bootstrap.js') }}
</head>
<body>
    <div class="header">
        <div class="navbar">
            <div class="navbar-inner">
                <a class="brand" href="{{ URL::to('/') }}">Liga de Jogos Virtuais</a>
                <ul class="nav">
                    <li class="active"><a href="#">Início</a></li>
                    <li><a href="#">Link</a></li>
                    <li><a href="#">Link</a></li>
                    <form action="" class="navbar-search pull-left">
                        <input type="text" class="search-query" placeholder="Busca">
                    </form>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            Configurações
                            <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li class="item2"><a href="">Jogos</a></li>
                            <li class="item1"><a href="{{ URL::to('plataformas') }}">Plataformas</a></li>
                            <li class="item3"><a href="">Tipos de Campeonato</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="container">
        @section('conteudo')
        @show
    </div>
    <div class="footer">

    </div>
</body>
</html>
