<?php

/**
 * Histórico de Formação Escolar do Servidor
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
    $intra = new Intra();

    # Verifica a fase do programa
    $fase = get('fase', 'upload');

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

    # Dados da rotina de UploadF    
    $pasta = PASTA_SUSPENSAO;
    $nome = "Publicação da Suspensão";
    $tabela = "tblicenca";
    $extensoes = ["pdf"];

    ################################################################

    switch ($fase) {
        case "" :
        case "upload" :
            # Limita a tela
            $grid = new Grid("center");
            $grid->abreColuna(12);

            # Título
            tituloTable("Upload da {$nome}");
            br();

            # Verifica se já existe um arquivo
            if (file_exists("{$pasta}{$id}.pdf")) {

                br(5);
                p("Já existe um arquivo cadastrado!<br/>Para substituí-lo é necessário excluir o atual.", "puploadMensagem");
                br(4);

                # Limita a tela
                $grid = new Grid("center");
                $grid->abreColuna(3);

                $botaoApaga = new Button("Excluir o Arquivo");
                $botaoApaga->set_url("?fase=apagaDocumento&id={$id}");
                $botaoApaga->set_title("Exclui o Arquivo PDF cadastrado");
                $botaoApaga->set_class("button alert");
                $botaoApaga->set_confirma("Tem certeza que você deseja excluir o arquivo do {$nome}?");
                $botaoApaga->show();

                $grid->fechaColuna();
                $grid->fechaGrid();

                # Define o link de voltar após o salvar
                $voltarsalvar = "?fase=uploadTerminado";

                # do Log
                $atividade = "Substituiu o arquivo do {$nome}";
            } else {

                # do Log
                $atividade = "Fez o upload do {$nome}";

                #####
                # Limita a tela
                $grid->fechaColuna();
                $grid->abreColuna(6);

                # Monta o formulário
                echo "<form class='upload' method='post' enctype='multipart/form-data'><br/>
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
                        $Objetolog->registraLog($idUsuario, $data, $atividade, $tabela, $id, 8, $idServidorPesquisado);

                        # Fecha a janela aberta
                        loadPage("?fase=uploadTerminado");
                    } else {
                        # volta a tela de upload
                        loadPage("?fase=upload&id=$id");
                    }
                }
            }

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        case "uploadTerminado" :
            # Informa que o aquivo foi substituído
            alert("Arquivo do {$nome} Cadastrado !!");

            # Registra nas variáveis
            $intra->set_variavel('dataUploadArquivos', date("d/m/Y H:i:s"));

            # Fecha a janela
            echo '<script type="text/javascript" language="javascript">window.close();</script>';
            break;

        case "apagaDocumento" :
            # Verifica se existe a pasta dos arquivos deletados
            if (!file_exists("{$pasta}_apagados/") || !is_dir("{$pasta}_apagados/")) {
                mkdir("{$pasta}_apagados/", 0755);
            }

            # Apaga o arquivo (na verdade renomeia)
            if (rename("{$pasta}{$id}.pdf", "{$pasta}_apagados/{$id}_" . $intra->get_usuario($idUsuario) . "_" . date("Y.m.d_H:i") . ".pdf")) {
                alert("Arquivo Excluído !!");

                # Registra nas variáveis
                $intra->set_variavel('dataUploadArquivos', date("d/m/Y H:i:s"));

                # Registra log
                $atividade = "Excluiu o arquivo do {$nome}";
                $Objetolog = new Intra();
                $data = date("Y-m-d H:i:s");
                $Objetolog->registraLog($idUsuario, $data, $atividade, $tabela, $id, 3, $idServidorPesquisado);

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