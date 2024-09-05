<?php

/**
 * Área de Fotografia
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
    $fase = get('fase');

    # Pega o id
    $idPessoa = get('idPessoa');
    $idServidor = $pessoal->get_idServidoridPessoa($idPessoa);

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a área de ato de investidura";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros
    $parametroNome = post('parametroNome', retiraAspas(get_session('parametroNome')));
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao'));

    # Joga os parâmetros par as sessions    
    set_session('parametroNome', $parametroNome);
    set_session('parametroLotacao', $parametroLotacao);

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

    # Dados da rotina de Upload
    $pasta = PASTA_ATOINVESTIDURA;
    $nome = "Ato de Investidura de {$pessoal->get_nome($id)}";
    $extensoes = ["pdf"];

    $grid = new Grid();
    $grid->abreColuna(12);

################################################################

    switch ($fase) {

        case "" :
        case "lista" :

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=exibeLista');
            break;

################################################################

        case "exibeLista" :

            ###
            # Formulário de Pesquisa
            $form = new Form('?');

            # Nome    
            $controle = new Input('parametroNome', 'texto', 'Nome:', 1);
            $controle->set_size(30);
            $controle->set_title('Pesquisa');
            $controle->set_valor($parametroNome);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $controle->set_autofocus(true);
            $form->add_item($controle);

            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                                      FROM tblotacao
                                                     WHERE ativo) UNION (SELECT distinct DIR, DIR
                                                      FROM tblotacao
                                                     WHERE ativo)
                                                  ORDER BY 2');
            array_unshift($result, array('*', '-- Todos --'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(8);
            $form->add_item($controle);

            $form->show();

            ###
            # Pega o time inicial
            $time_start = microtime(true);

            # Pega os dados
            $select = "SELECT idFuncional,
                              tbpessoa.nome,
                              concat(IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,'')) lotacao,
                              idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND situacao = 1
                          AND idPerfil = 1";

            # Nome
            if (!is_null($parametroNome)) {

                # Verifica se tem espaços
                if (strpos($parametroNome, ' ') !== false) {
                    # Separa as palavras
                    $palavras = explode(' ', $parametroNome);

                    # Percorre as palavras
                    foreach ($palavras as $item) {
                        $select .= " AND (tbpessoa.nome LIKE '%{$item}%')";
                    }
                } else {
                    $select .= " AND (tbpessoa.nome LIKE '%{$parametroNome}%')";
                }
            }

            # Lotação
            if (($parametroLotacao <> "*") AND ($parametroLotacao <> "")) {
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = '{$parametroLotacao}')";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            $select .= "  ORDER BY tbpessoa.nome";

            $resumo = $pessoal->select($select);

            if (count($resumo) > 0) {

                # Monta a tabela
                $tabela = new Tabela();
                $tabela->set_conteudo($resumo);
                $tabela->set_titulo("Atos de Investidura");
                $tabela->set_label(["IdFuncional", "Servidor", "Lotação", "Ato"]);
                $tabela->set_align(["center", "left", "left"]);
                $tabela->set_classe([null, null, null, "AtoInvestidura"]);
                $tabela->set_metodo([null, null, null, "exibeAto"]);
                $tabela->show();
            }

            # Pega o time final
            $time_end = microtime(true);
            $time = $time_end - $time_start;
            p(number_format($time, 4, '.', ',') . " segundos", "right", "f10");
            break;

        ########################################        

        case "exibeAto" :
            $grid = new Grid("center");
            $grid->abreColuna(12);

            if (file_exists("{$pasta}{$id}.pdf")) {



                # Cria um menu
                $menu = new MenuBar();

                # Voltar
                $botaoVoltar = new Link("Voltar", "?");
                $botaoVoltar->set_class('button');
                $botaoVoltar->set_title('Voltar a página anterior');
                $botaoVoltar->set_accessKey('V');
                $menu->add_link($botaoVoltar, "left");

                # Excluir
                if (Verifica::acesso($idUsuario, [1, 2])) {
                    $botaoApaga = new Button("Excluir o Arquivo");
                    $botaoApaga->set_url("?fase=apagaDocumento&id={$id}");
                    $botaoApaga->set_title("Exclui o Arquivo PDF cadastrado");
                    $botaoApaga->set_class("button alert");
                    $botaoApaga->set_confirma("Tem certeza que você deseja excluir o arquivo do {$nome}?");
                    $menu->add_link($botaoApaga, "right");
                }

                $menu->show();

                tituloTable($nome);
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
            br();

            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "?");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu->add_link($botaoVoltar, "left");
            $menu->show();

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

            # Registra nas variáveis
            $intra->set_variavel('dataUploadArquivos', date("d/m/Y H:i:s"));

            # Fecha a janela            
            #echo '<script type="text/javascript" language="javascript">window.close();</script>';
            loadPage("areaAtoInvestidura.php");
            break;

        ########################################    

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
                $Objetolog->registraLog($idUsuario, $data, $atividade, null, null, 3, $id);

                # Fecha a janela
                #echo '<script type="text/javascript" language="javascript">window.close();</script>';
                loadPage("?");
            } else {
                alert("Houve algum problema, O arquivo não pode ser excluído !!");

                # Fecha a janela
                #echo '<script type="text/javascript" language="javascript">window.close();</script>';
                loadPage("?");
            }
            break;

        ########################################
    }
    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


