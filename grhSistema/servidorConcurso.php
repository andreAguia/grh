<?php

/**
 * Dados Gerais do servidor
 *  
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null; # Servidor Editado na pesquisa do sistema do GRH
# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Verifica a fase do programa
    $fase = get('fase', 'ver');

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
    #$objeto->set_linkListar('?');
    $objeto->set_linkListar('servidorMenu.php');

    # botão voltar
    $objeto->set_voltarForm('servidorMenu.php');

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

        $select = "SELECT idServidor,
                          tbpessoa.nome,
                          tbcargo.nome
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbcargo USING (idCargo)
               WHERE situacao <> 1
                 AND idTipoCargo = {$idCargoPesquisado}
                 AND dtDemissao < '{$dtAdmPesquisado}'
                 AND idServidor NOT IN (SELECT idServidorOcupanteAnterior FROM tbservidor WHERE idServidorOcupanteAnterior IS NOT null AND idServidor <> {$idServidorPesquisado})    
            ORDER BY tbcargo.nome, tbpessoa.nome";

        # Pega os dados da combo concurso
        $ocupanteAnterior = $pessoal->select($select);

        array_unshift($ocupanteAnterior, array(null, "-- Inicial --"));
    } else {
        # Professor

        $vaga = new Vaga();
        # Preenche com o valor da tabela tbvagahistórico
        # Que é onde fica cadastrado o concurso dos docentes
        $idConcurso = $vaga->get_idConcursoProfessor($idServidorPesquisado);

        if (!vazio($idConcurso)) {

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
                    'col' => 7,
                    'size' => 15));
    }

    array_push($campos,
            array('linha' => 2,
                'nome' => 'dtPublicResultadoExameMedico',
                'label' => 'Resultado do Exame Médico:',
                'tipo' => 'data',
                'title' => 'Data da publicação do resultado do exame mádico',
                'col' => 4,
                'fieldset' => "Publicações",
                'size' => 15),
            array('linha' => 2,
                'nome' => 'pgPublicResultadoExameMedico',
                'label' => 'Página:',
                'tipo' => 'texto',
                'size' => 6,
                'title' => 'Página da publicação',
                'col' => 2),
            array('linha' => 2,
                'nome' => 'dtPublicAtoNomeacao',
                'label' => 'Ato de Nomeação:',
                'tipo' => 'data',
                'title' => 'Data da publicação do ato de nomeação',
                'col' => 4,
                'size' => 15),
            array('linha' => 2,
                'nome' => 'pgPublicAtoNomeacao',
                'label' => 'Página:',
                'tipo' => 'texto',
                'size' => 6,
                'title' => 'Página da publicação',
                'col' => 2),
            array('linha' => 3,
                'nome' => 'dtPublicAtoInvestidura',
                'label' => 'Ato de Investidura:',
                'tipo' => 'data',
                'title' => 'Data da publicação do ato de investidura',
                'col' => 4,
                'size' => 15),
            array('linha' => 3,
                'nome' => 'pgPublicAtoInvestidura',
                'label' => 'Página:',
                'tipo' => 'texto',
                'size' => 6,
                'title' => 'Página da publicação',
                'col' => 2),
            array('linha' => 3,
                'nome' => 'dtPublicTermoPosse',
                'label' => 'Termo de Posse:',
                'tipo' => 'data',
                'title' => 'Data da publicação do termo de posse',
                'col' => 4,
                'size' => 15),
            array('linha' => 3,
                'nome' => 'pgPublicTermoPosse',
                'label' => 'Página:',
                'tipo' => 'texto',
                'size' => 3,
                'title' => 'Página da publicação',
                'col' => 2),
            array('linha' => 4,
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