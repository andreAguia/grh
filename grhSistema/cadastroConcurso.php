<?php

/**
 * Cadastro de Concursos
 *  
 * By Alat
 */
# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {

    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de concurso";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega a rotina de origem
    $origem = get_session('origem');

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # Pega o id
    $id = soNumeros(get('id'));

    # Pega o tipo do concurso
    if (empty($id)) {
        $tipo = get_session('concursoTipo');
    } else {
        $concurso = new Concurso($id);
        $tipo = $concurso->get_tipo($id);
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    if ($tipo == 1) {
        $objeto->set_nome('Concursos para Administrativo e Técnico');
    } else {
        $objeto->set_nome('Concursos para Professor');
    }

    # botão de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # select da lista
    $select = "SELECT idConcurso,
                      anobase,
                      dtPublicacaoEdital,
                      regime,
                      CASE tipo
                        WHEN 1 THEN 'Adm & Tec'
                        WHEN 2 THEN 'Professor'
                        ELSE '--'
                      END,
                      orgExecutor,
                      idConcurso,
                      idConcurso,
                      idConcurso
                 FROM tbconcurso LEFT JOIN tbplano USING (idPlano)
                WHERE true
                  AND tipo = {$tipo}
             ORDER BY anobase desc, dtPublicacaoEdital desc";

    $objeto->set_selectLista($select);

    # select do edita
    $objeto->set_selectEdita("SELECT anobase,
                                     edital,
                                     dtPublicacaoEdital,
                                     regime,
                                     tipo,
                                     orgExecutor,
                                     idPlano,
                                     obs
                                FROM tbconcurso
                               WHERE idConcurso = {$id}");

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkGravar('?fase=gravar');

    if ($tipo == 1) {
        $objeto->set_voltarForm('cadastroConcursoAdm.php');
        $objeto->set_linkListar('cadastroConcursoAdm.php');
        $objeto->set_linkAposGravar('cadastroConcursoAdm.php');
    } else {
        $objeto->set_voltarForm('cadastroConcursoProf.php');
        $objeto->set_linkListar('cadastroConcursoProf.php');
        $objeto->set_linkAposGravar('cadastroConcursoProf.php');        
    }

    # Parametros da tabela
    $objeto->set_label(array("id", "Ano Base", "Publicação <br/>do Edital", "Regime", "Tipo", "Executor", "Ativos", "Inativos", "Total"));
    $objeto->set_width(array(5, 12, 12, 12, 12, 22, 5, 5, 5));
    $objeto->set_align(array("center"));

    $objeto->set_rowspan(1);
    $objeto->set_grupoCorColuna(1);

    $objeto->set_funcao(array(null, null, 'date_to_php'));

    $objeto->set_classe(array(null, null, null, null, null, null, "Concurso", "Concurso", "Concurso"));
    $objeto->set_metodo(array(null, null, null, null, null, null, "get_numServidoresAtivosConcurso", "get_numServidoresInativosConcurso", "get_numServidoresConcurso"));

    $objeto->set_excluirCondicional('?fase=excluir', 0, 8, "==");

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbconcurso');

    # Nome do campo id
    $objeto->set_idCampo('idConcurso');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Foco do form
    $objeto->set_formFocus('anobase');

    # Pega os dados da combo de Plano e Cargos
    $result = $pessoal->select('SELECT idPlano, 
                                      CONCAT("(",DATE_FORMAT(dtVigencia, "%d/%m/%Y"),") ",numDecreto)	
                                  FROM tbplano
                              ORDER BY dtVigencia DESC');

    array_push($result, array(null, null));

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'nome' => 'anobase',
            'label' => 'Ano:',
            'tipo' => 'texto',
            'autofocus' => true,
            'required' => true,
            'col' => 2,
            'size' => 4,
            'title' => 'Ano base do concurso'),
        array('linha' => 2,
            'nome' => 'edital',
            'label' => 'Processo do Edital:',
            'tipo' => 'texto',
            'title' => 'Número do processo do edital do concurso',
            'col' => 3,
            'size' => 20),
        array('linha' => 2,
            'nome' => 'dtPublicacaoEdital',
            'label' => 'Data da Publicação do Edital:',
            'tipo' => 'data',
            'title' => 'Data da Publicação do Edital',
            'col' => 3,
            'size' => 20),
        array('linha' => 3,
            'nome' => 'regime',
            'label' => 'Regime:',
            'tipo' => 'combo',
            'padrao' => "Estatutário",
            'array' => array("CLT", "Estatutário"),
            'required' => true,
            'col' => 3,
            'size' => 20),
        array('linha' => 3,
            'nome' => 'tipo',
            'label' => 'Tipo:',
            'tipo' => 'combo',
            'padrao' => $tipo,
            'required' => true,
            'array' => array(array(null, null),
                array(1, "Adm & Tec"),
                array(2, "Professor")),
            'col' => 3,
            'size' => 20),
        array('linha' => 3,
            'nome' => 'orgExecutor',
            'label' => 'Executor:',
            'tipo' => 'texto',
            'col' => 4,
            'size' => 30),
        array('linha' => 3,
            'nome' => 'idPlano',
            'label' => 'Plano de Cargos:',
            'tipo' => 'combo',
            'array' => $result,
            'col' => 5,
            'size' => 30),
        array('linha' => 4,
            'col' => 12,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(80, 5))));

    # idUsuário para o LogLicença sem vencimentosLicença sem vencimentos
    $objeto->set_idUsuario($idUsuario);
    
    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
            $objeto->listar();
            break;

        ################################################################

        case "editar" :
        case "excluir" :
        case "gravar" :
            $objeto->$fase($id);
            break;
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}