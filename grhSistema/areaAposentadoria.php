<?php
/**
 * Área de Aposentadoria
 *
 * By Alat
 */

# Reservado para o servidor logado
$idUsuario = NULL;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso){

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();
    $aposentadoria = new Aposentadoria();

    # Verifica a fase do programa
    $fase = get('fase');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh',FALSE);
    if($grh){
        # Grava no log a atividade
        $atividade = "Visualizou a área de Aposentadoria";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Pega os parâmetros
    $parametroAno = post('parametroAno',get_session('parametroAno',date('Y')));
    $parametroMotivo = post('parametroMotivo',get_session('parametroMotivo',3));
    $parametroSexo = post('parametroSexo',get_session('parametroSexo',"Feminino"));

    # Joga os parâmetros par as sessions
    set_session('parametroAno',$parametroAno);
    set_session('parametroMotivo',$parametroMotivo);
    set_session('parametroSexo',$parametroSexo);

    $grid = new Grid();
    $grid->abreColuna(12);

    # Cria um menu
    $menu1 = new MenuBar();

    if($fase == "previsao1"){
        # Voltar
        $botaoVoltar = new Link("Voltar","?");
        $botaoVoltar->set_class('button');
        $botaoVoltar->set_title('Voltar a página anterior');
        $botaoVoltar->set_accessKey('V');
        $menu1->add_link($botaoVoltar,"left");
    }elseif(($fase == "") OR ($fase == "porAno")){
        # Voltar
        $botaoVoltar = new Link("Voltar","grh.php");
        $botaoVoltar->set_class('button');
        $botaoVoltar->set_title('Voltar a página anterior');
        $botaoVoltar->set_accessKey('V');
        $menu1->add_link($botaoVoltar,"left");
    }

    $menu1->show();

    # Título
    titulo("Área de Aposentadoria");
    br();

    switch ($fase){

####################################################################################################################

        # Aposentados por ano
        case "" :
        case "porAno" :
            
            $grid2 = new Grid();
            $grid2->abreColuna(12,3);
            
            $painel = new Callout();
            $painel->abre();
            
            $menu = new Menu("menuProcedimentos");
            
            $menu->add_item("titulo","Servidores Aposentados");
            $menu->add_item("link","<b>Aposentados por Ano</b>","?fase=porAno","Servidores Aposentados por Ano de Aposentadoria");
            $menu->add_item("link","Aposentados por Tipo","?fase=motivo","Servidores Aposentados por Tipo de Aposentadoria");
            $menu->add_item("link","Estatística","?fase=anoEstatistica","Estatística dos Servidores Aposentados");
            
            $menu->add_item("titulo","Servidores Ativos");
            $menu->add_item("link","Previsão de Aposentadoria","?fase=previsao","Previsão de Aposentadoria de Servidores Ativos");
            
            $menu->show();

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->abreColuna(12,9);

            # Formulário de Pesquisa
            $form = new Form('?fase=porAno');

            # Cria um array com os anos possíveis
            $anoInicial = 1999;
            $anoAtual = date('Y');

            $controle = new Input('parametroAno','numero');
            $controle->set_size(6);
            $controle->set_title('Filtra por Ano exercício');
            $controle->set_valor(date("Y"));
            $controle->set_valor($parametroAno);
            $controle->set_autofocus(TRUE);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();

            ######################3

            $select = 'SELECT tbservidor.idfuncional,
                              tbpessoa.nome,
                              tbservidor.idServidor,
                              tbservidor.dtAdmissao,
                              tbservidor.dtDemissao,
                              tbmotivo.motivo
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         LEFT JOIN tbmotivo on (tbservidor.motivo = tbmotivo.idMotivo)
                        WHERE YEAR(tbservidor.dtDemissao) = "'.$parametroAno.'"
                          AND situacao = 2
                          AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                     ORDER BY dtDemissao';


            $result = $pessoal->select($select);

            $tabela = new Tabela();
            $tabela->set_titulo('Servidores Estatutários / Celetistas Aposentados em '.$parametroAno);
            $tabela->set_tituloLinha2('Com Informaçao de Contatos');
            $tabela->set_subtitulo('Ordenado pela Data de Saída');

            $tabela->set_label(array('IdFuncional','Nome','Cargo','Admissão','Saída','Motivo'));
            #$relatorio->set_width(array(10,20,10,10,10,10,10,10,10));
            $tabela->set_align(array('center','left','left','center','center','left'));
            $tabela->set_funcao(array(NULL,NULL,NULL,"date_to_php","date_to_php"));

            $tabela->set_classe(array(NULL,NULL,"pessoal"));
            $tabela->set_metodo(array(NULL,NULL,"get_cargo"));

            $tabela->set_conteudo($result);

            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editarAno');
            $tabela->show();
            
            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

####################################################################################################################

    case "previsao" :

            br(5);
            aguarde("Calculando ...");
            br();

            loadPage('?fase=previsao1');
            break;

################################################################

    # Listagem de servidores ativos com previsão para posentadoria
    case "previsao1" :

        $grid2 = new Grid();
        $grid2->abreColuna(4);

        # Formulário de Pesquisa
        $form = new Form('?fase=previsao');

        $controle = new Input('parametroSexo','combo','Sexo:',1);
        $controle->set_size(8);
        $controle->set_title('Filtra pelo Sexo');
        $controle->set_array(array("Masculino","Feminino"));
        $controle->set_valor(date("Y"));
        $controle->set_valor($parametroSexo);
        $controle->set_onChange('formPadrao.submit();');
        $controle->set_autofocus(TRUE);
        $controle->set_linha(1);
        $controle->set_col(6);
        $form->add_item($controle);

        $form->show();

        $grid2->fechaColuna();
        $grid2->abreColuna(8);

       $aposentadoria->exibeResumoPrevisao();

        $grid2->fechaColuna();
        $grid->fechaGrid();


        $select ='SELECT tbservidor.idServidor,
                         tbservidor.idFuncional,
                         tbpessoa.nome,
                         tbservidor.idServidor,
                         tbservidor.idServidor,
                         tbservidor.idServidor,
                         tbservidor.idServidor,
                         tbservidor.idServidor,
                         tbservidor.idServidor,                         
                         tbservidor.idServidor,
                         tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                   WHERE tbservidor.situacao = 1
                     AND idPerfil = 1
                     AND tbpessoa.sexo = "'.$parametroSexo.'"
                ORDER BY tbpessoa.dtNasc';

        $result = $pessoal->select($select);

        $tabela = new Tabela();
        $tabela->set_titulo('Estatutários Ativos com Previsão para Aposentadoria - Sexo: '.$parametroSexo);
        $tabela->set_subtitulo('Servidores do Sexo '.$parametroSexo);
        $tabela->set_label(array('','IdFuncional','Nome','Cargo','Idade','Data da Aposentadoria por idade','Data da Compulsória',"Tempo Serviço (dias)","Ocorrências (dias)","Dias Faltando",'Data da Aposentadoria por Tempo'));
        $tabela->set_width(array(5,10,10,10,5,10,10,5,5,10));
        $tabela->set_align(array("center","center","left","left"));
        #$tabela->set_funcao(array(NULL,NULL,NULL,NULL,"date_to_php","date_to_php"));

        $tabela->set_classe(array("Aposentadoria",NULL,NULL,"pessoal","pessoal","pessoal","pessoal","Aposentadoria","Aposentadoria","Aposentadoria","Aposentadoria"));
        $tabela->set_metodo(array("podeAposentar",NULL,NULL,"get_Cargo","get_idade","get_dataAposentadoria","get_dataCompulsoria","get_tempoGeral","get_ocorrencias","get_diasFaltando","get_dataAposentadoriaTS"));

        $tabela->set_conteudo($result);

        $tabela->set_idCampo('idServidor');
        $tabela->set_editar('?fase=editarPrevisao');

        $tabela->set_formatacaoCondicional(array(
                                              array('coluna' => 0,
                                                    'valor' => TRUE,
                                                    'operador' => '=',
                                                    'id' => 'pode'),
                                              array('coluna' => 0,
                                                    'valor' => FALSE,
                                                    'operador' => '=',
                                                    'id' => 'naoPode')
                                                    ));

        $pode = new Imagem(PASTA_FIGURAS.'accept.png','Pode Aposentar',15,15);
        $naoPode = new Imagem(PASTA_FIGURAS.'bloqueado2.png','Ainda Tem Pendências',15,15);

        $tabela->set_imagemCondicional(array(array('coluna' => 0,
                                                   'valor' => TRUE,
                                                   'operador' => '=',
                                                   'imagem' => $pode),
                                             array('coluna' => 0,
                                                   'valor' => FALSE,
                                                   'operador' => '=',
                                                   'imagem' => $naoPode)
                                        ));
        $tabela->show();
        break;

####################################################################################################################

        # Aposentadoria por Motivo / Tipo
        case "motivo" :
            
            $grid2 = new Grid();
            $grid2->abreColuna(12,3);
            
            $painel = new Callout();
            $painel->abre();
            
            $menu = new Menu("menuProcedimentos");
            
            $menu->add_item("titulo","Servidores Aposentados");
            $menu->add_item("link","Aposentados por Ano","?fase=porAno","Servidores Aposentados por Ano de Aposentadoria");
            $menu->add_item("link","<b>Aposentados por Tipo</b>","?fase=motivo","Servidores Aposentados por Tipo de Aposentadoria");
            $menu->add_item("link","Estatística","?fase=anoEstatistica","Estatística dos Servidores Aposentados");
            
            $menu->add_item("titulo","Servidores Ativos");
            $menu->add_item("link","Previsão de Aposentadoria","?fase=previsao","Previsão de Aposentadoria de Servidores Ativos");
            
            $menu->show();

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->abreColuna(12,9);

            # Formulário de Pesquisa
            $form = new Form('?fase=motivo');

            # Cria um array com os tipo possíveis
            $selectMotivo = "SELECT DISTINCT idMotivo,
                                  tbmotivo.motivo
                             FROM tbmotivo JOIN tbservidor ON (tbservidor.motivo = tbmotivo.idMotivo)
                             WHERE situacao = 2
                             AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                             ORDER BY 2";

            $motivosPossiveis = $pessoal->select($selectMotivo);

            $controle = new Input('parametroMotivo','combo','Motivo:',1);
            $controle->set_size(8);
            $controle->set_title('Filtra por Motivo');
            $controle->set_array($motivosPossiveis);
            $controle->set_valor(date("Y"));
            $controle->set_valor($parametroMotivo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_autofocus(TRUE);
            $controle->set_linha(1);
            $controle->set_col(6);
            $form->add_item($controle);

            $form->show();

            $select = 'SELECT tbservidor.idfuncional,
                              tbpessoa.nome,
                              tbservidor.idServidor,
                              tbservidor.dtAdmissao,
                              tbservidor.dtDemissao,
                              tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         LEFT JOIN tbmotivo on (tbservidor.motivo = tbmotivo.idMotivo)
                        WHERE tbservidor.motivo = '.$parametroMotivo.'
                          AND situacao = 2
                          AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                     ORDER BY dtDemissao';


            $result = $pessoal->select($select);

            $tabela = new Tabela();
            $tabela->set_titulo('Servidores Estatutários / Celetistas Aposentados por Tipo');
            $tabela->set_tituloLinha2('Com Informaçao de Contatos');
            $tabela->set_subtitulo('Ordenado pela Data de Saída');

            $tabela->set_label(array('IdFuncional','Nome','Cargo','Admissão','Saída','Perfil'));
            #$relatorio->set_width(array(10,20,10,10,10,10,10,10,10));
            $tabela->set_align(array('center','left','left'));
            $tabela->set_funcao(array(NULL,NULL,NULL,"date_to_php","date_to_php"));

            $tabela->set_classe(array(NULL,NULL,"pessoal",NULL,NULL,"pessoal"));
            $tabela->set_metodo(array(NULL,NULL,"get_cargo",NULL,NULL,"get_perfil"));

            $tabela->set_conteudo($result);

            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editarMotivo');
            $tabela->show();
            
            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

####################################################################################################################

        # Estatística
        case "anoEstatistica" :

            $grid = new Grid();
            $grid->abreColuna(12,3);
            
            $painel = new Callout();
            $painel->abre();
            
            $menu = new Menu("menuProcedimentos");
            
            $menu->add_item("titulo","Servidores Aposentados");
            $menu->add_item("link","Aposentados por Ano","?fase=porAno","Servidores Aposentados por Ano de Aposentadoria");
            $menu->add_item("link","Aposentados por Tipo","?fase=motivo","Servidores Aposentados por Tipo de Aposentadoria");
            $menu->add_item("link","<b>Estatística</b>","?fase=anoEstatistica","Estatística dos Servidores Aposentados");
            
            $menu->add_item("titulo","Servidores Ativos");
            $menu->add_item("link","Previsão de Aposentadoria","?fase=previsao","Previsão de Aposentadoria de Servidores Ativos");
            
            $menu->show();

            $painel->fecha();

            # Número de Servidores
            $painel = new Callout("success");
            $painel->abre();
                $numServidores = $aposentadoria->get_numServidoresAposentados();
                p($numServidores,"estatisticaNumero");
                p("Servidores Aposentados<br/>(Estatutários e Celetistas)","estatisticaTexto");
            $painel->fecha();

            $grid->fechaColuna();

    #################################################################

            $grid->abreColuna(12,9);
            # Abre um callout
            $panel = new Callout();
            $panel->abre();

            tituloTable("por Tipo de Aposentadoria");
            br();

            $grid = new Grid();
            $grid->abreColuna(6);

            # Monta o select
            $selectGrafico = 'SELECT tbmotivo.motivo, count(tbservidor.idServidor) as jj
                                FROM tbservidor LEFT JOIN tbmotivo on (tbservidor.motivo = tbmotivo.idMotivo)
                               WHERE tbservidor.situacao = 2
                               AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                            GROUP BY tbmotivo.motivo
                            ORDER BY 2 DESC ';

            $servidores = $pessoal->select($selectGrafico);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));


            $chart = new Chart("Pie",$servidores);
            $chart->set_idDiv("cargo");
            $chart->set_legend(FALSE);
            $chart->set_tamanho($largura = 300,$altura = 300);
            $chart->show();

            $grid->fechaColuna();

    #################################################################

            $grid->abreColuna(6);

            # Tabela
            $tabela = new Tabela();
            #$tabela->set_titulo("por Tipo de Aposentadoria");
            $tabela->set_conteudo($servidores);
            $tabela->set_label(array("Aposentadoria","Servidores"));
            $tabela->set_width(array(80,20));
            $tabela->set_align(array("left","center"));
            $tabela->set_rodape("Total de Servidores: ".$total);
            $tabela->show();

            $grid->fechaColuna();
            $grid->fechaGrid();

            $panel->fecha();

            $grid->fechaColuna();
            $grid->fechaGrid();

    #################################################################

            # Abre um callout
            $panel = new Callout();
            $panel->abre();

            # Título
            tituloTable("por Ano da Aposentadoria");

            # Geral - Por Perfil
            $selectGrafico = 'SELECT YEAR(tbservidor.dtDemissao), count(tbservidor.idServidor) as jj
                                FROM tbservidor LEFT JOIN tbmotivo on (tbservidor.motivo = tbmotivo.idMotivo)
                               WHERE tbservidor.situacao = 2
                            GROUP BY YEAR(tbservidor.dtDemissao)
                            ORDER BY 1 asc ';

            $servidores = $pessoal->select($selectGrafico);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            # Tabela
            $tabela = new Tabela();
            #$tabela->set_titulo("por Perfil");
            $tabela->set_conteudo($servidores);
            $tabela->set_label(array("Ano","Servidores"));
            $tabela->set_width(array(80,20));
            $tabela->set_align(array("left","center"));
            $tabela->set_rodape("Total de Servidores: ".$total);
            #$tabela->show();

            # Gráfico
            $chart = new Chart("ColumnChart",$servidores);
            $chart->set_idDiv("perfil");
            $chart->set_legend(FALSE);
            $chart->set_label(array("Ano","Nº de Servidores"));
            #$chart->set_tamanho($largura = 1000,$altura = 500);
            $chart->show();

            $panel->fecha();

            $grid->fechaColuna();
            $grid->fechaGrid();
        break;


####################################################################################################################

        case "editarAno" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado',$id);

            # Informa a origem
            set_session('origem','areaAposentadoria.php?');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

    ################################################################

        case "editarMotivo" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado',$id);

            # Informa a origem
            set_session('origem','areaAposentadoria.php?fase=motivo');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

    ################################################################

        case "editarPrevisao" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado',$id);

            # Informa a origem
            set_session('origem','areaAposentadoria.php?fase=previsao');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

    ################################################################

    }
    $page->terminaPagina();

}else{
    loadPage("../../areaServidor/sistema/login.php");
}
