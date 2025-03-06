<?php

/**
 * Controle de Frequência de servidor da Uenf
 *  
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;
$idServidorPesquisado = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $cessao = new Cessao();
    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Controle de frequência de servidor da Uebf cedido para outro órgão";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    # Pega o parametro de pesquisa (se tiver)
    if (is_null(post('parametro'))) {
        $parametro = retiraAspas(get_session('sessionParametro'));
    } else {
        $parametro = post('parametro');
        set_session('sessionParametro', $parametro);
    }

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
    
    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

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
                 WHERE idHistCessao = {$idHistCessao}
                   AND (YEAR(dtInicial) LIKE '%{$parametro}%' OR documento LIKE '%{$parametro}%')
                     ORDER BY 3 DESC";

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
                 WHERE idServidor = {$idServidorPesquisado}";

    if (empty($dtTerminoCessao)) {
        $select .= " AND (dtInicial >= '{$dtInicialCessao}' OR ADDDATE(dtInicial,numDias-1) >= '{$dtInicialCessao}')";
    } else {
        $select .= " AND ((dtInicial BETWEEN '{$dtInicialCessao}' AND '{$dtTerminoCessao}') 
                      OR (ADDDATE(dtInicial,numDias-1) BETWEEN '{$dtInicialCessao}' AND '{$dtTerminoCessao}') 
                      OR (dtInicial < '{$dtInicialCessao}' AND ADDDATE(dtInicial,numDias-1) > '{$dtTerminoCessao}'))";
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
                    WHERE tbservidor.idServidor = {$idServidorPesquisado}";

    if (empty($dtTerminoCessao)) {
        $select .= " AND (dtInicial >= '{$dtInicialCessao}' OR ADDDATE(dtInicial,numDias-1) >= '{$dtInicialCessao}')";
    } else {
        $select .= " AND ((dtInicial BETWEEN '{$dtInicialCessao}' AND '{$dtTerminoCessao}') 
                      OR (ADDDATE(dtInicial,numDias-1) BETWEEN '{$dtInicialCessao}' AND '{$dtTerminoCessao}') 
                      OR (dtInicial < '{$dtInicialCessao}' AND ADDDATE(dtInicial,numDias-1) > '{$dtTerminoCessao}'))";
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
                 WHERE tbservidor.idServidor = {$idServidorPesquisado}";

    if (empty($dtTerminoCessao)) {
        $select .= " AND (dtInicial >= '{$dtInicialCessao}' OR ADDDATE(dtInicial,numDias-1) >= '{$dtInicialCessao}')";
    } else {
        $select .= " AND ((dtInicial BETWEEN '{$dtInicialCessao}' AND '{$dtTerminoCessao}') 
                      OR (ADDDATE(dtInicial,numDias-1) BETWEEN '{$dtInicialCessao}' AND '{$dtTerminoCessao}') 
                      OR (dtInicial < '{$dtInicialCessao}' AND ADDDATE(dtInicial,numDias-1) > '{$dtTerminoCessao}'))";
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
                    WHERE tbservidor.idServidor = {$idServidorPesquisado}";

    if (empty($dtTerminoCessao)) {
        $select .= " AND (dtInicio >= '{$dtInicialCessao}' OR ADDDATE(dtInicio,numDias-1) >= '{$dtInicialCessao}')";
    } else {
        $select .= " AND ((dtInicio BETWEEN '{$dtInicialCessao}' AND '{$dtTerminoCessao}') 
                      OR (ADDDATE(dtInicio,numDias-1) BETWEEN '{$dtInicialCessao}' AND '{$dtTerminoCessao}') 
                      OR (dtInicio < '{$dtInicialCessao}' AND ADDDATE(dtInicio,numDias-1) > '{$dtTerminoCessao}'))";
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
                 WHERE tbservidor.idServidor = {$idServidorPesquisado}";

    if (empty($dtTerminoCessao)) {
        $select .= " AND (dtInicial >= '{$dtInicialCessao}' OR ADDDATE(dtInicial,numDias-1) >= '{$dtInicialCessao}')";
    } else {
        $select .= " AND ((dtInicial BETWEEN '{$dtInicialCessao}' AND '{$dtTerminoCessao}') 
                      OR (ADDDATE(dtInicial,numDias-1) BETWEEN '{$dtInicialCessao}' AND '{$dtTerminoCessao}') 
                      OR (dtInicial < '{$dtInicialCessao}' AND ADDDATE(dtInicial,numDias-1) > '{$dtTerminoCessao}'))";
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
                     WHERE tbservidor.idServidor = {$idServidorPesquisado}";

    if (empty($dtTerminoCessao)) {
        $select .= " AND (data >= '{$dtInicialCessao}' OR ADDDATE(data,dias-1) >= '{$dtInicialCessao}')";
    } else {
        $select .= " AND ((data BETWEEN '{$dtInicialCessao}' AND '{$dtTerminoCessao}') 
                      OR (ADDDATE(data,dias-1) BETWEEN '{$dtInicialCessao}' AND '{$dtTerminoCessao}') 
                      OR (data < '{$dtInicialCessao}' AND ADDDATE(data,dias-1) > '{$dtTerminoCessao}'))";
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
                     WHERE tbservidor.idServidor = {$idServidorPesquisado}";

    if (empty($dtTerminoCessao)) {
        $select .= " AND (data >= '{$dtInicialCessao}' OR ADDDATE(data,dias-1) >= '{$dtInicialCessao}')";
    } else {
        $select .= " AND ((data BETWEEN '{$dtInicialCessao}' AND '{$dtTerminoCessao}') 
                      OR (ADDDATE(data,dias-1) BETWEEN '{$dtInicialCessao}' AND '{$dtTerminoCessao}') 
                      OR (data < '{$dtInicialCessao}' AND ADDDATE(data,dias-1) > '{$dtTerminoCessao}'))";
    }


    $select .= ")ORDER BY 3 DESC";

    # Escolhe o select
    if ($afastamento == 1) {
        $afastamento = 2;
        $selectEscolhido = $select1;
        $labelBotao = "Exibe os Afastamentos";
    } else {
        $afastamento = 1;
        $selectEscolhido = $select;
        $botao = "warning";
        $labelBotao = "Oculta os Afastamentos";
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

    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

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
    if (Verifica::acesso($idUsuario, [1, 2])) {
        $objeto->set_excluirCondicional('?fase=excluir', '<span class=\'label primary\'>Frequência</span>', 5, "=");
    }

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
    $botao1 = new Button("Obs");
    $botao1->set_title("Acessa as observaçoes do servidor");
    $botao1->set_url('?fase=exibeObs');
    $botao1->set_target("_blank");

    $botao2 = new Button($labelBotao);
    $botao2->set_url('?afastamento=' . $afastamento);
    if (!empty($botao)) {
        $botao2->set_class("{$botao} button");
    }

    $objeto->set_botaoListarExtra([$botao2, $botao1]);

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

        case "exibeObs" :
            $grid = new Grid();
            $grid->abreColuna(12);

            br();
            tituloTable("Observações");
            echo "<pre>{$pessoal->get_obs($idServidorPesquisado)}</pre>";

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}