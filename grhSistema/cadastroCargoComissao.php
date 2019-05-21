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

if($acesso){    
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase','listar');
    
    # Verifica o post de quando exibe os histórico de servidores nesse cargo
    $parametroComissao = post('parametroComissao',get_session('parametroComissao'));
    
    # Joga os parâmetros par as sessions    
    set_session('parametroComissao',$parametroComissao);
        
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
    if($fase <> "relatorio"){
        AreaServidor::cabecalho();
    }

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
    $objeto->set_voltarLista('areaCargoComissao.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # ordenação
    if(is_null($orderCampo)){
        $orderCampo = "8 desc, 3";
    }

    if(is_null($orderTipo)){
        $orderTipo = 'asc';
    }

    # select da lista
    $objeto->set_selectLista ('SELECT idTipoComissao,
                                      descricao,
                                      simbolo,
                                      valsal,
                                      vagas,
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
    
    $objeto->set_formatacaoCondicional(array(array('coluna' => 6,
                                                    'valor' => 0,
                                                    'operador' => '<',
                                                    'id' => "comissaoVagasNegativas"),
                                             array('coluna' => 6,
                                                    'valor' => 0,
                                                    'operador' => '=',
                                                    'id' => "comissaoSemVagas"),
                                             array('coluna' => 6,
                                                    'valor' => 0,
                                                    'operador' => '>',
                                                    'id' => "comissaoComVagas")));

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    #$objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("id","Cargo","Simbolo","Valor (R$)","Vagas","Vagas<br/>Ocupadas","Vagas<br/>Disponíveis","Ativo?"));
    #$objeto->set_width(array(5,20,10,10,10,10,10,10,10));
    $objeto->set_align(array("center","left"));
    
    $objeto->set_funcao(array(NULL,NULL,NULL,"formataMoeda"));
    $objeto->set_classe(array(NULL,NULL,NULL,NULL,NULL,'Grh','pessoal'));
    $objeto->set_metodo(array(NULL,NULL,NULL,NULL,NULL,'get_numServidoresCargoComissao','get_cargoComissaoVagasDisponiveis'));

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

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);
    
    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir");
    $botaoRel->set_target("_blank");
    if($tipo){
        $botaoRel->set_url('../grhRelatorios/cargoComissaoAtivos.php');        
    }else{
        $botaoRel->set_url('../grhRelatorios/cargoComissaoInativos.php');
    }
    
    # Cargos Ativos
    $botaoAtivo = new Button("Cargos Ativos","?tipo=1");
    $botaoAtivo->set_title("Exibe os Cargos Ativos");
    if($tipo){
        $botaoAtivo->set_class("hollow button");
    }
    
    # Cargos Inativos
    $botaoInativo = new Button("Cargos Inativos","?tipo=0");
    $botaoInativo->set_title("Exibe os Cargos Inativos");
    if(!$tipo){
        $botaoInativo->set_class("hollow button");
    }
    
    # Cria o array de botões
    $arrayBotoes = array($botaoAtivo,$botaoInativo,$botaoRel);
    
    # Informa o array
    $objeto->set_botaoListarExtra($arrayBotoes);

    ################################################################
    
    switch ($fase) {
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
        
    ################################################################

        case "vigente" :
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
            
            # histórico
            $link = new Link("Histórico","?fase=historico&id=$id");
            $link->set_class('button');
            $link->set_title('Exibe o histórico e servidores neste cargo');
            $menu->add_link($link,"right");
            
            # Relatório
            $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
            $botaoRel = new Button();
            $botaoRel->set_imagem($imagem);
            $botaoRel->set_title("Imprimir");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("../grhRelatorios/cargosComissionados.php?comissao=".$id);
            $menu->add_link($botaoRel,"right");
            
            # Relatório
            $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
            $botaoRel = new Button();
            $botaoRel->set_imagem($imagem);
            $botaoRel->set_title("Imprimir");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("?fase=relatorio&id=".$id);
            #$menu->add_link($botaoRel,"right");
             
            $menu->show();
            
            # Pega o nome do cargo
            $servidor = new Pessoal();  
            $nomeCargo = $pessoal->get_nomeCargoComissao($id);
            $simbolo = $pessoal->get_cargoComissaoSimbolo($id);
            
            # select
            $select ='SELECT distinct tbservidor.idFuncional,
                             tbservidor.matricula,
                             IF(tbcomissao.ocupanteAnterior IS NULL, tbpessoa.nome,CONCAT(tbpessoa.nome,"<br/><span id=\"orgaoCedido\">(Anterior: ",tbcomissao.ocupanteAnterior,"</span>)")),
                             tbcomissao.dtNom,
                             tbcomissao.dtExo,
                             tbcomissao.idComissao,
                             idPerfil,
                             concat(tbtipocomissao.simbolo," - ",tbtipocomissao.descricao),
                             idComissao
                        FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                        LEFT JOIN tbcomissao ON(tbservidor.idServidor = tbcomissao.idServidor)
                                             JOIN tbtipocomissao ON(tbcomissao.idTipoComissao=tbtipocomissao.idTipoComissao)
                       WHERE tbtipocomissao.idTipoComissao = '.$id.'
                         AND (tbcomissao.dtExo IS NULL OR CURDATE() < tbcomissao.dtExo)
                  ORDER BY 8, tbcomissao.descricao, 4 desc';

            $result = $servidor->select($select);
            $label = array('IdFuncional','Matrícula','Nome','Nomeação','Exoneração','Nome do Cargo','Perfil');
            $align = array("center","center","left","center","center","left","center");
            $function = array(NULL,"dv",NULL,"date_to_php","date_to_php","descricaoComissao");
            $classe = array(NULL,NULL,NULL,NULL,NULL,NULL,"Pessoal");
            $metodo = array(NULL,NULL,NULL,NULL,NULL,NULL,"get_perfil");
           
            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label($label);
            $tabela->set_titulo("Servidores Atualmente exercendo o Cargo: $nomeCargo [$simbolo]");
            $tabela->set_align($align);
            $tabela->set_funcao($function);
            $tabela->set_classe($classe);
            $tabela->set_metodo($metodo);
            $tabela->set_idCampo('idComissao');
            $tabela->set_editar('?fase=editarCargo1');
            $tabela->set_formatacaoCondicional(array( array('coluna' => 4,
                                                    'valor' => NULL,
                                                    'operador' => '=',
                                                    'id' => 'vigente')));
            $tabela->show();
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
################################################################
        
        case "historico" :
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
            
            # Servidores atuais
            $link = new Link("Vigentes","?fase=vigente&id=$id");
            $link->set_class('button');
            $link->set_title('Exibe o histórico e servidores neste cargo');
            $menu->add_link($link,"right");
            
            # Relatório
            $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
            $botaoRel = new Button();
            $botaoRel->set_imagem($imagem);
            $botaoRel->set_title("Imprimir");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("../grhRelatorios/cargosComissionadosHistorico.php?cargo=".$id);
            $menu->add_link($botaoRel,"right");
             
            $menu->show();
            
            ###
            
            # Formulário de Pesquisa
            $form = new Form('?fase=historico&id='.$id);
            
            # Descrição do Cargo    
            $controle = new Input('parametroComissao','texto','Descrição do Cargo ou Nome do Servidor:',1);
            $controle->set_size(80);
            $controle->set_autofocus(TRUE);
            $controle->set_title('Filtra pela descrição do cargo');
            $controle->set_valor($parametroComissao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(5);
            $form->add_item($controle);

            $form->show();
            
            ###
            
            # Pega o nome do cargo
            $servidor = new Pessoal();  
            $nomeCargo = $pessoal->get_nomeCargoComissao($id);
            $simbolo = $pessoal->get_cargoComissaoSimbolo($id);
                        
            # select
            $select ='SELECT distinct tbservidor.idFuncional,
                             tbservidor.matricula,
                             IF(tbcomissao.ocupanteAnterior IS NULL, tbpessoa.nome,CONCAT(tbpessoa.nome,"<br/><span id=\"orgaoCedido\">(Anterior: ",tbcomissao.ocupanteAnterior,"</span>)")),
                             tbcomissao.dtNom,
                             tbcomissao.dtExo,
                             tbcomissao.idComissao,
                             idPerfil,
                             concat(tbtipocomissao.simbolo," - ",tbtipocomissao.descricao),
                             idComissao
                        FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                        LEFT JOIN tbcomissao ON(tbservidor.idServidor = tbcomissao.idServidor)
                                             JOIN tbtipocomissao ON(tbcomissao.idTipoComissao=tbtipocomissao.idTipoComissao)
                       WHERE tbtipocomissao.idTipoComissao = '.$id;
            
            # Pega o parâmetro da pesquisa
            if(!is_null($parametroComissao)){
                $select .= ' AND (tbcomissao.descricao LIKE "%'.$parametroComissao.'%"';
                $select .= ' OR tbpessoa.nome LIKE "%'.$parametroComissao.'%")';
            }
            
            $select .= ' ORDER BY 8, tbcomissao.descricao, 4 desc';

            $result = $servidor->select($select);
            $label = array('IdFuncional','Matrícula','Nome','Nomeação','Exoneração','Nome do Cargo','Perfil');
            $align = array("center","center","left","center","center","left","center");
            $function = array(NULL,"dv",NULL,"date_to_php","date_to_php","descricaoComissao");
            $classe = array(NULL,NULL,NULL,NULL,NULL,NULL,"Pessoal");
            $metodo = array(NULL,NULL,NULL,NULL,NULL,NULL,"get_perfil");
           
            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label($label);
            $tabela->set_titulo("Histórico do Cargo: $nomeCargo [$simbolo]");
            $tabela->set_align($align);
            $tabela->set_funcao($function);
            $tabela->set_classe($classe);
            $tabela->set_metodo($metodo);
            $tabela->set_idCampo('idComissao');
            $tabela->set_editar('?fase=editarCargo2');
            $tabela->set_formatacaoCondicional(array( array('coluna' => 4,
                                                    'valor' => NULL,
                                                    'operador' => '=',
                                                    'id' => 'vigente')));
            $tabela->show();
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
################################################################
        
        case "relatorio" :
            # Pega o nome do cargo
            $servidor = new Pessoal();  
            $nomeCargo = $pessoal->get_nomeCargoComissao($id);
            
            # Lista de Servidores Ativos
            $lista = new ListaServidores('Servidores Ativos');
            $lista->set_situacao(1);
            $lista->set_cargoComissao($id);
            $lista->showRelatorio();
            break;
        
################################################################
        
        case "editarCargo1" :
            # Vigentes
            br(8);
            aguarde();
            
            $comissao = new CargoComissao();
            $dados = $comissao->get_dados($id);
            $idServidor = $dados["idServidor"];
            $idTipoComissao = $dados["idTipoComissao"];
            
            # Informa o idComissao
            set_session("comissao",$idTipoComissao);
            
            # Informa o $id Servidor
            set_session('idServidorPesquisado',$idServidor);
            
            # Informa a origem
            set_session('origem','cargoComissaoVigente');
            
            # Carrega a página específica
            loadPage('servidorComissao.php?fase=editar&id='.$id);
            break; 
        
################################################################
        
        case "editarCargo2" :
            # Histórico
            br(8);
            aguarde();
            
            $comissao = new CargoComissao();
            $dados = $comissao->get_dados($id);
            $idServidor = $dados["idServidor"];
            $idTipoComissao = $dados["idTipoComissao"];
            
            # Informa o idComissao
            set_session("comissao",$idTipoComissao);
            
            # Informa o $id Servidor
            set_session('idServidorPesquisado',$idServidor);
            
            # Informa a origem
            set_session('origem','cargoComissaoHistorico');
            
            # Carrega a página específica
            loadPage('servidorComissao.php?fase=editar&id='.$id);
            break; 
        
    }
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}