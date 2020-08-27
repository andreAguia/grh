<?php

/**
 * Controle de Frequência de servidor da Uenf
 *  
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;              // Servidor logado
$idServidorPesquisado = null;   // Servidor Editado na pesquisa do sistema do GRH
# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $cessao = new Cessao();

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros
    $idHistCessao = soNumeros(get('idHistCessao', get_session('idHistCessao')));
    $afastamento = soNumeros(get('afastamento', get_session('afastamento', 1)));

    # Joga os parâmetros para as sessions
    set_session('idHistCessao', $idHistCessao);
    set_session('afastamento', $afastamento);

    # Pega a data Inicial e Final da Cessao
    $dadosCessao = $cessao->getDados($idHistCessao);
    $dtInicialCessao = $dadosCessao["dtInicio"];
    $dtTerminoCessao = $dadosCessao["dtFim"];

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Exibe os dados do Servidor
    $objeto->set_rotinaExtra("get_DadosFrequencia");
    $objeto->set_rotinaExtraParametro($idHistCessao);

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Controle de Frequência');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorCessao.php');

    # So Frequencia
    $select1 = "SELECT YEAR(dtInicial),
                       MONTH(dtInicial),
                       dtInicial,
                       dtFinal,
                       documento,
                       '<span class=\'label primary\'>Frequência</span>',
                       obs,
                       idFrequencia
                  FROM tbfrequencia
                 WHERE idHistCessao = {$idHistCessao}";

    # Frequencias e ferias
    $select = "(SELECT YEAR(dtInicial),
                       MONTH(dtInicial),
                       dtInicial,
                       dtFinal,
                       documento,
                       '<span class=\'label primary\'>Frequência</span>',
                       obs,
                       idFrequencia
                  FROM tbfrequencia
                 WHERE idHistCessao = {$idHistCessao}
          ) UNION (
                SELECT YEAR(dtInicial),
                       MONTH(dtInicial),
                       dtInicial,
                       ADDDATE(dtInicial,numDias-1) as dtFinal,
                        '---',                        
                       '<span class=\'label warning\'>Afastamento</span>',
                       CONCAT ('Ferias - Exercicio: ',anoExercicio),
                       ''
                  FROM tbferias
                 WHERE idServidor = {$idServidorPesquisado}
                   AND dtInicial >= '{$dtInicialCessao}'";

    if (!empty($dtTerminoCessao)) {
        $select .= " AND ADDDATE(dtInicial,numDias-1) <= '{$dtTerminoCessao}'";
    }

    # Licenças
    $select .= ") UNION (
                SELECT YEAR(tblicenca.dtInicial),
                       MONTH(tblicenca.dtInicial),
                       tblicenca.dtInicial,
                       ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1),
                        '---',
                       '<span class=\'label warning\'>Afastamento</span>',
                       tbtipolicenca.nome,
                       ''
                      FROM tblicenca JOIN tbservidor USING (idServidor)
                                     JOIN tbtipolicenca USING (idTpLicenca)
                    WHERE tbservidor.idServidor = {$idServidorPesquisado}
                      AND tblicenca.dtInicial >= '{$dtInicialCessao}'";

    if (!empty($dtTerminoCessao)) {
        $select .= " AND ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1) <= '{$dtTerminoCessao}'";
    }

    # Licença Premio
    $select .= ") UNION (
                SELECT YEAR(tblicencapremio.dtInicial),
                       MONTH(tblicencapremio.dtInicial),
                       tblicencapremio.dtInicial,
                       ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1),
                        '---',
                       '<span class=\'label warning\'>Afastamento</span>',
                       'Licença Prêmio',
                       ''
                  FROM tblicencapremio JOIN tbservidor USING (idServidor)
                 WHERE tbservidor.idServidor = {$idServidorPesquisado}
                   AND tblicencapremio.dtInicial >= '{$dtInicialCessao}'";

    if (!empty($dtTerminoCessao)) {
        $select .= " AND ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1) <= '{$dtTerminoCessao}'";
    }

    # Faltas Abonadas
    $select .= ") UNION (
                SELECT YEAR(tbatestado.dtInicio),
                       MONTH(tbatestado.dtInicio),
                       tbatestado.dtInicio,
                       ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1),
                        '---',                        
                       '<span class=\'label warning\'>Afastamento</span>',
                       'Falta Abonada',
                       ''
                     FROM tbatestado JOIN tbservidor USING (idServidor)
                    WHERE tbservidor.idServidor = {$idServidorPesquisado}
                      AND tbatestado.dtInicio >= '{$dtInicialCessao}'";

    if (!empty($dtTerminoCessao)) {
        $select .= " AND ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1) <= '{$dtTerminoCessao}'";
    }

    # Licença sem vencimentos
    $select .= ") UNION (
                SELECT YEAR(tblicencasemvencimentos.dtInicial),
                       MONTH(tblicencasemvencimentos.dtInicial),
                       tblicencasemvencimentos.dtInicial,
                       ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1),
                        '---',
                       '<span class=\'label warning\'>Afastamento</span>',
                       tbtipolicenca.nome,
                       ''
                  FROM tblicencasemvencimentos JOIN tbservidor USING (idServidor)
                                               JOIN tbtipolicenca USING (idTpLicenca)
                 WHERE tbservidor.idServidor = {$idServidorPesquisado}
                   AND tblicencasemvencimentos.dtInicial >= '{$dtInicialCessao}'";

    if (!empty($dtTerminoCessao)) {
        $select .= " AND ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1) <= '{$dtTerminoCessao}'";
    }

    # Trabalhando TRE
    $select .= ") UNION (
                SELECT YEAR(tbtrabalhotre.data),
                       MONTH(tbtrabalhotre.data),
                       tbtrabalhotre.data,
                       ADDDATE(tbtrabalhotre.data,tbtrabalhotre.dias-1),
                        '---',
                       '<span class=\'label warning\'>Afastamento</span>',
                       'Trabalhando no TRE',
                       ''
                     FROM tbtrabalhotre JOIN tbservidor USING (idServidor)
                     WHERE tbservidor.idServidor = {$idServidorPesquisado}
                   AND tbtrabalhotre.data >= '{$dtInicialCessao}'";

    if (!empty($dtTerminoCessao)) {
        $select .= " AND ADDDATE(tbtrabalhotre.data,tbtrabalhotre.dias-1) <= '{$dtTerminoCessao}'";
    }

    # Folga TRE
    $select .= ") UNION (
                   SELECT YEAR(tbfolga.data),
                          MONTH(tbfolga.data),
                          tbfolga.data,
                          ADDDATE(tbfolga.data,tbfolga.dias-1),
                           '---',
                           '<span class=\'label warning\'>Afastamento</span>',
                          'Folga TRE',                         
                          ''
                     FROM tbfolga JOIN tbservidor USING (idServidor)
                     WHERE tbservidor.idServidor = {$idServidorPesquisado}
                   AND tbfolga.data >= '{$dtInicialCessao}'";

    if (!empty($dtTerminoCessao)) {
        $select .= " AND ADDDATE(tbfolga.data,tbfolga.dias-1) <= '{$dtTerminoCessao}'";
    }


    $select .= ")ORDER BY 3";

    # Escolhe o select
    if ($afastamento == 1) {
        $afastamento = 2;
        $selectEscolhido = $select1;
        $botao = "primary";
    } else {
        $afastamento = 1;
        $selectEscolhido = $select;
        $botao = "warning";
    }

    # select da lista
    $objeto->set_selectLista($selectEscolhido);

    # select do edita
    $objeto->set_selectEdita("SELECT dtInicial,
                                     dtFinal,
                                     documento,
                                     obs,
                                     idHistCessao,
                                     idServidor
                                FROM tbfrequencia
                               WHERE idFrequencia = {$id}");

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_botaoEditar(false);
    #$objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    $objeto->set_rowspan(0);
    $objeto->set_grupoCorColuna(0);

    # Parametros da tabela
    $objeto->set_label(array("Ano", "Mês", "Data Inicial", "Data Final", "Documento", "Tipo", "Obs"));
    $objeto->set_align(array("center", "center", "center", "center", "left", "center", "left"));
    $objeto->set_funcao(array(null, "get_nomeMes", "date_to_php", "date_to_php"));
    $objeto->set_width(array(10, 10, 10, 10, 20, 10, 20));

    # Editar e excluir condicional
    $objeto->set_editarCondicional('?fase=editar', '<span class=\'label primary\'>Frequência</span>', 5, "=");
    $objeto->set_excluirCondicional('?fase=excluir', '<span class=\'label primary\'>Frequência</span>', 5, "=");

//    $objeto->set_formatacaoCondicional(array(
//        array('coluna' => 2,
//            'valor' => "Frequência",
//            'operador' => '<>',
//            'id' => 'naoFrequencia'),
//        array('coluna' => 2,
//            'valor' => "Frequência",
//            'operador' => '=',
//            'id' => "frequencia")
//    ));
    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbfrequencia');

    # Nome do campo id
    $objeto->set_idCampo('idFrequencia');

    # Campos para o formulario
    $objeto->set_campos(array(
        array('nome' => 'dtInicial',
            'label' => 'Data Inicial:',
            'tipo' => 'date',
            'required' => true,
            'autofocus' => true,
            'size' => 15,
            'col' => 3,
            'title' => 'Data Inicial',
            'padrao' => date_to_bd($cessao->getDataInicialFrequencia($idHistCessao)),
            'linha' => 1),
        array('nome' => 'dtFinal',
            'label' => 'Data Final:',
            'tipo' => 'date',
            'required' => true,
            'size' => 15,
            'col' => 3,
            'title' => 'Data Final',
            'padrao' => date_to_bd($cessao->getDataFinalFrequencia($idHistCessao)),
            'linha' => 1),
        array('nome' => 'documento',
            'label' => 'Documento:',
            'tipo' => 'texto',
            'size' => 100,
            'col' => 6,
            'title' => 'Documento',
            'linha' => 1),
        array('linha' => 2,
            'nome' => 'obs',
            'col' => 12,
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(80, 5)),
        array('nome' => 'idHistCessao',
            'label' => 'idHistCessao:',
            'tipo' => 'hidden',
            'padrao' => $idHistCessao,
            'size' => 11,
            'title' => 'idServidor',
            'linha' => 3),
        array('nome' => 'idServidor',
            'label' => 'idServidor:',
            'tipo' => 'hidden',
            'padrao' => $idServidorPesquisado,
            'size' => 11,
            'title' => 'idServidor',
            'linha' => 3)
    ));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    # Botao extra de Obs para digitaçao
    $botaoObs = new Button("Obs");
    $botaoObs->set_title("Acessa as observaçoes do servidor");
    $botaoObs->set_url('servidorObs.php');
    $botaoObs->set_target("_blank");

    $botaoObs = new Button("Afastamentos");
    $botaoObs->set_title("Exibe ou nao os afastamentos do periodo");
    $botaoObs->set_url('?afastamento=' . $afastamento);
    $botaoObs->set_class("{$botao} button");

    $objeto->set_botaoListarExtra([$botaoObs]);

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
            $objeto->listar();
            break;

        case "editar" :
        case "excluir" :
            $objeto->$fase($id);
            break;

        case "gravar" :
            $objeto->gravar($id, "servidorFrequenciaExtra.php");
            break;
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}