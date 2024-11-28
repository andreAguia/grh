<?php

/**
 * Importa Férias
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
    $fase = get('fase', 'inicial');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    $idServidor = soNumeros(get('idServidor'));

    # Pega os parâmetros
    $parametroAno = post('parametroAno', get_session('parametroAnoImportacao', "*"));

    # Joga os parâmetros par as sessions
    set_session('parametroAnoImportacao', $parametroAno);

    # Começa uma nova página
    $page = new Page();
    if ($fase == "importar") {
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

    # Limita o tamanho da tela
    $grid1 = new Grid();
    $grid1->abreColuna(12);

    # Verifica se tem arquivo CSV esperando importação
    $temArquivo = null;
    $select = "SELECT idFeriasSigrh                     
                 FROM tbferiassigrh";
    if ($pessoal->count($select) > 0) {
        $temArquivoCsv = true;
    } else {
        $temArquivoCsv = false;
    }

    switch ($fase) {

        #################################################    

        case "inicial" :

            br(5);
            if ($temArquivoCsv) {
                aguarde("Montando a Tabela");
            }

            loadPage("?fase=inicial2");
            break;

        #################################################

        case "inicial2":

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $linkVoltar = new Link("Voltar", "areaFeriasExercicio.php");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title('Voltar para página anterior');
            $linkVoltar->set_accessKey('V');
            $menu1->add_link($linkVoltar, "left");

            if ($temArquivoCsv) {
                $linkApagar = new Link("Descartar Arquivo CSV", "?fase=apagarBase");
                $linkApagar->set_class('button alert');
                $linkApagar->set_title('Apaga o arquivo CSV');
                $linkApagar->set_confirma('Deseja realmente descartar o arquivo csv da memória?');
                $menu1->add_link($linkApagar, "right");
            } else {
                $linkVoltar = new Link("Upload de Arquivo CSV", "?fase=importar");
                $linkVoltar->set_class('button');
                $linkVoltar->set_title('Carrerga para a memória um arquivo para posterior importação');
                $menu1->add_link($linkVoltar, "right");
            }

            $menu1->show();

            titulo("Área de Importação de Dados do SigRh");
            br();

            if ($temArquivoCsv) {
                # Área Lateral
                $grid2 = new Grid();
                $grid2->abreColuna(3);

                # Resumo por Ano Exercício
                # Pega os dados
                $select = "SELECT anoExercicio,
                              count(*) as tot                          
                         FROM tbferiassigrh JOIN tbservidor ON (tbservidor.idServidor = tbferiassigrh.idServidor)
                                       JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                       JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE tbhistlot.data =(select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                        GROUP BY anoExercicio ORDER BY anoExercicio";

                $resumo = $pessoal->select($select);

                # Pega a soma dos campos
                $soma = 0;
                foreach ($resumo as $value) {
                    $soma += $value['tot'];
                }

                # Monta a tabela
                $tabela = new Tabela();
                $tabela->set_conteudo($resumo);
                $tabela->set_label(["Exercício", "Solicitações"]);
                $tabela->set_totalRegistro(false);
                $tabela->set_rodape("Total de Solicitações: " . $soma);
                $tabela->set_align(["center"]);
                #$tabela->set_funcao(array("exibeDescricaoStatus"));
                $tabela->set_titulo("Ano Exercício");
                $tabela->show();

                #######################################
                # Resumo por Mês
                # Pega os dados
                $select = "SELECT month(dtInicial),
                              count(*) as tot                          
                         FROM tbferiassigrh JOIN tbservidor ON (tbservidor.idServidor = tbferiassigrh.idServidor)
                                       JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                       JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                         WHERE tbhistlot.data =(select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                         GROUP BY year(dtInicial),month(dtInicial) ORDER BY year(dtInicial),month(dtInicial)";

                $resumo = $pessoal->select($select);

                # Pega a soma dos campos
                $soma = 0;
                foreach ($resumo as $value) {
                    $soma += $value['tot'];
                }

                # Monta a tabela
                $tabela = new Tabela();
                $tabela->set_conteudo($resumo);
                $tabela->set_label(["Mês", "Solicitações"]);
                $tabela->set_totalRegistro(false);
                $tabela->set_rodape("Total de Solicitações: " . $soma);
                $tabela->set_align(["center"]);
                $tabela->set_funcao(["get_nomeMes"]);
                $tabela->set_titulo("Mensal (Data Inicial)");
                $tabela->show();

                #######################################
                # Área Principal            
                $grid2->fechaColuna();
                $grid2->abreColuna(9);

                # Filtro
                $form = new Form('?');

                # anos possíveis
                $select = "SELECT distinct anoExercicio,
                              anoExercicio
                         FROM tbferiassigrh
                    ORDER BY anoExercicio";

                $anosPossiveis = $pessoal->select($select);
                array_unshift($anosPossiveis, array("*", 'Todos'));

                $controle = new Input('parametroAno', 'combo', 'Ano Exercício:', 1);
                $controle->set_size(8);
                $controle->set_title('Filtra por Ano em que as férias foi/será fruída');
                $controle->set_array($anosPossiveis);
                $controle->set_valor($parametroAno);
                $controle->set_onChange('formPadrao.submit();');
                $controle->set_linha(1);
                $controle->set_col(3);
                $form->add_item($controle);

                $form->show();

                ########################################
                # Exibe os Problemas
                ########################################
                /*
                 *  Problema com o arquivo csv
                 */
                $select = "SELECT idServidor,
                              idFuncional,
                              anoExercicio,
                              dtInicial,
                              numDias,
                              date_format(ADDDATE(dtInicial,numDias-1),'%d/%m/%Y') as dtf,
                              obs,
                              erro,
                              idFeriasSigrh
                         FROM tbferiassigrh
                        WHERE erro IS NOT NULL";

                # Verifica se tem filtro por perfil
                if (($parametroAno <> "*") AND ($parametroAno <> "")) {
                    $select .= " AND tbferiassigrh.anoExercicio = '{$parametroAno}'";
                }

                $select .= " ORDER BY anoExercicio, dtInicial";

                $result = $pessoal->select($select);
                $problemas = $pessoal->count($select);

                if ($problemas > 0) {
                    $tabela = new Tabela();
                    $tabela->set_titulo("Problemas com o Arquivo CSV");
                    $tabela->set_label(['idServidor', 'idFuncional', 'Exercício', 'Inicio', 'Dias', 'Fim', 'Arquivo CSV', 'Erro']);
                    $tabela->set_align(["center", "center", "center", "center", "center", "center", "left", "left"]);
                    $tabela->set_funcao([null, null, null, "date_to_php"]);
                    $tabela->set_conteudo($result);

                    $tabela->set_editar('?fase=editaRegistro');
                    $tabela->set_idCampo('idFeriasSigrh');

                    $tabela->set_excluir('?fase=excluiRegistro&id=');
                    $tabela->set_idCampo('idFeriasSigrh');
                    $tabela->show();
                } else {
                    $painel = new Callout("warning", "center");
                    $painel->abre();

                    p("Não foi encontrado nenhum problema no arquivo csv", "f14", "center");

                    if ($parametroAno == "*") {

                        $linkConfirma = new Link("Fazer a Importação de {$soma} registros", "?fase=gravar");
                        $linkConfirma->set_class('button');
                        $linkConfirma->set_title("Insere os {$soma} registros do arquivo para o banco de dados de férias");
                        $linkConfirma->show();
                    }

                    $painel->fecha();
                }

                /*
                 * Aparentemente Sem problemas
                 */

                $select = "SELECT tbservidor.idServidor,
                                  tbferiassigrh.idFuncional,
                                  tbferiassigrh.anoExercicio,
                                  tbferiassigrh.dtInicial,
                                  tbferiassigrh.numDias,
                                  date_format(ADDDATE(tbferiassigrh.dtInicial,tbferiassigrh.numDias-1),'%d/%m/%Y') as dtf,                              
                                  idFeriasSigrh
                             FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                                  JOIN tbferiassigrh ON (tbservidor.idServidor = tbferiassigrh.idServidor)
                            WHERE tbferiassigrh.idServidor <> 0";

                # Verifica se tem filtro por perfil
                if (($parametroAno <> "*") AND ($parametroAno <> "")) {
                    $select .= " AND tbferiassigrh.anoExercicio = '{$parametroAno}'";
                }

                $select .= " ORDER BY tbpessoa.nome, tbferiassigrh.anoExercicio, dtInicial";

                $result = $pessoal->select($select);
                $contagem = $pessoal->count($select);

                # Cria o botão de exclusão em massa
                if ($contagem > 1 AND $parametroAno <> "*") {
                    # Cria um menu
                    $menu2 = new MenuBar();

                    # Relatórios
                    $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
                    $botaoRel = new Button();
                    $botaoRel->set_title("Relatório de {$parametroAno}");
                    $botaoRel->set_url("?fase=relatorio");
                    $botaoRel->set_target("_blank");
                    $botaoRel->set_imagem($imagem);
                    $menu2->add_link($botaoRel, "left");

                    # Excluir
                    $linkApagar = new Link("Excluir todos os {$contagem} registros de {$parametroAno}", "?fase=excluiRegistroAno");
                    $linkApagar->set_class('button alert');
                    $linkApagar->set_title("Exclui todos os {$contagem} registro do ano {$parametroAno}");
                    $linkApagar->set_confirma("Deseja realmente excluir todos os {$contagem} registros de {$parametroAno}");
                    $menu2->add_link($linkApagar, "right");

                    $menu2->show();
                }

                $tabela = new Tabela();
                $tabela->set_titulo("Dados do Arquivo CSV");
                $tabela->set_label(['Nome', 'idfunciional', 'Exercício', 'Inicio', 'Dias', 'Fim']);
                $tabela->set_align(["left"]);
                $tabela->set_funcao([null, null, null, "date_to_php"]);
                $tabela->set_classe(["pessoal"]);
                $tabela->set_metodo(["get_nomeECargoELotacao"]);
                $tabela->set_conteudo($result);
                $tabela->set_rowspan([0, 1]);
                $tabela->set_grupoCorColuna(0);

                $tabela->set_editar('?fase=editaRegistro');
                $tabela->set_idCampo('idFeriasSigrh');

                $tabela->set_excluir('?fase=excluiRegistro&id=');
                $tabela->set_idCampo('idFeriasSigrh');
                $tabela->show();

                $grid2->fechaColuna();
                $grid2->fechaGrid();
            } else {

                $grid = new Grid("center");
                $grid->abreColuna(8);
                br(2);

                tituloTable("Regras para a Importação");
                $painel = new Callout("warning");
                $painel->abre();
                br();

                p("Antes de fazer a importação verifique se:");
                p(" - O arquivo esta no formato CSV;");
                p(" - A coluna de observação foi deletada;");
                $painel->fecha();

                $grid->fechaColuna();
                $grid->fechaGrid();
            }
            break;

        #################################################
        /*
         * Imprimir relação de férias do ano
         */

        case "relatorio" :

            $subTitulo = null;

            # Pega os dados
            $select = "SELECT tbferiassigrh.idFuncional,
                              tbservidor.idServidor,                              
                              tbservidor.idServidor,                              
                              tbferiassigrh.anoExercicio,
                              tbferiassigrh.dtInicial,
                              tbferiassigrh.numDias,
                              date_format(ADDDATE(tbferiassigrh.dtInicial,tbferiassigrh.numDias-1),'%d/%m/%Y') as dtf,                              
                              idFeriasSigrh
                         FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                              JOIN tbferiassigrh ON (tbservidor.idServidor = tbferiassigrh.idServidor)
                        WHERE tbferiassigrh.idServidor <> 0";

            # Verifica se tem filtro por perfil
            if (($parametroAno <> "*") AND ($parametroAno <> "")) {
                $select .= " AND tbferiassigrh.anoExercicio = '{$parametroAno}'";
                $subTitulo = $parametroAno;
            }

            $select .= " ORDER BY tbpessoa.nome, tbferiassigrh.anoExercicio, dtInicial";

            # Monta o Relatório
            $relatorio = new Relatorio();
            $relatorio->set_titulo("Dados do Arquivo CSV");

            if (!is_null($subTitulo)) {
                $relatorio->set_subtitulo($subTitulo);
            }

            $result = $pessoal->select($select);

            $relatorio->set_label(['idfunciional', 'Nome', 'Lotação', 'Exercício', 'Inicio', 'Dias', 'Fim']);
            $relatorio->set_conteudo($result);
            $relatorio->set_align(["center", "left", "left"]);
            $relatorio->set_funcao([null, null, null, null, "date_to_php"]);
            $relatorio->set_classe([null, "pessoal", "pessoal"]);
            $relatorio->set_metodo([null, "get_nomeECargo", "get_lotacaosimples"]);
            $relatorio->set_bordaInterna(true);
            $relatorio->show();
            break;

        #################################################

        /*
         * Acessa as férias já cadastradas no sistema do servidor
         */

        case "editaServidorFerias":

            set_session('idServidorPesquisado', $idServidor);
            set_session('areaFerias', "importa");
            loadPage('servidorFerias.php');
            break;

        ################################################# 

        /*
         * Inicia a rotina que faz o upload do arquivo csv
         */

        case "importar" :

            $grid = new Grid();
            $grid->abreColuna(12);

            botaoVoltar("?");

            $grid->fechaColuna();
            $grid->fechaGrid();

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
                $upload = new UploadDoc($_FILES['doc'], $pasta, "ferias", $extensoes);

                # Salva e verifica se houve erro
                if ($upload->salvar()) {

                    # Registra log
                    $Objetolog = new Intra();
                    $data = date("Y-m-d H:i:s");
                    $atividade = "Importou o arquivo csv de férias do SigRh temporáriamente para memória";
                    $Objetolog->registraLog($idUsuario, $data, $atividade, null, null, 8);

                    # Volta para o menu
                    loadPage("?fase=importar1");
                } else {
                    loadPage("?fase=importar");
                }
            }
            $grid->fechaGrid();
            break;

        #################################################    

        /*
         * Exclui a tabela csv que porventura esteja cadastrado
         */

        case "importar1" :

            br(5);
            aguarde("Liberando Espaço na Memória");

            # Apaga a tabela tbsispatri
            $select = 'SELECT idFeriasSigrh
                         FROM tbferiassigrh';

            $row = $pessoal->select($select);

            $pessoal->set_tabela("tbferiassigrh");
            $pessoal->set_idCampo("idFeriasSigrh");

            foreach ($row as $tt) {
                $pessoal->excluir($tt[0]);
            }

            loadPage("?fase=importar2");
            break;

        #################################################    
        /*
         * Exibe a mensagem para o usuário
         */
        case "importar2" :

            br(5);
            aguarde("Fazendo o upload do arquivo");

            sleep(3);
            loadPage("?fase=importar3");
            break;

        #################################################

        /*
         * Salva o arquivo csv na tabela temporária para análise
         */

        case "importar3" :

            # Define o arquivo a ser importado
            $arquivo = "../_temp/ferias.csv";

            # Altere o divisor de acordo com o arquivo
            #$divisor = ";";
            $divisor = get_arquivoDivisor($arquivo);
            
            # Flags
            $certos = 0;
            $linhas = 0;

            # Verifica a existência do arquivo
            if (file_exists($arquivo)) {
                $lines = file($arquivo);
                $linhaDados = false;

                # Pega o cabeçalho
                $cabecalho = explode($divisor, $lines[0]);

                if (isset($cabecalho[14]) AND $cabecalho[14] == "obs") {
                    alert("Foi encontrado a coluna obs neste arquivo! Apague a coluna obs e importe novamente.");
                    loadPage("?");
                } else {
                    # Campos
                    $campos = array(
                        "idServidor",
                        "idFuncional",
                        "dtInicial",
                        "numDias",
                        "anoExercicio",
                        "erro",
                        "obs"
                    );

                    # Percorre o arquivo e guarda os dados em um array
                    foreach ($lines as $linha) {

                        $colunas = explode($divisor, $linha);

                        # Verifica se é cabeçalho
                        if ($colunas[0] <> "numfunc") {
                            if (!empty($colunas[$colunaIdfuncional])) {

                                # Inicia a variável de erro
                                $erro = null;

                                $idServidorImportado = $pessoal->get_idServidoridFuncionalAtivo($colunas[$colunaIdfuncional]);
                                $numDias = null;

                                # Verifica se o idFuncional está correto
                                if (empty($idServidorImportado)) {
                                    $idServidorImportado = null;
                                    $erro = "Não foi encontrado servidor com essa idfuncional";
                                }

                                # Verifica se tem data Inicial
                                if (empty($colunas[$colunaDtInicial])) {
                                    $erro = "A Data Inicial está em branco";
                                    $dtInicial = null;
                                } else {
                                    $dtInicial = substr($colunas[$colunaDtInicial], 0, 10);

                                    if (!empty($colunas[$colunaDtfinal])) {
                                        $numDias = getNumDias($dtInicial, substr($colunas[$colunaDtfinal], 0, 10));
                                    }

                                    $dtInicial = date_to_bd($dtInicial);
                                }

                                # Verifica se tem data Final
                                if (empty($colunas[$colunaDtfinal])) {
                                    $erro = "A Data Final está em branco";
                                }

                                $anoExercicio = year($colunas[$colunaAno]);

//                                echo $colunas[$colunaAno];
//                                br();
                                # Valores
                                $valor = array(
                                    $idServidorImportado,
                                    $colunas[$colunaIdfuncional],
                                    $dtInicial,
                                    $numDias,
                                    $anoExercicio,
                                    $erro,
                                    "IdFuncional: " . $colunas[$colunaIdfuncional] . "<br/>" . $colunas[$colunaNome],
                                );

                                #var_dump($valor);
                                # Grava
                                $pessoal->gravar($campos, $valor, null, "tbferiassigrh", "idFeriasSigrh");
                            }
                        } else {

                            # Define as colunas
                            $colunaIdfuncional = 0;
                            $colunaNome = array_search('nome', $colunas);
                            $colunaDtInicial = array_search('dtini', $colunas);
                            $colunaDtfinal = array_search('dtfim', $colunas);
                            $colunaAno = array_search('dtfim_per', $colunas);

                            #var_dump($colunas);
//                            br();
//                            echo "Nome: ", $colunaNome;
//                            br();
//                            echo "Inicial: ", $colunaDtInicial;
//                            br();
//                            echo "Final: ", $colunaDtfinal;
//                            br();
//                            echo "Ano: ", $colunaAno;
                        }
                    }
                }
            }

            loadPage("?");
            break;

        #################################################

        case "apagarBase" :

            br(5);
            aguarde("Apagando o Arquivo CSV");
            loadPage("?fase=apagarBase2");
            break;

        #################################################    

        case "apagarBase2" :

            # Apaga a tabela tbsispatri
            $select = 'SELECT idFeriasSigrh
                         FROM tbferiassigrh';

            $row = $pessoal->select($select);

            foreach ($row as $tt) {
                $pessoal->excluir($tt[0], "tbferiassigrh", "idFeriasSigrh");
            }

            # Registra log
            $Objetolog = new Intra();
            $data = date("Y-m-d H:i:s");
            $atividade = "Apagou o arquivo csv de férias do SigRh";
            $Objetolog->registraLog($idUsuario, $data, $atividade, null, null, 3);
            
            aguarde("Apagando os registros");

            loadPage("?");
            break;

        #################################################  

        case "editaRegistro" :

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $linkVoltar = new Link("Voltar", "?");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title('Voltar para página anterior');
            $linkVoltar->set_accessKey('V');
            $menu1->add_link($linkVoltar, "left");

            $menu1->show();

            titulotable("Editar Registro do Arquivo CSV");
            br();

            # Pega os dados
            $select = "SELECT idFuncional,
                              anoExercicio,
                              dtInicial,
                              numDias,
                              obs,
                              erro,
                              idFeriasSigrh
                         FROM tbferiassigrh
                        WHERE idFeriasSigrh = {$id}
                     ORDER BY anoExercicio, dtInicial";

            $result = $pessoal->select($select, false);

            # Verifica se tem erro
            if (!empty($result["erro"])) {
                tituloTable($result["erro"]);
                callout($result["obs"]);
                br();
            }

            # Monta o formulário
            $form = new Form("?fase=validaRegistro&id={$id}");

            # idFuncional
            $controle = new Input('idFuncional', 'texto', 'idFuncional:', 1);
            $controle->set_size(20);
            $controle->set_linha(1);
            $controle->set_col(2);
            $controle->set_autofocus(true);
            $controle->set_title('A idfuncional do Servidor');
            $controle->set_valor($result['idFuncional']);
            $form->add_item($controle);

            # anoexercicio
            $controle = new Input('anoExercicio', 'texto', 'Ano Exercicio:', 1);
            $controle->set_size(20);
            $controle->set_linha(1);
            $controle->set_col(2);
            $controle->set_title('O ano exercicio');
            $controle->set_valor($result['anoExercicio']);
            $form->add_item($controle);

            # dtInicial
            $controle = new Input('dtInicial', 'data', 'data Inicial:', 1);
            $controle->set_size(20);
            $controle->set_linha(1);
            $controle->set_col(3);
            $controle->set_title('A date inicial');
            $controle->set_valor($result['dtInicial']);
            $form->add_item($controle);

            # numDias
            $controle = new Input('numDias', 'texto', 'Dias:', 1);
            $controle->set_size(20);
            $controle->set_linha(1);
            $controle->set_col(2);
            $controle->set_title('Número de Dias');
            $controle->set_valor($result['numDias']);
            $form->add_item($controle);

            # obs
            $controle = new Input('obs', 'hidden', 'Obs:', 1);
            $controle->set_size(20);
            $controle->set_linha(2);
            $controle->set_col(2);
            $controle->set_title('obs');
            $controle->set_valor($result['obs']);
            $form->add_item($controle);

            # submit
            $controle = new Input('submit', 'submit');
            $controle->set_valor('Salvar');
            $controle->set_linha(3);
            $form->add_item($controle);

            $form->show();
            break;

        #################################################  
        case "validaRegistro" :

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $linkVoltar = new Link("Voltar", "#");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title('Voltar para página anterior');
            $linkVoltar->set_accessKey('V');
            $menu1->add_link($linkVoltar, "left");

            $menu1->show();

            titulotable("Editar Registro");
            br(6);

            aguarde("Salvando ...");

            # Pega os dados
            $idFuncional = post("idFuncional");
            $anoExercicio = post("anoExercicio");
            $dtInicial = post("dtInicial");
            $numDias = post("numDias");
            $obs = post("obs");

            # Trata os dados
            $idServidorImportado = $pessoal->get_idServidoridFuncionalAtivo($idFuncional);

            # Campos
            $campos = array(
                "idServidor",
                "idFuncional",
                "dtInicial",
                "numDias",
                "anoExercicio",
                "erro",
                "obs"
            );

            if (empty($idServidorImportado)) {
                $idServidorImportado = null;
                $erro = "Não foi encontrado servidor com essa idFuncional";
            } else {
                $erro = null;
            }

            # Valores
            $valor = array(
                $idServidorImportado,
                $idFuncional,
                $dtInicial,
                $numDias,
                $anoExercicio,
                $erro,
                $obs,
            );

            # Grava
            $pessoal->gravar($campos, $valor, $id, "tbferiassigrh", "idFeriasSigrh");

            loadPage("?");
            break;

        #################################################  

        case "excluiRegistro" :

            br(5);
            aguarde("Excluindo ...");

            # Exclui o registro
            $pessoal->excluir($id, "tbferiassigrh", "idFeriasSigrh");

            loadPage("?");
            break;

        #################################################  

        case "excluiRegistroAno" :

            br(5);
            aguarde("Excluindo Registros de {$parametroAno}  ...");

            # Apaga a tabela tbsispatri
            $select = "SELECT idFeriasSigrh
                         FROM tbferiassigrh
                         WHERE anoExercicio = {$parametroAno}";

            $row = $pessoal->select($select);

            foreach ($row as $tt) {
                $pessoal->excluir($tt[0], "tbferiassigrh", "idFeriasSigrh");
            }

            # Registra log
            $Objetolog = new Intra();
            $data = date("Y-m-d H:i:s");
            $atividade = "Apagou os registros do ano {$parametroAno} do arquivo csv de férias do SigRh";
            $Objetolog->registraLog($idUsuario, $data, $atividade, null, null, 3);

            # Apaga o ano da session
            set_session('parametroAnoImportacao');

            loadPage("?");
            break;

        #################################################  

        case "gravar" :

            # Verifica se tem arquivo CSV esperando importação
            $select = "SELECT idFeriasSigrh                     
                         FROM tbferiassigrh";
            $numRegistros = $pessoal->count($select);

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $linkVoltar = new Link("NÃO IMPORTAR", "?");
            $linkVoltar->set_class('button alert');
            $linkVoltar->set_title('Voltar para página anterior');
            $menu1->add_link($linkVoltar, "left");

            # Voltar
            $linkVoltar = new Link("SIM, Importar para a Tabela de Férias", "?fase=gravar2");
            $linkVoltar->set_class('button primary');
            $linkVoltar->set_title('Importa de forma definitiva para a tabela de férias do sistema');
            $menu1->add_link($linkVoltar, "right");

            $menu1->show();

            titulotable("Transferir os Dados Para a Tabela de Férias");
            br(5);

            p("Deseja Realmente Fazer a Importação de {$numRegistros} Registros<br/>para a Tabela de Férias do Sistema?", "f16", "center");
            break;

        #################################################  

        case "gravar2" :

            br(5);
            aguarde("Importando ...");

            loadPage("?fase=gravar3");
            break;

        #################################################  

        case "gravar3" :

            $select = "SELECT tbservidor.idServidor,
                              tbferiassigrh.anoExercicio,
                              tbferiassigrh.dtInicial,
                              tbferiassigrh.numDias
                         FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                              JOIN tbferiassigrh ON (tbservidor.idServidor = tbferiassigrh.idServidor)
                       ORDER BY tbpessoa.nome";

            $row = $pessoal->select($select);
            $numRegistros = $pessoal->count($select);

            # Valores para gravação (Cria um id para cada importação)
            $data = date("Y-m-d H:i:s");

            foreach ($row as $tt) {

                # Campos
                $campos = array(
                    "idServidor",
                    "anoExercicio",
                    "dtInicial",
                    "numDias",
                    "dtImportacao",
                    "status"
                );

                # Muda o status para solicitada ou fruída de acordo com a data Inicial e a data de hoje
                if ($tt[2] <= date("Y-m-d")) {
                    $status = "fruída";
                } else {
                    $status = "solicitada";
                }

                # Valores
                $valor = array(
                    $tt[0],
                    $tt[1],
                    $tt[2],
                    $tt[3],
                    $data,
                    $status
                );

                # Grava
                $pessoal->gravar($campos, $valor, null, "tbferias", "idFerias");
            }

            # Registra log
            $Objetolog = new Intra();
            $data = date("Y-m-d H:i:s");
            $atividade = "Importados {$numRegistros} registros do SIGRH (arquivo CSV) para a tabela de férias do sistema";
            $Objetolog->registraLog($idUsuario, $data, $atividade, null, null, 8);

            loadPage("?fase=apagarBase");
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}

$grid1->fechaColuna();
$grid1->fechaGrid();
