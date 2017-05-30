<?php
/**
 * Dados Gerais do servidor
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

if($acesso)
{    
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase','editar');
    
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

    # Pega o perfil do Servidor    
    $perfilServidor = $pessoal->get_idPerfil($idServidorPesquisado);
    
    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Dados Funcionais');

    # select do edita
    $selectEdita = 'SELECT idFuncional,
                           matricula,
                           idPerfil,';

    # Somente se for estatutário
    if ($perfilServidor == 1)
        $selectEdita .= 'idConcurso,';

    # Somente se for estatutário ou cedido
    if (($perfilServidor == 1) || ($perfilServidor == 2))
        $selectEdita .= ' idCargo,';

    # os demais
    $selectEdita .= 'situacao,
                    dtAdmissao,
                    processoAdm,
                    dtPublicAdm,
                    pgPublicAdm,
                    ciGepagAdm,
                    dtDemissao,
                    processoExo,
                    dtPublicExo,
                    pgPublicExo,
                    ciGepagExo,
                    motivo,
                    tipoAposentadoria,
                    motivoDetalhe
            FROM tbservidor
            WHERE idServidor = '.$idServidorPesquisado;


    $objeto->set_selectEdita($selectEdita);

    # Caminhos
    $objeto->set_linkGravar('?fase=gravar');
    #$objeto->set_linkListar('?');
    $objeto->set_linkListar('servidorMenu.php');

    # botão voltar
    $objeto->set_voltarForm('servidorMenu.php');

    # retira o botão incluir
    $objeto->set_botaoIncluir(FALSE);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbservidor');

    # Nome do campo id
    $objeto->set_idCampo('idServidor');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Pega os dados da combo perfil
    $perfil = $pessoal->select('SELECT idperfil,
                                       nome
                                  FROM tbperfil
                              ORDER BY nome');

    array_unshift($perfil, array(NULL,NULL)); 

    # Pega os dados da combo concurso
    $concurso = $pessoal->select('SELECT idconcurso,
                                       concat(anobase," - ",regime) as nome
                                  FROM tbconcurso
                              ORDER BY nome');

    array_unshift($concurso, array(NULL,NULL)); 

    # Pega os dados da combo cargo
    $cargo = $pessoal->select('SELECT idcargo,
                                       concat(tbtipocargo.cargo," - ",tbarea.area," - ",nome)
                                  FROM tbcargo LEFT JOIN tbtipocargo USING (idTipoCargo)
                                               LEFT JOIN tbarea USING (idarea)
                              ORDER BY tbtipocargo.cargo,tbarea.area,nome');

    array_unshift($cargo, array(0,NULL)); 

    # Pega os dados da combo situação
    $situacao = $pessoal->select('SELECT idsituacao,
                                       situacao
                                  FROM tbsituacao
                              ORDER BY situacao');

    array_unshift($situacao, array(NULL,NULL)); 
    
    # Pega os dados da combo motivo de Saída do servidor
    $motivo = $pessoal->select('SELECT idmotivo,
                                       motivo
                                  FROM tbmotivo
                              ORDER BY idmotivo');

    array_unshift($motivo, array(NULL,NULL)); 

    # Campos para o formulario
    $campos = array(array( 'linha' => 1,
                           'nome' => 'idFuncional',
                           'label' => 'id Funcional:',
                           'tipo' => 'texto',
                           'autofocus' => TRUE,
                           'size' => 10,
                           'col' => 2,
                           'title' => 'Número da id funcional do servidor.'),
                  array ( 'linha' => 1,
                           'nome' => 'matricula',
                           'label' => 'Matricula:',
                           'tipo' => 'texto',
                           'autofocus' => TRUE,
                           'size' => 10,
                           'unique'=> TRUE,
                           'col' => 2,
                           'title' => 'Matrícula do servidor.'),
                   array ('linha' => 1,
                           'nome' => 'idPerfil',
                           'label' => 'Perfil:',
                           'tipo' => 'combo',
                           'required' => TRUE,
                           'array' => $perfil,
                           'title' => 'Perfil do servidor', 
                           'col' => 3,
                           'size' => 15));

    # Somente se for estatutário
    if ($perfilServidor == 1)
    {
        array_push($campos, array ('linha' => 1,
                                   'nome' => 'idConcurso',
                                   'label' => 'Concurso:',
                                   'tipo' => 'combo',
                                   'array' => $concurso,
                                   'title' => 'Concurso',
                                   'col' => 3,
                                   'size' => 15));
    }
            
    # Somente se for estatutário ou cedido
    if (($perfilServidor == 1) || ($perfilServidor == 2))
    {
         array_push($campos, array ('linha' => 2,
                                    'nome' => 'idCargo',
                                    'label' => 'Cargo / Área / Função:',
                                    'tipo' => 'combo',
                                    'array' => $cargo,
                                    'title' => 'Cargo',
                                    'col' => 12,
                                    'size' => 15));
    }

    # os demais
    array_push($campos, array ('linha' => 2,
                               'nome' => 'situacao',
                               'label' => 'Situação:',
                               'tipo' => 'hidden',
                               'title' => 'Concurso',                           
                               'size' => 15),
                       array ( 'linha' => 3,
                               'nome' => 'dtAdmissao',
                               'label' => 'Data de Admissão:',
                               'tipo' => 'data',
                               'size' => 20,
                               'col' => 3,
                               'fieldset' => 'Dados da Admissão',
                               'required' => TRUE,
                               'title' => 'Data de Admissão.'),
                       array ( 'linha' => 3,
                               'nome' => 'processoAdm',
                               'label' => 'Processo de Admissão:',
                               'tipo' => 'texto',
                               'col' => 3,
                               'size' => 25,
                               'title' => 'Número do processo de admissão.'),
                       array ( 'linha' => 3,
                               'nome' => 'dtPublicAdm',
                               'label' => 'Data da Publicação:',
                               'tipo' => 'data',
                               'size' => 20,
                               'col' => 3,
                               'title' => 'Data da Publicação no DOERJ.'),
                       array ( 'linha' => 3,
                               'nome' => 'pgPublicAdm',
                               'label' => 'Pág.:',
                               'tipo' => 'texto',
                               'size' => 5,
                               'col' => 1,
                               'title' => 'Página no DOERJ da Publicação.'),
                       array ( 'linha' => 3,
                               'nome' => 'ciGepagAdm',
                               'label' => 'Documento:',
                               'tipo' => 'texto',
                               'size' => 30,
                               'col' => 2,
                               'title' => 'Documento informando a admissão.'),
                      array ( 'linha' => 4,
                               'nome' => 'dtDemissao',
                               'label' => 'Data da Saída:',
                               'tipo' => 'data',
                               'col' => 3,
                               'fieldset' => 'Dados da Saída do Servidor',
                               'size' => 20,
                               'title' => 'Data da Saída do Servidor.'),
                       array ( 'linha' => 4,
                               'nome' => 'processoExo',
                               'label' => 'Processo:',
                               'tipo' => 'texto',
                               'size' => 25,
                               'col' => 3,
                               'title' => 'Número do processo.'),
                       array ( 'linha' => 4,
                               'nome' => 'dtPublicExo',
                               'label' => 'Data da Publicação:',
                               'tipo' => 'data',
                               'size' => 20,
                               'title' => 'Data da Publicação no DOERJ.'),
                       array ( 'linha' => 4,
                               'nome' => 'pgPublicExo',
                               'label' => 'Pág.:',
                               'tipo' => 'texto',
                               'size' => 5,
                               'col' => 1,
                               'title' => 'Página no DOERJ da Publicação.'),
                       array ( 'linha' => 4,
                               'nome' => 'ciGepagExo',
                               'label' => 'Documento:',
                               'tipo' => 'texto',
                               'size' => 30,
                               'col' => 2,
                               'title' => 'Documento informando a saída do servidor'),
                       array ( 'linha' => 5,
                               'nome' => 'motivo',
                               'label' => 'Motivo:',
                               'tipo' => 'combo',
                               'array' => $motivo,
                               'col' => 4,
                               'size' => 30,
                               'title' => 'Motivo da Saida do Servidor.'),
                       array  ('linha' => 5,
                               'nome' => 'tipoAposentadoria',
                               'label' => 'Tipo:',
                               'tipo' => 'combo',
                               'array' => array("","Integral","Proporcional"),
                               'title' => 'Tipo de Aposentadoria', 
                               'col' => 2,
                               'size' => 15),
                       array ( 'linha' => 5,
                               'nome' => 'motivoDetalhe',
                               'label' => 'Motivo Detalhado:',
                               'tipo' => 'texto',
                               'size' => 100,
                               'col' => 6,
                               'title' => 'Motivo detalhado da Saida do Servidor.')    
                                );

    $objeto->set_campos($campos);

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ################################################################

    switch ($fase){
        case "editar" :
            $objeto->$fase($idServidorPesquisado);  
            break;

        case "gravar" :
            $objeto->gravar($idServidorPesquisado,'servidorFuncionaisExtra.php'); 	
            break;
    }
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}