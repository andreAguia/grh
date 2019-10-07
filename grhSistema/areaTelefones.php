<?php
/**
 * Cadastro de Lotação
 *  
 * By Alat
 */

# Reservado para o servidor logado
$idUsuario = NULL;

# Configuração
include("_config.php");

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
        $atividade = "Visualizou a area de Telefones";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
    }
    
    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    # Pega o parametro de pesquisa (se tiver)
    if (is_null(post('parametro')))					# Se o parametro n?o vier por post (for nulo)
        $parametro = retiraAspas(get_session('sessionParametro'));	# passa o parametro da session para a variavel parametro retirando as aspas
    else{ 
        $parametro = post('parametro');                # Se vier por post, retira as aspas e passa para a variavel parametro
        set_session('sessionParametro',$parametro);    # transfere para a session para poder recuperá-lo depois
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
    $objeto->set_nome($pessoal->get_nomeCompletoLotacao($id));

    # botão de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);
    
    # select da lista
    $objeto->set_selectLista ("SELECT DIR,
                                      GER,
                                      nome,
                                      ramais,
                                      email,
                                      idLotacao
                                 FROM tblotacao
                                WHERE ativo
                                  AND (DIR LIKE '%$parametro%'
                                   OR GER LIKE '%$parametro%'
                                   OR nome LIKE '%$parametro%'
                                   OR ramais LIKE '%$parametro%')
                             ORDER BY DIR asc, GER asc, nome asc");

    # select do edita
    $objeto->set_selectEdita('SELECT ramais,
                                     email
                                FROM tblotacao
                               WHERE idLotacao = '.$id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    #$objeto->set_linkExcluir('?fase=excluir');     // Retirado para evidar exclusão acidental
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');
    
    $objeto->set_botaoIncluir(FALSE);

    # Parametros da tabela
    $objeto->set_label(array("Diretoria","Gerência","Nome","Telefones","Email"));
    $objeto->set_align(array("center","center","left","left","left"));
    $objeto->set_funcao(array(NULL,NULL,NULL,"nl2br"));
    
    $objeto->set_rowspan(0);
    $objeto->set_grupoCorColuna(0);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tblotacao');

    # Nome do campo id
    $objeto->set_idCampo('idLotacao');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);
    
    # Campos para o formulario
    $objeto->set_campos(array(
        array ('linha' => 1,
               'col' => 12,
               'nome' => 'ramais',
               'label' => 'Ramais:',
               'title' => 'Número dos telefones/ramais/faxes da lotação',
               'tipo' => 'textarea',
               'tagHtml' => TRUE,
               'autofocus' => TRUE,
               'size' => array(80,4)),
        array ('linha' => 2,
               'col' => 12,
               'nome' => 'email',
               'label' => 'Email:',
               'title' => 'Email do Setor',
               'tipo' => 'texto',
               'size' => 50)));
    
    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);

    ################################################################
    
    switch ($fase){
        case "" :            
        case "listar" :            
            $objeto->listar();
            break;
        
        case "editar" :
        case "excluir" :	
        case "gravar" :
            $objeto->$fase($id);
            break;
    }
        
    ################################################################
        
}else{
    loadPage("../../areaServidor/sistema/login.php");
}