<?php

/**
 * Cadastro de Publicação de Licenças Prêmios
 *  
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;
$idServidorPesquisado = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12, 19]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $licenca = new LicencaPremio();
    $intra = new Intra();

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Começa uma nova página
    $page = new Page();
    if ($fase == "upload") {
        $page->set_ready('$(document).ready(function(){
                                $("form input").change(function(){
                                    $("form p").text(this.files.length + " arquivo(s) selecionado");
                                });
                            });');
    }
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
    $objeto->set_nome('Cadastro de Publicações de Licança Prêmio');

    # bot?o de voltar da lista
    $objeto->set_voltarLista('servidorLicencaPremio.php');

    # controle de pesquisa
    #$objeto->set_parametroLabel('Pesquisar');
    #$objeto->set_parametroValue($parametro);
    # select da lista
    $objeto->set_selectLista('SELECT dtPublicacao,
                                     idPublicacaoPremio,
                                     numDias,
                                     idPublicacaoPremio,
                                     idPublicacaoPremio,
                                     idPublicacaoPremio,
                                     obs,
                                     idPublicacaoPremio                                     
                                FROM tbpublicacaopremio
                                WHERE idServidor = ' . $idServidorPesquisado . '
                             ORDER BY dtInicioPeriodo desc');

    # select do edita
    $objeto->set_selectEdita('SELECT dtPublicacao,
                                     dtInicioPeriodo,
                                     dtFimPeriodo,
                                     numDias,
                                     obs,
                                     idServidor
                                FROM tbpublicacaopremio
                               WHERE idPublicacaoPremio = ' . $id);

    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12) AND !Verifica::acesso($idUsuario, 19)) {
        $objeto->set_modoLeitura(true);
    }

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(["Data da Publicação", "Período Aquisitivo", "Dias <br/> Publicados", "Dias <br/> Fruídos", "Dias <br/> Disponíveis", "DO", "Obs"]);
    $objeto->set_width([10, 15, 10, 10, 10, 10, 20]);
    $objeto->set_align(["center", "center", "center", "center", "center", "center", "left"]);
    $objeto->set_funcao(['date_to_php']);
    $objeto->set_classe([null, 'LicencaPremio', null, 'LicencaPremio', 'LicencaPremio', 'LicencaPremio']);
    $objeto->set_metodo([null, "exibePeriodoAquisitivo2", null, 'get_numDiasFruidosPorPublicacao', 'get_numDiasDisponiveisPorPublicacao', 'exibeDoerj']);

    $objeto->set_numeroOrdem(true);
    $objeto->set_numeroOrdemTipo("d");
    $objeto->set_exibeTempoPesquisa(false);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbpublicacaopremio');

    # Nome do campo id
    $objeto->set_idCampo('idPublicacaoPremio');

    # Campos para o formulario
    $objeto->set_campos(array(
        array('nome' => 'dtPublicacao',
            'label' => 'Data da Pub. no DOERJ:',
            'autofocus' => true,
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'required' => true,
            'title' => 'Data da Publicação no DOERJ.',
            'linha' => 1),
        array('nome' => 'dtInicioPeriodo',
            'label' => 'Período Aquisitivo Início:',
            'tipo' => 'data',
            'col' => 3,
            'size' => 20,
            'required' => true,
            'padrao' => date_to_bd($licenca->get_dataInicialProximoPeriodo($idServidorPesquisado)),
            'title' => 'Data de início do período aquisitivo',
            'linha' => 1),
        array('nome' => 'dtFimPeriodo',
            'label' => 'Período Aquisitivo Término:',
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'required' => true,
            'padrao' => date_to_bd($licenca->get_dataFinalProximaPeriodo($idServidorPesquisado)),
            'title' => 'Data de término do período aquisitivo',
            'linha' => 1),
        array('nome' => 'numDias',
            'label' => 'Dias:',
            'tipo' => 'numero',
            'padrao' => 90,
            'readOnly' => true,
            'size' => 5,
            'col' => 2,
            'required' => true,
            'title' => 'Dias de Férias.',
            'linha' => 1),
        array('linha' => 5,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'linha' => 3,
            'col' => 12,
            'size' => array(80, 5)),
        array('nome' => 'idServidor',
            'label' => 'idServidor:',
            'tipo' => 'hidden',
            'padrao' => $idServidorPesquisado,
            'size' => 5,
            'title' => 'Matrícula',
            'linha' => 6)));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    # Dados da rotina de Upload
    $pasta = PASTA_PUBLICACAO_PREMIO;
    $nome = "Publicação";
    $tabela = "tbpublicacaopremio";
    $extensoes = ["pdf"];

    # Botão de Upload
    if (!empty($id)) {

        # Botão de Upload
        $botao = new Button("Upload {$nome}");
        $botao->set_url("servidorPublicacaoPremioUpload.php?fase=upload&id={$id}");
        $botao->set_title("Faz o Upload do {$nome}");
        $botao->set_target("_blank");

        $objeto->set_botaoEditarExtra([$botao]);
    }

    ################################################################
    switch ($fase) {
        case "" :
        case "listar" :

            # Exibe quadro de licença prêmio
            #Grh::quadroLicencaPremio($idServidorPesquisado);
            # Pega os dados para o alerta
            $licenca = new LicencaPremio();
            $diasPublicados = $licenca->get_numDiasPublicados($idServidorPesquisado);
            $diasFruidos = $licenca->get_numDiasFruidos($idServidorPesquisado);
            $diasDisponiveis = $licenca->get_numDiasDisponiveis($idServidorPesquisado);

            # Exibe alerta se $diasDisponíveis for negativo
            if ($diasDisponiveis < 0) {
                $mensagem1 = "Servidor tem mais dias fruídos de Licença prêmio do que publicados.";
                $objeto->set_rotinaExtraListar("callout");
                $objeto->set_rotinaExtraListarParametro($mensagem1);
                #$objeto->set_botaoIncluir(false);
            }

            if ($diasDisponiveis == 0) {
                $mensagem1 = "Servidor sem dias disponíveis. É necessário cadastrar uma publicação antes de incluir uma licença prêmio.";
                $objeto->set_rotinaExtraListar("callout");
                $objeto->set_rotinaExtraListarParametro($mensagem1);
                #$objeto->set_botaoIncluir(false);
            }


            $objeto->listar();
            break;

        case "editar" :
            $objeto->$fase($id);
            break;
        
        case "gravar" :
            $objeto->$fase($id, "servidorPublicacaoPremioExtra.php");
            break;

        case "excluir" :
            # Verifica se tem arquivo vinculado
            if (file_exists("{$pasta}{$id}.pdf")) {

                # Verifica se existe a pasta dos arquivos apagados
                if (!file_exists("{$pasta}_apagados/") || !is_dir("{$pasta}_apagados/")) {
                    mkdir("{$pasta}_apagados/", 0755);
                }

                # Move o arquivo para a pasta dos arquivos apagados
                rename("{$pasta}{$id}.pdf", "{$pasta}_apagados/{$id}_" . $intra->get_usuario($idUsuario) . "_" . date("Y.m.d_H:i") . ".pdf");
            }


            # Exclui o registro
            $objeto->excluir($id);
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}