<?php

/**
 * Área de Abono Permanência
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

    # Verifica a fase do programa
    $fase = get('fase');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a área de abono permanência";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # Começa uma nova página
    $page = new Page();
    if ($fase == "ci") {
        $page->set_ready("CKEDITOR.replace('textoCi',{height: 360});");
    }
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Pega os parâmetros
    $parametroSexo = post('parametroSexo', get_session('parametroSexo', "Feminino"));
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao'));
    $parametroCargo = post('parametroCargo', get_session('parametroCargo'));
    $parametroProcesso = post('parametroProcesso', get_session('parametroProcesso'));

    # Joga os parâmetros par as sessions
    set_session('parametroSexo', $parametroSexo);
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroCargo', $parametroCargo);
    set_session('parametroProcesso', $parametroProcesso);

    # Pega as idades de aposentadoria
    if ($parametroSexo == "Feminino") {
        $idadeAposent = $intra->get_variavel("aposentadoria.integral.idade.feminino");
    } else {
        $idadeAposent = $intra->get_variavel("aposentadoria.integral.idade.masculino");
    }

    $grid = new Grid();
    $grid->abreColuna(12);

    if ($fase <> "ci") {
        # Cria um menu
        $menu = new MenuBar();

        # Voltar
        $botaoVoltar = new Link("Voltar", "grh.php");
        $botaoVoltar->set_class('button');
        $botaoVoltar->set_title('Voltar a página anterior');
        $botaoVoltar->set_accessKey('V');
        $menu->add_link($botaoVoltar, "left");

        if ($parametroProcesso == 3 AND $parametroLotacao <> "*") {
            # ci
            $botaoci = new Link("CI", "?fase=ci");
            $botaoci->set_target("_blank");
            $botaoci->set_class('button');
            $botaoci->set_title('CI dos servidores que NÃO solicitaram abono');
            $menu->add_link($botaoci, "right");
        }

        $menu->show();
    } else {
        # Titulo
        br();
        titulo("CI dos Servidores que NÃO Solicitaram Abono Permanência");
        br();
    }

    switch ($fase) {

        ################################################################

        case "" :

            br(5);
            aguarde("Calculando ...");

            loadPage('?fase=listaAbono');
            break;

        ################################################################

        case "listaAbono" :

            $grid->fechaColuna();
            $grid->abreColuna(8);

            # Formulário de Pesquisa
            $form = new Form('?');

            /*
             *  Lotação
             */
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
            array_unshift($result, array("*", 'Todas'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(8);
            $form->add_item($controle);

            /*
             * Sexo
             */

            $controle = new Input('parametroSexo', 'combo', 'Sexo:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Sexo');
            $controle->set_array([["Feminino", "Feminino"], ["Masculino", "Masculino"]]);
            $controle->set_valor($parametroSexo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);

            /*
             *  Cargos
             */
            $result1 = $pessoal->select('SELECT tbcargo.idCargo, 
                                                concat(tbtipocargo.cargo," - ",tbarea.area," - ",tbcargo.nome) as cargo
                                           FROM tbcargo LEFT JOIN tbtipocargo USING (idTipoCargo)
                                                        LEFT JOIN tbarea USING (idArea)    
                                        ORDER BY 2');

            # cargos por nivel
            $result2 = $pessoal->select('SELECT cargo,cargo FROM tbtipocargo WHERE cargo <> "Professor Associado" AND cargo <> "Professor Titular" ORDER BY 2');

            # junta os dois
            $result = array_merge($result2, $result1);

            # acrescenta Professor
            array_unshift($result, array('Professor', 'Professores'));

            # acrescenta todos
            array_unshift($result, array('*', '-- Todos --'));

            $controle = new Input('parametroCargo', 'combo', 'Cargo - Área - Função:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Cargo');
            $controle->set_array($result);
            $controle->set_valor($parametroCargo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(2);
            $controle->set_col(8);
            $form->add_item($controle);

            /*
             * Processo
             */

            $controle = new Input('parametroProcesso', 'combo', 'Processo:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Processo de Solicitação de Abono');
            $controle->set_array([
                ["*", "Todos"],
                [1, "Deferido"],
                [2, "Indeferido"],
                [3, "Não Solicitado"]
            ]);
            $controle->set_valor($parametroProcesso);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(2);
            $controle->set_col(4);
            $form->add_item($controle);

            $form->show();

            $grid->fechaColuna();
            $grid->abreColuna(4);

            $aposentadoria->exibeRegrasIntegral();

            $grid->fechaColuna();
            $grid->abreColuna(12);

            if ($parametroLotacao == "*") {
                $parametroLotacao = null;
            }

            if ($parametroCargo == "*") {
                $parametroCargo = null;
            }

            if ($parametroProcesso == "*") {
                $parametroProcesso = null;
            }

            # Exibe a lista
            $select = "SELECT idFuncional,
                              dtAdmissao,
                              TIMESTAMPDIFF(YEAR, dtNasc, NOW()) AS idade,
                              idServidor,
                              CASE
                                    WHEN status = 1 THEN 'Deferido'
                                    WHEN status = 2 THEN 'Indeferido'
                                    ELSE 'Não Solicitado'
                              END as status
                      FROM tbservidor LEFT JOIN tbpessoa USING(idPessoa)
                                      LEFT JOIN tbhistlot USING (idServidor)
                                           JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                      LEFT JOIN tbcargo ON (tbservidor.idCargo = tbcargo.idCargo)
                                      LEFT JOIN tbtipocargo ON (tbcargo.idTipoCargo = tbtipocargo.idTipoCargo)
                                      LEFT JOIN tbabono USING (idServidor)
                     WHERE tbservidor.situacao = 1
                       AND idPerfil = 1
                       AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                       AND tbpessoa.sexo = '{$parametroSexo}'
                       AND TIMESTAMPDIFF(YEAR, dtNasc, NOW()) >= {$idadeAposent}";

            # lotação
            if (!is_null($parametroLotacao)) {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            # cargo
            if (!is_null($parametroCargo)) {
                if (is_numeric($parametroCargo)) {
                    $select .= " AND (tbcargo.idcargo = '{$parametroCargo}')";
                } else { # senão é nivel do cargo
                    if ($parametroCargo == "Professor") {
                        $select .= " AND (tbcargo.idcargo = 128 OR  tbcargo.idcargo = 129)";
                    } else {
                        $select .= " AND (tbtipocargo.cargo = '{$parametroCargo}')";
                    }
                }
            }

            # Solicitação de abono
            if (!is_null($parametroProcesso)) {
                if ($parametroProcesso == 3) {
                    $select .= " AND (status is null)";
                } else {
                    $select .= " AND (status = '{$parametroProcesso}')";
                }
            }

            $select .= " ORDER BY idade";

            $result = $pessoal->select($select);
            $resultado = [];

            # Percorre o banco para verificar se já pode aposentar
            foreach ($result as $lista) {

                # Pega a data de aposentadoria desse servidor
                $data = $aposentadoria->get_dataAposentadoriaIntegral($lista["idServidor"]);

                # Verifica se a data colhida já passou
                if (jaPassou($data)) {
                    $resultado[] = [
                        $lista["idFuncional"],
                        $lista["idServidor"],
                        $lista["idServidor"],
                        $lista["dtAdmissao"],
                        $lista["idade"],
                        $aposentadoria->get_tempoServicoTotal($lista["idServidor"]), // tempo total
                        dias_to_diasMesAno($aposentadoria->get_tempoServicoUenf($lista["idServidor"])), // tempo no cargo
                        $data,
                        $lista["status"],
                        $lista["idServidor"]
                    ];
                }
            }

            # Tabela com os valores de aposentadoria
            $tabela = new Tabela();
            $tabela->set_titulo("Servidores Ativos com Direito a Abono Permanência");
            $tabela->set_label(['idFuncional', 'Servidor', 'Cargo', 'Admissão', 'Idade', 'Tempo de Serviço', 'Tempo no Cargo', 'a partir de:', 'Solicitação', 'Editar']);
            $tabela->set_align(['center', 'left', 'left']);
            $tabela->set_conteudo($resultado);
            $tabela->set_classe([null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, "get_nomeECargo", "get_lotacao"]);
            $tabela->set_funcao([null, null, null, "date_to_php"]);

            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 8,
                    'valor' => "Deferido",
                    'operador' => '=',
                    'id' => 'deferido'),
                array('coluna' => 8,
                    'valor' => "Indeferido",
                    'operador' => '=',
                    'id' => 'indeferido'),
                array('coluna' => 8,
                    'valor' => "Não Solicitado",
                    'operador' => '=',
                    'id' => 'ns')
            ));

            # Aposentadoria integral
            $servidorBtn = new Link(null, "?fase=editaServidor&id=");
            $servidorBtn->set_imagem(PASTA_FIGURAS_GERAIS . 'bullet_edit.png', 20, 20);
            $servidorBtn->set_title("Vai para o cadastro do servidor");

            # Coloca os links na tabela			
            $tabela->set_link([null, null, null, null, null, null, null, null, null, $servidorBtn]);
            $tabela->show();
            break;

        ################################################################

        case "editaServidor" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAbonoPermanencia.php');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        ################################################################

        case "ci" :

            $abono = new Abono();

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
            $form = new Form("../grhRelatorios/ciAbono.php");

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
            $controle->set_size([90, 95]);
            $controle->set_title('Texto da CI');
            $controle->set_valor($abono->get_textoCi());
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
    }
    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
