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

    # select da lista
    $objeto->set_selectLista("SELECT idMenuDocumentos,
                                     categoria,
                                     texto,
                                     title,
                                     idMenuDocumentos,
                                     idMenuDocumentos
                                FROM tbmenudocumentos
                       ORDER BY categoria, texto");

    # select do edita
    $objeto->set_selectEdita("SELECT categoria,
                                     texto,
                                     title
                                FROM tbmenudocumentos
                               WHERE idMenuDocumentos = {$id}");

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(["Id", "Categoria", "Texto", "Title", "Ver", "Upload"]);
    $objeto->set_width([5, 20, 30, 30, 5, 5]);
    $objeto->set_align(["center", "center", "left", "left"]);

    $objeto->set_classe([null, null, null, null, "MenuDocumentos", "MenuDocumentos"]);
    $objeto->set_metodo([null, null, null, null, "exibeDocumento", "exibeBotaoUpload"]);

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
            'col' => 6,
            'title' => 'A Categoria do Documento.',
            'linha' => 1),
        array('nome' => 'texto',
            'label' => 'Texto do link',
            'tipo' => 'texto',
            'required' => true,
            'size' => 200,
            'col' => 6,
            'title' => 'O nome do documento, o texto que aparecerá no menu.',
            'linha' => 2),
        array('nome' => "title",
            'label' => 'Descrição',
            'tipo' => 'texto',
            'size' => 200,
            'col' => 6,
            'title' => 'O texto que aparecerá no mouseover.',
            'linha' => 3)));

    # Log
    $objeto->set_idUsuario($idUsuario);

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
        case "editar" :
        case "excluir" :
        case "gravar" :
            $objeto->$fase($id);
            break;
        ################################################################

        case "upload" :
            $grid = new Grid("center");
            $grid->abreColuna(12);

            # Botão voltar
            botaoVoltar('?');

            # Título
            tituloTable("Upload de Documentos");

            # Limita a tela
            $grid->fechaColuna();
            $grid->abreColuna(6);

            # Monta o formulário
            echo "<form class='upload' method='post' enctype='multipart/form-data'><br>
                        <input type='file' name='doc'>
                        <p>Click aqui ou arraste o arquivo.</p>
                        <button type='submit' name='submit'>Enviar</button>
                    </form>";

            # Pasta onde será guardado o arquivo
            $pasta = PASTA_DOCUMENTOS;

            # Se não existe o programa cria
            if (!file_exists($pasta) || !is_dir($pasta)) {
                mkdir($pasta, 0755);
            }

            # Extensões possíveis
            $extensoes = ["pdf", "doc", "docx"];

            # Pega os valores do php.ini
            $postMax = limpa_numero(ini_get('post_max_size'));
            $uploadMax = limpa_numero(ini_get('upload_max_filesize'));
            $limite = menorValor(array($postMax, $uploadMax));

            $texto = "Extensões Permitidas:";
            foreach ($extensoes as $pp) {
                $texto .= " $pp";
            }
            $texto .= "<br/>Tamanho Máximo do Arquivo: $limite M";

            br();
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
                    loadPage("?fase=upload&id=$id");
                }
            }

            # Informa caso exista um arquivo com o mesmo nome
            $arquivoDocumento = $pasta . $id . ".pdf";
            if (file_exists($arquivoDocumento)) {
                p("Já existe um documento para este registro no servidor!!<br/>O novo documento irá sobrescrevê-lo e o antigo será apagado !!", "puploadMensagem");
                br();
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