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
        $atividade = "Visualizou a área de fotografia";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros
    $parametroNome = post('parametroNome', retiraAspas(get_session('parametroNome')));
    $parametroFoto = post('parametroFoto', retiraAspas(get_session('parametroFoto', "Todos")));
    $parametroOrdenacao = post('parametroOrdenacao', retiraAspas(get_session('parametroOrdenacao', "tbpessoa.nome asc")));
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', $pessoal->get_idLotacao($intra->get_idServidor($idUsuario))));

    # Joga os parâmetros par as sessions    
    set_session('parametroNome', $parametroNome);
    set_session('parametroFoto', $parametroFoto);
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroOrdenacao', $parametroOrdenacao);

    # Começa uma nova página
    $page = new Page();
    if ($fase == "uploadFoto") {
        $page->set_ready('$(document).ready(function(){
                                $("form input").change(function(){
                                    $("form p").text(this.files.length + " arquivo(s) selecionado");
                                });
                            });');
    }
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "relatorio") {
        AreaServidor::cabecalho();
    }

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
            br();

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "grh.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dessa pesquisa");
            $botaoRel->set_url("../grhRelatorios/acumulacao.geral.php");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            #$menu1->add_link($botaoRel,"right");

            $menu1->show();

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
            $controle->set_col(3);
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
            $controle->set_col(4);
            $form->add_item($controle);

            $controle = new Input('parametroFoto', 'combo', 'Fotos:', 1);
            $controle->set_size(30);
            $controle->set_title('Fotos');
            $controle->set_array(["Todos", "Com Foto", "Sem Foto"]);
            $controle->set_valor($parametroFoto);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $form->add_item($controle);

            $ordenacaoCombo = array(
                array("tbpessoa.nome asc", "por Nome asc"),
                array("tbpessoa.nome desc", "por Nome desc"),
                array("UADM asc, DIR asc, GER asc", "por Lotação asc"),
                array("UADM desc, DIR desc, GER desc", "por Lotação desc"),
                array("3 asc", "por Admissão asc"),
                array("3 desc", "por Admissão desc")
            );

            $controle = new Input('parametroOrdenacao', 'combo', 'Ordenado:', 1);
            $controle->set_size(30);
            $controle->set_title('Ordenação');
            $controle->set_array($ordenacaoCombo);
            $controle->set_valor($parametroOrdenacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            $form->add_item($controle);

            $form->show();

            ###
            # Pega o time inicial
            $time_start = microtime(true);

            # Pega os dados
            $select = "SELECT idServidor,
                              idServidor,
                              dtAdmissao,
                              idPessoa
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND situacao = 1";

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

            $select .= " ORDER BY {$parametroOrdenacao}";

            $resumo = $pessoal->select($select);

            if ($pessoal->count($select) > 0) {
                $resumo2[] = null;

                # Monta a tabela
                $tabela = new Tabela();
                $tabela->set_titulo("Área de Fotografias dos Servidores");
                $tabela->set_label(["IdFuncional", "Servidor", "Admissão", "Foto"]);
                $tabela->set_align(["center", "left"]);
                $tabela->set_funcao([null, null, "date_to_php", "exibeFoto"]);
                $tabela->set_classe(["Pessoal", "Pessoal"]);
                $tabela->set_metodo(["get_idFuncionalEMatricula", "get_nomeECargoELotacaoEPerfil"]);

                if ($parametroFoto == "Com Foto" AND count($resumo) > 0) {
                    foreach ($resumo as $item) {
                        # Verifica se tem pasta desse servidor
                        if (file_exists(PASTA_FOTOS . $item["idPessoa"] . ".jpg")) {
                            $resumo2[] = $item;
                        }
                    }

                    $tabela->set_conteudo(array_filter($resumo2));
                }

                if ($parametroFoto == "Sem Foto" AND count($resumo) > 0) {
                    foreach ($resumo as $item) {

                        # Verifica se tem pasta desse servidor
                        if (!file_exists(PASTA_FOTOS . $item["idPessoa"] . ".jpg")) {
                            $resumo2[] = $item;
                        }
                    }

                    $tabela->set_conteudo(array_filter($resumo2));
                }

                if ($parametroFoto == "Todos" AND count($resumo) > 0) {
                    $tabela->set_conteudo($resumo);
                }

                # Exibe a tabela
                $tabela->show();

                # Pega o time final
                $time_end = microtime(true);
                $time = $time_end - $time_start;
                p(number_format($time, 4, '.', ',') . " segundos", "right", "f10");
            } else {
                tituloTable("Área de Fotografias dos Servidores");
                callout("Nenhum Resgistro encontrado", "secondary");
            }

            break;

        ##################################################################

        case "exibeFoto" :

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "?");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $menu1->add_link($botaoVoltar, "left");

            # Alterar Foto
            if (Verifica::acesso($idUsuario, [1, 2])) {
                $botaoalterar = new Link("Alterar Foto", "?fase=uploadFoto&idPessoa={$idPessoa}");
                $botaoalterar->set_class('button');
                $botaoalterar->set_title('Altera a foto do Servidor');
                $menu1->add_link($botaoalterar, "right");
            }

            $menu1->show();

            # Dados do Servidor
            get_DadosServidor($idServidor);

            $grid = new Grid("center");
            $grid->abreColuna(6);

            br();

            $painel = new Callout("secondary", "center");
            $painel->abre();

            $foto = new ExibeFoto();
            $foto->set_fotoLargura(300);
            $foto->set_fotoAltura(400);
            $foto->show($idPessoa);

            $painel->fecha();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ##################################################################

        case "uploadFoto" :

            # Botão de Voltar
            botaoVoltar("?fase=exibeFoto&idPessoa=$idPessoa");

            # Nome
            $nome = $pessoal->get_nomeidPessoa($idPessoa);

            # Dados do Servidor
            get_DadosServidor($idServidor);
            br();

            $grid = new Grid("center");
            $grid->abreColuna(6);

            # Gera a área de upload
            echo "<form class='upload' method='post' enctype='multipart/form-data'><br>
                        <input type='file' name='foto'>
                        <p>Click aqui ou arraste o arquivo.</p>
                        <button type='submit' name='submit'>Enviar</button>
                    </form>";

            $pasta = PASTA_FOTOS;

            # Se não existe o programa cria
            if (!file_exists($pasta) || !is_dir($pasta)) {
                mkdir($pasta, 0755);
            }

            # Extensões possíveis
            $extensoes = array("jpg");

            # Pega os valores do php.ini
            $postMax = limpa_numero(ini_get('post_max_size'));
            $uploadMax = limpa_numero(ini_get('upload_max_filesize'));
            $limite = menorValor(array($postMax, $uploadMax));

            $texto = "Extensões Permitidas:";
            foreach ($extensoes as $pp) {
                $texto .= " $pp";
            }

            #$texto .= "<br/>Tamanho Máximo do Arquivo: $limite M";
            #br(2);
            p($texto, "f14", "center");

            if ((isset($_POST["submit"])) && (!empty($_FILES['foto']))) {
                $upload = new UploadImage($_FILES['foto'], 1000, 800, $pasta, $idPessoa, $extensoes);

                # Salva e verifica se houve erro
                if ($upload->salvar()) {

                    # Registra log
                    $Objetolog = new Intra();
                    $data = date("Y-m-d H:i:s");
                    $atividade = "Alterou a foto do servidor $nome";
                    $Objetolog->registraLog($idUsuario, $data, $atividade, null, null, 8, $idPessoa);

                    # Volta para o menu
                    loadPage("?fase=exibeFoto&idPessoa=$idPessoa");
                } else {
                    loadPage("?fase=uploadFoto&idPessoa=$idPessoa");
                }
            }

            #br(4);                
            #callout("Somente é permitido uma foto para cada servidor<br/>E a foto deverá ser no formato jpg ou img.");
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ##################################################################
    }
    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


    