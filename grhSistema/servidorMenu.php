<?php

/**
 * Menu de Servidores
 *  
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;
$idServidorPesquisado = null;

# Configuração
include ("_config.php");

# Zera session usadas
set_session('sessionParametro');
set_session('sessionPaginacao');
set_session('origem2');

# Verifica a origem 
$origem = get_session("origem");
$origemId = get_session("origemId");

# Verifica se veio menu grh e registra o acesso no log
$grh = get('grh', false);

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();

    # Verifica a fase do programa
    $fase = get('fase', 'menu');

    # Pega os parâmetros
    $parametroAno = post('parametroAno', get_session('parametroAno', date("Y")));

    # Joga os parâmetros par as sessions    
    set_session('parametroAno', $parametroAno);

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

    if (($fase <> "despacho") AND ($fase <> "despachoChefia")) {
        # Cabeçalho da Página
        AreaServidor::cabecalho();
    }

    # Limita o tamanho da tela
    $grid = new Grid();
    $grid->abreColuna(12);

    if ($fase == "menu") {

        # Registra no log  
        if ($grh) {
            $atividade = "Cadastro do servidor - Menu";
            $data = date("Y-m-d H:i:s");
            $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
        }

        # Cria um menu
        $menu = new MenuBar();

        # Verifica a origem
        if (empty($origem)) {
            $caminhoVolta = 'servidor.php';
        } else {
            $caminhoVolta = $origem;
        }

        $linkBotao1 = new Link("Voltar", $caminhoVolta);
        $linkBotao1->set_class('button');
        $linkBotao1->set_title('Volta para a página anterior');
        $linkBotao1->set_accessKey('V');
        $menu->add_link($linkBotao1, "left");
        
        # Atendimentos
        $linkAtend = new Link("Registro de Atendimentos", "servidorAtendimento.php");
        $linkAtend->set_class('button');
        $linkAtend->set_title('Acessa o registro de Atendimentos');
        #$linkProc->set_target("_blank4");
        $menu->add_link($linkAtend, "right");

        # Procedimentos
        $linkProc = new Link("Procedimentos", "../../areaServidor/sistema/procedimentos.php");
        $linkProc->set_class('button');
        $linkProc->set_title('Acessa a área de procedimentos');
        $linkProc->set_target("_blank4");
        $menu->add_link($linkProc, "right");

        if (Verifica::acesso($idUsuario, 1)) {

            # Histórico
            $linkBotao4 = new Link("Histórico", "../../areaServidor/sistema/historico.php?idServidor=" . $idServidorPesquisado);
            $linkBotao4->set_class('button success');
            $linkBotao4->set_title('Exibe as alterações feita no cadastro desse servidor');
            $linkBotao4->set_accessKey('H');
            $menu->add_link($linkBotao4, "right");

            # Excluir
            $linkBotao5 = new Link("Excluir", "servidorExclusao.php");
            $linkBotao5->set_class('alert button');
            $linkBotao5->set_title('Excluir Servidor');
            $linkBotao5->set_accessKey('x');
            $menu->add_link($linkBotao5, "right");
        }

        $menu->show();
    } elseif ($fase == "pasta") {

        # Cria um menu
        $menu = new MenuBar();

        $linkBotao1 = new Link("Voltar", "?");
        $linkBotao1->set_class('button');
        $linkBotao1->set_title('Volta para a página anterior');
        $linkBotao1->set_accessKey('V');
        $menu->add_link($linkBotao1, "left");

        $menu->show();
    } else {
        if ($fase <> "despacho" AND $fase <> "despachoChefia" AND $fase <> "acumulacao" AND $fase <> "oficioAberturaConta") {
            botaoVoltar("?");
        }
    }

    if ($fase <> "despacho" AND $fase <> "despachoChefia" AND $fase <> "acumulacao" AND $fase <> "oficioAberturaConta") {

        # Exibe os dados do Servidor
        Grh::listaDadosServidor($idServidorPesquisado);
    }

    switch ($fase) {
        ##################################################################
        # Exibe o Menu Inicial
        case "menu" :
            # monta o menu do servidor
            $menu = new MenuServidor($idServidorPesquisado, $idUsuario);

            # Exibe o rodapé da página
            Grh::rodape($idUsuario, $idServidorPesquisado, $pessoal->get_idPessoa($idServidorPesquisado));
            break;

        ##################################################################

        case "exibeFoto" :
            $grid = new Grid("center");
            $grid->abreColuna(6);

            $fotoLargura = 300;
            $fotoAltura = 400;

            # Define a pasta
            $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);

            # Verifica qual arquivo foi gravado
            $arquivo = PASTA_FOTOS . "$idPessoa.jpg";

            $painel = new Callout("secondary", "center");
            $painel->abre();

            # Monta o Menu
            $menu = new MenuGrafico(1);

            # Verifica se tem pasta desse servidor
            if (file_exists($arquivo)) {
                $botao = new BotaoGrafico("foto");
                $botao->set_url('?');
                $botao->set_imagem($arquivo, $fotoLargura, $fotoAltura);
                $botao->set_title('Foto do Servidor');
                $menu->add_item($botao);
            } else {
                $botao = new BotaoGrafico("foto");
                $botao->set_url('?');
                $botao->set_imagem(PASTA_FIGURAS . 'foto.png', $fotoLargura, $fotoAltura);
                $botao->set_title('Servidor sem foto cadastrada');
                $menu->add_item($botao);
            }

            $menu->show();

            if (Verifica::acesso($idUsuario, [1, 2])) {
                br(2);
                $link = new Link("Alterar Foto", "?fase=uploadFoto");
                $link->set_id("alteraFoto");
                $link->show();
            }

            $painel->fecha();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ##################################################################

        case "uploadFoto" :

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

            br();
            p($texto, "f14", "center");

            $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);

            if ((isset($_POST["submit"])) && (!empty($_FILES['foto']))) {
                $upload = new UploadImage($_FILES['foto'], 1000, 800, $pasta, $idPessoa);
                # Salva e verifica se houve erro
                if ($upload->salvar()) {

                    # Registra log
                    $Objetolog = new Intra();
                    $data = date("Y-m-d H:i:s");
                    $atividade = "Alterou a foto do servidor " . $pessoal->get_nome($idServidorPesquisado);
                    $Objetolog->registraLog($idUsuario, $data, $atividade, null, null, 8, $idServidorPesquisado);

                    # Volta para o menu
                    loadPage("?");
                } else {
                    loadPage("?fase=uploadFoto");
                }
            }

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ##################################################################

        case "despachoChefia" :
            # Limita a tela
            $grid = new Grid("center");
            $grid->abreColuna(10);
            br(3);

            # Pega os valores
            $idServidorChefia = $pessoal->get_chefiaImediata($idServidorPesquisado);
            $nomeChefia = $pessoal->get_nome($idServidorChefia);
            $chefiaImediataDescricao = $pessoal->get_chefiaImediataDescricao($idServidorPesquisado);

            # Título
            titulo("Despacho para Chefia - Resultado Setor - Ato Servidor");
            $painel = new Callout();
            $painel->abre();

            # Monta o formulário
            $form = new Form('../grhRelatorios/despacho.AtoReitor.php');

            # Chefia
            $controle = new Input('chefia', 'texto', 'Chefia:', 1);
            $controle->set_size(200);
            $controle->set_linha(1);
            $controle->set_col(12);
            $controle->set_valor($nomeChefia);
            $controle->set_autofocus(true);
            $controle->set_title('A chefia imediata.');
            $form->add_item($controle);

            # Cargo da Chefia
            $controle = new Input('cargo', 'texto', 'Cargo da Chefia:', 1);
            $controle->set_size(200);
            $controle->set_linha(1);
            $controle->set_col(12);
            $controle->set_valor($chefiaImediataDescricao);
            $controle->set_title('O cargo da chefia imediata.');
            $form->add_item($controle);

            # Cargo da Chefia
            $controle = new Input('ato', 'texto', 'Ato de:', 1);
            $controle->set_size(200);
            $controle->set_linha(1);
            $controle->set_col(12);
            $controle->set_title('O Ato ao qual o despacho se refere.');
            $form->add_item($controle);

            # submit
            $controle = new Input('imprimir', 'submit');
            $controle->set_valor('Imprimir');
            $controle->set_linha(5);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ##################################################################

        case "acumulacao" :

            # Limita a tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Botão de Voltar
            $origem = get_session("origem");
            if (is_null($origem)) {
                botaoVoltar("servidorMenu.php");
            } else {
                botaoVoltar($origem);
            }

            # Exibe os dados do Servidor
            Grh::listaDadosServidor($idServidorPesquisado);
            br();

            # Limita a tela
            $grid = new Grid("center");
            $grid->abreColuna(8);

            $tamanhoImage = 60;
            $menu = new MenuGrafico(2);
            $menu->set_espacoEntreLink(true);

            $botao = new BotaoGrafico();
            $botao->set_label('Controle do Processo de Acumulação de Cargos Públicos');
            $botao->set_url('servidorAcumulacao.php?grh=1');
            $botao->set_imagem(PASTA_FIGURAS . 'acumulacao.jpg', $tamanhoImage, $tamanhoImage);
            $botao->set_title('Controle de Acumulação de Cargo Público');
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            #$botao->set_novo(true);
            $botao->set_label('Controle da Entrega da Declaração Anual');
            $botao->set_url('servidorAcumulacaoDeclaracao.php?grh=1');
            $botao->set_imagem(PASTA_FIGURAS . 'declaracao.png', $tamanhoImage, $tamanhoImage);
            $botao->set_title('Controle da entrega da declaração anual de acumulação de cargos públicos');
            $menu->add_item($botao);

            $menu->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
            br(2);

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

###################################################################
        # Ofício de Abertura de conta
        case "oficioAberturaConta" :

            # Limita a tela
            $grid = new Grid();
            $grid->abreColuna(12);
            br();

            tituloTable("Ofício de Abertura de Conta");
            br();

            # Monta o formulário
            $form = new Form("../grhRelatorios/admissao.OficioAberturaConta.php");

            # Número do Ofício
            $controle = new Input('numero', 'texto', 'Número do Oficio:', 1);
            $controle->set_size(200);
            $controle->set_linha(1);
            $controle->set_col(4);
            $controle->set_required(true);
            $controle->set_autofocus(true);
            $controle->set_title('O número do ofício.');
            $form->add_item($controle);

            # Ano
            $controle = new Input('ano', 'texto', 'Ano:', 1);
            $controle->set_size(200);
            $controle->set_linha(1);
            $controle->set_col(4);
            $controle->set_valor(date("Y"));
            $controle->set_required(true);
            $controle->set_title('O ano.');
            $form->add_item($controle);

            # submit
            $controle = new Input('salvar', 'submit');
            $controle->set_valor('Imprimir');
            $controle->set_linha(5);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ###################################################################
    }

    $grid->fechaColuna();
    $grid->fechaGrid();
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
