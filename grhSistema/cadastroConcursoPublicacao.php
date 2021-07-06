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
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();

    # Verifica a fase do programa
    $fase = get('fase', 'listar');
    set_session('origem', basename( __FILE__ )."?fase={$fase}");
    $idConcurso = get_session('idConcurso');

    # Pega o tipo do concurso
    $concurso = new Concurso($idConcurso);
    $tipo = $concurso->get_tipo($idConcurso);

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Começa uma nova página
    $page = new Page();
    if ($fase == "uploadPublicacao") {
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

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    $objeto->set_numeroOrdem(true);
    $objeto->set_numeroOrdemTipo('d');

    $objeto->set_classe(array("ConcursoPublicacao", null, null, "ConcursoPublicacao"));
    $objeto->set_metodo(array("exibeDescricao", null, null, "exibePublicacao"));

    # Parametros da tabela
    $objeto->set_label(["Descrição", "Data", "Pag", "Ver", "Upload"]);
    $objeto->set_width([40, 15, 15, 15, 15]);
    $objeto->set_align(["left"]);
    $objeto->set_funcao([null, "date_to_php"]);

    # Botão de Upload
    $botao = new BotaoGrafico();
    $botao->set_label('');
    $botao->set_url("?fase=uploadPublicacao&id=");
    $botao->set_imagem(PASTA_FIGURAS . 'upload.png', 20, 20);

    # Coloca o objeto link na tabela			
    $objeto->set_link(array(null, null, null, null, $botao));

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
            'autofocus' => true,
            'required' => true,
            'size' => 30),
        array('linha' => 1,
            'nome' => 'descricao',
            'label' => 'Descrição:',
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
        case "excluir" :
        case "gravar" :
            $objeto->$fase($id);
            break;

        ################################################################

        case "uploadPublicacao" :
            $grid = new Grid("center");
            $grid->abreColuna(12);

            # Botão voltar
            botaoVoltar('?');

            tituloTable("Upload de Publicações");

            $grid->fechaColuna();
            $grid->abreColuna(6);

            echo "<form class='upload' method='post' enctype='multipart/form-data'><br>
                        <input type='file' name='doc'>
                        <p>Click aqui ou arraste o arquivo.</p>
                        <button type='submit' name='submit'>Enviar</button>
                    </form>";

            $pasta = PASTA_CONCURSO;

            # Se não existe o programa cria
            if (!file_exists($pasta) || !is_dir($pasta)) {
                mkdir($pasta, 0755);
            }

            # Extensões possíveis
            $extensoes = array("pdf");

            # Pega os valores do php.ini
            $postMax = limpa_numero(ini_get('post_max_size'));
            $uploadMax = limpa_numero(ini_get('upload_max_filesize'));
            $limite = menorValor(array($postMax, $uploadMax));

            $texto = "Extensões Permitidas:";
            foreach ($extensoes as $pp) {
                $texto .= " $pp";
            }
            $texto .= "<br/>Tamanho Máximo do Arquivo: $limite M";

            br(2);
            p($texto, "f14", "center");

            if ((isset($_POST["submit"])) && (!empty($_FILES['doc']))) {
                $upload = new UploadDoc($_FILES['doc'], $pasta, $id, $extensoes);

                # Salva e verifica se houve erro
                if ($upload->salvar()) {

                    # Registra log
                    $Objetolog = new Intra();
                    $data = date("Y-m-d H:i:s");
                    $atividade = "Fez o upload de publicação de concurso";
                    $Objetolog->registraLog($idUsuario, $data, $atividade, null, $id, 8);

                    # Volta para o menu
                    loadPage("?fase=listar");
                } else {
                    loadPage("?fase=uploadPublicacao&id=$id");
                }
            }

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ##################################################################
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}