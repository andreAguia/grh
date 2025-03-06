<?php

/**
 * Histórico de Formação Escolar do Servidor
 *  
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do menu de documentos";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
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
    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Cadastro do Menu de Documentos');

    # botão de voltar da lista
    $objeto->set_voltarLista("../../areaServidor/sistema/administracao.php");
    
    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # select da lista
    $objeto->set_selectLista("SELECT idMenuDocumentos,
                                     categoria,
                                     CASE
                                        WHEN tipo = 1 THEN 'Documento'
                                        WHEN tipo = 2 THEN 'Link'
                                        ELSE '---'
                                     END,
                                     texto,
                                     title,
                                     idMenuDocumentos,
                                     idMenuDocumentos
                                FROM tbmenudocumentos
                                WHERE categoria LIKE '%{$parametro}%'
                                   OR texto LIKE '%{$parametro}%'
                                   OR title LIKE '{$parametro}'
                       ORDER BY categoria, texto");

    # select do edita
    $objeto->set_selectEdita("SELECT categoria,
                                     tipo, 
                                     texto,
                                     title,
                                     link
                                FROM tbmenudocumentos
                               WHERE idMenuDocumentos = {$id}");

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(["Id", "Categoria", "Tipo", "Texto", "Title", "Ver"]);
    $objeto->set_width([5, 20, 5, 25, 30, 5, 5]);
    $objeto->set_align(["center", "center", "center", "left", "left"]);

    $objeto->set_classe([null, null, null, null, null, "MenuDocumentos"]);
    $objeto->set_metodo([null, null, null, null, null, "exibeDocumento"]);

    $objeto->set_rowspan(1);
    $objeto->set_grupoCorColuna(1);

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbmenudocumentos');

    # Nome do campo id
    $objeto->set_idCampo('idMenuDocumentos');

    # Pega os dados da datalist categorias
    $valoresCategorias = $pessoal->select('SELECT distinct categoria
                                      FROM tbmenudocumentos
                                  ORDER BY categoria');
    array_unshift($valoresCategorias, array(null));

    # Campos para o formulario
    $objeto->set_campos(array(
        array('nome' => 'categoria',
            'label' => 'Categoria:',
            'tipo' => 'texto',
            'datalist' => $valoresCategorias,
            'required' => true,
            'autofocus' => true,
            'size' => 200,
            'col' => 8,
            'title' => 'A Categoria do Documento.',
            'linha' => 1),
        array('linha' => 1,
            'col' => 2,
            'nome' => 'tipo',
            'label' => 'Tipo:',
            'tipo' => 'combo',
            'required' => true,
            'array' => [[null, null], [1, "Documento"], [2, "Link"]],
            'size' => 15),
        array('nome' => 'texto',
            'label' => 'Texto do link',
            'tipo' => 'texto',
            'required' => true,
            'size' => 200,
            'col' => 8,
            'title' => 'O nome do documento, o texto que aparecerá no menu.',
            'linha' => 2),
        array('nome' => "title",
            'label' => 'Descrição:',
            'tipo' => 'texto',
            'size' => 200,
            'col' => 8,
            'title' => 'O texto que aparecerá no mouseover.',
            'linha' => 3),
        array('nome' => "link",
            'label' => 'Link:',
            'tipo' => 'texto',
            'size' => 200,
            'col' => 8,
            'title' => 'O Caminho quando o tipo foor link.',
            'linha' => 3),
        ));

    # Log
    $objeto->set_idUsuario($idUsuario);

    # Dados da rotina de Upload
    $pasta = PASTA_DOCUMENTOS;
    $nome = "Documento";
    $tabela = "tbmenudocumentos";
    $extensoes = ["pdf"];

    # Botão de Upload
    if (!empty($id)) {

        # Botão de Upload
        $botao = new Button("Upload {$nome}");
        $botao->set_url("cadastroMenuDocumentosUpload.php?fase=upload&id={$id}");
        $botao->set_title("Faz o Upload do {$nome}");
        $botao->set_target("_blank");

        $objeto->set_botaoEditarExtra([$botao]);
    }

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
        case "editar" :
        case "gravar" :
            $objeto->$fase($id);
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

    ################################################################
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}