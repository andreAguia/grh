<?php

/**
 * Histórico de Triênios
 *
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null; # Servidor logado
$idServidorPesquisado = null; # Servidor Editado na pesquisa do sistema do GRH
# Configuração
include "_config.php";

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();
    $trienio = new Trienio();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Histórico de triênios";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

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
    # Exibe os dados do Servidor
    $objeto->set_rotinaExtra("get_DadosServidor");
    $objeto->set_rotinaExtraParametro($idServidorPesquisado);

    # Pega os dados do último percentual
    $ultimoPercentual = $trienio->getPercentual($idServidorPesquisado);
    $ultimoTrienio = $trienio->getDataInicial($idServidorPesquisado);
    $dataAdmissao = $pessoal->get_dtAdmissao($idServidorPesquisado);

    # Sugere o próximo triênio
    if (is_null($ultimoTrienio)) {
        $proximoTrienio = addAnos($dataAdmissao, 3);
    } else {
        $proximoTrienio = addAnos($ultimoTrienio, 3);
    }

    # retira o botão de incluir triênio quando estiver no máximo
    #if ($ultimoPercentual == "60"){
    #    $objeto->set_botaoIncluir(false);
    #}
    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Cadastro de Triênios do Servidor');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');
    
    # select da lista
    $objeto->set_selectLista("SELECT dtInicial,
                                     percentual,
                                     CONCAT(date_format(dtInicioPeriodo,'%d/%m/%Y'),' - ',date_format(dtFimPeriodo,'%d/%m/%Y')),
                                     numProcesso,
                                     dtPublicacao,
                                     documento,
                                     obs,
                                     idTrienio
                                FROM tbtrienio
                               WHERE idServidor = {$idServidorPesquisado}
                            ORDER BY dtInicial desc, 2 DESC");

    # select do edita
    $objeto->set_selectEdita('SELECT percentual,
                                     dtInicial,
                                     dtInicioPeriodo,
                                     dtFimPeriodo,
                                     documento,
                                     numProcesso,
                                     dtPublicacao,
                                     obs,
                                     idServidor
                                FROM tbtrienio
                               WHERE idTrienio = ' . $id);
    
    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("a partir de", "%", "Período Aquisitivo", "Processo", "DOERJ", "Documento","Obs"));
    $objeto->set_width(array(10,5,20,15,10,15,15));
    $objeto->set_align(array("center"));
    $objeto->set_funcao(array("date_to_php", null, null, null, "date_to_php"));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbtrienio');

    # Nome do campo id
    $objeto->set_idCampo('idTrienio');

    # Monta o array para o campo percentual
    $percentuaisPossiveis = array("10", "15", "20", "25", "30", "35", "40", "45", "50", "55", "60");

    if (is_null($id)) { // se for novo triênio
        if (is_null($ultimoPercentual)) {
            $percentuais = $percentuaisPossiveis;
        } else {
            $percentuais = array();
            $indice = array_search($ultimoPercentual, $percentuaisPossiveis);

            if ($ultimoPercentual != "60") {
                array_push($percentuais, $percentuaisPossiveis[$indice + 1]);
            }
        }
    } else {
        $percentuais = $percentuaisPossiveis;
    }

    # Campos para o formulario
    $objeto->set_campos(array(
        array(
            'nome' => 'percentual',
            'label' => 'Percentual:',
            'tipo' => 'combo',
            'required' => true,
            'autofocus' => true,
            'array' => $percentuaisPossiveis,
            'padrao' => $percentuais[0],
            'size' => 20,
            'col' => 2,
            'title' => 'período de férias',
            'linha' => 1),
        array(
            'nome' => 'dtInicial',
            'label' => 'Data Inicial:',
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'required' => true,
            'padrao' => date_to_bd($proximoTrienio),
            'title' => 'Data inícial do Triênio.',
            'linha' => 1),
        array('nome' => 'dtInicioPeriodo',
            'label' => 'Início do período aquisitivo:',
            'fieldset' => 'Período Aquisitivo',
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'required' => true,
            'title' => 'Data de início do período aquisitivo',
            'linha' => 1),
        array('nome' => 'dtFimPeriodo',
            'label' => 'Término do período aquisitivo:',
            'tipo' => 'data',
            'col' => 3,
            'size' => 20,
            'required' => true,
            'title' => 'Data de término do período aquisitivo',
            'linha' => 1),
        array('nome' => 'documento',
            'fieldset' => 'fecha',
            'label' => 'Documento:',
            'tipo' => 'texto',
            'size' => 30,
            'col' => 3,
            'title' => 'Documento comunicando a nova progressão.',
            'linha' => 3),
        array('nome' => 'numProcesso',
            'label' => 'Processo:',
            'tipo' => 'texto',
            'size' => 30,
            'col' => 3,
            'title' => 'Número do Processo',
            'linha' => 3),
        array('nome' => 'dtPublicacao',
            'label' => 'Data da Pub. no DOERJ:',
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'title' => 'Data da Publicação no DOERJ.',
            'linha' => 3),
        array('linha' => 4,
            'nome' => 'obs',
            'col' => 12,
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(80, 5)),
        array('nome' => 'idServidor',
            'label' => 'idServidor:',
            'tipo' => 'hidden',
            'padrao' => $idServidorPesquisado,
            'size' => 5,
            'title' => 'Matrícula',
            'linha' => 5)));

    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir Relatório de Histórico de Triênios");
    $botaoRel->set_url("../grhRelatorios/servidorTrienio.php");
    $botaoRel->set_target("_blank");

    $objeto->set_botaoListarExtra(array($botaoRel));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ################################################################

    switch ($fase) {
        case "editar":
            # Informa da porcentagem do triênio
            if (is_null($id)) { // se for inclusão
                if (!is_null($ultimoPercentual)) {
                    $mensagem1 = 'O último triênio desse servidor foi de ' . $ultimoPercentual . '%, o próximo percentual deverá ser de ' . $percentuais[0] . '%.';
                    $objeto->set_rotinaExtraEditar("callout");
                    $objeto->set_rotinaExtraEditarParametro($mensagem1);
                }
            }

            $objeto->editar($id);
            break;

        case "":
        case "listar":

            if ($ultimoPercentual == "60") {
                $mensagem2 = 'Servidor já alcançou o teto do triênio: 60%';
            } else {
                $mensagem2 = 'Próximo Triênio: ' . $proximoTrienio;

                if (jaPassou($proximoTrienio)) {
                    $mensagem2 .= ' (Atenção: A data já passou!)';
                }
            }

            ####

            function teste($idServidor)
        {

                # Monta a tabela de tempo averbado
                $select = 'SELECT dtInicial,
                        dtFinal,
                        dias,
                        empresa,
                        CASE empresaTipo
                           WHEN 1 THEN "Pública"
                           WHEN 2 THEN "Privada"
                        END,
                        CASE regime
                           WHEN 1 THEN "Celetista"
                           WHEN 2 THEN "Estatutário"
                           WHEN 3 THEN "Próprio"
                        END,
                        cargo,
                        dtPublicacao,
                        processo,
                        idAverbacao
                   FROM tbaverbacao
                  WHERE empresaTipo = 1 AND idServidor = ' . $idServidor . '
                ORDER BY 1 desc';

                $pessoal = new Pessoal();
                $result = $pessoal->select($select);

                $label = array("Data Inicial", "Data Final", "Dias", "Empresa", "Tipo", "Regime", "Cargo", "Publicação", "Processo");
                $align = array("center", "center", "center", "left");
                $funcao = array("date_to_php", "date_to_php", null, null, null, null, null, "date_to_php");

                $tabela = new Tabela();
                $tabela->set_titulo('Tempo Público Averbado');
                $tabela->set_conteudo($result);
                $tabela->set_label($label);
                $tabela->set_align($align);
                $tabela->set_funcao($funcao);
                $tabela->set_idCampo('idAverbacao');
                #$tabela->set_editar('?fase=editar&id=');
                #$tabela->set_excluir('?fase=excluir&id=');
                $tabela->show();
            }

            ###

            $objeto->set_rotinaExtraListar(array("callout", "teste"));
            $objeto->set_rotinaExtraListarParametro(array($mensagem2, $idServidorPesquisado));

            $objeto->listar($id);
            break;

        case "excluir":
            $objeto->$fase($id);
            break;

        case "gravar":
            $objeto->gravar($id, "servidorTrienioExtra.php");
            break;
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
