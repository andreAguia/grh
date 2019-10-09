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
    $idVagaDocente = get_session('idVagaDocente');
    
    # Pega os dados dessa vaga
    $vaga = new VagaDocente();
    $vagaDados = $vaga->get_dados($idVagaDocente);
    
    $centro = $vagaDados['centro'];
    $idCargo = $vagaDados['idCargo'];
    
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
    $objeto->set_selectLista ('SELECT idConcurso,
                                      idLotacao,
                                      area,
                                      idServidor,
                                      obs,
                                      idConcursoVaga
                                 FROM tbconcursovaga
                                WHERE idVagaDocente = '.$idVagaDocente.' ORDER BY idConcurso');

    # select do edita
    $objeto->set_selectEdita('SELECT idVagaDocente,
                                     idConcurso,
                                     idLotacao,
                                     area,
                                     idServidor,
                                     obs
                                FROM tbconcursovaga
                               WHERE idConcursoVaga = '.$id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Concurso","Laboratório","Área","Servidor","Obs"));
    #$objeto->set_width(array(15,30,45));
    $objeto->set_align(array("left","left","left","left","left"));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbconcursovaga');

    # Nome do campo id
    $objeto->set_idCampo('idConcursoVaga');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);
    
    ###############
    
    # Pega os dados da combo de vagas
    $vagas = $pessoal->select('SELECT idVagaDocente,
                                      concat(centro," / ",tbcargo.nome)
                                 FROM tbvagadocente LEFT JOIN tbcargo USING (idCargo)
                                 WHERE idVagaDocente = '.$idVagaDocente);

    array_unshift($vagas, array(0,NULL));
    
    ###############
    
    # Pega os dados para combo concurso 
    $concurso = $pessoal->select('SELECT idconcurso,
                                         concat(anoBase," - Edital: ",DATE_FORMAT(dtPublicacaoEdital,"%d/%m/%Y")) as concurso
                                    FROM tbconcurso
                                    WHERE tipo = 2
                                ORDER BY dtPublicacaoEdital desc');

    array_unshift($concurso, array(0,NULL)); 
    
    ###############
    
    # Pega os dados da combo lotacao
    $selectLotacao = 'SELECT idlotacao, 
                             concat(IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) as lotacao                       
                        FROM tblotacao 
                        WHERE tblotacao.DIR = "'.$centro.'"  
                        ORDER BY ativo desc, lotacao';
    
    $result = $pessoal->select($selectLotacao);
    array_unshift($result, array(NULL,NULL)); # Adiciona o valor de nulo
    
    ###############
    
    # Pega os dados da combo idServidor
    $select = 'SELECT idServidor,
                      tbpessoa.nome
                 FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa) 
                WHERE (idCargo = 128 OR idCargo = 129)
                  AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
             ORDER BY tbpessoa.nome';

    $docente = $pessoal->select($select);
    array_unshift($docente, array(NULL,NULL)); # Adiciona o valor de nulo
    
    ###############

    # Campos para o formulario
    $objeto->set_campos(array(
        array ('linha' => 1,
               'nome' => 'idVagaDocente',
               'label' => 'Centro / Cargo',
               'tipo' => 'combo',
               'array' => $vagas,
               'col' => 3,
               'padrao' => $idVagaDocente,
               'size' => 30),
        array ('linha' => 1,
               'nome' => 'idConcurso',
               'label' => 'Concurso:',
               'tipo' => 'combo',
               'array' => $concurso,
               'col' => 3,
               'required' => TRUE,
               'autofocus' => TRUE,
               'size' => 30),
        array ('nome' => 'idLotacao',
               'label' => 'Laboratório:',
               'tipo' => 'combo',
               'required' => TRUE,
               'array' => $result,
               'size' => 20,
               'col' => 6,
               'title' => 'Em qual setor o servidor está lotado',
               'linha' => 1),
        array ('nome' => 'area',
               'label' => 'Área:',
               'tipo' => 'texto',
               'size' => 255,
               'col' => 12,                                   
               'title' => 'Área de atuação.',
               'linha' => 3),
        array ('nome' => 'idServidor',
               'label' => 'Docente Empossado:',
               'tipo' => 'combo',
               'array' => $docente,
               'size' => 50,
               'col' => 12,
               'title' => 'Docente ocupante dessa vaga',
               'linha' => 4),
        array ('linha' => 5,
               'col' => 12,
               'nome' => 'obs',
               'label' => 'Observação:',
               'tipo' => 'textarea',
               'size' => array(80,5))
        ));

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