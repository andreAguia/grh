<?php

/**
 * Pastas e Pasta Funcional
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

# Pega o parametro de pesquisa (se tiver)
if (is_null(post('parametro'))) {
    $parametro = retiraAspas(get_session('sessionParametro'));
} else {
    $parametro = post('parametro');
    set_session('sessionParametro', $parametro);
}

if ($acesso) {
    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Pasta Funcional";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Verifica a origem 
    $origem = get_session("origem", "servidorMenu.php");

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

    # Nome do Modelo
    $objeto->set_nome('Documentos da Pasta Funcional');

    $objeto->set_voltarLista($origem);
    $objeto->set_voltarForm('?fase=listar');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # select da lista
    $objeto->set_selectLista("SELECT CASE tipo
                                        WHEN 1 THEN 'Documentos'
                                        WHEN 2 THEN 'Processos'
                                     END,
                                     descricao,
                                     idPasta,
                                     idPasta
                                FROM tbpasta
                          WHERE idServidor={$idServidorPesquisado}
                            AND descricao LIKE '%{$parametro}%'  
                       ORDER BY tipo, descricao");

    # select do edita
    $objeto->set_selectEdita("SELECT tipo,
                                     descricao,
                                     obs,
                                     idServidor
                                FROM tbpasta
                               WHERE idPasta = {$id}");

    # Caminhos
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    if (Verifica::acesso($idUsuario, [1, 4])) {
        $objeto->set_linkEditar('?fase=editar');
        $objeto->set_linkExcluir('?fase=excluir');
    } else {
        $objeto->set_botaoIncluir(false);
    }

    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

    # Parametros da tabela
    $objeto->set_label(["Tipo", "Descrição", "Obs", "Ver"]);
    $objeto->set_width([15, 50, 10, 10, 5, 5]);
    $objeto->set_align(["center", "left"]);
    $objeto->set_classe([null, null, "PastaFuncional", "PastaFuncional"]);
    $objeto->set_metodo([null, null, "exibeObs", "exibePasta"]);
    $objeto->set_rowspan(0);
    $objeto->set_grupoCorColuna(0);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbpasta');

    # Nome do campo id
    $objeto->set_idCampo('idPasta');

    # Campos para o formulario
    $objeto->set_campos(array(
        array('nome' => 'tipo',
            'label' => 'Tipo:',
            'tipo' => 'combo',
            'autofocus' => true,
            'required' => true,
            'array' => array(
                array(1, 'Documento'),
                array(2, 'Processo')),
            'size' => 20,
            'title' => 'Qual o tipo de Documento',
            'col' => 3,
            'linha' => 1),
        array('linha' => 1,
            'nome' => 'descricao',
            'label' => 'Descrição:',
            'tipo' => 'texto',
            'required' => true,
            'col' => 9,
            'size' => 250),
        array('linha' => 2,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(80, 4)),
        array('linha' => 3,
            'nome' => 'idServidor',
            'label' => 'Servidor:',
            'tipo' => 'hidden',
            'padrao' => $idServidorPesquisado,
            'col' => 9,
            'size' => 10)));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    # Dados da rotina de Upload
    $pasta = PASTA_FUNCIONAL;
    $nome = "Documento";
    $tabela = "tbpasta";
    $extensoes = ["pdf"];

    # Botão de Upload
    if (!empty($id)) {

        # Botão de Upload
        $botao = new Button("Upload {$nome}");
        $botao->set_url("servidorPastaUpload.php?fase=upload&id={$id}");
        $botao->set_title("Faz o Upload do {$nome}");
        $botao->set_target("_blank");

        $objeto->set_botaoEditarExtra([$botao]);
    }

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
            $objeto->listar();
            break;

        case "editar" :
        case "gravar" :
            if (Verifica::acesso($idUsuario, [1, 4])) {
                $objeto->$fase($id);
            } else {
                # Acesso não autorizado
                loadPage("?");
            }
            break;

        case "excluir" :
            if (Verifica::acesso($idUsuario, [1, 4])) {
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
            } else {
                # Acesso não autorizado
                loadPage("?");
            }
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}