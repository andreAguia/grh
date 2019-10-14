<?php
/**
 * Cadastro de Banco
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
    
    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh',FALSE);
    if($grh){
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de bancos";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    # Pega os parâmetros
    $parametroCentro = post('parametroCentro',get_session('parametroCentro'));
    $parametroCargo = retiraAspas(post('parametroCargo',get_session('parametroCargo')));
        
    # Joga os parâmetros par as sessions    
    set_session('parametroCentro',$parametroCentro);
    set_session('parametroCargo',$parametroCargo);

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################

    # Nome do Modelo
    $objeto->set_nome('Controle de Vagas de Docentes');

    # Botão de voltar da lista
    $objeto->set_voltarLista('grh.php');
    
    # select da lista
    $select = 'SELECT centro,
                    tbcargo.nome,
                    idVaga,
                    idVaga,
                    idVaga,
                    idVaga,
                    idVaga,
                    idVaga,
                    idVaga
               FROM tbvaga LEFT JOIN tbcargo USING (idCargo)
               WHERE TRUE ';
    
    # parametroCentro
    if(!vazio($parametroCentro)){
        $select .= "AND centro = '$parametroCentro'";
    }
    
    # parametroCentro
    if($parametroCargo <> 0){
        $select .= "AND idCargo = $parametroCargo";
    }
    
    $select .= ' ORDER BY centro';
    
    # select da lista
    $objeto->set_selectLista ($select);

    # select do edita
    $objeto->set_selectEdita('SELECT centro,
                                     idCargo
                                FROM tbvaga
                               WHERE idVaga = '.$id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');
    
    
    
    $objeto->set_formatacaoCondicional(array( array('coluna' => 2,
                                                    'valor' => 'em Aberto',
                                                    'operador' => '=',
                                                    'id' => 'emAberto'),
                                              array('coluna' => 2,
                                                    'valor' => 'Ocupado',
                                                    'operador' => '=',
                                                    'id' => 'alerta')
                                                    ));

    # Parametros da tabela
    $objeto->set_label(array("Centro","Cargo","Status","Concurso","Laboratório","Área","Último Ocupante","Obs","Núm<br/>Concursos"));
    #$objeto->set_width(array(15,30,45));
    $objeto->set_align(array("center","center","center","center","left","center"));
    
    $objeto->set_excluirCondicional('?fase=excluir',0,8,"==");
    
    $objeto->set_classe(array(NULL,NULL,"Vaga","Vaga","Vaga","Vaga","Vaga","Vaga","Vaga"));
    $objeto->set_metodo(array(NULL,NULL,"get_status","get_concursoOcupante","get_laboratorioOcupante","get_areaOcupante","get_servidorOcupante","get_obsOcupante","get_numConcursoVaga"));
    
    #$objeto->set_rowspan(0);
    #$objeto->set_grupoCorColuna(0);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbvaga');

    # Nome do campo id
    $objeto->set_idCampo('idVaga');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);
    
    # Pega os dados da combo cargo
    $cargo = $pessoal->select('SELECT idcargo,nome
                                 FROM tbcargo LEFT JOIN tbtipocargo USING (idTipoCargo)
                                              LEFT JOIN tbarea USING (idarea)
                                WHERE idCargo = 128 OR idCargo = 129              
                             ORDER BY tbtipocargo.cargo,tbarea.area,nome');

    array_unshift($cargo, array(0,NULL)); 

    # Campos para o formulario
    $objeto->set_campos(array(
        array ('linha' => 1,
               'col' => 2,
               'nome' => 'centro',
               'label' => 'Centro:',
               'tipo' => 'combo',
               'array' => array(NULL,"CCT","CCTA","CCH","CBB"),
               'required' => TRUE,
               'autofocus' => TRUE,
               'size' => 30),
        array ('linha' => 1,
               'col' => 4,
               'nome' => 'idCargo',
               'label' => 'Cargo:',
               'tipo' => 'combo',
               'array' => $cargo,
               'required' => TRUE,
               'size' => 30)));

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);
    
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
            
            ###
            
            # Formulário de Pesquisa
            $form = new Form('?'); 
            
            # Centro 
            $controle = new Input('parametroCentro','combo','Centro:',1);
            $controle->set_size(10);
            $controle->set_col(2);
            $controle->set_array(array(NULL,"CCT","CCTA","CCH","CBB"));
            $controle->set_autofocus(TRUE);
            $controle->set_valor($parametroCentro);
            $controle->set_onChange('formPadrao.submit();');
            $form->add_item($controle);
            
            # Cargo 
            $controle = new Input('parametroCargo','combo','Cargo:',1);
            $controle->set_size(10);
            $controle->set_col(3);
            $controle->set_array($cargo);
            $controle->set_valor($parametroCargo);
            $controle->set_onChange('formPadrao.submit();');
            $form->add_item($controle);

            $form->show();
            
            ###
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            
            $objeto->listar();
            break;

        case "editar" :	
            if(!vazio($id)){
                set_session('idVaga',$id);
                loadPage("cadastroVagaHistorico.php");
            }else{
                $objeto->editar();
            }
            break;
        case "excluir" :	
        case "gravar" :
            $objeto->$fase($id);
            break;
    }									 	 		

    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}