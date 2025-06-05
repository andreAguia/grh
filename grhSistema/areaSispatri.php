<?php

/**
 * Área de Licença Prêmio
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
    $fase = get('fase', "inicio");

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a área do Sispatri";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros    
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', 66));
    $parametroNomeMat = retiraAspas(post('parametroNomeMat', get_session('parametroNomeMat')));
    $parametroSituacao = post('parametroSituacao', get_session('parametroSituacao', 'Entregaram'));
    $parametroAfastamento = post('parametroAfastamento', get_session('parametroAfastamento', 'Todos'));

    $exibeAfastamento = post('exibeAfastamento', get_session('exibeAfastamento', 1));
    $exibeEmail = post('exibeEmail', get_session('exibeEmail', 1));
    
    # Joga os parâmetros par as sessions   
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroNomeMat', empty($parametroNomeMat) ? $parametroNomeMat: rtrim(ltrim($parametroNomeMat)));
    set_session('parametroSituacao', $parametroSituacao);
    set_session('parametroAfastamento', $parametroAfastamento);

    set_session('exibeAfastamento', $exibeAfastamento);
    set_session('exibeEmail', $exibeEmail);

    # Começa uma nova página
    $page = new Page();
    if ($fase == "importar") {
        $page->set_ready('$(document).ready(function(){
                                $("form input").change(function(){
                                    $("form p").text(this.files.length + " arquivo(s) selecionado");
                                });
                            });');
    }

    if ($fase == "ci") {
        $page->set_ready("CKEDITOR.replace('textoCi',{height: 360});");
    }
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    $grid = new Grid();
    $grid->abreColuna(12);

    # Cria um menu
    $menu1 = new MenuBar();

    # Voltar
    if ($fase <> "ci" AND $fase <> "email") {
        if ($fase == "importar" OR $fase == "regras" OR $fase == "config") {
            $botaoVoltar = new Link("Voltar", "?");
        } else {
            $botaoVoltar = new Link("Voltar", "grh.php");
        }
        $botaoVoltar->set_class('button');
        $botaoVoltar->set_title('Voltar a página anterior');
        $botaoVoltar->set_accessKey('V');
        $menu1->add_link($botaoVoltar, "left");

        # Importar
        if ($fase == "resumo") {

            if (Verifica::acesso($idUsuario, [1, 13])) {
                $botaoImp = new Link("Importar", "?fase=regras");
                $botaoImp->set_class('button');
                $botaoImp->set_title('Importa arquivo cvs');
                $botaoImp->set_accessKey('I');
                $menu1->add_link($botaoImp, "right");
            }

            if ($parametroSituacao == "Não Entregaram") {

                if ($parametroLotacao <> "Todos") {
                    # ci
                    $botaoci = new Link("CI", "?fase=ci");
                    $botaoci->set_target("_blank");
                    $botaoci->set_class('button');
                    $botaoci->set_title('CI dos servidores que NÃO entregaram o Sispatri');
                    $menu1->add_link($botaoci, "right");
                }

                # e-mail
                $botaoci = new Link("Enviar e-mails", "?fase=email");
                $botaoci->set_target("_blank");
                $botaoci->set_class('button');
                $botaoci->set_title('Relação de e-mails dos servidores desta listagem');
                $menu1->add_link($botaoci, "right");
            }

            # Relatório
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório");
            $botaoRel->set_url("../grhRelatorios/sispatri.php");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            $menu1->add_link($botaoRel, "right");
        }

        $menu1->show();

        # Titulo
        titulo("Área do Sispatri");
        br();
    }

    if ($fase == "ci") {
        # Titulo
        br();
        titulo("CI dos Servidores que NÃO Entregaram a Declaração do Sispatri");
        br();
    }

    if ($fase == "email") {
        br();
        titulo("E-mails dos Servidores");
        br();
    }

    # Inicia a Classe
    $sispatri = new Sispatri();
    $sispatri->set_lotacao($parametroLotacao);
    $sispatri->set_matNomeId($parametroNomeMat);
    $sispatri->exibeEmail($exibeEmail);
    $sispatri->exibeAfastamento($exibeAfastamento);
    
################################################################

    switch ($fase) {

        case "inicio" :

            br(5);
            aguarde("Montando a tabela");

            loadPage("?fase=resumo");
            break;

        case "resumo" :

            # Área Lateral
            $grid = new Grid();
            $grid->abreColuna(12, 3);

            # Exibe a data da Última importação
            $sispatri->exibeDataUltimaImportacao();

            # Resumo
            $sispatri->exibeResumo();

            if ($parametroSituacao == "Entregaram") {
                $sispatri->exibeResumoPorCargoEntregaram();
            } else {
                $sispatri->exibeResumoPorCargoNaoEntregaram();
            }

            $grid->fechaColuna();

            ##############
            # Coluna de Conteúdo
            $grid->abreColuna(12, 9);

            # Formulário de Pesquisa
            $form = new Form('?');

            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                          FROM tblotacao
                                         WHERE ativo) UNION (SELECT distinct DIR, DIR
                                          FROM tblotacao
                                         WHERE ativo)
                                      ORDER BY 2');
            array_unshift($result, array('Todos', '-- Todos --'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_autofocus(true);
            $controle->set_col(6);

            $form->add_item($controle);
            
            # Nome ou Matrícula
            $controle = new Input('parametroNomeMat', 'texto', 'Nome, Mat ou Id:', 1);
            $controle->set_size(55);
            $controle->set_title('Nome, matrícula ou ID:');
            $controle->set_valor($parametroNomeMat);
            $controle->set_autofocus(true);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            $form->add_item($controle);

            # Situação no sispatri
            $array = ["Entregaram", "Não Entregaram"];

            $controle = new Input('parametroSituacao', 'combo', 'Situação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Situação');
            $controle->set_array($array);
            $controle->set_valor($parametroSituacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            $form->add_item($controle);

            if ($parametroSituacao <> "Entregaram") {

                # Afastamentos
                $array = ["Todos", "Férias", "Licença Prêmio", "Licença Médica"];

                $controle = new Input('parametroAfastamento', 'combo', 'Afastamento:', 1);
                $controle->set_size(30);
                $controle->set_title('Filtra por Afastamento');
                $controle->set_array($array);
                $controle->set_valor($parametroAfastamento);
                $controle->set_onChange('formPadrao.submit();');
                $controle->set_linha(2);
                $controle->set_col(6);
                $form->add_item($controle);

                # E-mail
                $controle = new Input('exibeEmail', 'simnao2', 'E-mail:', 1);
                $controle->set_size(5);
                $controle->set_title('Exibe ou não o e-mail');
                $controle->set_valor($exibeEmail);
                $controle->set_onChange('formPadrao.submit();');
                $controle->set_linha(2);
                $controle->set_col(3);
                $form->add_item($controle);

                # Afastamento
                $controle = new Input('exibeAfastamento', 'simnao2', 'Afastamento:', 1);
                $controle->set_size(5);
                $controle->set_title('Exibe ou não o afastamento');
                $controle->set_valor($exibeAfastamento);
                $controle->set_onChange('formPadrao.submit();');
                $controle->set_linha(2);
                $controle->set_col(3);
                $form->add_item($controle);
            }

            $form->show();

            ##############
            # Exibe os problemas de importação
            if ($sispatri->get_numProblemas() > 0) {
                $sispatri->exibeProblemas();
            }

            if ($parametroSituacao == "Entregaram") {
                
                # Exibe os servidores inativos que entregaram o sispatri
                $sispatri->exibeServidoresEntregaramInativos();

                # Exibe os servidores ativos que entregaram o sispatri
                $sispatri->exibeServidoresEntregaramAtivos();

            } else {
                # Exibe os servidores ativos que Não entregaram o sispatri
                if ($parametroAfastamento == "Todos") {
                    $sispatri->exibeServidoresNaoEntregaramAtivos();
                }

                if ($parametroAfastamento == "Férias") {
                    $sispatri->exibeServidoresNaoEntregaramAtivosFerias();
                }

                if ($parametroAfastamento == "Licença Prêmio") {
                    $sispatri->exibeServidoresNaoEntregaramAtivosLicPremio();
                }

                if ($parametroAfastamento == "Licença Médica") {
                    $sispatri->exibeServidoresNaoEntregaramAtivosLicMedica();
                }
            }

            # Fecha o grid
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "excluir" :
            # Conecta com o banco de dados
            $pessoal->set_tabela("tbsispatri");
            $pessoal->set_idCampo("idSispatri");

            if ($pessoal->excluir($id)) {
                $intra->registraLog($idUsuario,
                        date("Y-m-d H:i:s"),
                        "Apagou registro importado",
                        "tbsispatri",
                        $id,
                        3);
            }
            loadPage("?");
            break;
        ################################################################

        case "editaServidor" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaSispatri.php');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        ################################################################

        case "ci" :

            # Pega o idServidor da Chefia
            $idChefia = $pessoal->get_chefiaImediataIdLotacao($parametroLotacao);

            # Verifica se temos o idChefia
            if (empty($idChefia)) {
                $nomeLotacao = $pessoal->get_nomeLotacao2($parametroLotacao);
                $chefia = null;
            } else {
                $nomeLotacao = $pessoal->get_cargoComissaoDescricao($idChefia);
                $chefia = $pessoal->get_nome($idChefia);

                # Verifica se conseguiu  a descrição do cargo
                if (empty($nomeLotacao)) {
                    $nomeLotacao = $pessoal->get_nomeLotacao2($lotacao);
                }
            }

            # Formuário da CI
            $form = new Form("../grhRelatorios/sispatri.ci.php");

            # usuário
            $controle = new Input('ci', 'numero', 'N° CI:', 1);
            $controle->set_size(5);
            $controle->set_linha(1);
            $controle->set_col(3);
            $controle->set_required(true);
            $controle->set_autofocus(true);
            $controle->set_tabIndex(1);
            $controle->set_title('O número da CI');
            $form->add_item($controle);

            # chefia
            $controle = new Input('chefia', 'texto', 'Chefia Imediata:', 1);
            $controle->set_size(200);
            $controle->set_linha(1);
            $controle->set_col(9);
            $controle->set_tabIndex(2);
            $controle->set_title('O Destinatário da CI');
            $controle->set_valor($chefia);
            $form->add_item($controle);

            # texto
            $controle = new Input('textoCi', 'editor', 'Texto da CI:', 1);
            $controle->set_linha(2);
            $controle->set_size([90, 10]);
            $controle->set_title('Texto da CI');
            $controle->set_valor($sispatri->get_textoCi());
            $form->add_item($controle);

            # submit
            $controle = new Input('submit', 'submit');
            $controle->set_valor('Vizualizar');
            $controle->set_linha(3);
            $controle->set_tabIndex(3);
            $controle->set_accessKey('E');
            $form->add_item($controle);

            $form->show();
            break;

        ################################################################

        case "email" :
            # Exibe a lista de email para ser compiada e colada
            # quando se deseja enviar e-mails para todos os
            # servidores da listagem
            
            if ($parametroAfastamento == "Todos") {
                $sispatri->exibeEmails();
            }

            if ($parametroAfastamento == "Férias") {
                $sispatri->exibeEmailsFerias();
            }

            if ($parametroAfastamento == "Licença Prêmio") {
                $sispatri->exibeEmailsLicPremio();
            }

            if ($parametroAfastamento == "Licença Médica") {
                $sispatri->exibeEmailsLicMedica();
            }
            break;

        ################################################################
        # Importar
        case "regras" :

            $grid = new Grid("center");
            $grid->abreColuna(6);
            br(2);

            $painel = new Callout("warning");
            $painel->abre();

            p("Regras para a importação dos dados do SISPATRI", "center");
            br();
            p("- A importação é referente aos servidores que ENTREGARAM a Declaração do Sispatri;");
            p("- O arquivo deverá estar no formato de planilha csv;");
            p("- Deve ser utilizado o ponto e vírgula para separar as colunas;");
            p("- Na planilha o servidor é identificado pelo CPF;");
            p("- Linhas que não tiverem o número de CPF serão ignoradas;");
            p("- Toda nova importação apagará os dados importados anteriormente sobrescrevendo com os novos dados;");
            $painel->fecha();
            br();

            # Cria um menu
            $menu1 = new MenuBar();

            # Importar
            $botaoImp = new Link("Continuar", "?fase=importar");
            $botaoImp->set_class('button');
            $botaoImp->set_title('Importa arquivo cvs');
            $menu1->add_link($botaoImp, "right");
            $menu1->show();

            $grid->fechaGrid();
            break;

        case "importar" :

            $grid = new Grid("center");
            $grid->abreColuna(6);

            # Gera a área de upload
            echo "<form class='upload' method='post' enctype='multipart/form-data'><br>
                        <input type='file' name='doc'>
                        <p>Click aqui ou arraste o arquivo.</p>
                        <button type='submit' name='submit'>Enviar</button>
                    </form>";

            $pasta = "../_temp/";

            # Se não existe o programa cria
            if (!file_exists($pasta) || !is_dir($pasta)) {
                mkdir($pasta, 0755);
            }

            # Extensões possíveis
            $extensoes = array("csv");

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
                $upload = new UploadDoc($_FILES['doc'], $pasta, "sispatri", $extensoes);

                # Salva e verifica se houve erro
                if ($upload->salvar()) {

                    # Registra log
                    $Objetolog = new Intra();
                    $data = date("Y-m-d H:i:s");
                    $atividade = "Importou arquivo csv do sispatri";
                    $Objetolog->registraLog($idUsuario, $data, $atividade, null, null, 8);

                    # Volta para o menu
                    loadPage("?fase=importar1");
                } else {
                    loadPage("?fase=importar");
                }
            }
            $grid->fechaGrid();
            break;

        case "importar1" :

            br(5);
            aguarde("Apagando a Base Antiga");

            loadPage("?fase=importar2");
            break;

        case "importar2" :

            # Apaga a tabela tbsispatri
            $select = 'SELECT idSispatri
                         FROM tbsispatri';

            $row = $pessoal->select($select);

            $pessoal->set_tabela("tbsispatri");
            $pessoal->set_idCampo("idSispatri");

            foreach ($row as $tt) {
                $pessoal->excluir($tt[0]);
            }

            loadPage("?fase=importar3");
            break;

        case "importar3" :

            br(5);
            aguarde("Fazendo o upload do arquivo");

            loadPage("?fase=importar4");
            break;

        case "importar4" :
            # Define o arquivo a ser importado
            $arquivo = "../_temp/sispatri.csv";
            $certos = 0;
            $linhas = 0;

            # Verifica a existência do arquivo
            if (file_exists($arquivo)) {
                $lines = file($arquivo);
                $linhaDados = false;

                # Percorre o arquivo e guarda os dados em um array
                foreach ($lines as $linha) {

                    # Zera as variáveis de gravação
                    $obs = null;
                    $cpf = null;
                    $contador = 1;

                    # incrementa as linhas
                    $linhas++;

                    # Divide as colunas
                    $parte = explode(";", $linha);

                    # Percorre as partes da linha
                    foreach ($parte as $pp) {

                        # Verifica se a linha está em branco
                        if (!empty($pp)) {

                            if ($linhaDados) {
                                # Guarda a terceira coluna para o cpf
                                if ($contador == 3) {
                                    $cpf = $pp;
                                }

                                # Guarda as outras coluna para a obs
                                if ($contador == 7) {
                                    $obs .= $pp;
                                } else {
                                    $obs .= "{$pp} | ";
                                }
                            } else {
                                if ($pp == "Nome do Agente") {
                                    $linhaDados = true;
                                    break;
                                } else {
                                    break;
                                }
                            }
                            $contador++;
                        }


                        if (validaCpf($cpf)) {
                            $certos++;
                        } else {
                            $cpf = null;
                        }
                    }

                    if (!empty($cpf)) {
                        # Grava na tabela tbsispatri
                        $campos = array("cpf", "obs");
                        $valor = array(utf8_encode($cpf), utf8_encode($obs));
                        $pessoal->gravar($campos, $valor, null, "tbsispatri", "idSispatri");
                    }
                }
            }
            loadPage("?fase=importar5");
            break;

        case "importar5" :

            br(5);
            aguarde("Vinculando os dados importados<br/>com a base de dados existente.");

            loadPage("?fase=importar6");
            break;

        case "importar6" :

            $problema = 0;

            br();
            $select = 'SELECT idSispatri, cpf FROM tbsispatri';
            $row = $pessoal->select($select);
            $contador = 0;

            foreach ($row as $tt) {

                $cpfFinalizado = $tt[1];

                $select2 = "SELECT idPessoa
                              FROM tbdocumentacao
                             WHERE CPF = '$cpfFinalizado'";

                $row2 = $pessoal->select($select2, false);

                if (empty($row2[0])) {
                    $problema++;
                } else {
                    $idServidorPesquisado = $pessoal->get_idServidoridPessoa($row2[0]);

                    # Grava na tabela tbsispatri
                    $campos = array("idServidor");
                    $valor = array($idServidorPesquisado);
                    $pessoal->gravar($campos, $valor, $tt[0], "tbsispatri", "idSispatri");
                }
            }

            # Atualiza nas variaveis de sistema a data da importação
            $intra->set_variavel('dataUltimaImportacao', date("d/m/Y H:i:s"));
            $intra->set_variavel('usuarioUltimaImportacao', $intra->get_nickUsuario($idUsuario));

            if ($problema > 0) {
                alert("A importação foi concluída com {$problema} problema(s)");
                loadPage("?");
            } else {
                alert("A importação foi concluída SEM problemas");
                loadPage("?");
            }
            break;

        ################################################################
    }

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


