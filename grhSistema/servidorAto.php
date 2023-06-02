<?php

/**
 * Ato de Investidura
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
    # Verifica a fase do programa
    $fase = get('fase');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Ato de Investidura do Servidor";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $id);
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

    if ($fase == "upload") {
        # Cabeçalho da Página
        AreaServidor::cabecalho();
    }

    # Dados da rotina de Upload
    $pasta = PASTA_ATOINVESTIDURA;
    $nome = "Ato de Investidura de {$pessoal->get_nome($id)}";
    $extensoes = ["pdf"];

    ################################################################

    switch ($fase) {
        case "" :
            $grid = new Grid("center");
            $grid->abreColuna(12);            

            if (file_exists("{$pasta}{$id}.pdf")) {
                
                tituloTable($nome);
                br();;

                # Cria um menu
                $menu = new MenuBar("small button-group");

                $botaoApaga = new Button("Excluir o Arquivo");
                $botaoApaga->set_url("?fase=apagaDocumento&id={$id}");
                $botaoApaga->set_title("Exclui o Arquivo PDF cadastrado");
                $botaoApaga->set_class("button alert");
                $botaoApaga->set_confirma("Tem certeza que você deseja excluir o arquivo do {$nome}?");
                $menu->add_link($botaoApaga, "right");

                $menu->show();

                
                iframe("{$pasta}{$id}.pdf");
            } else {
                loadPage("?fase=upload&id={$id}");
            }

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ########################################        

        case "upload" :
            # Limita a tela
            $grid = new Grid("center");
            $grid->abreColuna(12);
            br();

            # Exibe o Título
            if (!file_exists("{$pasta}{$id}.pdf")) {

                # Título
                tituloTable("Upload do {$nome}");

                # do Log
                $atividade = "Fez o upload do<br>{$nome}";
            } else {
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
                    $Objetolog->registraLog($idUsuario, $data, $atividade, null, null, 8, $id);

                    # Fecha a janela aberta
                    loadPage("?fase=uploadTerminado");
                } else {
                    # volta a tela de upload
                    loadPage("?fase=upload&id={$id}");
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

        ########################################

        case "uploadTerminado" :
            # Informa que o bim foi substituído
            alert("Arquivo do {$nome} Cadastrado !!");

            # Fecha a janela
            
            echo '<script type="text/javascript" language="javascript">window.close();</script>';
            break;

        ########################################    

        case "apagaDocumento" :

            # Apaga o arquivo (na verdade renomeia)
            if (rename("{$pasta}{$id}.pdf", "{$pasta}apagado_{$id}_" . $intra->get_usuario($idUsuario) . "_" . date("Y.m.d_H:i") . ".pdf")) {
                alert("Arquivo Excluído !!");

                # Registra log
                $atividade = "Excluiu o arquivo do {$nome}";
                $Objetolog = new Intra();
                $data = date("Y-m-d H:i:s");
                $Objetolog->registraLog($idUsuario, $data, $atividade, null, null, 3, $id);

                # Fecha a janela
                echo '<script type="text/javascript" language="javascript">window.close();</script>';
            } else {
                alert("Houve algum problema, O arquivo não pode ser excluído !!");

                # Fecha a janela
                echo '<script type="text/javascript" language="javascript">window.close();</script>';
            }
            break;

        ########################################
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}