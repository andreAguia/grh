<?php
/**
 * Cadastro de Cargos em Comissão
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
    
    # Verifica se veio menu grh e registra o acesso no log
    $origem = get('origem',FALSE);
    if($origem){
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de cargo em comissão";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
    }
    
    # Verifica tipo de cargo será exibido (1->ativos ou 0->inativos)
    $tipo = get('tipo',1);

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    # Pega o parametro de pesquisa (se tiver)
    if (is_null(post('parametro'))){					# Se o parametro n?o vier por post (for nulo)
        $parametro = retiraAspas(get_session('sessionParametro'));	# passa o parametro da session para a variavel parametro retirando as aspas
    }else{ 
        $parametro = post('parametro');                # Se vier por post, retira as aspas e passa para a variavel parametro
        set_session('sessionParametro',$parametro);    # transfere para a session para poder recuperá-lo depois
    }

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

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    if($tipo){
        $complemento = " Ativos";
    }else{
        $complemento = " Inativos";
    }
    $objeto->set_nome('Cargos em Comissão'.$complemento);
    
    # botão de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # ordenação
    if(is_null($orderCampo))
            $orderCampo = "9 desc, 3";

    if(is_null($orderTipo))
            $orderTipo = 'asc';

    # select da lista
    $objeto->set_selectLista ('SELECT idTipoComissao,
                                      descricao,
                                      simbolo,
                                      valsal,
                                      vagas,
                                      idTipoComissao,
                                      idTipoComissao,
                                      idTipoComissao,
                                      IF(ativo = 0, "Não", "Sim") as ativo
                                 FROM tbtipocomissao
                                WHERE ativo = '.$tipo.'
                                  AND (descricao LIKE "%'.$parametro.'%"
                                   OR simbolo LIKE "%'.$parametro.'%" 
                                   OR idTipoComissao LIKE "%'.$parametro.'%") 
                             ORDER BY '.$orderCampo.' '.$orderTipo);

    # select do edita
    $objeto->set_selectEdita('SELECT descricao,
                                     simbolo,
                                     valsal,
                                     vagas,
                                     ativo,
                                     obs
                                FROM tbtipocomissao
                               WHERE idTipoComissao = '.$id);

    # ordem da lista
    $objeto->set_orderCampo($orderCampo);
    $objeto->set_orderTipo($orderTipo);
    $objeto->set_orderChamador('?fase=listar');

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    #$objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("id","Cargo","Simbolo","Valor (R$)","Vagas","Vagas Ocupadas","Ver Servidores","Vagas Disponíveis","Cargo Ativo?"));
    $objeto->set_width(array(5,20,10,10,10,10,10,10,10));
    $objeto->set_align(array("center"));
    
    $objeto->set_funcao(array(NULL,NULL,NULL,"formataMoeda"));
    $objeto->set_classe(array(NULL,NULL,NULL,NULL,NULL,'pessoal',NULL,'pessoal'));
    $objeto->set_metodo(array(NULL,NULL,NULL,NULL,NULL,'get_servidoresCargoComissao',NULL,'get_cargoComissaoVagasDisponiveis'));

    # Botão de exibição dos servidores
    $botao = new BotaoGrafico();
    $botao->set_label('');
    #$botao->set_title('Servidores com permissão a essa regra');
    $botao->set_url('?fase=listaServidores&id=');       
    $botao->set_image(PASTA_FIGURAS_GERAIS.'ver.png',20,20);

    # Coloca o objeto link na tabela			
    $objeto->set_link(array("","","","","","",$botao));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbtipocomissao');

    # Nome do campo id
    $objeto->set_idCampo('idTipoComissao');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Pega os dados da combo de Plano e Cargos
    $tabela = new Pessoal();
    $result = $tabela->select('SELECT idPlano, 
                                      numDecreto
                                  FROM tbplano
                              ORDER BY numDecreto');

    # Campos para o formulario
    $objeto->set_campos(array(
        array ('linha' => 1,
               'col' => 5,
               'nome' => 'descricao',
               'label' => 'Cargo em Comissão:',
               'tipo' => 'texto',
               'autofocus' => TRUE,
               'size' => 50),
         array ('linha' => 1,
                'col' => 3,
               'nome' => 'simbolo',
               'label' => 'Símbolo:',
               'tipo' => 'texto',
               'size' => 10),
        array ('linha' => 1,
               'col' => 2,
               'nome' => 'valsal',
               'label' => 'Valor do Salário:',
               'tipo' => 'moeda',
               'size' => 10),
        array ('linha' => 1,
               'col' => 1,
               'nome' => 'vagas',
               'label' => 'Vagas:',
               'tipo' => 'numero',
               'size' => 10),
        array ('linha' => 1,
               'col' => 1,
               'nome' => 'ativo',
               'title' => 'Informa se o cargo está ativo',
               'label' => 'Ativo:',
               'tipo' => 'combo',
               'padrao' => 1,
               'array' => array(array(0,"Não"),array(1,"Sim")),
               'size' => 10),        
        array ('linha' => 3,
               'nome' => 'obs',
               'label' => 'Observação:',
               'tipo' => 'textarea',
               'size' => array(80,5))));

    # Matrícula para o Log
    $objeto->set_idUsuario($idUsuario);
    
    # Relatório
    $imagem2 = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
    $botaoRel = new Button();
    $botaoRel->set_title("Abre relatório dos cargos exibidos na listagem abaixo");
    if($tipo){
        $botaoRel->set_onClick("window.open('../grhRelatorios/cargoComissaoAtivos.php','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");
    }else{
        $botaoRel->set_onClick("window.open('../grhRelatorios/cargoComissaoInativos.php','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");
    }
    $botaoRel->set_imagem($imagem2);
    #$botaoRel->set_accessKey('R');
    
    # Cargos Ativos
    $botaoAtivo = new Button("Cargos Ativos","?tipo=1");
    $botaoAtivo->set_title("Exibe os Cargos Ativos");
    
    # Cargos Ativos
    $botaoInativo = new Button("Cargos Inativos","?tipo=0");
    $botaoInativo->set_title("Exibe os Cargos Inativos");
    
    # Cria o array de botões
    $arrayBotoes = array($botaoRel);
    if($tipo){
        array_unshift($arrayBotoes,$botaoInativo);
    }else{
        array_unshift($arrayBotoes,$botaoAtivo);
    }
    
    # Informa o array
    $objeto->set_botaoListarExtra($arrayBotoes);

    ################################################################
    switch ($fase)
    {
        case "" :
        case "listar" :
            $objeto->listar();
            break;

        case "editar" :
            $objeto->editar($id);        
            break;

        case "excluir" :	
        case "gravar" :
            $objeto->$fase($id);
            break;

        case "listaServidores" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);
            
            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $linkVoltar = new Link("Voltar","?");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title('Volta para a página anterior');
            $linkVoltar->set_accessKey('V');
            $menu->add_link($linkVoltar,"left");
            
            # Relatórios
            $imagem2 = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dos Servidores");
            $botaoRel->set_onClick("abreFechaDivId('RelServidor');");
            $botaoRel->set_imagem($imagem2);
            $menu->add_link($botaoRel,"right");
             
            $menu->show();
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            
            # Menu Relatório    
            $div = new Div("RelServidor");
            $div->abre();

            $grid = new Grid("right");
            $grid->abreColuna(3);

            echo '<nav aria-label="You are here:" role="navigation">';
            echo '<ul class="breadcrumbs">';

            # Servidores
            echo '<li>';
            $link = new Link("Servidores Ativos","?fase=relatorio&&id=".$id);
            $link->set_title("Exibe a Lista de Servidores");
            $link->set_janela(TRUE);    
            $link->show();
            echo '</li>';

            # Histórico
            echo '<li>';
            $link = new Link("Histórico","../grhRelatorios/cargosComissionadosHistorico.php?cargo=".$id);
            $link->set_title("Exibe a Lista de aniversariantes deste setor");
            #$link->set_class("disabled");
            $link->set_janela(TRUE);    
            $link->show();
            echo '</li>';

            echo '</ul>';
            echo '</nav>';

            $grid->fechaColuna();
            $grid->fechaGrid();
            $div->fecha();
            
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);
            
            # Pega o nome do cargo
            $servidor = new Pessoal();  
            $nomeCargo = $pessoal->get_nomeCargoComissao($id);
            $simbolo = $pessoal->get_cargoComissaoSimbolo($id);
            
            # Lista de Servidores Ativos
            $lista = new ListaServidores('Servidores Ativos no Cargo de '.$nomeCargo.' ('.$simbolo.')');
            $lista->set_situacao(1);
            $lista->set_cargoComissao($nomeCargo);
            $lista->showTabela();
            
            #---------------------            
            # Histórico do cargo
            #---------------------
            
            # select
            $select ='SELECT distinct tbservidor.idFuncional,
                            tbservidor.matricula,
                            tbpessoa.nome,
                            tbcomissao.dtNom,
                            tbcomissao.dtExo,
                            concat(tbcomissao.descricao," ",if(protempore = 1," (pro tempore)","")),
                            concat(tbtipocomissao.simbolo," - ",tbtipocomissao.descricao)
                       FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                       LEFT JOIN tbcomissao ON(tbservidor.idServidor = tbcomissao.idServidor)
                                            JOIN tbtipocomissao ON(tbcomissao.idTipoComissao=tbtipocomissao.idTipoComissao)
                       WHERE tbtipocomissao.idTipoComissao = '.$id.'                    
                  ORDER BY 7, tbcomissao.descricao, 4 desc';

            $result = $servidor->select($select);
            $label = array('IdFuncional','Matrícula','Nome','Nomeação','Exoneração','Nome do Cargo');
            $align = array("center","center","left","center","center","left");
            $function = array(NULL,"dv",NULL,"date_to_php","date_to_php");
           
            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label($label);
            $tabela->set_titulo("Histórico");
            $tabela->set_align($align);
            $tabela->set_funcao($function);
            $tabela->set_formatacaoCondicional(array( array('coluna' => 4,
                                                    'valor' => NULL,
                                                    'operador' => '=',
                                                    'id' => 'vigente')));
            $tabela->show();
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
        case "relatorio" :
            # Pega o nome do cargo
            $servidor = new Pessoal();  
            $nomeCargo = $pessoal->get_nomeCargoComissao($id);
            
            # Lista de Servidores Ativos
            $lista = new ListaServidores('Servidores Ativos');
            $lista->set_situacao(1);
            $lista->set_cargoComissao($nomeCargo);
            $lista->showRelatorio();
            break;
    }
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}