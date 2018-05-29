<?php
/**
 * Área de Férias
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
    $intra = new Intra();
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase');
    
    # Verifica se veio menu grh e registra o acesso no log
    $origem = get('origem',FALSE);
    if($origem){
        # Grava no log a atividade
        $atividade = "Visualizou a área de férias";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
    }
    
    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    set_session('areaFerias',FALSE);
    
    # Pega os parâmetros
    $parametroAno = post('parametroAno',get_session('parametroAno',date("Y")));
    $parametroLotacao = post('parametroLotacao',get_session('parametroLotacao'));
    
    # Agrupamento do Relatório
    $agrupamentoEscolhido = post('agrupamento',0);
    
    # Session do Relatório
    $select = get_session('sessionSelect');
    $titulo = get_session('sessionTitulo');
    $subTitulo = get_session('sessionSubTitulo');
        
    # Joga os parâmetros par as sessions    
    set_session('parametroAno',$parametroAno);
    set_session('parametroLotacao',$parametroLotacao);
    
    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();
    
    # Cabeçalho da Página
    if($fase <> "relatorio"){
        AreaServidor::cabecalho();
    }
    
    $grid = new Grid();
    $grid->abreColuna(12);

    # Cria um menu
    $menu1 = new MenuBar();

    # Voltar
    $botaoVoltar = new Link("Voltar","grh.php");
    $botaoVoltar->set_class('button');
    $botaoVoltar->set_title('Voltar a página anterior');
    $botaoVoltar->set_accessKey('V');
    $menu1->add_link($botaoVoltar,"left");
    
    # Ano Exercício
    $botaoExercicio = new Link("Ano Exercício","areaFeriasExercicio.php");
    $botaoExercicio->set_class('hollow button');
    $botaoExercicio->set_title('Férias por Ano Exercício');
    $menu1->add_link($botaoExercicio,"right");
    
    # Ano por Fruíção
    $botaoFruicao = new Link("Ano de Fruição");
    $botaoFruicao->set_class('button');
    $botaoFruicao->set_title('Férias por Ano em que foi realmente fruído');
    $menu1->add_link($botaoFruicao,"right");

    $menu1->show();
    
    # Título
    titulo("Área de Férias - Ano de Fruição (Data Inicial)");
    
    ################################################################
    
    # Formulário de Pesquisa
    $form = new Form('areaFeriasFruicao.php');

    # Cria um array com os anos possíveis
    $anoInicial = 1999;
    $anoAtual = date('Y');
    $anos = arrayPreenche($anoInicial,$anoAtual+2);
    
    $controle = new Input('parametroAno','combo','Ano de Fruição:',1);
    $controle->set_size(8);
    $controle->set_title('Filtra por Ano em que as férias foi/será fruída');
    $controle->set_array($anos);
    $controle->set_valor($parametroAno);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_linha(1);
    $controle->set_col(3);
    $form->add_item($controle);

    # Lotação
    $result = $pessoal->select('SELECT idlotacao, 
                                       concat(IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) lotacao
                                  FROM tblotacao
                                 WHERE ativo
                              ORDER BY ativo desc,lotacao');
    array_unshift($result,array("*",'Todas'));
    
    $controle = new Input('parametroLotacao','combo','Lotação:',1);
    $controle->set_size(30);
    $controle->set_title('Filtra por Lotação');
    $controle->set_array($result);
    $controle->set_valor($parametroLotacao);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_linha(1);
    $controle->set_col(9);
    $form->add_item($controle);

    $form->show();
            
    ################################################################
    
    switch ($fase){
        case "" : 
            br(4);
            aguarde();
            br();
            
            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
                p("Aguarde...","center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=exibeLista');
            break;
        
        case "exibeLista" :
        
            $grid2 = new Grid();
            
            # Área Lateral
            $grid2->abreColuna(3);
            
            #######################################
            
            # Resumo por Ano Exercício
            
            # Conecta com o banco de dados
            $servidor = new Pessoal();

            # Pega os dados
            $select = "SELECT anoExercicio,
                              count(*) as tot                          
                         FROM tbferias JOIN tbservidor ON (tbservidor.idServidor = tbferias.idServidor)
                                       JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                       JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE YEAR(tbferias.dtInicial)= $parametroAno
                          AND tbhistlot.data =(select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

                    if(($parametroLotacao <> "*") AND ($parametroLotacao <> "")){
                        $select .= ' AND (tblotacao.idlotacao = "'.$parametroLotacao.'")';
                    }

                    $select .= " GROUP BY anoExercicio ORDER BY anoExercicio";

            $resumo = $servidor->select($select);
            
            # Pega a soma dos campos
            $soma = 0;
            foreach ($resumo as $value){
                $soma += $value['tot'];
            }

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_label(array("Exercício","Solicitações"));
            $tabela->set_totalRegistro(FALSE);
            $tabela->set_rodape("Total de Solicitações: ".$soma);
            $tabela->set_align(array("center"));
            #$tabela->set_funcao(array("exibeDescricaoStatus"));
            $tabela->set_titulo("Solicitações Por Ano Exercício");
            $tabela->show();
            
            #######################################
            
            # Resumo por status
            
            # Conecta com o banco de dados
            $servidor = new Pessoal();

            # Pega os dados
            $select = "SELECT status,
                              count(*) as tot                          
                         FROM tbferias JOIN tbservidor ON (tbservidor.idServidor = tbferias.idServidor)
                                       JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                       JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE YEAR(tbferias.dtInicial)= $parametroAno
                          AND tbhistlot.data =(select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

                    if($parametroLotacao <> "*"){
                        $select .= ' AND (tblotacao.idlotacao = "'.$parametroLotacao.'")';
                    }

                    $select .= " GROUP BY status ORDER BY status";

            $resumo = $servidor->select($select);

            # Pega a soma dos campos
            $soma = 0;
            foreach ($resumo as $value){
                $soma += $value['tot'];
            }

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_label(array("Status","Solicitações"));
            $tabela->set_totalRegistro(FALSE);
            $tabela->set_rodape("Total de Solicitações: ".$soma);
            $tabela->set_align(array("center"));
            $tabela->set_funcao(array("exibeDescricaoStatus"));
            $tabela->set_titulo("Solicitações Por Status");
            $tabela->show();
            
            #######################################
            
            # Relatórios
            $menu = new Menu();
            $menu->add_item('titulo','Relatórios');
            $menu->add_item('linkWindow','Relatório Anual de Férias','../grhRelatorios/feriasAnual.php?parametroAno='.$parametroAno.'&lotacaoArea='.$parametroLotacao);  
            
            $menu->show();
            
            #######################################
            
            # Área Principal            
            $grid2->fechaColuna();
            $grid2->abreColuna(9);
            
                # Conecta com o banco de dados
                $servidor = new Pessoal();

                $select ='SELECT tbservidor.idfuncional,        
                             tbpessoa.nome,
                             tbservidor.idServidor,
                             tbferias.anoExercicio,
                             tbferias.dtInicial,
                             tbferias.numDias,
                             idFerias,
                             date_format(ADDDATE(tbferias.dtInicial,tbferias.numDias-1),"%d/%m/%Y") as dtf,
                             tbferias.status
                        FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                             JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                             JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                             JOIN tbferias ON (tbservidor.idServidor = tbferias.idServidor)
                       WHERE tbhistlot.data =(select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                       AND YEAR(tbferias.dtInicial)= '.$parametroAno;

                    if($parametroLotacao <> "*"){
                        $select .= ' AND (tblotacao.idlotacao = "'.$parametroLotacao.'")';
                    }

                    $select .= ' ORDER BY dtInicial';

                $result = $servidor->select($select);

                $tabela = new Tabela();
                $tabela->set_titulo("Por Solicitação");
                $tabela->set_label(array('IdFuncional','Nome','Lotação','Exercício','Dt Inicial','Dias','Período','Dt Final','Status'));
                $tabela->set_align(array("center","left","left"));
                $tabela->set_funcao(array(NULL,NULL,NULL,NULL,"date_to_php",NULL,NULL,NULL,NULL));
                $tabela->set_classe(array(NULL,NULL,"pessoal",NULL,NULL,NULL,"pessoal"));
                $tabela->set_metodo(array(NULL,NULL,"get_lotacaoSimples",NULL,NULL,NULL,"get_feriasPeriodo"));
                $tabela->set_conteudo($result);
                $tabela->show();
                                   
            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

        ###############################
    }
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}
