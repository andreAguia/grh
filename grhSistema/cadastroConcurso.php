<?php
/**
 * Cadastro de Concursos
 *  
 * By Alat
 */

# Reservado para o servidor logado
$idUsuario = NULL;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase','listar');
    $subFase = get('subFase',1);
    
    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh',FALSE);
    if($grh){
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de concurso";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    # Pega os parâmetros
    $parametroAno = post('parametroAno',get_session('parametroAno'));
    $parametroTipo = post('parametroTipo',get_session('parametroTipo'));
        
    # Joga os parâmetros par as sessions    
    set_session('parametroAno',$parametroAno);
    set_session('parametroTipo',$parametroTipo);
    
    # Pega os dados do concurso
    $concurso = new Concurso($id);
    
    # Pega os dados do concurso
    if(!vazio($id)){
         $dados = $concurso->get_dados();
    }
    
    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    if($fase <> "relatorio"){
        AreaServidor::cabecalho();
    }

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Concursos');

    # botão de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # select da lista
    $select = 'SELECT idConcurso,
                      anobase,
                      dtPublicacaoEdital,
                      regime,
                      CASE tipo
                        WHEN 1 THEN "Adm & Tec"
                        WHEN 2 THEN "Professor"
                        ELSE "--"
                      END,
                      orgExecutor,
                      idConcurso,
                      idConcurso,
                      idConcurso
                 FROM tbconcurso LEFT JOIN tbplano USING (idPlano)
                WHERE TRUE';
    
    if(!vazio($parametroAno)){
        $select .= ' AND anoBase = '.$parametroAno;
    }
    
    if(!vazio($parametroTipo)){
        $select .= ' AND tipo = '.$parametroTipo;
    }
    
    $select .= ' ORDER BY anobase desc, dtPublicacaoEdital desc';
    
    $objeto->set_selectLista ($select);

    # select do edita
    $objeto->set_selectEdita('SELECT anobase,
                                     edital,
                                     dtPublicacaoEdital,
                                     regime,
                                     tipo,
                                     orgExecutor,
                                     idPlano,
                                     obs
                                FROM tbconcurso
                               WHERE idConcurso = '.$id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('cadastroConcurso.php?fase=editar&id='.$id);
    $objeto->set_voltarForm('cadastroConcurso.php?fase=editar&id='.$id);
    
    # Exclusão somente para administradores
    if(Verifica::acesso($idUsuario,1)){
        $objeto->set_linkExcluir('?fase=excluir');
    }

    # Parametros da tabela
    $objeto->set_label(array("id","Ano Base","Publicação <br/>do Edital","Regime","Tipo","Executor","Ativos","Inativos"));
    $objeto->set_width(array(5,10,10,10,10,20,10,10));
    $objeto->set_align(array("center"));
    
    $objeto->set_rowspan(1);
    $objeto->set_grupoCorColuna(1);
    
    $objeto->set_funcao(array(NULL,NULL,'date_to_php'));

    $objeto->set_classe(array(NULL,NULL,NULL,NULL,NULL,NULL,"Grh","Grh"));
    $objeto->set_metodo(array(NULL,NULL,NULL,NULL,NULL,NULL,"get_numServidoresAtivosConcurso","get_numServidoresInativosConcurso"));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbconcurso');

    # Nome do campo id
    $objeto->set_idCampo('idConcurso');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Foco do form
    $objeto->set_formFocus('anobase');

    # Pega os dados da combo de Plano e Cargos
    $result = $pessoal->select('SELECT idPlano, 
                                      numDecreto
                                  FROM tbplano
                              ORDER BY numDecreto');

    # Campos para o formulario
    $objeto->set_campos(array(
        array ('linha' => 1,
               'nome' => 'anobase',
               'label' => 'Ano:',
               'tipo' => 'texto',
               'autofocus' => TRUE,
               'col' => 2,
               'size' => 10,
               'title' => 'Ano base do concurso'),
        array ('linha' => 2,
               'nome' => 'edital',
               'label' => 'Processo do Edital:',
               'tipo' => 'texto',
               'title' => 'Número do processo do edital do concurso',
               'col' => 3,
               'size' => 20),
        array ('linha' => 2,
               'nome' => 'dtPublicacaoEdital',
               'label' => 'Data da Publicação do Edital:',
               'tipo' => 'data',
               'title' => 'Data da Publicação do Edital',
               'col' => 3,
               'size' => 20),
         array ('linha' => 3,
               'nome' => 'regime',
               'label' => 'Regime:',
               'tipo' => 'combo',                              
               'array' => array("CLT","Estatutário"),
               'col' => 3,
               'size' => 20),
        array ('linha' => 3,
               'nome' => 'tipo',
               'label' => 'Tipo:',
               'tipo' => 'combo',                              
               'array' => array(array(NULL,NULL),
                                array(1,"Adm & Tec"),
                                array(2,"Professor")),
               'col' => 3,
               'size' => 20),
         array ('linha' => 3,
               'nome' => 'orgExecutor',
               'label' => 'Executor:',
               'tipo' => 'texto',
                'col' => 4,
               'size' => 30),
        array ('linha' => 3,
               'nome' => 'idPlano',
               'label' => 'Plano de Cargos:',
               'tipo' => 'combo',
               'array' => $result,
               'col' => 3,
               'size' => 30),
        array ('linha' => 4,
               'col' => 12,
               'nome' => 'obs',
               'label' => 'Observação:',
               'tipo' => 'textarea',
               'size' => array(80,5))));

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);
    
    # Imagem
    $imagem1 = new Imagem(PASTA_FIGURAS.'pie.png',NULL,15,15);
    
    # Grafico
    $botaoGra = new Button();
    $botaoGra->set_title("Exibe gráfico da quantidade de servidores");
    $botaoGra->set_url("?fase=grafico");
    $botaoGra->set_imagem($imagem1);
    #$botaoGra->set_accessKey('G');
    #$objeto->set_botaoListarExtra(array($botaoGra));
    
    $objeto->set_botaoVoltarLista(FALSE);
    $objeto->set_botaoIncluir(FALSE);

    ################################################################
    
    switch ($fase){
        
        case "" :            
        case "listar" :            
            
            # Limita o tamanho da tela
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
            
            # Incluir
            $botaoInserir = new Button("Incluir","?fase=editar");
            $botaoInserir->set_title("Incluir"); 
            $menu1->add_link($botaoInserir,"right");
            
            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dessa pesquisa");
            $botaoRel->set_url("../grhRelatorios/acumulacao.geral.php");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            #$menu1->add_link($botaoRel,"right");

            $menu1->show();
            
            # Pega os dados da combo ano
            $result = $pessoal->select('SELECT distinct anoBase, anoBase
                                          FROM tbconcurso
                                      ORDER BY anoBase');
            
            array_unshift($result, array(NULL,"Todos")); 
            
            # Formulário de Pesquisa
            $form = new Form('?');
            
            # Ano    
            $controle = new Input('parametroAno','combo','Ano:',1);
            $controle->set_size(20);
            $controle->set_title('Ano base');
            $controle->set_valor($parametroAno);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $controle->set_array($result);
            $controle->set_autofocus(TRUE);
            $form->add_item($controle);
            
            # Tipo    
            $controle = new Input('parametroTipo','combo','Tipo:',1);
            $controle->set_size(20);
            $controle->set_title('Tipo do concurso');
            $controle->set_valor($parametroTipo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            $controle->set_array(array(array(NULL,"Todos"),array(1,"Adm & Tec"),array(2,"Professor")));            
            $form->add_item($controle);
            
            $form->show();            
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            
            $objeto->listar();
            break;
        
    ################################################################
			
        case "editar" :
            
            # Limita a Tela 
            $grid = new Grid();
            $grid->abreColuna(12);
            
            if(vazio($id)){
                # Vai para a rotina de inclusão
                $objeto->editar();
            }else{
                
                # Rotina de edição
                $menu1 = new MenuBar();

                # Voltar
                $botaoVoltar = new Link("Voltar","?");
                $botaoVoltar->set_class('button');
                $botaoVoltar->set_title('Voltar a página anterior');
                $botaoVoltar->set_accessKey('V');
                $menu1->add_link($botaoVoltar,"left");
                
                # Incluir Publicação
                $botaoInserir = new Button("Incluir Publicação","cadastroConcursoPublicacao.php?fase=editar&idConcurso=".$id);
                $botaoInserir->set_title("Incluir Publicação");
                $menu1->add_link($botaoInserir,"right");

                # Editar
                #$botaoEditar = new Button("Editar Concurso","?fase=editardeFato&id=".$id);
                #$botaoEditar->set_title("Editar concurso"); 
                #$menu1->add_link($botaoEditar,"right");

                $menu1->show();

                # Exibe os dados do Concurso
                $concurso->exibeDadosConcurso();
                
                $grid->fechaColuna();
                
                #######################################################
                # Menu
                
                $grid->abreColuna(3);

                $painel = new Callout();
                $painel->abre();

                # Inicia o Menu de Cargos                
                $menu = new Menu("menuProcedimentos");
                $menu->add_item('titulo','Menu');                
                $menu->add_item('link','<b>Publicações</b>','?fase=editar&id='.$id);
                
                if($dados["tipo"] == 2){
                    $menu->add_item('link','Vagas','?fase=concursoVagas&id='.$id);
                }else{
                    $menu->add_item('link','Servidores Ativos','?fase=listaServidoresAtivos&id='.$id);
                    $menu->add_item('link','Servidores Inativos','?fase=listaServidoresInativos&id='.$id);
                }
                
                $menu->show();

                $painel->fecha();

                $grid->fechaColuna();
                
                #######################################################3
                
                $grid->abreColuna(9);
                
                # Exibe o Edital
                
                $select ="SELECT 'Edital Oficial do Concurso',
                                 dtPublicacaoEdital,
                                 idConcurso
                            FROM tbconcurso
                           WHERE idConcurso = $id";
                
                $conteudo = $pessoal->select($select);
                $numConteudo = $pessoal->count($select);
                
                if($numConteudo > 0){
                    # Monta a tabela
                    $tabela = new Tabela();
                    $tabela->set_conteudo($conteudo);
                    $tabela->set_label(array("Descrição","Data","Publicação"));
                    $tabela->set_titulo("Edital");
                    $tabela->set_funcao(array(NULL,"date_to_php"));
                    $tabela->set_align(array("left"));
                    $tabela->set_width(array(70,10,10));
                    $tabela->set_totalRegistro(FALSE);
                    
                    $formatacaoCondicional = array( array('coluna' => 0,
                                                          'valor' => 'Edital Oficial do Concurso',
                                                          'operador' => '=',
                                                          'id' => 'listaDados'));
                    $tabela->set_formatacaoCondicional($formatacaoCondicional);
                    
                    $tabela->set_classe(array(NULL,NULL,"Concurso"));
                    $tabela->set_metodo(array(NULL,NULL,"exibeEdital"));
                    
                    $tabela->show();
                }else{
                    tituloTable("Publicações");
                    callout("Nenhum Registro Encontrado","secondary");
                }
                
                # Exibe as Publicações

                $select ="SELECT descricao,
                                 data,
                                 pag,
                                 idConcursoPublicacao,
                                 idConcursoPublicacao,
                                 idConcursoPublicacao
                            FROM tbconcursopublicacao
                           WHERE idConcurso = $id  
                        ORDER BY data desc";
                
                $conteudo = $pessoal->select($select);
                $numConteudo = $pessoal->count($select);
                
                if($numConteudo > 0){
                    # Monta a tabela
                    $tabela = new Tabela();
                    $tabela->set_conteudo($conteudo);
                    $tabela->set_label(array("Descrição","Data","Pag","Publicação"));
                    $tabela->set_titulo("Publicações");
                    $tabela->set_funcao(array(NULL,"date_to_php"));
                    $tabela->set_align(array("left"));
                    $tabela->set_width(array(50,10,10,10));
                    
                    $tabela->set_classe(array(NULL,NULL,NULL,"ConcursoPublicacao"));
                    $tabela->set_metodo(array(NULL,NULL,NULL,"exibePublicacao"));
                    
                    $tabela->set_editar('cadastroConcursoPublicacao.php?fase=editar&idConcurso='.$id);
                    $tabela->set_idCampo('idConcursoPublicacao');
                    
                    $tabela->set_excluir('cadastroConcursoPublicacao.php?fase=excluir&idConcurso='.$id);
                    $tabela->set_idCampo('idConcursoPublicacao');
                    
                    $tabela->show();
                }else{
                    tituloTable("Publicações");
                    callout("Nenhum Registro Encontrado","secondary");
                }
            
                $grid->fechaColuna();
                
                 #######################################################
            }
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
    ################################################################
			
        case "concursoVagas" :
            
            # Limita a Tela 
            $grid = new Grid();
            $grid->abreColuna(12);
            
                # Rotina de edição
                $menu1 = new MenuBar();

                # Voltar
                $botaoVoltar = new Link("Voltar","?");
                $botaoVoltar->set_class('button');
                $botaoVoltar->set_title('Voltar a página anterior');
                $botaoVoltar->set_accessKey('V');
                $menu1->add_link($botaoVoltar,"left");

                # Incluir Vaga
                $botaoInserir = new Button("Incluir Vaga","#");
                $botaoInserir->set_title("Incluir Vaga");
                $menu1->add_link($botaoInserir,"right");

                # Editar
                #$botaoEditar = new Button("Editar Concurso","?fase=editardeFato&id=".$id);
                #$botaoEditar->set_title("Editar concurso"); 
                #$menu1->add_link($botaoEditar,"right");

                $menu1->show();

                # Exibe os dados do Concurso
                $concurso->exibeDadosConcurso();
                
                $grid->fechaColuna();
                
                #######################################################
                # Menu
                
                $grid->abreColuna(3);

                $painel = new Callout();
                $painel->abre();

                # Inicia o Menu de Cargos
                $menu = new Menu("menuProcedimentos");
                $menu->add_item('titulo','Menu');
                
                $menu->add_item('link','Publicações','?fase=editar&id='.$id);
                $menu->add_item('link','<b>Vagas</b>','?fase=concursoVagas&id='.$id);
                
                $menu->show();

                $painel->fecha();

                $grid->fechaColuna();
                
                #######################################################3
                
                $grid->abreColuna(9);
                
                # Pega o tipo do concurso
                $dados = $concurso->get_dados();
                $tipo = $dados["tipo"];
                
                if($tipo == 1){
                    echo "adm";
                }else{
                    # Exibe as vagas de Docente
                    $select ='SELECT tblotacao.DIR,
                                     tblotacao.GER,
                                     tbcargo.nome,
                                     area,
                                     idServidor,
                                     tbvagahistorico.obs,
                                     idVagaHistorico
                                FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                                                     JOIN tblotacao USING (idLotacao)
                                                     JOIN tbvaga USING (idVaga)
                                                     JOIN tbcargo USING (idCargo)
                               WHERE idConcurso = '.$id.' ORDER BY tblotacao.DIR, tblotacao.GER desc';

                    $conteudo = $pessoal->select($select);
                    $numConteudo = $pessoal->count($select);

                    if($numConteudo > 0){
                        # Monta a tabela
                        $tabela = new Tabela();
                        $tabela->set_conteudo($conteudo);
                        $tabela->set_align(array("center","center","center","left","left"));
                        $tabela->set_label(array("Centro","Laboratório","Cargo","Área","Servidor","Obs"));
                        $tabela->set_titulo("Vagas de Professores");
                        $tabela->set_classe(array(NULL,NULL,NULL,NULL,"Vaga"));
                        $tabela->set_metodo(array(NULL,NULL,NULL,NULL,"get_Nome"));
                        $tabela->set_numeroOrdem(TRUE);
                        
                        $tabela->set_rowspan(0);
                        $tabela->set_grupoCorColuna(0);
                        
                        $tabela->show();
                    }else{
                        tituloTable("Vagas de Professores");
                        callout("Nenhuma vaga cadastrada","secondary");
                    }
                }
            
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
    ################################################################
			
        case "editardeFato" :
            $objeto->editar($id);
            break;
        
    ################################################################        
        
        case "excluir" :	
        case "gravar" :
            $objeto->$fase($id);
            break;
        
    ################################################################
			
        case "aguarde" :
            br(10);
            aguarde();
            br();
            loadPage('?fase=listaServidores&id='.$id);
            break;
    
    ################################################################

        case "listaServidoresAtivos" :            
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);
            
            # Informa a origem
            set_session('origem','cadastroConcurso.php?fase=listaServidoresAtivos&id='.$id);
			
            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $linkVoltar = new Link("Voltar","?");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title('Volta para a página anterior');
            $linkVoltar->set_accessKey('V');
            $menu->add_link($linkVoltar,"left");
             
            # Relatório
            $imagem2 = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dos Servidores");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("?fase=relatorio&subFase=1&id=$id");            
            $botaoRel->set_imagem($imagem2);
            $menu->add_link($botaoRel,"right");

            $menu->show();

            # Lista de Servidores Ativos
            $lista = new ListaServidores('Servidores Estatutários Ativos do Concurso de '.$pessoal->get_nomeConcurso($id));
            $lista->set_situacao(1);				
            $lista->set_concurso($id);            
            $lista->showTabela();
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
################################################################
            
        case "listaServidoresInativos" :            
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);
            
            # Informa a origem
            set_session('origem','cadastroConcurso.php?fase=listaServidoresInativos&id='.$id);
						
            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $linkVoltar = new Link("Voltar","?");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title('Volta para a página anterior');
            $linkVoltar->set_accessKey('V');
            $menu->add_link($linkVoltar,"left");
             
            # Relatório
            $imagem2 = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dos Servidores");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("?fase=relatorio&subFase=2&id=$id");            
            $botaoRel->set_imagem($imagem2);
            $menu->add_link($botaoRel,"right");

            $menu->show();

            # Lista de Servidores Inativos
            $lista = new ListaServidores('Servidores Inativos do Concurso de '.$pessoal->get_nomeConcurso($id));
            $lista->set_situacao(1);				
            $lista->set_situacaoSinal("<>");
            $lista->set_concurso($id);            
            $lista->showTabela();
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
################################################################
			
        case "relatorio" :
            if($subFase == 1){        
                # Lista de Servidores Ativos
                $lista = new ListaServidores('Servidores Ativos');
                $lista->set_situacao(1);				
	        $lista->set_concurso($id);   
                $lista->showRelatorio();
            }else{            
                # Lista de Servidores Inativos
                $lista = new ListaServidores('Servidores Inativos');
               	$lista->set_situacao(1);				
	        $lista->set_situacaoSinal("<>");
	        $lista->set_concurso($id);         
                $lista->showRelatorio();
            }
            break;
    
################################################################
            
        case "grafico" :
            # Botão voltar
            botaoVoltar('?');
            
            # Exibe o Título
            $grid = new Grid();
            $grid->abreColuna(12);
            
            # Pega os dados
            $selectGrafico = 'SELECT anoBase, count(tbservidor.idServidor) 
                                FROM tbservidor LEFT JOIN tbconcurso ON (tbservidor.idConcurso = tbconcurso.idConcurso)
                               WHERE tbservidor.situacao = 1
                                 AND tbservidor.idPerfil = 1
                            GROUP BY anoBase';

            $servidores = $pessoal->select($selectGrafico);
            
            titulo('Servidores Estatutários Concursados por Concurso');
            
            $grid3 = new Grid();
            $grid3->abreColuna(4);
            br();

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($servidores);
            $tabela->set_label(array("Concurso","Servidores"));
            $tabela->set_width(array(80,20));
            $tabela->set_align(array("left","center"));    
            $tabela->show();

            $grid3->fechaColuna();
            $grid3->abreColuna(8);

            $chart = new Chart("Pie",$servidores);
            $chart->show();

            $grid3->fechaColuna();
            $grid3->fechaGrid();
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
################################################################
            
        case "listaVagasConcurso" :            
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);
            
            $concurso = new Concurso($id);
            $dados = $concurso->get_dados();
            
            $anoBase = $dados["anobase"];
            $dtPublicacao = $dados["dtPublicacaoEdital"];
            
            if(!vazio($dtPublicacao)){
                $dtPublicacao = date_to_php($dtPublicacao);
            }
            
            $titulo = "Vagas do concurso de $anoBase<br>Edital Publicado em $dtPublicacao";
						
            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $linkVoltar = new Link("Voltar","?");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title('Volta para a página anterior');
            $linkVoltar->set_accessKey('V');
            $menu->add_link($linkVoltar,"left");

            $menu->show();

            # Conecta com o banco de dados
            $servidor = new Pessoal();

            $select ='SELECT concat(tbconcurso.anobase," - Edital: ",DATE_FORMAT(tbconcurso.dtPublicacaoEdital,"%d/%m/%Y")) as concurso,
                             concat(IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) as lotacao,
                             area,
                             idServidor,
                             tbvagahistorico.obs,
                             idVagaHistorico
                        FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                                             JOIN tblotacao USING (idLotacao)
                       WHERE idConcurso = '.$id.' ORDER BY tbconcurso.dtPublicacaoEdital desc';

            $conteudo = $pessoal->select($select);
            $numConteudo = $pessoal->count($select);
            
            if($numConteudo > 0){
                # Monta a tabela
                $tabela = new Tabela();
                $tabela->set_conteudo($conteudo);
                $tabela->set_align(array("left","left","left","left","left"));
                $tabela->set_label(array("Concurso","Laboratório","Área","Servidor","Obs"));
                $tabela->set_titulo($titulo);
                $tabela->set_classe(array(NULL,NULL,NULL,"Vaga"));
                $tabela->set_metodo(array(NULL,NULL,NULL,"get_Nome"));
                $tabela->set_numeroOrdem(TRUE);
                $tabela->show();
            }else{
                tituloTable($titulo);
                callout("Nenhuma vaga cadastrada","secondary");
            }
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
    ################################################################
    }
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}