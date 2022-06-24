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
    $objeto->set_label(["Id", "Categoria", "Texto", "Title", "Ver"]);
    $objeto->set_width([5, 20, 30, 30, 5, 5]);
    $objeto->set_align(["center", "center", "left", "left"]);

    $objeto->set_classe([null, null, null, null, "MenuDocumentos"]);
    $objeto->set_metodo([null, null, null, null, "exibeDocumento"]);

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

   # Dados da rotina de Upload
    $pasta = PASTA_DOCUMENTOS;
    $nome = "Documento";
    $tabela = "tbmenudocumentos";
    $extensoes = ["pdf"];

    # Botão de Upload
    if (!empty($id)) {

        # Botão de Upload
        $botao = new Button("Upload {$nome}");
        $botao->set_url("?fase=upload&id={$id}");
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
            # apaga o Documento relacionado
            if (file_exists("{$pasta}{$id}.pdf")) {
                rename("{$pasta}{$id}.pdf", "{$pasta}apagado_{$id}_" . $intra->get_usuario($idUsuario) . "_" . date("Y.m.d_H:i") . ".pdf");
            }

            # Exclui o registro
            $objeto->excluir($id);
            break;

        ################################################################

        case "upload" :
            # Limita a tela
            $grid = new Grid("center");
            $grid->abreColuna(12);

            # Exibe o Título
            if (!file_exists("{$pasta}{$id}.pdf")) {
                br();

                # Título
                tituloTable("Upload do {$nome}");

                # do Log
                $atividade = "Fez o upload do {$nome}";
            } else {
                # Monta o Menu
                $menu = new MenuBar();

                $botaoApaga = new Button("Excluir o Arquivo");
                $botaoApaga->set_url("?fase=apagaDocumento&id={$id}");
                $botaoApaga->set_title("Exclui o Arquivo PDF cadastrado");
                $botaoApaga->set_class("button alert");
                $botaoApaga->set_confirma("Tem certeza que você deseja excluir o arquivo do {$nome}?");
                $menu->add_link($botaoApaga, "right");
                $menu->show();

                # Título
                tituloTable("Substituir o Arquivo Cadastrado");

                # Define o link de voltar após o salvar
                $voltarsalvar = "?fase=uploadTerminado";

                # do Log
                $atividade = "Substituiu o arquivo do {$nome}";
            }

            #####
            # Limita a tela
            $grid->fechaColuna();
            $grid->abreColuna(6);

            # Monta o formulário
            echo "<form class='upload' method='post' enctype='multipart/form-data'><br>
                        <input type='file' name='doc'>
                        <p>Click aqui ou arraste o arquivo.</p>
                        <button type='submit' name='submit'>Enviar</button>
                    </form>";

            # Se não existe o programa cria
            if (!file_exists($pasta) || !is_dir($pasta)) {
                mkdir($pasta, 0755);
            }

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
                    $Objetolog->registraLog($idUsuario, $data, $atividade, $tabela, $id, 8);

                    # Fecha a janela aberta
                    loadPage("?fase=uploadTerminado");
                } else {
                    # volta a tela de upload
                    loadPage("?fase=upload&id=$id");
                }
            }

            # Informa caso exista um arquivo com o mesmo nome
            if (file_exists("{$pasta}{$id}.pdf")) {
                p("Já existe um documento para este registro!!<br/>O novo documento irá substituir o antigo !", "puploadMensagem");
                br();
            }

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        case "uploadTerminado" :
            # Informa que o bim foi substituído
            alert("Arquivo do {$nome} Cadastrado !!");

            # Fecha a janela
            echo '<script type="text/javascript" language="javascript">window.close();</script>';
            break;

        case "apagaDocumento" :

            # Apaga o arquivo (na verdade renomeia)
            if (rename("{$pasta}{$id}.pdf", "{$pasta}apagado_{$id}_" . $intra->get_usuario($idUsuario) . "_" . date("Y.m.d_H:i") . ".pdf")) {
                alert("Arquivo Excluído !!");

                # Registra log
                $atividade = "Excluiu o arquivo do {$nome}";
                $Objetolog = new Intra();
                $data = date("Y-m-d H:i:s");
                $Objetolog->registraLog($idUsuario, $data, $atividade, $tabela, $id, 3);

                # Fecha a janela
                echo '<script type="text/javascript" language="javascript">window.close();</script>';
            } else {
                alert("Houve algum problema, O arquivo não pode ser excluído !!");

                # Fecha a janela
                echo '<script type="text/javascript" language="javascript">window.close();</script>';
            }
            break;

        ################################################################
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}