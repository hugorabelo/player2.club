<!doctype html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Liga de Jogos Virtuais</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.1.47/jquery.form-validator.min.js"></script>
    {{ HTML::script('js/funcoes.js', '', '') }}

</head>
<body>
    <div class="header">
        <nav class="navbar navbar-default" role="navigation">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a class="navbar-brand" href="{{ URL::to('/') }}">Liga de Jogos Virtuais</a>
                </div>

                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav">
                        <li class="active"><a href="{{ URL::to('/') }}">Início</a></li>
                        <li><a class="link" href="{{ URL::to('plataformas') }}">Plataformas</a></li>
                        <li><a class="link" href="campeonatos" id="link">Link</a></li>
                        <form action="" class="navbar-form navbar-left">
                            <div class="form-group">
                                <input type="text" class="form-control" placeholder="Busca">
                            </div>
                        </form>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                Configurações
                                <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <li class="item2"><a class="link" href="{{ URL::to('campeonatos') }}">Campeonatos</a></li>
                                <li class="item2"><a class="link" href="{{ URL::to('jogos') }}">Jogos</a></li>
                                <li class="item1"><a class="link" href="{{ URL::to('plataformas') }}">Plataformas</a></li>
                                <li class="item3"><a class="link" href="{{ URL::to('campeonatoTipos') }}">Tipos de Campeonato</a></li>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                Usuários
                                <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <li class="item3"><a class="link" href="{{ URL::to('users') }}">Usuários</a></li>
                                <li class="item3"><a class="link"     href="{{ URL::to('usuarioTipos') }}">Tipos de Usuário</a></li>
                            </ul>
                        </li>
                    </ul>
                </div
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-12" id="carrega_ajax">

                @yield('main')

            </div>
        </div>
    </div>

    <div class="footer">
    </div>

    @section('custom_script')
    @show

    <script type="text/javascript">
        /*
        $(document).ready(function() {
            $("a.link").click(function(e) {
                e.preventDefault();
                $('#carrega_ajax').load($(this).attr("href"));
            });

            $("#carrega_ajax").on("click", 'a.link', function(e) {
                e.preventDefault();
                $('#carrega_ajax').load($(this).attr("href"));
            });
        });
        */

    </script>
</body>
</html>
