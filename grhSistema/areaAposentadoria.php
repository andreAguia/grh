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

    # Voltar
    if($fase == ""){
        $botaoVoltar = new Link("Voltar","grh.php");
    }else{
        $botaoVoltar = new Link("Voltar","?");
    }
    $botaoVoltar->set_class('button');
    $botaoVoltar->set_title('Voltar a página anterior');
    $botaoVoltar->set_accessKey('V');
    $menu1->add_link($botaoVoltar,"left");

    $menu1->show();
    
    # Título
    titulo("Área de Aposentadoria");
    br();
    
    switch ($fase){
    
####################################################################################################################
    
        # Aposentados por ano
        case "" : 
            
            # Inicia o Grid
            $grid = new Grid();
            
            # Coluna Lateral
            $grid->abreColuna(12,12,3);

                # Número de Servidores
                $painel = new Callout("success");
                $painel->abre();
                    $numServidores = $aposentadoria->get_numServidoresAposentados();
                    p($numServidores,"estatisticaNumero");
                    p("Servidores Aposentados","estatisticaTexto");
                $painel->fecha(); 

                # Menu
                $painel = new Callout();
                $painel->abre();
                titulo('Menu');
                
                # Inicia o menu 
                $menu = new Menu("menuProcedimentos");
                $menu->add_item('titulo','Servidores Aposentados','#');
                $menu->add_item('link','por Ano da Aposentadoria','?','Exibe os servidores aposentados por ano');
                $menu->add_item('link','por Motivo da Aposentadoria','?fase=motivo','Exibe os servidores aposentados e o motivo da aposentadoris');
                $menu->add_item('titulo','Servidores Ativos','#');
                $menu->add_item('link','por Idade','?fase=dataIdade','Exibe os servidores e a idade para se aposentar');
                $menu->add_item('link','por Tempo Averbado e Idade','?fase=dataIdadeTempo','Exibe os servidores com o tempo averbado e idade para aposentar');

                $menu->show();            
                $painel->fecha();

            $grid->fechaColuna();
           
            # Área Central 
            $grid->abreColuna(12,12,9);
    
                $grid2 = new Grid();
                $grid2->abreColuna(6);
                
                # Formulário de Pesquisa
                $form = new Form('?');

                # Cria um array com os anos possíveis
                $anoInicial = 1999;
                $anoAtual = date('Y');
                $anosPossiveis = arrayPreenche($anoAtual,$anoInicial,"d");

                $controle = new Input('parametroAno','combo');
                $controle->set_size(8);
                $controle->set_title('Filtra por Ano exercício');
                $controle->set_array($anosPossiveis);
                $controle->set_valor(date("Y"));
                $controle->set_valor($parametroAno);
                $controle->set_onChange('formPadrao.submit();');
                $controle->set_linha(1);
                $controle->set_col(6);
                $form->add_item($controle);

                $form->show();
                
                $grid2->fechaColuna();
                $grid2->abreColuna(6);
                
                # Cria um menu
                $menu = new MenuBar();
                
                # Botão Estatística
                $botao = new Button('Estatística','?fase=anoEstatistica');
                $botao->set_title('Exibe estatística dos servidores aposentados por ano');
                $menu->add_link($botao,"right");

                $menu->show();
                
                $grid2->fechaColuna();
                $grid->fechaGrid();

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
                         ORDER BY dtDemissao';		


                $result = $pessoal->select($select);

                $tabela = new Tabela();
                $tabela->set_titulo('Relatório Anual de Servidores Aposentados em '.$parametroAno);
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

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
    
####################################################################################################################
    
        # Aposentadoria por Motivo
        case "motivo" : 
            
            # Inicia o Grid
            $grid = new Grid();
            
            # Coluna Lateral
            $grid->abreColuna(12,12,4);

                # Abre um callout
                $panel = new Callout();
                $panel->abre();
                
                # Geral - Por Perfil
                $selectGrafico = 'SELECT tbmotivo.motivo, count(tbservidor.idServidor) as jj
                                    FROM tbservidor LEFT JOIN tbmotivo on (tbservidor.motivo = tbmotivo.idMotivo)
                                   WHERE tbservidor.situacao = 2
                                GROUP BY tbmotivo.motivo
                                ORDER BY 2 DESC ';

                $servidores = $pessoal->select($selectGrafico);

                # Soma a coluna do count
                $total = array_sum(array_column($servidores, "jj"));
                
                # Tabela
                $tabela = new Tabela();
                $tabela->set_titulo("Resumo");
                $tabela->set_conteudo($servidores);
                $tabela->set_label(array("Aposentadoria","Servidores"));
                $tabela->set_width(array(80,20));
                $tabela->set_align(array("left","center"));
                $tabela->set_rodape("Total de Servidores: ".$total);                
                $tabela->show();
                
                # Gráfico
                $chart = new Chart("Pie",$servidores);
                $chart->set_idDiv("perfil");
                $chart->set_legend(FALSE);
                #$chart->set_tamanho($largura = 300,$altura = 300);
                #$chart->show();

                $panel->fecha();

            $grid->fechaColuna();
           
            # Área Central 
            $grid->abreColuna(12,12,8);
    
    
                # Formulário de Pesquisa
                $form = new Form('?fase=motivo');

                # Cria um array com os tipo possíveis
                $selectMotivo = "SELECT DISTINCT idMotivo,
                                      tbmotivo.motivo 
                                 FROM tbmotivo JOIN tbservidor ON (tbservidor.motivo = tbmotivo.idMotivo)
                                 WHERE situacao = 2
                                 ORDER BY 2";

                $motivosPossiveis = $pessoal->select($selectMotivo);

                $controle = new Input('parametroMotivo','combo','Motivo:',1);
                $controle->set_size(8);
                $controle->set_title('Filtra por Motivo');
                $controle->set_array($motivosPossiveis);
                $controle->set_valor(date("Y"));
                $controle->set_valor($parametroMotivo);
                $controle->set_onChange('formPadrao.submit();');
                $controle->set_linha(1);
                $controle->set_col(6);
                $form->add_item($controle);

                $form->show();

                $select = 'SELECT tbservidor.idfuncional,
                                  tbpessoa.nome,
                                  tbservidor.idServidor,
                                  tbservidor.dtAdmissao,
                                  tbservidor.dtDemissao
                             FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                             LEFT JOIN tbmotivo on (tbservidor.motivo = tbmotivo.idMotivo)
                            WHERE tbservidor.motivo = '.$parametroMotivo.'
                              AND situacao = 2
                         ORDER BY dtDemissao';		


                $result = $pessoal->select($select);

                $tabela = new Tabela();
                $tabela->set_titulo('Relatório Anual de Servidores Aposentados por Motivo');
                $tabela->set_tituloLinha2('Com Informaçao de Contatos');
                $tabela->set_subtitulo('Ordenado pela Data de Saída');

                $tabela->set_label(array('IdFuncional','Nome','Cargo','Admissão','Saída'));
                #$relatorio->set_width(array(10,20,10,10,10,10,10,10,10));
                $tabela->set_align(array('center','left','left','center','center','left'));
                $tabela->set_funcao(array(NULL,NULL,NULL,"date_to_php","date_to_php"));

                $tabela->set_classe(array(NULL,NULL,"pessoal"));
                $tabela->set_metodo(array(NULL,NULL,"get_cargo"));

                $tabela->set_conteudo($result);

                $tabela->set_idCampo('idServidor');
                $tabela->set_editar('?fase=editarMotivo');
                $tabela->show();

        $grid->fechaColuna();
        $grid->fechaGrid();
        break;
    
####################################################################################################################

        # Estatística por Ano
        case "anoEstatistica" : 
            
            $grid = new Grid();
            $grid->abreColuna(12);
            
            # Abre um callout
            $panel = new Callout();
            $panel->abre();
            
            # Título
            tituloTable("Estatística por Ano da Aposentadoria");
            
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
    
    # Listagem de idade para aposentadoria
    case "dataIdade" : 
    
        # Formulário de Pesquisa
        $form = new Form('?fase=dataIdade');

        $controle = new Input('parametroSexo','combo','Sexo:',1);
        $controle->set_size(8);
        $controle->set_title('Filtra pelo Sexo');
        $controle->set_array(array("Masculino","Feminino"));
        $controle->set_valor(date("Y"));
        $controle->set_valor($parametroSexo);
        $controle->set_onChange('formPadrao.submit();');
        $controle->set_linha(1);
        $controle->set_col(3);
        $form->add_item($controle);

        $form->show();

        $select ='SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbservidor.dtAdmissao,
                     tbpessoa.dtNasc,
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
        $tabela->set_titulo('Relatório de Estatutários com Idade para Aposentadoria');
        $tabela->set_subtitulo('Servidores do Sexo '.$parametroSexo);
        $tabela->set_label(array('IdFuncional','Nome','Lotaçao','Admissão','Nascimento','Idade','Aposentadoria','Compulsória'));
        #$relatorio->set_width(array(10,30,30,0,10,10,10));
        $tabela->set_align(array("center","left","left"));
        $tabela->set_funcao(array(NULL,NULL,NULL,"date_to_php","date_to_php"));

        $tabela->set_classe(array(NULL,NULL,"pessoal",NULL,NULL,"pessoal","pessoal","pessoal"));
        $tabela->set_metodo(array(NULL,NULL,"get_CargoRel",NULL,NULL,"get_idade","get_dataAposentadoria","get_dataCompulsoria"));

        $tabela->set_conteudo($result);
        
        $tabela->set_idCampo('idServidor');
        $tabela->set_editar('?fase=editarIdade');
        $tabela->show();
        break;
    
    ####################################################################################################################
    
    # Listagem de idade e tempo averbado para aposentadoria
    case "dataIdadeTempo" : 
    
        # Formulário de Pesquisa
        $form = new Form('?fase=dataIdadeTempo');

        $controle = new Input('parametroSexo','combo','Sexo:',1);
        $controle->set_size(8);
        $controle->set_title('Filtra pelo Sexo');
        $controle->set_array(array("Masculino","Feminino"));
        $controle->set_valor(date("Y"));
        $controle->set_valor($parametroSexo);
        $controle->set_onChange('formPadrao.submit();');
        $controle->set_linha(1);
        $controle->set_col(3);
        $form->add_item($controle);

        $form->show();

        $select ='SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbservidor.dtAdmissao,
                     tbpessoa.dtNasc,
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
        $tabela->set_titulo('Relatório de Estatutários com Idade e Tempo de Serviço para Aposentadoria');
        $tabela->set_subtitulo('Servidores do Sexo '.$parametroSexo);
        $tabela->set_label(array('IdFuncional','Nome','Lotaçao','Admissão','Nascimento','Idade','Aposentadoria','Compulsória',"Tempo Serviço (dias)","Ocorrências (dias)","Dias Faltando"));
        #$relatorio->set_width(array(10,30,30,0,10,10,10));
        $tabela->set_align(array("center","left","left"));
        $tabela->set_funcao(array(NULL,NULL,NULL,"date_to_php","date_to_php"));

        $tabela->set_classe(array(NULL,NULL,"pessoal",NULL,NULL,"pessoal","pessoal","pessoal","Aposentadoria","Aposentadoria","Aposentadoria"));
        $tabela->set_metodo(array(NULL,NULL,"get_Cargo",NULL,NULL,"get_idade","get_dataAposentadoria","get_dataCompulsoria","get_tempoGeral","get_ocorrencias","get_diasFaltando"));

        $tabela->set_conteudo($result);
        
        $tabela->set_idCampo('idServidor');
        $tabela->set_editar('?fase=editarTempo');
        $tabela->show();
        break;
    
    ################################################################

        
        case "editarAno" :
            br(8);
            aguarde();
            
            # Informa o $id Servidor
            set_session('idServidorPesquisado',$id);
            
            # Informa a origem
            set_session('origem','areaAposentadoria.php');
            
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
        
        case "editarIdade" :
            br(8);
            aguarde();
            
            # Informa o $id Servidor
            set_session('idServidorPesquisado',$id);
            
            # Informa a origem
            set_session('origem','areaAposentadoria.php?fase=dataIdade');
            
            # Carrega a página específica
            loadPage('servidorMenu.php');
            break; 
        
    ################################################################
        
        case "editarTempo" :
            br(8);
            aguarde();
            
            # Informa o $id Servidor
            set_session('idServidorPesquisado',$id);
            
            # Informa a origem
            set_session('origem','areaAposentadoria.php?fase=dataIdadeTempo');
            
            # Carrega a página específica
            loadPage('servidorMenu.php');
            break; 
        
    ################################################################
        
    }
    $page->terminaPagina();  
    
}else{
    loadPage("../../areaServidor/sistema/login.php");
}
