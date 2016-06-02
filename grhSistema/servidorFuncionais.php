<?php
/**
 * Dados Gerais do servidor
 *  
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$matricula = null;		  # Reservado para a matrícula do servidor logado
$matriculaGrh = null;		  # Reservado para a matrícula pesquisada

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($matricula,13);

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
    $objeto->set_rotinaExtraParametro($matriculaGrh); 

    # Pega o perfil do Servidor    
    $perfilServidor = $pessoal->get_idPerfil($matriculaGrh);

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Dados Funcionais');

    # select do edita
    $selectEdita = 'SELECT idFuncional,
                           idPerfil,';

    # Somente se for estatutário
    if ($perfilServidor == 1)
        $selectEdita .= 'idConcurso,';

    # Somente se for estatutário ou cedido
    if (($perfilServidor == 1) || ($perfilServidor == 2))
        $selectEdita .= ' idCargo,';

    # os demais
    $selectEdita .= 'sit,
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
                    motivo
            FROM tbfuncionario
            WHERE matricula = '.$matriculaGrh;


    $objeto->set_selectEdita($selectEdita);

    # Caminhos
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?');

    # botão salvar
    $objeto->set_botaoSalvarGrafico(false);

    # botão voltar
    $objeto->set_voltarForm('servidorMenu.php');

    # retira o botão incluir
    $objeto->set_botaoIncluir(false);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbfuncionario');

    # Nome do campo id
    $objeto->set_idCampo('matricula');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Pega os dados da combo perfil
    $perfil = $pessoal->select('SELECT idperfil,
                                       nome
                                  FROM tbperfil
                              ORDER BY nome');

    array_push($perfil, array(null,null)); 

    # Pega os dados da combo concurso
    $concurso = $pessoal->select('SELECT idconcurso,
                                       concat(anobase," - ",regime) as nome
                                  FROM tbconcurso
                              ORDER BY nome');

    array_push($concurso, array(null,null)); 

    # Pega os dados da combo cargo
    $cargo = $pessoal->select('SELECT idcargo,
                                       nome
                                  FROM tbcargo
                              ORDER BY nome');

    array_push($cargo, array(0,null)); 

    # Pega os dados da combo situação
    $situacao = $pessoal->select('SELECT idsit,
                                       sit
                                  FROM tbsituacao
                              ORDER BY sit');

    array_push($situacao, array(null,null)); 

    # Campos para o formulario
    $campos = array(array ( 'linha' => 1,
                           'nome' => 'idFuncional',
                           'label' => 'id Funcional:',
                           'tipo' => 'numero',
                           'autofocus' => true,
                           'size' => 10,
                           'col' => 3,
                           'title' => 'Número da id funcional do servidor.'),
                   array ('linha' => 1,
                           'nome' => 'idPerfil',
                           'label' => 'Perfil:',
                           'tipo' => 'combo',
                           'array' => $perfil,
                           'title' => 'Perfil do servidor', 
                           'col' => 6,
                           'size' => 15),
                    array ('linha' => 1,
                           'nome' => 'sit',
                           'label' => 'Situação:',
                           'tipo' => 'combo',
                           'array' => $situacao,
                           'title' => 'Concurso', 
                           'col' => 3,
                           'size' => 15));

    # Somente se for estatutário
    if ($perfilServidor == 1)
    {
        array_push($campos, array ('linha' => 2,
                                   'nome' => 'idConcurso',
                                   'label' => 'Concurso:',
                                   'tipo' => 'combo',
                                   'array' => $concurso,
                                   'title' => 'Concurso',
                                   'col' => 6,
                                   'size' => 15));
    }
    # Somente se for estatutário ou cedido
    if (($perfilServidor == 1) || ($perfilServidor == 2))
    {
         array_push($campos, array ('linha' => 2,
                                    'nome' => 'idCargo',
                                    'label' => 'Cargo:',
                                    'tipo' => 'combo',
                                    'array' => $cargo,
                                    'title' => 'Cargo',
                                    'col' => 6,
                                    'size' => 15));
    }

    # os demais
    array_push($campos,array ( 'linha' => 3,
                               'nome' => 'dtAdmissao',
                               'label' => 'Data de Admissão:',
                               'tipo' => 'data',
                               'size' => 20,
                               'col' => 3,
                               'fieldset' => 'Dados da Admissão',
                               'required' => true,
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
                               'label' => 'Data de Exoneração:',
                               'tipo' => 'data',
                               'col' => 3,
                               'fieldset' => 'Dados da Demissão',
                               'size' => 20,
                               'title' => 'Data de Exoneração.'),
                       array ( 'linha' => 4,
                               'nome' => 'processoExo',
                               'label' => 'Processo de Exoneração:',
                               'tipo' => 'texto',
                               'size' => 25,
                               'col' => 3,
                               'title' => 'Número do processo de Exoneração.'),
                       array ( 'linha' => 4,
                               'nome' => 'dtPublicExo',
                               'label' => 'Data da Publicação:',
                               'tipo' => 'data',
                               'size' => 20,
                               'title' => 'Data da Publicação da Exoneração no DOERJ.'),
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
                               'title' => 'Documento informando a exoneração'),
                       array ( 'linha' => 5,
                               'nome' => 'motivo',
                               'label' => 'Motivo da Exoneração:',
                               'tipo' => 'texto',
                               'size' => 50,
                               'col' => 6,
                               'title' => 'Motivo da Exoneração.')    
                                );

    $objeto->set_campos($campos);

    # Matrícula para o Log
    $objeto->set_matricula($matricula);

    ################################################################
    switch ($fase)
    {
        case "editar" :            
        case "excluir" :	
        case "gravar" :
            $objeto->$fase($matriculaGrh);
            break;	
    }				
    $page->terminaPagina();
}
?>
