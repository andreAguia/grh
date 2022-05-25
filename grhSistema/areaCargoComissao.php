<?php

/**
 * Estatística
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
    $comissao = new CargoComissao();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de cargo em comissão";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # Verifica a fase do programa
    $fase = get('fase', 'inicial');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Verifica o post de quando exibe os histórico de servidores nesse cargo
    $parametroCargo = get('parametroCargo', get_session('parametroCargo', 13));
    $parametroDescricao = post('parametroDescricao', get_session('parametroDescricao', 'Todos'));
    $parametroStatus = post('parametroStatus', get_session('parametroStatus', "Vigente"));
    $parametroAno = post('parametroAno', get_session('parametroAno', date("Y")));
    $parametroMes = post('parametroMes', get_session('parametroMes', date('m')));

    # Garante que quando se muda de cargo a descrição volta a todos
    if ($parametroCargo <> get_session('parametroCargo')) {
        $parametroDescricao = "Todos";
    }

    # Joga os parâmetros par as sessions
    set_session('parametroCargo', $parametroCargo);
    set_session('parametroDescricao', $parametroDescricao);
    set_session('parametroStatus', $parametroStatus);
    set_session('parametroAno', $parametroAno);
    set_session('parametroMes', $parametroMes);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Limita o tamanho da tela
    $grid1 = new Grid();
    $grid1->abreColuna(12);

    # Cria um menu
    $menu1 = new MenuBar();

    # Voltar
    if ($fase == "inicial") {
        $linkVoltar = new Link("Voltar", "grh.php");
    } else {
        $linkVoltar = new Link("Voltar", "?");
    }
    $linkVoltar->set_class('button');
    $linkVoltar->set_title('Voltar para página anterior');
    $linkVoltar->set_accessKey('V');
    $menu1->add_link($linkVoltar, "left");

    # Cadastro de Cargos em Comissão
    $botao = new Button('Cargos');
    $botao->set_title('Acessa o Cadastro de Cargos em Comissão');
    $botao->set_url("cadastroCargoComissao.php");
    $menu1->add_link($botao, "right");

    # Cadastro de Descrição
    $botao = new Button('Descrição');
    $botao->set_title('Acessa o Cadastro de Descrição dos Cargos em Comissão');
    $botao->set_url("cadastroDescricaoComissao.php");
    $menu1->add_link($botao, "right");

    $menu1->show();

    titulo("Cargo em Comissão");
    br();

    switch ($fase) {
        case "inicial":
            $grid = new Grid();

            ####################################
            ## Menu Lateral
            ####################################

            $grid->abreColuna(12, 3);

            # Pega o nome do cargo
            $nomeCargo = $pessoal->get_nomeCargoComissao($parametroCargo);
            $simbolo = $pessoal->get_cargoComissaoSimbolo($parametroCargo);

            $painel = new Callout();
            $painel->abre();

            # Pega os cargos
            $select = "SELECT idTipoComissao,
                                  descricao,
                                  simbolo,
                                  valSal
                             FROM tbtipocomissao
                            WHERE ativo = true
                         ORDER BY simbolo, descricao";
            $row = $pessoal->select($select);

            # Inicia o Menu de Cargos
            titulo("Menu");

            $menu = new Menu("menuProcedimentos");
            $menu->add_item('titulo', 'Cargos em Comissão');

            # Preenche com os cargos
            foreach ($row as $item) {
                if ($parametroCargo == $item[0]) {
                    $menu->add_item('link', '<b>' . $item[2] . ' - ' . $item[1] . '</b>', '?fase=inicial&parametroCargo=' . $item[0]);
                } else {
                    $menu->add_item('link', $item[2] . ' - ' . $item[1], '?fase=inicial&parametroCargo=' . $item[0]);
                }
            }

            $menu->add_item("titulo", "Movimentação Mensal");
            $menu->add_item("link", "Por Nomeação/Exoneração", "?fase=movimentacaoPorNomExo", "Movimentação Mensal por Data de Nomeações & Exonerações");
            $menu->add_item("link", "Por Data da Publicação", "?fase=movimentacaoPorPublicacao", "Movimentação Mensal por Data da Publicação");
            $menu->add_item("link", "Por Data do Ato do Reitor", "?fase=movimentacaoPorAto", "Movimentação Mensal por Data do Ato do Reitor");

            $menu->add_item('titulo', 'Relatórios');
            $menu->add_item('linkWindow', 'Cargo Vigente e Anterior', '../grhRelatorios/cargoComissaoVigenteEAnterior.php');
            $menu->add_item('linkWindow', 'Planilhão Histórico', '../grhRelatorios/cargoComissaoPlanilhaoHistorico.php');
            $menu->add_item('linkWindow', 'Planilhão Vigente', '../grhRelatorios/cargoComissaoPlanilhaoVigente.php');
            $menu->show();

            $painel->fecha();

            $grid->fechaColuna();

            ####################################
            ## Área central de conteúdo
            ####################################

            $grid->abreColuna(12, 9);

            # Informa a origem
            set_session('origem', 'areaCargoComissao.php');

            $form = new Form('?');

            $select = 'SELECT idDescricaoComissao,
                              tbdescricaocomissao.descricao
                         FROM tbdescricaocomissao JOIN tbtipocomissao USING (idTipoComissao)
                        WHERE tbdescricaocomissao.idTipoComissao = ' . $parametroCargo . '
                     ORDER BY tbtipocomissao.simbolo, tbtipocomissao.descricao, tbdescricaocomissao.descricao';

            $descricao = $pessoal->select($select);

            array_unshift($descricao, array("Todos", "Todos"));

            # Descrição
            $controle = new Input('parametroDescricao', 'combo', 'Descrição:', 1);
            $controle->set_size(200);
            $controle->set_title('Filtra por Descrição');
            $controle->set_array($descricao);
            $controle->set_valor($parametroDescricao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_autofocus(true);
            $controle->set_col(9);
            $form->add_item($controle);

            # Status
            $controle = new Input('parametroStatus', 'combo', 'Status', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Status');
            $controle->set_array(array("Vigente", "Todos"));
            $controle->set_valor($parametroStatus);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            $form->add_item($controle);

            $form->show();

            $comissao->exibeResumo($parametroCargo);
            
            # Exibe observação (quando tiver)
            $obs = $comissao->get_obs($parametroCargo);
            
            if(!is_null($obs)){
                callout($obs);
            }

            # select
            $select = "SELECT tbcomissao.idComissao,
                              tbcomissao.idComissao,
                              tbcomissao.idComissao,
                              tbcomissao.idComissao,
                              tbcomissao.idComissao,
                              tbcomissao.dtExo
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         LEFT JOIN tbcomissao USING(idServidor)
                                         LEFT JOIN tbdescricaocomissao USING (idDescricaoComissao)
                                              JOIN tbtipocomissao ON(tbcomissao.idTipoComissao=tbtipocomissao.idTipoComissao)
                       WHERE tbtipocomissao.idTipoComissao = $parametroCargo";

            if ($parametroDescricao <> "Todos") {
                $select .= " AND tbcomissao.idDescricaoComissao = $parametroDescricao";
            }

            if ($parametroStatus == "Vigente") {
                $select .= " AND (tbcomissao.dtExo IS null OR CURDATE() < tbcomissao.dtExo)
                        ORDER BY tbpessoa.nome, tbdescricaocomissao.descricao, tbcomissao.dtNom desc";
            } else {
                $select .= " ORDER BY tbdescricaocomissao.descricao, tbcomissao.dtNom desc";
            }

            $result = $pessoal->select($select);
            $label = array('Nome', 'Nomeação', 'Exoneração', 'Descrição', 'Ocupante Anterior');
            $align = array("left", "left", "left", "left", "left");
            $function = array(null, null, null, "descricaoComissao");
            $classe = array("CargoComissao", "CargoComissao", "CargoComissao", null, "CargoComissao");
            $metodo = array("get_nomeECargoSimplesEPerfil", "exibeDadosNomeacao", "exibeDadosExoneracao", null, "exibeOcupanteAnterior");

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label($label);
            $tabela->set_titulo("Servidores Nomeados");
            $tabela->set_width([20, 20, 20, 20, 20]);
            $tabela->set_align($align);
            $tabela->set_funcao($function);
            $tabela->set_classe($classe);
            $tabela->set_metodo($metodo);
            $tabela->set_idCampo('idComissao');
            $tabela->set_editar('?fase=editarCargo');
            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 2,
                    'operador' => 'is_null',
                    'id' => 'vigente')));
            $tabela->show();
            break;

        ################################################################

        case "movimentacaoPorNomExo":

            # Informa a origem
            set_session('origem', 'areaCargoComissao.php?fase=movimentacaoPorNomExo');

            $form = new Form('?fase=movimentacaoPorNomExo');
            $controle = new Input('parametroAno', 'texto', 'Ano:', 1);
            $controle->set_size(8);
            $controle->set_title('Filtra pelo Ano');
            $controle->set_valor($parametroAno);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_autofocus(true);
            $controle->set_col(2);
            $form->add_item($controle);

            $controle = new Input('parametroMes', 'combo', 'Mês:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra pelo Mês');
            $controle->set_array($mes);
            $controle->set_valor($parametroMes);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            $form->add_item($controle);

            $form->show();

            # Nomeações
            $select = 'SELECT * FROM
                      (SELECT "Nomeação",
                              tbcomissao.dtNom,
                              tbcomissao.dtPublicNom,
                              tbcomissao.dtAtoNom,
                              tbservidor.idFuncional,
                              tbpessoa.nome,
                              CONCAT(simbolo," - ",tbtipocomissao.descricao),
                              tbcomissao.numProcNom,
                              tbcomissao.idComissao
                         FROM tbcomissao JOIN tbtipocomissao USING (idTipoComissao)
                                         JOIN tbservidor USING (idServidor)
                                         JOIN tbpessoa USING (idPessoa)
                         WHERE YEAR(tbcomissao.dtNom) = ' . $parametroAno . '
                           AND MONTH(tbcomissao.dtNom) = ' . $parametroMes . '
                     UNION
                     SELECT "Exoneração",
                              tbcomissao.dtExo,
                              tbcomissao.dtPublicExo,
                              tbcomissao.dtAtoExo,
                              tbservidor.idFuncional,
                              tbpessoa.nome,
                              CONCAT(simbolo," - ",tbtipocomissao.descricao),
                              tbcomissao.numProcExo,
                              tbcomissao.idComissao
                         FROM tbcomissao JOIN tbtipocomissao USING (idTipoComissao)
                                         JOIN tbservidor USING (idServidor)
                                         JOIN tbpessoa USING (idPessoa)
                         WHERE YEAR(tbcomissao.dtExo) = ' . $parametroAno . '
                           AND MONTH(tbcomissao.dtExo) = ' . $parametroMes . ') a
                     ORDER BY 2, 6, 1 asc';

            $result = $pessoal->select($select);
            $label = array('Tipo', 'Data', 'Publicação', 'Ato Reitor', 'Id Funcional', 'Nome', 'Cargo', 'Processo');
            $align = array("center", "center", "center", "center", "center", "left", "left", "left");
            $function = array(null, "date_to_php", "date_to_php", "date_to_php");

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label($label);
            $tabela->set_titulo("Nomeações & Exonerações de " . get_nomeMes($parametroMes) . " de $parametroAno");
            $tabela->set_align($align);
            $tabela->set_funcao($function);
            $tabela->set_idCampo('idComissao');
            $tabela->set_editar('?fase=editarCargo');
            $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
                    'valor' => "Exoneração",
                    'operador' => '=',
                    'id' => "comissaoVagasNegativas"),
                array('coluna' => 0,
                    'valor' => "Nomeação",
                    'operador' => '=',
                    'id' => "comissaoComVagas")));

            $tabela->show();
            break;

        ################################################################

        case "movimentacaoPorPublicacao":

            # Informa a origem
            set_session('origem', 'areaCargoComissao.php?fase=movimentacaoPorPublicacao');

            $form = new Form('?fase=movimentacaoPorPublicacao');
            $controle = new Input('parametroAno', 'texto', 'Ano:', 1);
            $controle->set_size(8);
            $controle->set_title('Filtra pelo Ano');
            $controle->set_valor($parametroAno);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_autofocus(true);
            $controle->set_col(2);
            $form->add_item($controle);

            $controle = new Input('parametroMes', 'combo', 'Mês:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra pelo Mês');
            $controle->set_array($mes);
            $controle->set_valor($parametroMes);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            $form->add_item($controle);

            $form->show();

            # Verifica as publicações desse mês
            $publicMes = "SELECT * FROM
                          (SELECT tbcomissao.dtPublicNom FROM tbcomissao WHERE YEAR(tbcomissao.dtPublicNom) = $parametroAno AND MONTH(tbcomissao.dtPublicNom) = $parametroMes
                           UNION
                          SELECT tbcomissao.dtPublicExo FROM tbcomissao WHERE YEAR(tbcomissao.dtPublicExo) = $parametroAno AND MONTH(tbcomissao.dtPublicExo) = $parametroMes) b
                           ORDER BY 1";

            $result1 = $pessoal->select($publicMes);

            foreach ($result1 as $dd) {

                # Nomeações
                $select = 'SELECT * FROM
                          (SELECT "Nomeação",
                                  tbcomissao.dtPublicNom,
                                  tbcomissao.dtAtoNom,
                                  tbcomissao.dtNom,
                                  tbservidor.idFuncional,
                                  tbpessoa.nome,
                                  CONCAT(simbolo," - ",tbtipocomissao.descricao),
                                  tbcomissao.numProcNom,
                                  tbcomissao.idComissao
                             FROM tbcomissao JOIN tbtipocomissao USING (idTipoComissao)
                                             JOIN tbservidor USING (idServidor)
                                             JOIN tbpessoa USING (idPessoa)
                             WHERE tbcomissao.dtPublicNom = "' . $dd[0] . '"
                         UNION
                         SELECT "Exoneração",
                                  tbcomissao.dtPublicExo,
                                  tbcomissao.dtAtoExo,
                                  tbcomissao.dtExo,
                                  tbservidor.idFuncional,
                                  tbpessoa.nome,
                                  CONCAT(simbolo," - ",tbtipocomissao.descricao),
                                  tbcomissao.numProcExo,
                                  tbcomissao.idComissao
                             FROM tbcomissao JOIN tbtipocomissao USING (idTipoComissao)
                                             JOIN tbservidor USING (idServidor)
                                             JOIN tbpessoa USING (idPessoa)
                             WHERE tbcomissao.dtPublicExo = "' . $dd[0] . '") a
                         ORDER BY 1, 6';

                $result = $pessoal->select($select);
                $label = array('Tipo', 'Publicação', 'Ato Reitor', 'Data', 'Id Funcional', 'Nome', 'Cargo', 'Processo');
                $align = array("center", "center", "center", "center", "center", "left", "left", "left");
                $function = array(null, "date_to_php", "date_to_php", "date_to_php");

                # Monta a tabela
                $tabela = new Tabela();
                $tabela->set_conteudo($result);
                $tabela->set_label($label);
                $tabela->set_titulo("Publicadas em " . date_to_php($dd[0]));
                $tabela->set_align($align);
                $tabela->set_funcao($function);
                $tabela->set_idCampo('idComissao');
                $tabela->set_editar('?fase=editarCargo');
                $tabela->set_formatacaoCondicional(array(
                    array('coluna' => 0,
                        'valor' => "Exoneração",
                        'operador' => '=',
                        'id' => "comissaoVagasNegativas"),
                    array('coluna' => 0,
                        'valor' => "Nomeação",
                        'operador' => '=',
                        'id' => "comissaoComVagas")));
                $tabela->set_rowspan(0);
                $tabela->set_grupoCorColuna(0);
                $tabela->show();
            }

            /*
              # Nomeações
              $select = 'SELECT * FROM
              (SELECT "Nomeação",
              tbcomissao.dtPublicNom,
              tbcomissao.dtAtoNom,
              tbcomissao.dtNom,
              tbservidor.idFuncional,
              tbpessoa.nome,
              CONCAT(simbolo," - ",tbtipocomissao.descricao),
              tbcomissao.numProcNom,
              tbcomissao.idComissao
              FROM tbcomissao JOIN tbtipocomissao USING (idTipoComissao)
              JOIN tbservidor USING (idServidor)
              JOIN tbpessoa USING (idPessoa)
              WHERE YEAR(tbcomissao.dtPublicNom) = '.$parametroAno.'
              AND MONTH(tbcomissao.dtPublicNom) = '.$parametroMes.'
              UNION
              SELECT "Exoneração",
              tbcomissao.dtPublicExo,
              tbcomissao.dtAtoExo,
              tbcomissao.dtExo,
              tbservidor.idFuncional,
              tbpessoa.nome,
              CONCAT(simbolo," - ",tbtipocomissao.descricao),
              tbcomissao.numProcExo,
              tbcomissao.idComissao
              FROM tbcomissao JOIN tbtipocomissao USING (idTipoComissao)
              JOIN tbservidor USING (idServidor)
              JOIN tbpessoa USING (idPessoa)
              WHERE YEAR(tbcomissao.dtPublicExo) = '.$parametroAno.'
              AND MONTH(tbcomissao.dtPublicExo) = '.$parametroMes.') a
              ORDER BY 2, 3, 6';

              $result = $pessoal->select($select);
              $label = array('Tipo','Publicação','Ato Reitor','Data','Id Funcional','Nome','Cargo','Processo');
              $align = array("center","center","center","center","center","left","left","left");
              $function = array(null,"date_to_php","date_to_php","date_to_php");

              # Monta a tabela
              $tabela = new Tabela();
              $tabela->set_conteudo($result);
              $tabela->set_label($label);
              $tabela->set_titulo("Nomeações & Exonerações Publicadas em ".get_nomeMes($parametroMes)." de $parametroAno");
              $tabela->set_align($align);
              $tabela->set_funcao($function);
              $tabela->set_idCampo('idComissao');
              $tabela->set_editar('?fase=editarCargo');
              $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
              'valor' => "Exoneração",
              'operador' => '=',
              'id' => "comissaoVagasNegativas"),
              array('coluna' => 0,
              'valor' => "Nomeação",
              'operador' => '=',
              'id' => "comissaoComVagas")));

              $tabela->show();
             *
             */
            break;

        ################################################################

        case "movimentacaoPorAto":

            # Informa a origem
            set_session('origem', 'areaCargoComissao.php?fase=movimentacaoPorAto');

            $form = new Form('?fase=movimentacaoPorAto');
            $controle = new Input('parametroAno', 'texto', 'Ano:', 1);
            $controle->set_size(8);
            $controle->set_title('Filtra pelo Ano');
            $controle->set_valor($parametroAno);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_autofocus(true);
            $controle->set_col(2);
            $form->add_item($controle);

            $controle = new Input('parametroMes', 'combo', 'Mês:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra pelo Mês');
            $controle->set_array($mes);
            $controle->set_valor($parametroMes);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            $form->add_item($controle);

            $form->show();

            # Verifica as publicações desse mês
            $publicMes = "SELECT * FROM
                          (SELECT tbcomissao.dtAtoNom FROM tbcomissao WHERE YEAR(tbcomissao.dtAtoNom) = $parametroAno AND MONTH(tbcomissao.dtAtoNom) = $parametroMes
                           UNION
                          SELECT tbcomissao.dtAtoExo FROM tbcomissao WHERE YEAR(tbcomissao.dtAtoExo) = $parametroAno AND MONTH(tbcomissao.dtAtoExo) = $parametroMes) b
                           ORDER BY 1";

            $result1 = $pessoal->select($publicMes);

            foreach ($result1 as $dd) {

                # Nomeações
                $select = 'SELECT * FROM
                          (SELECT "Nomeação",
                                  tbcomissao.dtPublicNom,
                                  tbcomissao.dtAtoNom,
                                  tbcomissao.dtNom,
                                  tbservidor.idFuncional,
                                  tbpessoa.nome,
                                  CONCAT(simbolo," - ",tbtipocomissao.descricao),
                                  tbcomissao.numProcNom,
                                  tbcomissao.idComissao
                             FROM tbcomissao JOIN tbtipocomissao USING (idTipoComissao)
                                             JOIN tbservidor USING (idServidor)
                                             JOIN tbpessoa USING (idPessoa)
                             WHERE tbcomissao.dtAtoNom = "' . $dd[0] . '"
                         UNION
                         SELECT "Exoneração",
                                  tbcomissao.dtPublicExo,
                                  tbcomissao.dtAtoExo,
                                  tbcomissao.dtExo,
                                  tbservidor.idFuncional,
                                  tbpessoa.nome,
                                  CONCAT(simbolo," - ",tbtipocomissao.descricao),
                                  tbcomissao.numProcExo,
                                  tbcomissao.idComissao
                             FROM tbcomissao JOIN tbtipocomissao USING (idTipoComissao)
                                             JOIN tbservidor USING (idServidor)
                                             JOIN tbpessoa USING (idPessoa)
                             WHERE tbcomissao.dtAtoExo = "' . $dd[0] . '") a
                         ORDER BY 1,6';

                $result = $pessoal->select($select);
                $label = array('Tipo', 'Publicação', 'Ato Reitor', 'Data', 'Id Funcional', 'Nome', 'Cargo', 'Processo');
                $align = array("center", "center", "center", "center", "center", "left", "left", "left");
                $function = array(null, "date_to_php", "date_to_php", "date_to_php");

                # Monta a tabela
                $tabela = new Tabela();
                $tabela->set_conteudo($result);
                $tabela->set_label($label);
                $tabela->set_titulo("Ato do Reitor de " . date_to_php($dd[0]));
                $tabela->set_align($align);
                $tabela->set_funcao($function);
                $tabela->set_idCampo('idComissao');
                $tabela->set_editar('?fase=editarCargo');
                $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
                        'valor' => "Exoneração",
                        'operador' => '=',
                        'id' => "comissaoVagasNegativas"),
                    array('coluna' => 0,
                        'valor' => "Nomeação",
                        'operador' => '=',
                        'id' => "comissaoComVagas")));
                $tabela->set_rowspan(0);
                $tabela->set_grupoCorColuna(0);
                $tabela->show();
            }

            /*
              # Nomeações
              $select = 'SELECT * FROM
              (SELECT "Nomeação",
              tbcomissao.dtPublicNom,
              tbcomissao.dtAtoNom,
              tbcomissao.dtNom,
              tbservidor.idFuncional,
              tbpessoa.nome,
              CONCAT(simbolo," - ",tbtipocomissao.descricao),
              tbcomissao.numProcNom,
              tbcomissao.idComissao
              FROM tbcomissao JOIN tbtipocomissao USING (idTipoComissao)
              JOIN tbservidor USING (idServidor)
              JOIN tbpessoa USING (idPessoa)
              WHERE YEAR(tbcomissao.dtPublicNom) = '.$parametroAno.'
              AND MONTH(tbcomissao.dtPublicNom) = '.$parametroMes.'
              UNION
              SELECT "Exoneração",
              tbcomissao.dtPublicExo,
              tbcomissao.dtAtoExo,
              tbcomissao.dtExo,
              tbservidor.idFuncional,
              tbpessoa.nome,
              CONCAT(simbolo," - ",tbtipocomissao.descricao),
              tbcomissao.numProcExo,
              tbcomissao.idComissao
              FROM tbcomissao JOIN tbtipocomissao USING (idTipoComissao)
              JOIN tbservidor USING (idServidor)
              JOIN tbpessoa USING (idPessoa)
              WHERE YEAR(tbcomissao.dtPublicExo) = '.$parametroAno.'
              AND MONTH(tbcomissao.dtPublicExo) = '.$parametroMes.') a
              ORDER BY 2, 3, 6';

              $result = $pessoal->select($select);
              $label = array('Tipo','Publicação','Ato Reitor','Data','Id Funcional','Nome','Cargo','Processo');
              $align = array("center","center","center","center","center","left","left","left");
              $function = array(null,"date_to_php","date_to_php","date_to_php");

              # Monta a tabela
              $tabela = new Tabela();
              $tabela->set_conteudo($result);
              $tabela->set_label($label);
              $tabela->set_titulo("Nomeações & Exonerações Publicadas em ".get_nomeMes($parametroMes)." de $parametroAno");
              $tabela->set_align($align);
              $tabela->set_funcao($function);
              $tabela->set_idCampo('idComissao');
              $tabela->set_editar('?fase=editarCargo');
              $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
              'valor' => "Exoneração",
              'operador' => '=',
              'id' => "comissaoVagasNegativas"),
              array('coluna' => 0,
              'valor' => "Nomeação",
              'operador' => '=',
              'id' => "comissaoComVagas")));

              $tabela->show();
             *
             */
            break;

        ################################################################

        case "editarCargo" :
            # Vigentes
            br(8);
            aguarde();

            $dados = $comissao->get_dados($id);
            $idServidor = $dados["idServidor"];

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $idServidor);

            # Carrega a página específica
            loadPage('servidorComissao.php?fase=editar&id=' . $id);
            break;

        ################################################################
    }

    # Fecha o grid
    $grid1->fechaColuna();
    $grid1->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
