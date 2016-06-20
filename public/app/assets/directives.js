function pageTitle(e,a){return{link:function(i,t){var o=function(e,i,o,r,l){var n="LIGA VIRTUAL";i.data&&i.data.pageTitle&&(n="LIGA VIRTUAL | "+i.data.pageTitle),a(function(){t.text(n)})};e.$on("$stateChangeStart",o)}}}function sideNavigation(){return{restrict:"A",link:function(e,a){a.metisMenu()}}}function iboxTools(e){return{restrict:"A",scope:!0,templateUrl:"app/views/comum/ibox_tools.html",controller:function(a,i){a.showhide=function(){var a=i.closest("div.ibox"),t=i.find("i:first"),o=a.find("div.ibox-content");o.slideToggle(200),t.toggleClass("fa-chevron-up").toggleClass("fa-chevron-down"),a.toggleClass("").toggleClass("border-bottom"),e(function(){a.resize(),a.find("[id^=map-]").resize()},50)},a.closebox=function(){var e=i.closest("div.ibox");e.remove()}}}}function minimalizaSidebar(e){return{restrict:"A",template:'<a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="" ng-click="minimalize()"><i class="fa fa-bars"></i></a>',controller:function(a,i){a.minimalize=function(){$("body").toggleClass("mini-navbar"),!$("body").hasClass("mini-navbar")||$("body").hasClass("body-small")?($("#side-menu").hide(),e(function(){$("#side-menu").fadeIn(500)},100)):$("#side-menu").removeAttr("style")}}}}function icheck(e){return{restrict:"A",require:"ngModel",link:function(a,i,t,o){return e(function(){var e;return e=t.value,a.$watch(t.ngModel,function(e){$(i).iCheck("update")}),$(i).iCheck({checkboxClass:"icheckbox_square-green",radioClass:"iradio_square-green"}).on("ifChanged",function(r){return"checkbox"===$(i).attr("type")&&t.ngModel&&a.$apply(function(){return o.$setViewValue(r.target.checked)}),"radio"===$(i).attr("type")&&t.ngModel?a.$apply(function(){return o.$setViewValue(e)}):void 0})})}}}AplicacaoLiga.directive("pageTitle",pageTitle),AplicacaoLiga.directive("sideNavigation",sideNavigation),AplicacaoLiga.directive("iboxTools",iboxTools),AplicacaoLiga.directive("minimalizaSidebar",minimalizaSidebar),AplicacaoLiga.directive("icheck",icheck),AplicacaoLiga.directive("modalConfirma",[function(){return{templateUrl:"app/views/comum/confirmaModal.html",replace:!0}}]),AplicacaoLiga.directive("formularioCampeonato",[function(){return{templateUrl:"app/views/campeonato/formModal.html",replace:!0}}]),AplicacaoLiga.directive("detalhesCampeonato",[function(){return{templateUrl:"app/views/campeonato/detalhesCampeonato.html",replace:!0}}]),AplicacaoLiga.directive("formularioFase",[function(){return{templateUrl:"app/views/campeonato/formModalFase.html",replace:!0}}]),AplicacaoLiga.directive("formularioDetalhesFase",[function(){return{templateUrl:"app/views/campeonato/formModalDetalhesFase.html",replace:!0}}]),AplicacaoLiga.directive("formularioCampeonatoTipo",[function(){return{templateUrl:"app/views/campeonatoTipo/formModal.html",replace:!0}}]),AplicacaoLiga.directive("formularioPlataforma",[function(){return{templateUrl:"app/views/plataforma/formModal.html",replace:!0}}]),AplicacaoLiga.directive("formularioJogo",[function(){return{templateUrl:"app/views/jogo/formModal.html",replace:!0}}]),AplicacaoLiga.directive("formularioUsuarioTipo",[function(){return{templateUrl:"app/views/usuarioTipo/formModal.html",replace:!0}}]),AplicacaoLiga.directive("formularioUsuario",[function(){return{templateUrl:"app/views/usuario/formModal.html",replace:!0}}]),AplicacaoLiga.directive("formularioMenu",[function(){return{templateUrl:"app/views/menu/formModal.html",replace:!0}}]),AplicacaoLiga.directive("formularioContestacaoResultado",[function(){return{templateUrl:"app/views/meus_campeonatos/formContestacaoResultado.html",replace:!0}}]),AplicacaoLiga.directive("fileUpload",function(){return{scope:!0,link:function(e,a,i){a.bind("change",function(a){for(var i=a.target.files,t=0;t<i.length;t++)e.$emit("fileSelected",{file:i[t]})})}}});
