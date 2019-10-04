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
    $objeto->set_selectLista ('SELECT idConcursoVaga,
                                      idLotacao,
                                      area,
                                      idConcurso,
                                      idServidor,
                                      idConcursoVagaServidores
                                 FROM tbconcursovagaservidores
                             ORDER BY centro');

    # select do edita
    $objeto->set_selectEdita('SELECT idConcursoVaga,
                                     idLotacao,
                                     area,
                                     idConcurso,
                                     idServidor,
                                     obs
                                FROM tbconcursovagaservidores
                               WHERE idConcursoVagaServidores = '.$id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Vaga","Laboratório","Área","Concurso","Servidor"));
    #$objeto->set_width(array(15,30,45));
    $objeto->set_align(array("center","left","left","center","left"));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbconcursovagaservidores');

    # Nome do campo id
    $objeto->set_idCampo('idConcursoVagaServidores');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);
    
    # Pega os dados da combo de vagas
    $vagas = $pessoal->select('SELECT idConcursoVaga,
                                      concat(tbcargo.nome," - ",centro)
                                 FROM tbconcursoVagas LEFT JOIN tbcargo USING (idCargo)');

    array_unshift($vagas, array(0,NULL)); 
    
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
               'nome' => 'idConcursoVaga',
               'label' => 'Vaga:',
               'tipo' => 'combo',
               'array' => $vagas,
               'required' => TRUE,
               'autofocus' => TRUE,
               'size' => 30),
        array ('linha' => 1,
               'col' => 4,
               'nome' => 'idConcurso',
               'label' => 'Concurso:',
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