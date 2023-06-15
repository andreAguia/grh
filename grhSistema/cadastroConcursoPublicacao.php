<?php

/**
 * Cadastro de Banco
 *  
 * By Alat
 */
# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();

    # Verifica a fase do programa
    $fase = get('fase', 'listar');
    set_session('origem', basename(__FILE__) . "?fase={$fase}");
    $idConcurso = get_session('idConcurso');

    # Pega o tipo do concurso
    $concurso = new Concurso($idConcurso);
    $tipo = $concurso->get_tipo($idConcurso);

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
    } else {

        # Grava no log a atividade
        $atividade = "Visualizou as publicações do concurso " . $concurso->get_nomeConcurso($idConcurso);
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Nome do Modelo
    $objeto->set_nome('Publicações');

    # Botão de voltar da lista
    if ($tipo == 1) {
        $objeto->set_voltarLista('areaConcursoAdm.php');
    } else {
        $objeto->set_voltarLista('areaConcursoProf.php');
    }

    # select da lista
    $objeto->set_selectLista('SELECT idConcursoPublicacao,
                                     data,
                                     pag,
                                     idConcursoPublicacao,
                                     idConcursoPublicacao,
                                     idConcursoPublicacao
                                FROM tbconcursopublicacao
                               WHERE idConcurso = ' . $idConcurso . ' 
                            ORDER BY data desc');

    # select do edita
    $objeto->set_selectEdita('SELECT idConcurso,
                                     descricao,
                                     data,
                                     pag,
                                     obs
                                FROM tbconcursopublicacao
                               WHERE idConcursoPublicacao = ' . $id);

    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    $objeto->set_numeroOrdem(true);
    $objeto->set_numeroOrdemTipo('d');

    $objeto->set_classe(array("ConcursoPublicacao", null, null, "ConcursoPublicacao", "ConcursoPublicacao"));
    $objeto->set_metodo(array("exibeDescricao", null, null, "exibeObs", "exibePublicacao"));

    # Parametros da tabela
    $objeto->set_label(["Descrição", "Data", "Pag", "Obs", "Ver"]);
    $objeto->set_width([50, 10, 10, 10, 10]);
    $objeto->set_align(["left"]);
    $objeto->set_funcao([null, "date_to_php"]);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbconcursopublicacao');

    # Nome do campo id
    $objeto->set_idCampo('idConcursoPublicacao');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Pega os dados para combo concurso 
    $concurso = $pessoal->select('SELECT idconcurso,
                                         concat(anoBase," - Edital: ",DATE_FORMAT(dtPublicacaoEdital,"%d/%m/%Y")) as concurso
                                    FROM tbconcurso
                                    WHERE tipo = 2
                                ORDER BY dtPublicacaoEdital desc');

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'nome' => 'idConcurso',
            'label' => 'Concurso:',
            'tipo' => 'hidden',
            'array' => $concurso,
            'padrao' => $idConcurso,
            'col' => 3,
            'required' => true,
            'size' => 30),
        array('linha' => 1,
            'nome' => 'descricao',
            'label' => 'Descrição:',
            'autofocus' => true,
            'tipo' => 'texto',
            'required' => true,
            'col' => 12,
            'size' => 250),
        array('linha' => 2,
            'nome' => 'data',
            'label' => 'Data:',
            'tipo' => 'data',
            'title' => 'Data da Publicação',
            'required' => true,
            'col' => 3,
            'size' => 20),
        array('linha' => 2,
            'nome' => 'pag',
            'label' => 'Página:',
            'tipo' => 'texto',
            'col' => 2,
            'size' => 10),
        array('linha' => 3,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(80, 5))));

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);

    # Dados da rotina de Upload
    $pasta = PASTA_CONCURSO;
    $nome = "Publicacao";
    $tabela = "tbconcursopublicacao";
    $extensoes = ["pdf"];

    # Botão de Upload
    if (!empty($id)) {

        # Botão de Upload
        $botao = new Button("Upload {$nome}");
        $botao->set_url("cadastroConcursoPublicacaoUpload.php?fase=upload&id={$id}");
        $botao->set_title("Faz o Upload do {$nome}");
        $botao->set_target("_blank");

        $objeto->set_botaoEditarExtra([$botao]);
    }

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :

            # Cria uma rotina extra

            function rotinaLateral($idConcurso) {
                $grid = new Grid();
                $grid->abreColuna(3);

                # Exibe os dados do Concurso
                $concurso = new Concurso($idConcurso);
                $concurso->exibeDadosConcurso($idConcurso, true);

                # menu
                $concurso->exibeMenu($idConcurso, "Publicações");

                $grid->fechaColuna();
                $grid->abreColuna(9);
            }

            $objeto->set_rotinaExtraListar("rotinaLateral");
            $objeto->set_rotinaExtraListarParametro($idConcurso);

            $objeto->listar();
            break;

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