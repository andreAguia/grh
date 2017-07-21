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

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    # Verifica a paginacão
    $paginacao = get('paginacao',get_session('sessionPaginacao',0));	// Verifica se a paginação vem por get, senão pega a session
    set_session('sessionPaginacao',$paginacao);    
    
    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Cargos em Comissão');
    
    # botão de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # controle de pesquisa
    #$objeto->set_parametroLabel('Pesquisar');
    #$objeto->set_parametroValue($parametro);

    # select da lista
    $objeto->set_selectLista ('SELECT idComissao,
                                      idServidor,
                                      idTipoComissao,
                                      descricao,
                                      idLotacao
                                 FROM tbcomissao
                             ORDER BY idcomissao desc');

    # select do edita
    $objeto->set_selectEdita('SELECT descricao,
                                     idLotacao
                                FROM tbcomissao
                               WHERE idComissao = '.$id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    #$objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("id","Servidor","Cargo","Descrição","Lotacão"));
    $objeto->set_align(array("center","left","left","left","left"));
    
    #$objeto->set_funcao(array(NULL,NULL,NULL,"formataMoeda"));
    $objeto->set_classe(array(NULL,'pessoal','pessoal',NULL,'pessoal'));
    $objeto->set_metodo(array(NULL,'get_nome','get_nomeCargoComissao',NULL,'get_nomeCompletoLotacao'));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbcomissao');

    # Nome do campo id
    $objeto->set_idCampo('idComissao');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # combo lotacao
    $selectLotacao = 'SELECT idlotacao, 
                             concat(IFNULL(tblotacao.UADM,"")," - ",IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) as lotacao
                        FROM tblotacao 
                       WHERE ativo
                    ORDER BY lotacao';
    
    $result = $pessoal->select($selectLotacao);
    array_unshift($result, array(NULL,NULL)); # Adiciona o valor de nulo

    # Campos para o formulario
    $objeto->set_campos(array(
        array ('linha' => 1,
               'col' => 12,
               'nome' => 'descricao',
               'label' => 'Cargo em Comissão:',
               'tipo' => 'texto',
               'autofocus' => TRUE,
               'size' => 50),
        array ('nome' => 'idLotacao',                                   
               'label' => 'Setor:',
               'tipo' => 'combo',
               'array' => $result,
               'required' => TRUE,
               'size' => 80,
               'col' => 6,
               'title' => 'Setor de onde o cargo é vinculado.',
               'linha' => 2)));
    
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
    }
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}