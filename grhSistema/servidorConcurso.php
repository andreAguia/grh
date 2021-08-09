<?php

/**
 * Dados Gerais do servidor
 *  
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;
$idServidorPesquisado = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Cadastro de concurso";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica a fase do programa
    $fase = get('fase', 'ver');
    $origem = get_session("origem");

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

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Dados do Concurso');

    # Pega o tipo do cargo (Adm & Tec ou Professor)
    $tipoCargo = $pessoal->get_cargoTipo($idServidorPesquisado);

    # select do edita
    if ($tipoCargo == "Adm/Tec") {
        $objeto->set_selectEdita("SELECT idConcurso,
                                     idServidorOcupanteAnterior,
                                     dtPublicConcursoResultado,
                                     pgPublicConcursoResultado,
                                     classificacaoConcurso,
                                     instituicaoConcurso,
                                     dtPublicConvocacao,
                                     pgPublicConvocacao,
                                     dtPublicResultadoExameMedico,
                                     pgPublicResultadoExameMedico,
                                     dtPublicAtoNomeacao,
                                     pgPublicAtoNomeacao,
                                     dtPublicAtoInvestidura,
                                     pgPublicAtoInvestidura,
                                     dtPublicTermoPosse,
                                     pgPublicTermoPosse,
                                     obsConcurso
                                FROM tbservidor
                               WHERE idServidor = {$idServidorPesquisado}");
    } else {
        $objeto->set_selectEdita("SELECT idConcurso,
                                     dtPublicConcursoResultado,
                                     pgPublicConcursoResultado,
                                     classificacaoConcurso,
                                     dtPublicConvocacao,
                                     pgPublicConvocacao,
                                     dtPublicResultadoExameMedico,
                                     pgPublicResultadoExameMedico,
                                     dtPublicAtoNomeacao,
                                     pgPublicAtoNomeacao,
                                     dtPublicAtoInvestidura,
                                     pgPublicAtoInvestidura,
                                     dtPublicTermoPosse,
                                     pgPublicTermoPosse,
                                     obsConcurso
                                FROM tbservidor
                               WHERE idServidor = {$idServidorPesquisado}");
    }

    # Caminhos
    $objeto->set_linkGravar('?fase=gravar');

    # botão voltar
    if (is_null($origem)) {
        $objeto->set_voltarForm('servidorMenu.php');
        $objeto->set_linkListar('servidorMenu.php');
    } else {
        $objeto->set_voltarForm($origem);
        $objeto->set_linkListar($origem);
    }


    # retira o botão incluir
    $objeto->set_botaoIncluir(false);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbservidor');

    # Nome do campo id
    $objeto->set_idCampo('idServidor');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Trata o tipo
    if ($tipoCargo == "Adm/Tec") {
        /*
         *  Combo concurso
         */
        $select = "SELECT idconcurso,
                          concat(anoBase,' - Edital: ',DATE_FORMAT(dtPublicacaoEdital,'%d/%m/%Y')) as concurso
                     FROM tbconcurso
               WHERE tipo = 1     
            ORDER BY dtPublicacaoEdital desc";

        # Pega os dados da combo concurso
        $concurso = $pessoal->select($select);
        $idConcurso = null;

        array_unshift($concurso, array(null, null));

        /*
         *  Combo ocupante anterior
         */

        # Pega o cargo do servidor pesquisado
        $idCargoPesquisado = $pessoal->get_idTipoCargoServidor($idServidorPesquisado);

        # Pega a data de admissão do servidor pesquisado
        $dtAdmPesquisado = date_to_bd($pessoal->get_dtAdmissao($idServidorPesquisado));

        $select = "SELECT tbservidor.idServidor,
                          CONCAT(date_format(dtAdmissao,'%d/%m/%Y'),' - ',date_format(dtDemissao,'%d/%m/%Y'),' - ',UADM,' - ',tbpessoa.nome),
                          tbcargo.nome
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbcargo USING (idCargo)
                                     LEFT JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                     LEFT JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)                           
               WHERE situacao <> 1
                 AND idTipoCargo = {$idCargoPesquisado}
                 AND dtDemissao < '{$dtAdmPesquisado}'
                 AND tbservidor.idServidor NOT IN (SELECT idServidorOcupanteAnterior FROM tbservidor WHERE idServidorOcupanteAnterior IS NOT null AND idServidor <> {$idServidorPesquisado})
                 AND (idPerfil = 1 OR idPerfil = 4)
                 AND idConcurso IS NOT NULL
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
            ORDER BY tbcargo.nome, dtDemissao";

        # Pega os dados da combo concurso
        $ocupanteAnterior = $pessoal->select($select);

        array_unshift($ocupanteAnterior, array("0", "-- primeiro servidor a ocupar a vaga --"));
        array_unshift($ocupanteAnterior, array(null, null));
    } else {
        # Professor

        $vaga = new Vaga();
        # Preenche com o valor da tabela tbvagahistórico
        # Que é onde fica cadastrado o concurso dos docentes
        $idConcurso = $vaga->get_idConcursoProfessor($idServidorPesquisado);

        if (!empty($idConcurso)) {

            $select = "SELECT idconcurso,
                              concat(anoBase,' - Edital: ',DATE_FORMAT(dtPublicacaoEdital,'%d/%m/%Y')) as concurso
                         FROM tbconcurso
                   WHERE idConcurso = $idConcurso";

            # Pega os dados da combo concurso
            $concurso = $pessoal->select($select);
        } else {
            $concurso = null;
            $idConcurso = null;
        }
    }

    # Campos para o formulario
    $campos = array(
        array('linha' => 1,
            'nome' => 'idConcurso',
            'label' => 'Concurso:',
            'tipo' => 'combo',
            'array' => $concurso,
            'title' => 'Concurso',
            'padrao' => $idConcurso,
            'col' => 5,
            'size' => 15));

    if ($tipoCargo == "Adm/Tec") {
        array_push($campos,
                array('linha' => 1,
                    'nome' => 'idServidorOcupanteAnterior',
                    'label' => 'Vaga Anteriormente Ocupada por:',
                    'tipo' => 'combo',
                    'array' => $ocupanteAnterior,
                    'title' => 'Servidor que ocupava anteriormente esta vaga (quando houver)',
                    'optgroup' => true,
                    'padrao' => null,
                    'col' => 7,
                    'size' => 15));
    }

    array_push($campos,
            array('linha' => 2,
                'nome' => 'dtPublicConcursoResultado',
                'label' => 'Resultado Final do Concurso:',
                'tipo' => 'data',
                'title' => 'Data da publicação do resultado final do concurso',
                'fieldset' => "Publicações",
                'col' => 4,
                'size' => 15),
            array('linha' => 2,
                'nome' => 'pgPublicConcursoResultado',
                'label' => 'Página:',
                'tipo' => 'texto',
                'size' => 6,
                'title' => 'Página da publicação',
                'col' => 2),
            array('linha' => 2,
                'nome' => 'classificacaoConcurso',
                'label' => 'Classificação:',
                'tipo' => 'numero',
                'size' => 6,
                'title' => 'Classificação final do concurso',
                'col' => 2));

    if ($tipoCargo == "Adm/Tec") {
        array_push($campos,
                array('linha' => 2,
                    'nome' => 'instituicaoConcurso',
                    'label' => 'Instituição (se houver):',
                    'tipo' => 'combo',
                    'array' => [[null, null], ["Fenorte", "Fenorte"], ["Tecnorte", "Tecnorte"], ["Uenf", "Uenf"]],
                    'title' => 'Instituição do Concurso (quando houver)',
                    'padrao' => null,
                    'col' => 3,
                    'size' => 15));
    }

    array_push($campos,
            array('linha' => 3,
                'nome' => 'dtPublicConvocacao',
                'label' => 'Convocação:',
                'tipo' => 'data',
                'title' => 'Data da publicação da convocação do servidor',
                'col' => 4,
                'size' => 15),
            array('linha' => 3,
                'nome' => 'pgPublicConvocacao',
                'label' => 'Página:',
                'tipo' => 'texto',
                'size' => 6,
                'title' => 'Página da publicação',
                'col' => 2),
            array('linha' => 3,
                'nome' => 'dtPublicResultadoExameMedico',
                'label' => 'Resultado do Exame Médico:',
                'tipo' => 'data',
                'title' => 'Data da publicação do resultado do exame médico',
                'col' => 4,
                'size' => 15),
            array('linha' => 3,
                'nome' => 'pgPublicResultadoExameMedico',
                'label' => 'Página:',
                'tipo' => 'texto',
                'size' => 6,
                'title' => 'Página da publicação',
                'col' => 2),
            array('linha' => 4,
                'nome' => 'dtPublicAtoNomeacao',
                'label' => 'Ato de Nomeação:',
                'tipo' => 'data',
                'title' => 'Data da publicação do ato de nomeação',
                'col' => 4,
                'size' => 15),
            array('linha' => 4,
                'nome' => 'pgPublicAtoNomeacao',
                'label' => 'Página:',
                'tipo' => 'texto',
                'size' => 6,
                'title' => 'Página da publicação',
                'col' => 2),
            array('linha' => 4,
                'nome' => 'dtPublicAtoInvestidura',
                'label' => 'Ato de Investidura:',
                'tipo' => 'data',
                'title' => 'Data da publicação do ato de investidura',
                'col' => 4,
                'size' => 15),
            array('linha' => 4,
                'nome' => 'pgPublicAtoInvestidura',
                'label' => 'Página:',
                'tipo' => 'texto',
                'size' => 6,
                'title' => 'Página da publicação',
                'col' => 2),
            array('linha' => 6,
                'nome' => 'dtPublicTermoPosse',
                'label' => 'Termo de Posse:',
                'tipo' => 'data',
                'title' => 'Data da publicação do termo de posse',
                'col' => 4,
                'size' => 15),
            array('linha' => 6,
                'nome' => 'pgPublicTermoPosse',
                'label' => 'Página:',
                'tipo' => 'texto',
                'size' => 3,
                'title' => 'Página da publicação',
                'col' => 2),
            array('linha' => 7,
                'col' => 12,
                'fieldset' => "fecha",
                'nome' => 'obsConcurso',
                'label' => 'Observação:',
                'tipo' => 'textarea',
                'size' => array(80, 3)));

    $objeto->set_campos($campos);

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ###############################################################
    # Inicia o Menu de Cargos
    # Pega o idConcurso
    $idConcursoServidor = $pessoal->get_idConcurso($idServidorPesquisado);

    if (!empty($idConcursoServidor)) {

        $menu = new Menu("menuVertical");
        $menu->add_item('titulo', 'Publicações Gerais');

        $select = "SELECT descricao,
                      data,
                      pag,
                      idConcursoPublicacao,
                      obs
                 FROM tbconcursopublicacao
                WHERE idConcurso = $idConcursoServidor  
             ORDER BY data, idConcursoPublicacao";

        $conteudo = $pessoal->select($select);

        # Preenche com os cargos
        foreach ($conteudo as $item) {
            $menu->add_item('linkWindow', date_to_php($item[1]) . ' - ' . $item[0], PASTA_CONCURSO . $item[3] . ".pdf", $item[4]);
        }
        $objeto->set_menuLateralEditar($menu);
    }

    ################################################################
    switch ($fase) {
        case "ver" :
        case "editar" :
            $objeto->$fase($idServidorPesquisado);
            break;

        case "gravar" :
            $objeto->$fase($idServidorPesquisado);
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}