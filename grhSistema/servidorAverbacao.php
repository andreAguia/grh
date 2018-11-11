<?php
/**
 * Cadastro de Tempo de Serviço
 *  
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = NULL;              # Servidor logado
$idServidorPesquisado = NULL;	# Servidor Editado na pesquisa do sistema do GRH

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso){    
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase','listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    # Pega o parametro de pesquisa (se tiver)
    $parametro = retiraAspas(post('parametro',get('parametro')));

    # Ordem da tabela
    $orderCampo = get('orderCampo');
    $orderTipo = get('orderTipo');

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################

    # Exibe os dados do Servidor
    $objeto->set_rotinaExtra("get_DadosServidor");
    $objeto->set_rotinaExtraParametro($idServidorPesquisado); 

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Cadastro de Tempo de Serviço Averbado');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # ordenação
    if (is_null($orderCampo)) {
        $orderCampo = "1";
    }

    if (is_null($orderTipo)) {
        $orderTipo = 'desc';
    }
    
    $select = 'SELECT dtInicial,
                    dtFinal,
                    dias,
                    empresa,
                    CASE empresaTipo
                       WHEN 1 THEN "Pública"
                       WHEN 2 THEN "Privada"
                    END,
                    CASE regime
                       WHEN 1 THEN "Celetista"
                       WHEN 2 THEN "Estatutário"
                       WHEN 3 THEN "Próprio"
                    END,
                    cargo,
                    dtPublicacao,
                    processo,
                    idAverbacao
               FROM tbaverbacao
              WHERE idServidor = '.$idServidorPesquisado.'
           ORDER BY '.$orderCampo.' '.$orderTipo;

    # select da lista
    $objeto->set_selectLista($select);

    # select do edita
    $objeto->set_selectEdita('SELECT empresa,
                                     empresaTipo,
                                     dtPublicacao,
                                     processo,
                                     dtInicial,
                                     dtFinal,
                                     dias,                                                                 
                                     regime,
                                     cargo,
                                     obs,
                                     idServidor
                                FROM tbaverbacao
                               WHERE idAverbacao = '.$id);

    # ordem da lista
    $objeto->set_orderCampo($orderCampo);
    $objeto->set_orderTipo($orderTipo);
    $objeto->set_orderChamador('?fase=listar');

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');
    
    $label = array("Data Inicial","Data Final","Dias","Empresa","Tipo","Regime","Cargo","Publicação","Processo");
    $align = array("center","center","center","left");
    $funcao = array("date_to_php","date_to_php",NULL,NULL,NULL,NULL,NULL,"date_to_php");

    # Parametros da tabela
    $objeto->set_label($label);
    #$objeto->set_width(array(10,10,5,25,5,5,8,10,12));	
    $objeto->set_align($align);
    $objeto->set_funcao($funcao);

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbaverbacao');

    # Nome do campo id
    $objeto->set_idCampo('idAverbacao');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'empresa',
                                       'label' => 'Empresa:',
                                       'tipo' => 'texto',
                                       'required' => TRUE,
                                       'autofocus' => TRUE,
                                       'size' => 80,                                   
                                       'title' => 'Nome da Empresa.',
                                       'col' => 6,
                                       'linha' => 1),
                               array ( 'nome' => 'empresaTipo',
                                       'label' => 'Tipo:',
                                       'tipo' => 'combo',
                                       'required' => TRUE,
                                       'array' => Array(Array(1,"Pública"),Array(2,"Privada")),
                                       'size' => 20,
                                       'col' => 2,
                                       'title' => 'Tipo da Empresa',
                                       'linha' => 1),
                               array ( 'nome' => 'dtPublicacao',
                                       'label' => 'Data da Pub. no DOERJ:',
                                       'tipo' => 'data',
                                       'required' => TRUE,
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Data da Publicação no DOERJ.',
                                       'linha' => 2),
                               array ( 'nome' => 'processo',
                                       'label' => 'Processo:',
                                       'tipo' => 'processo',
                                       'required' => TRUE,
                                       'size' => 30,
                                       'col' => 3,
                                       'title' => 'Número do Processo',
                                       'linha' => 2), 
                               array ( 'nome' => 'dtInicial',
                                       'label' => 'Data Inicial:',
                                       'tipo' => 'data',
                                       'notNull' => TRUE,
                                       'size' => 20,
                                       'col' => 3,
                                       'required' => TRUE,
                                       'title' => 'Data inícial do Período.',
                                       'linha' => 3),
                               array ( 'nome' => 'dtFinal',
                                       'label' => 'Data Final:',
                                       'tipo' => 'data',
                                       'required' => TRUE,
                                       'size' => 20,
                                       'col' => 3,
                                       'notNull' => TRUE,
                                       'title' => 'Data final do Período.',
                                       'linha' => 3),
                               array ( 'nome' => 'dias',
                                       'label' => 'Dias:',
                                       'tipo' => 'numero',
                                       'required' => TRUE,
                                       'size' => 5,
                                       'col' => 2,
                                       'notNull' => TRUE,
                                       'title' => 'Quantidade de Dias Averbado.',
                                       'linha' => 3),
                               array ( 'nome' => 'regime',
                                       'label' => 'Regime:',
                                       'tipo' => 'combo',
                                       'col' => 3,
                                       'required' => TRUE,
                                       'array' => Array(Array(1,"Celetista"),Array(2,"Estatutário"),Array(3,"Próprio")),
                                       'size' => 20,                               
                                       'title' => 'Tipo do Regime',
                                       'linha' => 4),
                               array ( 'nome' => 'cargo',
                                       'label' => 'Cargo:',
                                       'tipo' => 'texto',
                                       'col' => 6,
                                       'size' => 100,                               
                                       'title' => 'Cargo',
                                       'linha' => 4),
                                array ('linha' => 9,
                                       'nome' => 'obs',
                                       'label' => 'Observação:',
                                       'tipo' => 'textarea',
                                       'size' => array(80,5)),
                               array ( 'nome' => 'idServidor',
                                       'label' => 'idServidor:',
                                       'tipo' => 'hidden',
                                       'padrao' => $idServidorPesquisado,
                                       'size' => 5,
                                       'title' => 'Matrícula',
                                       'linha' => 10)));
    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ################################################################

    switch ($fase){
        case "" :
        case "listar" :
            #$objeto->listar();
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);
            
            # Analisa o $parametro
            $dtSaida = $pessoal->get_dtSaida($idServidorPesquisado);      # Data de Saída de servidor inativo
            $dtHoje = date("Y-m-d");                                      # Data de hoje
            $dtFinal = NULL;                                              # Data a ser usada como final
            $dtDigitado = $parametro;
            
            # Dados para o controle
            $disabled = FALSE;
            $tipoControle = "data";
            $autofocus = TRUE;
            
            # Analisa a data
            if(!vazio($dtSaida)){           // Se tem saída é a saída
                $dtFinal = $dtSaida;
                $disabled = TRUE;
                $tipoControle = "texto";
                $autofocus = FALSE;
            }elseif(!vazio($dtDigitado)){   // Não tem saída e tem digitado, então é o digitado
                $dtFinal = $dtDigitado;     
            }else{                          // Não tem saída nem digitado então é hoje
                $dtFinal = $dtHoje;         
            }
            
            # Finalmente define o valor
            $parametro = $dtFinal;
            
            # Cria um menu
            $menu = new MenuBar();

            # Botão voltar
            $linkBotaoVoltar = new Button('Voltar','servidorMenu.php');
            $linkBotaoVoltar->set_title('Volta para a página anterior');
            $linkBotaoVoltar->set_accessKey('V');
            $menu->add_link($linkBotaoVoltar,"left");
            
            $imagem = new Imagem(PASTA_FIGURAS.'ajuda.png',NULL,15,15);
            $botaoHelp = new Button();
            $botaoHelp->set_imagem($imagem);
            $botaoHelp->set_title("Ajuda");
            $botaoHelp->set_url("https://docs.google.com/document/d/e/2PACX-1vSH4_OkFekLul3KY6AlTHP0WjDblvsQXdX1uA319UV4REs3d9YklhQJqSFoL_yrHfYEaSmX94RtQ47Q/pub");
            $botaoHelp->set_target("_blank");            
            $menu->add_link($botaoHelp,"right");
            
            # Relatório
            $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
            $botaoRel = new Button();
            $botaoRel->set_imagem($imagem);
            $botaoRel->set_title("Imprimir Relatório de Histórico de Tempo de Serviço Averbado");
            $botaoRel->set_url("../grhRelatorios/servidorAverbacao.php?data=$parametro");
            $botaoRel->set_target("_blank");
            $menu->add_link($botaoRel,"right");
            
            # Botão Incluir
            $linkBotaoIncluir = new Button('Incluir','?fase=editar');
            $linkBotaoIncluir->set_title('Incluir um Registro');
            $linkBotaoIncluir->set_accessKey('I');
            $menu->add_link($linkBotaoIncluir,"right");
            
            $menu->show();
            
            # Exibe os dados do servidor
            get_DadosServidor($idServidorPesquisado);
            
            #############################################################
            # Controle
            
            $grid1 = new Grid();
            $grid1->abreColuna(3);
            
            # Inicia o form
            $form = new Form('?');

            $controle = new Input('parametro',$tipoControle,'Data Final',1);
            $controle->set_size(30);
            $controle->set_title('Data final para contagem de dias. (Padrão: HOJE)');
            $controle->set_valor($parametro);
            $controle->set_autofocus($autofocus);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_disabled($disabled);
            $controle->set_col(12);
            $form->add_item($controle);
            $form->show();
            
            $grid1->fechaColuna();            
            $grid1->abreColuna(9);
            
            #############################################################
            # Cálculos
            
            ####
            # Tempo de Serviço
            
            # Pega o sexo do servidor
            $sexo = $pessoal->get_sexo($idServidorPesquisado);
            
            # Tempo de Serviço
            $uenf = $pessoal->get_tempoServicoUenf($idServidorPesquisado,$parametro);
            $publica = $pessoal->get_totalAverbadoPublico($idServidorPesquisado);
            $privada = $pessoal->get_totalAverbadoPrivado($idServidorPesquisado);
            $totalTempo = $uenf + $publica + $privada;
            
            $dados1 = array(
                    array("Tempo de Serviço na UENF ",$uenf),
                    array("Tempo Averbado Empresa Pública",$publica),
                    array("Tempo Averbado Empresa Privada",$privada),
                    array("Total",$totalTempo." dias<br/>(".dias_to_diasMesAno($totalTempo).")")
            );
            
            ####
            # Ocorrências
            $reducao = "SELECT tbtipolicenca.nome as tipo,
                               SUM(numDias) as dias
                          FROM tblicenca JOIN tbtipolicenca USING(idTpLicenca)
                         WHERE idServidor = $idServidorPesquisado
                           AND tbtipolicenca.tempoServico IS TRUE
                      GROUP BY tbtipolicenca.nome";
            
            $dados2 = $pessoal->select($reducao);
            
            # Somatório
            $totalOcorrencias = array_sum(array_column($dados2, 'dias') );
            
            ####
            # Resumo
            
            # Define a idade que dá direito para cada gênero
            switch ($sexo){
                case "Masculino" :
                    $diasAposentadoria = 12775;
                    break;
                case "Feminino" :
                    $diasAposentadoria = 10950;
                    break;
            }
            
            # Calcula o tempo de serviço geral
            $totalTempoGeral = $totalTempo - $totalOcorrencias;
            
            # Dias que faltam
            $faltam = $diasAposentadoria - $totalTempoGeral;
            
            if($faltam < 0){
                $texto = "Dias Sobrando";
            }else{
                $texto = "Dias Faltando";
            }
            
            $dados3 = array(
                      array("Tempo de Serviço ",$totalTempo),
                      array("Ocorrências","($totalOcorrencias)"),
                      array("Total",$totalTempoGeral),
                      array("Dias para aposentadoria",$diasAposentadoria),
                      array($texto,$faltam." dias<br/>(".dias_to_diasMesAno($faltam).")")
            );
            
            ####
            # Aposentadoria
            $dtNascimento = $pessoal->get_dataNascimento($idServidorPesquisado);
            $idade = $pessoal->get_idade($idServidorPesquisado);
            $aposentadoria = $pessoal->get_dataAposentadoria($idServidorPesquisado);
            $Compulsoria = $pessoal->get_dataCompulsoria($idServidorPesquisado);
            
            # Define a idade que dá direito para cada gênero
            switch ($sexo){
                case "Masculino" :
                    $ii = 60;
                    break;
                case "Feminino" :
                    $ii = 55;
                    break;
            }
            
            $dados4 = array(
                    array("Idade do Servidor ",$idade),
                    array("Data de Nascimento ",$dtNascimento),
                    array("Data com Direito a Aposentadoria ($ii anos)",$aposentadoria),
                    array("Data da Compulsória (75 anos)",$Compulsoria)
            );
            
            ####
            # Análise
            
            $painel = new Callout("primary","center");
            $painel->abre();
                
                # Verifica se servidor é ativo
                $select2 = 'SELECT tbsituacao.idSituacao,
                                   tbsituacao.situacao
                              FROM tbsituacao LEFT JOIN tbservidor ON (tbservidor.situacao = tbsituacao.idsituacao)
                             WHERE idServidor = '.$idServidorPesquisado;

                $situacao = $pessoal->select($select2,FALSE);
                
                if($situacao[0] <> 1){
                    echo "Servidor $situacao[1] com $totalTempo dias registrados até a data de saída ($dtSaida)";
                }else{            
                    # Análise por dia
                    if($diasAposentadoria > $totalTempoGeral){
                        p("Ainda faltam <b>$faltam</b> dias para o servidor alcançar os <b>$diasAposentadoria</b> dias de serviço necessários para solicitar a aposentadoria.","exibeOcorrencia");
                    }else{
                        p("O servidor já alcançou os <b>$diasAposentadoria</b> dias de serviço para solicitar aposentadoria.","exibeOcorrencia");
                    }

                    # Análise por idade
                    if($ii > $idade){
                        p("O servidor ainda não alcançou os <b>$ii</b> anos de idade de para solicitar aposentadoria.","exibeOcorrencia");
                    }else{
                        p("O servidor já alcançou a idade para solicitar aposentadoria.","exibeOcorrencia");
                    }
                }
            
            $painel->fecha();
            
            $grid1->fechaColuna();
            $grid1->fechaGrid();
            
            #############################################################
            # Tempo de Serviço
            
            $grid1 = new Grid();
            $grid1->abreColuna(3);
            
            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_titulo('Tempo de Serviço');
            $tabela->set_conteudo($dados1);
            $tabela->set_label(array("Descrição","Dias"));
            $tabela->set_align(array("left","center"));
            $tabela->set_totalRegistro(FALSE);
            $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
                                                    'valor' => "Total",
                                                    'operador' => '=',
                                                    'id' => 'totalTempo')));
            
            $tabela->show();            
            $grid1->fechaColuna();
            
            #############################################################
            # Ocorrências que reduzem do Tempo de Serviço
            
            $grid1->abreColuna(3);
            
            # Adiciona na tabela
            if($totalOcorrencias == 0){
                array_push($dados2,array("Sem Ocorrências","---"));
            }else{
                array_push($dados2,array("Total",$totalOcorrencias));
            }
            
            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_titulo('Ocorrências');
            $tabela->set_conteudo($dados2);
            $tabela->set_label(array("Descrição","Dias"));
            $tabela->set_align(array("left","center"));
            $tabela->set_totalRegistro(FALSE);
            $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
                                                           'valor' => "Total",
                                                           'operador' => '=',
                                                           'id' => 'totalTempo')
                ));
            $tabela->show();            
            $grid1->fechaColuna();
            
            #############################################################
            # Resumo
            
            $grid1->abreColuna(3); 
            
            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_titulo('Resumo Geral');
            $tabela->set_conteudo($dados3);
            $tabela->set_label(array("Descrição","Dias"));
            $tabela->set_align(array("left","center"));
            $tabela->set_totalRegistro(FALSE);
            $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
                                                        'valor' => "Total",
                                                        'operador' => '=',
                                                        'id' => 'totalTempo'),
                                                    array('coluna' => 0,
                                                        'valor' => "Ocorrências",
                                                        'operador' => '=',
                                                        'id' => 'ocorrencia'),
                                                     array('coluna' => 0,
                                                           'valor' => "Dias Sobrando",
                                                           'operador' => '=',
                                                           'id' => 'diasSobrando'),
                                                     array('coluna' => 0,
                                                           'valor' => "Dias Faltando",
                                                           'operador' => '=',
                                                           'id' => 'diasFaltando')));
            $tabela->show();            
            $grid1->fechaColuna();
            
            #############################################################
            # Aposentadoria
            
            $grid1->abreColuna(3);
            
            
            
            # Monta a tabela do resumo de tempo
            $tabela = new Tabela();
            $tabela->set_titulo('Idade para Aposentadoria');
            $tabela->set_conteudo($dados4);
            $tabela->set_label(array("Descrição","Valor"));
            $tabela->set_align(array("left","center"));
            $tabela->set_totalRegistro(FALSE);
            $tabela->show();
            
            $grid1->fechaColuna();
            $grid1->fechaGrid();            
            
            #############################################################
            
            # Pega os dados
            $result = $pessoal->select($select);
            
            # Monta a tabela de tempo averbado
            $tabela = new Tabela();
            $tabela->set_titulo('Cadastro de Tempo de Serviço Averbado');
            $tabela->set_conteudo($result);
            $tabela->set_label($label);
            $tabela->set_align($align);
            $tabela->set_funcao($funcao);
            $tabela->set_idCampo('idAverbacao');
            $tabela->set_editar('?fase=editar&id=');
            $tabela->set_excluir('?fase=excluir&id=');
            $tabela->show();
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
            
        case "editar" :			
        case "excluir" :	
        case "gravar" :
            $objeto->$fase($id);
            break;
    }									 	 		

    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}