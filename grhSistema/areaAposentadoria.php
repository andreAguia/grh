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
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();
    $aposentadoria = new Aposentadoria();
    #$previsao = new PrevisaoAposentadoria();
    # Verifica a fase do programa
    $fase = get('fase');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a área de aposentadoria";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "regras") {
        AreaServidor::cabecalho();
    }

    # Pega os parâmetros
    $parametroAno = post('parametroAno', get_session('parametroAno', $aposentadoria->get_ultimoAnoAposentadoria()));
    $parametroMotivo = post('parametroMotivo', get_session('parametroMotivo', 3));
    $parametroSexo = post('parametroSexo', get_session('parametroSexo', "Feminino"));
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', $pessoal->get_idLotacao($intra->get_idServidor($idUsuario))));
    $parametroIdade = post('parametroIdade', get_session('parametroIdade', 55));
    $parametroTempoCargo = post('parametroTempoCargo', get_session('parametroTempoCargo', 5));
    $parametroServicoPublico = post('parametroServicoPublico', get_session('parametroServicoPublico', 10));
    $parametroCargoTipo = post('parametroCargoTipo', get_session('parametroCargoTipo', "*"));

    # Joga os parâmetros par as sessions
    set_session('parametroSexo', $parametroSexo);
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroAno', $parametroAno);
    set_session('parametroMotivo', $parametroMotivo);
    set_session('parametroIdade', $parametroIdade);
    set_session('parametroTempoCargo', $parametroTempoCargo);
    set_session('parametroServicoPublico', $parametroServicoPublico);
    set_session('parametroCargoTipo', $parametroCargoTipo);

    # Limita a página
    $grid = new Grid();
    $grid->abreColuna(12);

    if ($fase <> "regras") {

        # Cria um menu
        $menu = new MenuBar();

        # Voltar
        $botaoVoltar = new Link("Voltar", "grh.php");
        $botaoVoltar->set_class('button');
        $botaoVoltar->set_title('Voltar a página anterior');
        $botaoVoltar->set_accessKey('V');
        $menu->add_link($botaoVoltar, "left");

        # Regras
        $botaoVoltar = new Link("Regras", "?fase=regras");
        $botaoVoltar->set_class('button');
        $botaoVoltar->set_target('_blank');
        $botaoVoltar->set_title('Regras de aposentadoria');
        #$menu->add_link($botaoVoltar, "right");

        $menu->show();

        # Título
        titulo("Área de Aposentadoria");
        br();
    }

    switch ($fase) {

        /*
         *  Aposentados por ano
         */
        case "" :
        case "porAno" :

            $grid2 = new Grid();
            $grid2->abreColuna(12, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(1);

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 9);

            # Formulário de Pesquisa
            $form = new Form('?fase=porAno');

            # Cria um array com os anos possíveis
            $select = 'SELECT DISTINCT YEAR(tbservidor.dtDemissao), YEAR(tbservidor.dtDemissao)
                         FROM tbservidor 
                        WHERE situacao = 2
                          AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                     ORDER BY 1 desc';

            $anos = $pessoal->select($select);

            $controle = new Input('parametroAno', 'combo');
            $controle->set_size(6);
            $controle->set_title('Filtra por Ano exercício');
            $controle->set_array($anos);
            $controle->set_valor($parametroAno);
            $controle->set_autofocus(true);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();

            # Exibe a lista
            $aposentadoria->exibeAposentadosPorAno($parametroAno);

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

        /*
         *  Aposentadoria por Motivo / Tipo
         */
        case "motivo" :

            $grid2 = new Grid();
            $grid2->abreColuna(12, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(2);

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 9);

            # Formulário de Pesquisa
            $form = new Form('?fase=motivo');

            # Cria um array com os tipo possíveis
            $selectMotivo = "SELECT DISTINCT idMotivo,
                                    tbmotivo.motivo
                               FROM tbmotivo JOIN tbservidor ON (tbservidor.motivo = tbmotivo.idMotivo)
                              WHERE situacao = 2
                                AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                           ORDER BY 2";

            $motivosPossiveis = $pessoal->select($selectMotivo);

            $controle = new Input('parametroMotivo', 'combo', 'Motivo:', 1);
            $controle->set_size(8);
            $controle->set_title('Filtra por Motivo');
            $controle->set_array($motivosPossiveis);
            $controle->set_valor(date("Y"));
            $controle->set_valor($parametroMotivo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_autofocus(true);
            $controle->set_linha(1);
            $controle->set_col(6);
            $form->add_item($controle);

            $form->show();

            # Exibe a lista
            $aposentadoria->exibeAposentadosPorTipo($parametroMotivo);

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

        /*
         *  Estatística
         */
        case "anoEstatistica" :

            $grid = new Grid();
            $grid->abreColuna(12, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(3);

            $painel->fecha();

            $grid->fechaColuna();

            #################################################################

            $grid->abreColuna(12, 9);

            $grid = new Grid();
            $grid->abreColuna(6);

            # Monta o select
            $selectGrafico = 'SELECT tbmotivo.motivo, count(tbservidor.idServidor) as jj
                                FROM tbservidor LEFT JOIN tbmotivo on (tbservidor.motivo = tbmotivo.idMotivo)
                               WHERE tbservidor.situacao = 2
                               AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                            GROUP BY tbmotivo.motivo
                            ORDER BY 2 DESC ';

            $servidores = $pessoal->select($selectGrafico);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            $chart = new Chart("Pie", $servidores);
            $chart->set_idDiv("cargo");
            $chart->set_legend(false);
            $chart->set_tamanho($largura = 400, $altura = 300);
            $chart->show();

            $grid->fechaColuna();

            #################################################################

            $grid->abreColuna(6);

            # Tabela
            $tabela = new Tabela();
            $tabela->set_titulo("por Tipo de Aposentadoria");
            $tabela->set_conteudo($servidores);
            $tabela->set_label(array("Aposentadoria", "Servidores"));
            $tabela->set_width(array(80, 20));
            $tabela->set_align(array("left", "center"));
            $tabela->set_rodape("Total de Servidores: " . $total);
            $tabela->show();

            $grid->fechaColuna();
            $grid->fechaGrid();

            ###            
            # Select
            $selectGrafico = 'SELECT YEAR(tbservidor.dtDemissao), count(tbservidor.idServidor) as jj
                                FROM tbservidor LEFT JOIN tbmotivo on (tbservidor.motivo = tbmotivo.idMotivo)
                               WHERE tbservidor.situacao = 2
                            GROUP BY YEAR(tbservidor.dtDemissao)
                            ORDER BY 1 asc ';

            $servidores = $pessoal->select($selectGrafico);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            hr();

            tituloTable("por Ano da Aposentadoria");

            # Tabela
            $tabela = new Tabela();
            $tabela->set_titulo("por Ano da Aposentadoria");
            $tabela->set_conteudo($servidores);
            $tabela->set_label(array("Ano", "Servidores"));
            $tabela->set_width(array(80, 20));
            $tabela->set_align(array("left", "center"));
            $tabela->set_rodape("Total de Servidores: " . $total);
            #$tabela->show();
            # Gráfico
            $chart = new Chart("ColumnChart", $servidores);
            $chart->set_idDiv("perfil");
            $chart->set_legend(false);
            $chart->set_label(array("Ano", "Nº de Servidores"));
            $chart->set_tamanho($largura = 900, $altura = 500);
            $chart->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        /*
         * Compulsória por Ano
         */

        case "compulsoria" :

            $grid2 = new Grid();
            $grid2->abreColuna(12, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(8);

            $compulsoria = new AposentadoriaCompulsoria();
            $compulsoria->exibeRegras();

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 9);

            # Idade obrigatória
            $idade = $intra->get_variavel("aposentadoria.compulsoria.idade");

            # Formulário de Pesquisa
            $form = new Form('?fase=compulsoria');

            # Cria um array com os anos possíveis
            $anos = arrayPreenche(date("Y") - 2, date("Y") + 20);

            $controle = new Input('parametroAno', 'combo');
            $controle->set_size(6);
            $controle->set_title('Filtra por Ano exercício');
            $controle->set_array($anos);
            $controle->set_valor($parametroAno);
            $controle->set_autofocus(true);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();

            # Exibe a lista

            $select = "SELECT month(dtNasc),  
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          TIMESTAMPDIFF(YEAR,tbpessoa.dtNasc,CURDATE()),
                          ADDDATE(dtNasc, INTERVAL 75 YEAR),
                          tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                    WHERE tbservidor.situacao = 1
                    AND idPerfil = 1
                    AND ({$parametroAno} - YEAR(tbpessoa.dtNasc) = {$idade})                    
                    ORDER BY dtNasc";

            $result = $pessoal->select($select);
            $count = $pessoal->count($select);
            $titulo = "Servidores Estatutários Ativos que Fazem / Fizeram  {$idade} anos em {$parametroAno}";

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['Mês', 'Servidor', 'Lotação', "Idade", "Fará {$idade}"]);
            $tabela->set_align(['center', 'left', 'center', 'center', 'center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, "Pessoal", "Pessoal", null, null, "Pessoal"]);
            $tabela->set_metodo([null, "get_nomeECargo", "get_lotacao"]);
            $tabela->set_funcao(["get_nomeMes", null, null, null, "date_to_php"]);
            #$tabela->set_editar($this->linkEditar);
            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);
            $tabela->set_idCampo('idServidor');

            if ($count > 0) {
                $tabela->show();
            } else {
                br();
                tituloTable($titulo);
                $callout = new Callout();
                $callout->abre();
                p('Nenhum item encontrado !!', 'center');
                $callout->fecha();
            }

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

        ################################################################

        /*
         * Compulsória por Lotação
         */

        case "compulsoriaGeral" :

            $grid2 = new Grid();
            $grid2->abreColuna(12, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(7);

            $compulsoria = new AposentadoriaCompulsoria();
            $compulsoria->exibeRegras();

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 9);

            # Idade obrigatória
            $idade = $intra->get_variavel("aposentadoria.compulsoria.idade");

            # Formulário de Pesquisa
            $form = new Form('?fase=compulsoriaGeral');

            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
            array_unshift($result, array("Todos", 'Todas'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(12);
            $form->add_item($controle);
            $form->show();

            # Exibe a lista
            $select = "SELECT idFuncional,  
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          TIMESTAMPDIFF(YEAR,tbpessoa.dtNasc,CURDATE()),
                          ADDDATE(dtNasc, INTERVAL 75 YEAR),
                          TIMESTAMPDIFF(DAY,CURDATE(),ADDDATE(dtNasc, INTERVAL 75 YEAR))
                     FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                     JOIN tbhistlot USING (idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND idPerfil = 1";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }


            $select .= " ORDER BY dtNasc";

            $result = $pessoal->select($select);
            $count = $pessoal->count($select);
            $titulo = "Previsão de Aposentadoria Compulsória";

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Servidor', 'Lotação', 'Idade', "Compulsória em:", "Faltam<br/>(dias)"]);
            $tabela->set_align(['center', 'left']);
            $tabela->set_width([15, 40, 15, 10, 15]);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, "get_nomeECargo", "get_lotacao"]);
            $tabela->set_funcao([null, null, null, null, "date_to_php"]);
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editarCompulsorioLotacao');

            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 3,
                    'valor' => '74',
                    'operador' => '>',
                    'id' => 'emAberto')
            ));
            $tabela->show();

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

        ################################################################  

        case "editaServidor" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?fase=previsao');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        ################################################################

        case "editarAno" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        ################################################################

        case "editarMotivo" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?fase=motivo');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        ################################################################

        case "editarCompulsorioLotacao" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?fase=compulsoriaGeral');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        ################################################################

        case "configuracaoCompulsoria" :
            $grid2 = new Grid();
            $grid2->abreColuna(12, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(9);

            $painel->fecha();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 9);

            titulotable("Configuração - Aposentadoria Compulsória");

            # Verifica se está bloqueado
            $bloqueado = get("b", true);

            # Pega os valores
            $idadeAposent = $intra->get_variavel("aposentadoria.compulsoria.idade");

            # Pega os comentarios
            $idadeAposentComentario = $intra->get_variavelComentario("aposentadoria.compulsoria.idade");

            # Formuário exemplo de login
            if ($bloqueado) {
                $form = new Form('?fase=configuracaoCompulsoria&b=0', 'login');
            } else {
                br();
                callout("Atenção ! Configurar as variáveis de aposentadoria de acordo com a legislação vigente.<br/>"
                        . "Qualquer alteração destes valores irá afetar as previsões de aposentadoria do sistema.");
                $form = new Form('?fase=validaConfiguracaoCompulsoria', 'login');
            }

            # Idade Feminino
            $controle = new Input('idade', 'numero', 'Idade:', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(2);
            $controle->set_valor($idadeAposent);
            $controle->set_autofocus(true);
            #$controle->set_helptext($idadeAposentFemininoComentario);
            $controle->set_title($idadeAposentComentario);
            if ($bloqueado) {
                $controle->set_readonly(true);
                $controle->set_disabled(true);
            }
            $form->add_item($controle);

            # submit
            $controle = new Input('submit', 'submit');
            $controle->set_fieldset("fecha");
            if ($bloqueado) {
                $controle->set_valor('Editar');
            } else {
                $controle->set_valor('Salvar');
            }
            $controle->set_linha(3);
            $form->add_item($controle);

            $form->show();

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

        ################################################################

        case "validaConfiguracaoCompulsoria" :

            # Recebe os valores digitados
            $idade = post("idade");

            # Recebe os valores atuais
            $idadeAposent = $intra->get_variavel("aposentadoria.compulsoria.idade");

            # Inicia as variáveis do erro
            $msgErro = null;
            $erro = 0;

            # Verifica se foi preenchido
            if (empty($idade)) {
                $msgErro .= 'O campo idade para aposentadoria compulsória é obrigatório!\n';
                $erro = 1;
            }

            # Verifica se tem erro
            if ($erro == 0) {
                if ($idadeAposent <> $idade) {
                    $intra->set_variavel("aposentadoria.compulsoria.idade", $idade);
                    $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), "Alterou de {$idadeAposent} para {$idade} a idade da aposentadiria compulsória dos servidores", "tbvariaveis", null, 2);
                }

                loadPage("?fase=configuracaoCompulsoria");
            } else {
                alert($msgErro);
                back(1);
            }

            break;

        ################################################################

        /*
         * Por idade e tempo de contribuição
         */

        case "porIdadeContribuicao" :

            $grid2 = new Grid();
            $grid2->abreColuna(12, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(5);

            $painel->fecha();

            $permanente = new AposentadoriaPermanente1();
            $permanente->exibeRegras();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 9);

            # Formulário de Pesquisa
            $form = new Form('?fase=porIdadeContribuicao');

            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
            array_unshift($result, array("Todos", 'Todas'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(12);
            $form->add_item($controle);
            $form->show();

            # Exibe a lista
            $select = "SELECT idFuncional, 
                          tbservidor.idServidor,
                          TIMESTAMPDIFF(YEAR,tbpessoa.dtNasc,CURDATE()),
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.idServidor
                     FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                     JOIN tbhistlot USING (idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND idPerfil = 1";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }


            $select .= " ORDER BY tbpessoa.nome";

            $result = $pessoal->select($select);
            $count = $pessoal->count($select);
            $titulo = "Previsão de Aposentadoria por Idade e Tempo de Contribuição";

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Servidor', "Idade", "Contribuição<br/>(dias)", "Serviço Público<br/>(dias)", "Cargo Efetivo<br/>(dias)", "Aposenta em:", "Faltam<br/>(dias)"]);
            $tabela->set_align(['center', 'left']);
            $tabela->set_width([10, 30, 10, 10, 10, 10, 10, 5]);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, "Pessoal", null, "Aposentadoria", "Aposentadoria", "Aposentadoria", "AposentadoriaPermanente1", "AposentadoriaPermanente1"]);
            $tabela->set_metodo([null, "get_nomeECargoELotacao", null, "get_tempoTotal", "get_tempoServicoUenf", "get_tempoPublicoIninterrupto", "getDataAposentadoria", "getDiasFaltantes"]);
            #$tabela->set_funcao([null, null, "date_to_php"]);
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editarIdadeContribuicao');

            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 7,
                    'valor' => '0',
                    'operador' => '=',
                    'id' => 'emAberto')
            ));
            $tabela->show();

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

        ################################################################

        case "editarIdadeContribuicao" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?fase=porIdadeContribuicao');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        ################################################################

        /*
         * Por idade 
         */

        case "porIdade" :

            $grid2 = new Grid();
            $grid2->abreColuna(12, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(6);

            $painel->fecha();

            $permanente = new AposentadoriaPermanente2();
            $permanente->exibeRegras();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 9);

            # Formulário de Pesquisa
            $form = new Form('?fase=porIdade');

            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
            array_unshift($result, array("Todos", 'Todas'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(12);
            $form->add_item($controle);
            $form->show();

            # Exibe a lista
            $select = "SELECT idFuncional, 
                          tbservidor.idServidor,
                          TIMESTAMPDIFF(YEAR,tbpessoa.dtNasc,CURDATE()),
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.idServidor
                     FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                     JOIN tbhistlot USING (idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND idPerfil = 1";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }


            $select .= " ORDER BY tbpessoa.nome";

            $result = $pessoal->select($select);
            $count = $pessoal->count($select);
            $titulo = "Previsão de Aposentadoria por Idade";

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Servidor', "Idade", "Serviço Público<br/>(dias)", "Cargo Efetivo<br/>(dias)", "Aposenta em:", "Faltam<br/>(dias)"]);
            $tabela->set_align(['center', 'left']);
            $tabela->set_width([10, 40, 10, 10, 10, 10, 5]);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, "Pessoal", null, "Aposentadoria", "Aposentadoria", "AposentadoriaPermanente2", "AposentadoriaPermanente2"]);
            $tabela->set_metodo([null, "get_nomeECargoELotacao", null, "get_tempoServicoUenf", "get_tempoPublicoIninterrupto", "getDataAposentadoria", "getDiasFaltantes"]);
            #$tabela->set_funcao([null, null, "date_to_php"]);
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editarIdadeContribuicao');

            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 6,
                    'valor' => '0',
                    'operador' => '=',
                    'id' => 'emAberto')
            ));
            $tabela->show();

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

        ################################################################

        case "editarIdade" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?fase=porIdade');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        ################################################################

        /*
         * EC nº 41/2003
         */

        case "transicao1" :

            $grid2 = new Grid();
            $grid2->abreColuna(12, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(10);

            $painel->fecha();

            $permanente = new AposentadoriaTransicao1();
            $permanente->exibeRegras();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 9);

            # Formulário de Pesquisa
            $form = new Form('?fase=transicao1');

            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
            array_unshift($result, array("Todos", 'Todas'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(12);
            $form->add_item($controle);
            $form->show();

            # Exibe a lista
            $select = "SELECT idFuncional, 
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          TIMESTAMPDIFF(YEAR,tbpessoa.dtNasc,CURDATE()),
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.idServidor
                     FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                     JOIN tbhistlot USING (idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND idPerfil = 1";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }


            $select .= " ORDER BY tbpessoa.nome";

            $result = $pessoal->select($select);
            $count = $pessoal->count($select);

            tituloTable("ART. 6º. DA EC Nº. 41/2003");
            callout("É concedido aos servidores que ingressaram no serviço público até 31 de dezembro de 2003.");

            $titulo = null;

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Servidor', 'Ingresso', "Idade", "Tempo de Contribuição<br/>(dias)", "Serviço Público<br/>(dias)", "Carreira<br/>(dias)", "Cargo Efetivo<br/>(dias)","Aposenta em:", "Faltam<br/>(dias)"]);
            $tabela->set_align(['center', 'left', 'center']);
            #$tabela->set_width([10, 20, 10, 10, 10, 10, 10, 10, 10]);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, "Pessoal", "Aposentadoria", null, "AposentadoriaTransicao1", "Aposentadoria", "Aposentadoria", "Aposentadoria", "AposentadoriaTransicao1", "AposentadoriaTransicao1"]);
            $tabela->set_metodo([null, "get_nomeECargoELotacao", "get_dtIngresso", null, "getTempoContribuicao", "get_tempoPublicoIninterrupto", "get_tempoServicoUenf", "get_tempoServicoUenf", "getDataAposentadoria", "getDiasFaltantes"]);
            #$tabela->set_funcao([null, null, "date_to_php"]);
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editartransicao1');

            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 9,
                    'valor' => '0',
                    'operador' => '=',
                    'id' => 'pode'),
                array('coluna' => 9,
                    'valor' => '---',
                    'operador' => '=',
                    'id' => 'naoPode'),
            ));
            $tabela->show();

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

        ################################################################

        case "editartransicao1" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?fase=transicao1');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        ################################################################

        /*
         * EC nº 47/2005
         */

        case "transicao2" :

            $grid2 = new Grid();
            $grid2->abreColuna(12, 3);

            $painel = new Callout();
            $painel->abre();

            $aposentadoria->exibeMenu(11);

            $painel->fecha();

            $permanente = new AposentadoriaTransicao2();
            $permanente->exibeRegras();

            $grid2->fechaColuna();
            $grid2->abreColuna(12, 9);

            # Formulário de Pesquisa
            $form = new Form('?fase=transicao1');

            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
            array_unshift($result, array("Todos", 'Todas'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(12);
            $form->add_item($controle);
            $form->show();

            # Exibe a lista
            $select = "SELECT idFuncional, 
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          TIMESTAMPDIFF(YEAR,tbpessoa.dtNasc,CURDATE()),
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.idServidor
                     FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                     JOIN tbhistlot USING (idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND idPerfil = 1";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }


            $select .= " ORDER BY tbpessoa.nome";

            $result = $pessoal->select($select);
            $count = $pessoal->count($select);

            tituloTable("ART. 3º. DA EC Nº. 47/2005");
            callout("É concedido aos servidores que ingressaram no serviço público até 16 de dezembro de 1998.");

            $titulo = null;

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Servidor', 'Ingresso', "Idade", "Tempo de Contribuição<br/>(dias)", "Serviço Público<br/>(dias)", "Carreira<br/>(dias)", "Cargo Efetivo<br/>(dias)","Aposenta em:", "Faltam<br/>(dias)"]);
            $tabela->set_align(['center', 'left', 'center']);
            #$tabela->set_width([10, 20, 10, 10, 10, 10, 10, 10, 10]);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, "Pessoal", "Aposentadoria", null, "AposentadoriaTransicao2", "Aposentadoria", "Aposentadoria", "Aposentadoria", "AposentadoriaTransicao2", "AposentadoriaTransicao2"]);
            $tabela->set_metodo([null, "get_nomeECargoELotacao", "get_dtIngresso", null, "getTempoContribuicao", "get_tempoPublicoIninterrupto", "get_tempoServicoUenf", "get_tempoServicoUenf", "getDataAposentadoria", "getDiasFaltantes"]);
            #$tabela->set_funcao([null, null, "date_to_php"]);
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editartransicao2');

            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 9,
                    'valor' => '0',
                    'operador' => '=',
                    'id' => 'pode'),
                array('coluna' => 9,
                    'valor' => '---',
                    'operador' => '=',
                    'id' => 'naoPode'),
            ));
            $tabela->show();

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

        ################################################################

        case "editartransicao2" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?fase=transicao2');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        ################################################################

    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
    