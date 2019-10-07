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

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################

    # Nome do Modelo
    $objeto->set_nome('Controle de Vagas de Professores');

    # Botão de voltar da lista
    $objeto->set_voltarLista('grh.php');
    
    # select da lista
    $objeto->set_selectLista ('SELECT centro,
                                      tbcargo.nome,
                                      tbconcursovagas.obs,
                                      idConcursoVagas
                                 FROM tbconcursovagas LEFT JOIN tbcargo USING (idCargo)
                             ORDER BY centro');

    # select do edita
    $objeto->set_selectEdita('SELECT centro,
                                     idCargo,
                                     obs
                                FROM tbconcursovagas
                               WHERE idConcursoVagas = '.$id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Centro","Cargo","Obs"));
    $objeto->set_width(array(15,30,45));
    $objeto->set_align(array("center","center","left"));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbconcursovagas');

    # Nome do campo id
    $objeto->set_idCampo('idConcursoVagas');

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
               'array' => array("CCT","CCTA","CCH","CBB"),
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
               'size' => 30),
        array ('linha' => 2,
               'nome' => 'obs',
               'label' => 'Observação:',
               'tipo' => 'textarea',
               'size' => array(80,5))));

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

    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}