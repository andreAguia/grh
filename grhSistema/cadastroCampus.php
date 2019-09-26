<?php
/**
 * Cadastro de Campus
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
        $atividade = "Visualizou o cadastro de campus";
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
    $objeto->set_nome('Campus Universitários');

    # Botão de voltar da lista
    $objeto->set_voltarLista('grh.php');
    
    # select da lista
    $objeto->set_selectLista ('SELECT idCampus,
                                      campus,
                                      obs,
                                      idCampus
                                 FROM tbcampus
                             ORDER BY campus');

    # select do edita
    $objeto->set_selectEdita('SELECT campus,
                                     obs
                                FROM tbcampus
                               WHERE idCampus = '.$id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Id","Campus","Obs"));
    $objeto->set_width(array(5,40,45));
    $objeto->set_align(array("center","center","left"));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbcampus');

    # Nome do campo id
    $objeto->set_idCampo('idCampus');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array(
        array ('linha' => 1,
               'nome' => 'campus',
               'label' => 'Campus:',
               'tipo' => 'texto',
               'required' => TRUE,
               'autofocus' => TRUE,
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