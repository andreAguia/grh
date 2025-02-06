<?php

/**
 * Área de Aposentadoria
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
    $pessoal = new Pessoal();
    $intra = new Intra();
    $auxTransporte = new AuxilioTransporte();

    # Verifica a fase do programa
    $fase = get('fase');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Calcula a data oadrão
    $anoPadrao = date('Y');
    $mesPadrao = date('m');

    if ($mesPadrao == 1) {
        $mesPadrao = 12;
        $anoPadrao--;
    } else {
        $mesPadrao--;
    }

    # Pega os parâmetros
    $parametroAno = post('parametroAno', get_session('parametroAno', $anoPadrao));
    $parametroMes = post('parametroMes', get_session('parametroMes', $mesPadrao));
    $parametroNome = post('parametroNome', retiraAspas(get_session('parametroNome')));
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', 66));
    $parametroPerfil = post('parametroPerfil', get_session('parametroPerfil', '*'));
    $parametroRecebeu = post('parametroRecebeu', get_session('parametroRecebeu', 'Sim'));

    # Coloca o mês com 2 digitos
    $parametroMes = str_pad($parametroMes, 2, '0', STR_PAD_LEFT);

    # Joga os parâmetros par as sessions
    set_session('parametroAno', $parametroAno);
    set_session('parametroMes', $parametroMes);
    set_session('parametroNome', $parametroNome);
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroPerfil', $parametroPerfil);
    set_session('parametroRecebeu', $parametroRecebeu);

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);

    # Pega o nome da lotação
    if (is_numeric($parametroLotacao)) {
        $lotacao = $pessoal->get_nomeLotacao($parametroLotacao);
    } else { # senão é uma diretoria genérica
        $lotacao = $parametroLotacao;
    }

    # Grava no log a atividade
    $atividade = "Visualizou a área de auxílio transporte<br/>de {$parametroMes}/{$parametroAno} da lotação {$lotacao}";
    $data = date("Y-m-d H:i:s");
    $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);

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

    # Dados da rotina de Upload
    $pasta = PASTA_TRANSPORTE;
    $nome = "Listagem Auxilio Transporte - " . get_nomeMes($parametroMes) . " / {$parametroAno}";
    $extensoes = ["csv"];
    $arquivo = "{$pasta}{$parametroAno}" . str_pad($parametroMes, 2, '0', STR_PAD_LEFT) . ".csv";

    # Limita a página
    $grid = new Grid();
    $grid->abreColuna(12);

    ################################################################

    switch ($fase) {
        case "" :
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

            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "grh.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu->add_link($botaoVoltar, "left");

            if (Verifica::acesso($idUsuario, [1, 2])) {
                if (file_exists($arquivo)) {
                    $informa = new Link("Apagar os Dados", "?fase=apagar");
                    $informa->set_class('button alert');
                    $informa->set_title("Apaga os dados deste mês");
                    $informa->set_confirma("Deseja Realmente apagar os dados de {$parametroMes}/{$parametroAno}!!");
                    $menu->add_link($informa, "right");
                }

                # Botão de Upload
                $botao = new Button("Upload da Listagem");
                $botao->set_url("?fase=upload&id={$id}");
                $botao->set_title("Faz o Upload do arquivo CSV com a listagem de servidores que receberam auxílio transporte neste mês");
                #$botao->set_target("_blank");
                $menu->add_link($botao, "right");
            }

            $menu->show();

            # Formulário de Pesquisa
            $form = new Form('?');

            # Nome    
            $controle = new Input('parametroNome', 'texto', 'Nome:', 1);
            $controle->set_size(30);
            $controle->set_title('Pesquisa');
            $controle->set_valor($parametroNome);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(5);
            $controle->set_autofocus(true);
            $form->add_item($controle);

            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                            FROM tblotacao
                                           WHERE ativo) UNION (SELECT distinct DIR, DIR
                                            FROM tblotacao
                                           WHERE ativo)
                                        ORDER BY 2');

            array_unshift($result, array("todos", "Todos"));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(7);
            $form->add_item($controle);

            # Cria um array com os anos possíveis
            $anoInicial = 1999;
            $anoAtual = date('Y');
            $anoExercicio = arrayPreenche($anoInicial, $anoAtual, "d");

            $controle = new Input('parametroAno', 'combo', 'Ano:', 1);
            $controle->set_size(8);
            $controle->set_title('Filtra por Ano exercício');
            $controle->set_array($anoExercicio);
            $controle->set_valor(date("Y"));
            $controle->set_valor($parametroAno);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(2);
            $controle->set_col(3);
            $form->add_item($controle);

            $controle = new Input('parametroMes', 'combo', 'Mês:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra pelo Mês');
            $controle->set_array($mes);
            $controle->set_valor($parametroMes);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(2);
            $controle->set_col(3);
            $form->add_item($controle);

            # Perfil
            $result = $pessoal->select('SELECT idperfil, nome
                                          FROM tbperfil                                
                                      ORDER BY 1');
            array_unshift($result, array('*', '-- Todos --'));

            $controle = new Input('parametroPerfil', 'combo', 'Perfil:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Perfil');
            $controle->set_array($result);
            $controle->set_valor($parametroPerfil);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(2);
            $controle->set_col(4);
            $form->add_item($controle);

            $controle = new Input('parametroRecebeu', 'combo', 'Recebeu?:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por recebimento do auxílio');
            $controle->set_array(["Sim", "Não"]);
            $controle->set_valor($parametroRecebeu);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(2);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();

            ################################################################
            # Área Lateral
            $grid->fechaColuna();
            $grid->abreColuna(3);

            $auxTransporte->exibeResumo($parametroLotacao, $parametroMes, $parametroAno);

            $grid->fechaColuna();
            $grid->abreColuna(9);
            
            #################################################################################

            # Exibe a tabela de problemas
            $selectProblemas = "SELECT obs
                                  FROM tbtransporte
                                 WHERE idServidor IS NULL
                                   AND ano = '{$parametroAno}'
                                   AND mes = '{$parametroMes}'";

            $resultProblemas = $pessoal->select($selectProblemas);

            $tabela = new Tabela();
            $tabela->set_titulo('Problemas de Importação - ' . get_nomeMes($parametroMes) . " / {$parametroAno}");
            $tabela->set_label(["Obs"]);
            $tabela->set_width([100]);
            $tabela->set_conteudo($resultProblemas);
            $tabela->set_align(["left"]);
            $tabela->set_bordaInterna(true);
            $tabela->show();
            
            #################################################################################                                  
            # Servidores que receberam
            if ($parametroRecebeu == "Sim") {
                $select = "SELECT tbservidor.idfuncional,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              CONCAT(tbservidor.idServidor,'-','{$parametroMes}','-','{$parametroAno}'),
                              CONCAT(tbservidor.idServidor,'-','{$parametroMes}','-','{$parametroAno}')
                         FROM tbtransporte JOIN tbservidor USING (idServidor) 
                                           JOIN tbpessoa USING (idPessoa)                                         
                                           JOIN tbhistlot USING (idServidor)
                                           JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao) 
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)                        
                          AND ano = '{$parametroAno}'
                          AND mes = '{$parametroMes}'";

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

                # lotacao
                if ($parametroLotacao <> "todos") {
                    # Verifica se o que veio é numérico
                    if (is_numeric($parametroLotacao)) {
                        $select .= " AND tblotacao.idlotacao = {$parametroLotacao}";
                    } else { # senão é uma diretoria genérica
                        $select .= " AND tblotacao.DIR = '{$parametroLotacao}'";
                    }
                }

                # perfil
                if ($parametroPerfil <> "*") {
                    $select .= " AND idperfil = {$parametroPerfil}";
                }
                $select .= " ORDER BY situacao desc, tbpessoa.nome";
            }

            #################################################################################
            # Servidores que não receberam
            if ($parametroRecebeu == "Não") {

                $select = "SELECT tbservidor.idfuncional,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              CONCAT(tbservidor.idServidor,'-','{$parametroMes}','-','{$parametroAno}'),
                              CONCAT(tbservidor.idServidor,'-','{$parametroMes}','-','{$parametroAno}')
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)                                         
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                         JOIN tbperfil USING (idPerfil)
                        WHERE situacao = 1
                          AND tbperfil.tipo <> 'Outros'
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND tbservidor.idServidor NOT IN (SELECT idServidor FROM tbtransporte WHERE idServidor IS NOT NULL AND ano = '{$parametroAno}' AND mes = '{$parametroMes}')";

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

                # lotacao
                if ($parametroLotacao <> "todos") {
                    # Verifica se o que veio é numérico
                    if (is_numeric($parametroLotacao)) {
                        $select .= " AND tblotacao.idlotacao = {$parametroLotacao}";
                    } else { # senão é uma diretoria genérica
                        $select .= " AND tblotacao.DIR = '{$parametroLotacao}'";
                    }
                }

                # perfil
                if ($parametroPerfil <> "*") {
                    $select .= " AND idperfil = {$parametroPerfil}";
                }

                $select .= " ORDER BY tbpessoa.nome";
            }

            #################################################################################
            # Exibe a tabela
            $result = $pessoal->select($select);

            $tabela = new Tabela();
            $tabela->set_titulo('Área de Auxílio Transporte - ' . get_nomeMes($parametroMes) . " / {$parametroAno}");
            $tabela->set_label(["IdFuncional", "Servidor", "Lotação", "Situação", "Recebeu?"]);
            $tabela->set_width([10, 30, 20, 30, 10]);
            $tabela->set_conteudo($result);
            $tabela->set_align(["center", "left", "left", "left"]);
            $tabela->set_classe([null, "pessoal", "pessoal"]);
            $tabela->set_metodo([null, "get_nomeECargoEPerfilESituacao", "get_lotacao"]);
            $tabela->set_funcao([null, null, null, "exibeSituacaoAuxilioTransporte", "exibeRecebeuAuxilioTransporte"]);
            #$tabela->set_bordaInterna(true);
            
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editaServidor');
            $tabela->show();
            break;

        ################################################################
        # Chama o menu do Servidor que se quer editar
        case "editaServidor" :

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAuxilioTransporte.php');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        ################################################################

        case "upload" :
            # Limita a tela
            $grid = new Grid("center");
            $grid->abreColuna(12);

            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "?");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu->add_link($botaoVoltar, "left");
            $menu->show();

            # Exibe o Título
            if (!file_exists($arquivo)) {
                br();

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
                $upload = new UploadDoc($_FILES['doc'], $pasta, $parametroAno . str_pad($parametroMes, 2, '0', STR_PAD_LEFT), $extensoes);

                # Salva e verifica se houve erro
                if ($upload->salvar()) {

                    # Registra log
                    $Objetolog = new Intra();
                    $data = date("Y-m-d H:i:s");
                    $Objetolog->registraLog($idUsuario, $data, $atividade, null, $id, 8, $idServidorPesquisado);

                    # Fecha a janela aberta
                    loadPage("?fase=upload1");
                } else {
                    # volta a tela de upload
                    loadPage("?fase=upload&id=$id");
                }
            }

            # Informa caso exista um arquivo com o mesmo nome
            if (file_exists($arquivo)) {
                p("Já existe um documento para este registro!!<br/>O novo documento irá substituir o antigo !", "puploadMensagem");
                br();
            }

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        case "upload1" :

            br(5);
            aguarde("Apagando os dados anteriores de " . get_nomeMes($parametroMes) . " / {$parametroAno}");

            loadPage("?fase=upload2");
            break;

        case "upload2" :
            # Apaga os dados da tabela
            $select = "SELECT idTransporte
                         FROM tbtransporte
                        WHERE ano = {$parametroAno}
                          AND mes = {$parametroMes}";

            $row = $pessoal->select($select);

            $pessoal->set_tabela("tbtransporte");
            $pessoal->set_idCampo("idTransporte");

            foreach ($row as $tt) {
                $pessoal->excluir($tt[0]);
            }

            loadPage("?fase=upload3");
            break;

        case "upload3" :

            br(5);
            aguarde("Fazendo o upload do arquivo");

            loadPage("?fase=upload4");
            break;

        case "upload4" :
            $certos = 0;
            $linhas = 0;

            # Verifica a existência do arquivo
            if (file_exists($arquivo)) {

                # Zera as variáveis de gravação
                $lines = file($arquivo);
                $linhaDados = false;
                $obs = null;
                $problemas = 0;
                $idServidor = null;

                # Percorre o arquivo e guarda os dados em um array
                foreach ($lines as $linha) {

                    # incrementa as linhas
                    $linhas++;

                    # Divide as colunas
                    $parte = explode(",", $linha);

                    # Verifica se é linha de dados
                    if ($linhaDados) {
                        # IdServidor
                        if (empty($parte[0])) {
                            $idServidor = null;
                        } else {
                            # Verifica se é servidor ativo primeiro
                            $idServidor = $pessoal->get_idServidoridFuncionalAtivo($parte[0]);

                            # se não achar verifica se é inativo
                            if (empty($idServidor)) {
                                $idServidor = $pessoal->get_idServidoridFuncional($parte[0]);
                            }
                        }

                        # Verifica se houve problemas
                        if (empty($idServidor)) {
                            if (!empty($parte[3])) {
                                $obs = $parte[0] . " - " . $parte[3] . " - Servidor não encontrado! Verifique se a Id Funcional está correta.";
                                $problemas++;
                            }
                        }
                    } else {
                        # Pula a primeira linha do cabeçalho
                        if ($parte[0] == "IDFUNCIONAL") {
                            $linhaDados = true;
                            continue;
                        } else {
                            continue;
                        }
                    }

                    if ((!empty($idServidor)) OR (!empty($obs))) {
                        # Grava na tabela tbsispatri
                        $campos = array("idServidor", "ano", "mes", "obs");
                        $valor = array($idServidor, $parametroAno, $parametroMes, $obs);
                        $pessoal->gravar($campos, $valor, null, "tbtransporte", "idTransporte");

                        # Limpa a obs
                        $obs = null;
                    }
                }
            }

            if ($problemas > 0) {
                alert("A importação foi concluída com {$problemas} problema(s)");
            } else {
                alert("A importação foi concluída SEM problemas");
            }
            #echo '<script type="text/javascript" language="javascript">window.close();</script>';
            loadPage("?");
            break;

        case "apagar" :
            br(5);
            aguarde("Apagando os dados de " . get_nomeMes($parametroMes) . " / {$parametroAno}");

            loadPage("?fase=apagar2");
            break;

        case "apagar2" :
            # Apaga os dados da tabela
            $select = "SELECT idTransporte
                         FROM tbtransporte
                        WHERE ano = {$parametroAno}
                          AND mes = {$parametroMes}";

            $row = $pessoal->select($select);

            $pessoal->set_tabela("tbtransporte");
            $pessoal->set_idCampo("idTransporte");

            foreach ($row as $tt) {
                $pessoal->excluir($tt[0]);
            }

            # Apaga o arquivo cvs
            unlink($arquivo);

            loadPage("?fase=apagar3");
            break;

        case "apagar3" :
            alert("Dados Excluídos com Sucesso!");
            loadPage("?");
            break;

################################################################
    }
    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


