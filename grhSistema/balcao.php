<?php

/**
 * Cadastro de Atendimento no balcão
 *
 * By Alat
 */
# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 8, 12]);

if ($acesso) {

    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();

    # Verifica a fase do programa
    $fase = get('fase', 'lista');
    $editar = get('editar', 0);

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o controle de atendimento do balcão";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega o mes e o ano
    $parametroAno = post('parametroAno', get_session('parametroAno', date('Y')));
    $parametroMes = post('parametroMes', get_session('parametroMes', date('m')));

    # Joga os parâmetros par as sessions
    set_session('parametroAno', $parametroAno);
    set_session('parametroMes', $parametroMes);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    $grid1 = new Grid();
    $grid1->abreColuna(12);

    # Pega o id do Servidor logado
    $idservidorLogado = $intra->get_idServidor($idUsuario);

#########################################################################################################################

    switch ($fase) {
        # Exibe o Menu Inicial
        case "lista" :
            # Editar ou salvar
            if ($editar == 1) {
                echo '<form method=post id="formPadrao" name="formPadrao" action="?fase=valida">';

                # Cria um menu
                $menu1 = new MenuBar();

                # Sair da Área do Servidor
                $linkVoltar = new Link("Não Salvar", "?");
                $linkVoltar->set_class('button');
                $linkVoltar->set_title('Volta Sem Salvar');
                $menu1->add_link($linkVoltar, "left");

                # Salvar
                $linkEditar = new Input("Editar", "submit");
                $linkEditar->set_valor('Salvar');
                $menu1->add_link($linkEditar, "right");

                $menu1->show();
            } else {
                # Cria um menu
                $menu1 = new MenuBar();

                # Voltar
                $linkVoltar = new Link("Voltar", "grh.php");
                $linkVoltar->set_class('button');
                $linkVoltar->set_title('Voltar');
                $menu1->add_link($linkVoltar, "left");

                if (Verifica::acesso($idUsuario, [1, 8])) {
                    # Servidores
                    $linkServ = new Link("Servidores", "?fase=servidores");
                    $linkServ->set_class('button');
                    $linkServ->set_title('Informa os servidores que entram no rodizio de atendimento');
                    $menu1->add_link($linkServ, "right");

                    # Editar
                    $linkEditar = new Link("Editar", "?editar=1");
                    $linkEditar->set_class('button');
                    $linkEditar->set_title('Informa entre os servidores do rodizio o dia de atendimento de cada um');
                    $menu1->add_link($linkEditar, "right");
                }

                # Relatórios
                $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
                $botaoRel = new Button();
                $botaoRel->set_imagem($imagem);
                $botaoRel->set_title("Relatório de Licença");
                $botaoRel->set_url("../grhRelatorios/balcao.php");
                $botaoRel->set_target("_blank");
                $menu1->add_link($botaoRel, "right");

                $menu1->show();
            }

            # Formulário de Pesquisa
            if ($editar <> 1) {
                $form = new Form('?');

                # Cria um array com os anos possíveis
                $anoAtual = date('Y');
                $anosPossiveis = arrayPreenche($anoAtual - 1, $anoAtual + 2);

                $controle = new Input('parametroAno', 'combo', 'Ano:', 1);
                $controle->set_size(30);
                $controle->set_title('Filtra pelo Ano');
                $controle->set_array($anosPossiveis);
                $controle->set_valor($parametroAno);
                $controle->set_autofocus(true);
                $controle->set_onChange('formPadrao.submit();');
                $controle->set_linha(1);
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
            } else {
                tituloTable(get_nomeMes($parametroMes) . " / " . $parametroAno);
                br();
            }

            ###########################################################################################################
            $grid1 = new Grid();
            $grid1->abreColuna(6);

            $painel = new Callout();
            $painel->abre();

            $cal = new Calendario($parametroMes, $parametroAno);
            $cal->show();

            $painel->fecha();

            $painel = new Callout();
            $painel->abre();

            # Exibe a tabela de Servidores afastados;
            $afast = new ListaAfastamentos();
            $afast->set_ano($parametroAno);
            $afast->set_mes($parametroMes);
            $afast->set_lotacao(66);
            $afast->set_idFuncional(false);
            $afast->set_nomeSimples(true);
            $afast->exibeDetalhes(false);
            $afast->exibeTabela();

            $painel->fecha();

            $grid1->fechaColuna();

            ###########################################################################################################

            $grid1->abreColuna(6);

            # Cabeçalho
            echo '<table class="tabelaPadrao">';

            echo '<caption>Controle de Atendimento no Balcão</caption>';

            echo '<col style="width:5%">';
            echo '<col style="width:15%">';
            echo '<col style="width:20%">';
            echo '<col style="width:20%">';
            echo '<col style="width:20%">';
            echo '<col style="width:20%">';

            # Cabeçalho
            echo '<tr>';
            echo '<th rowspan="2">Dia</th>';
            echo '<th rowspan="2">Dia da Semana</th>';
            echo '<th colspan="2">Manhã</th>';
            echo '<th colspan="2">Tarde</th>';
            echo '</tr>';
            echo '<th>Presencial</th>';
            echo '<th>Online</th>';
            echo '<th>Presencial</th>';
            echo '<th>Online</th>';

            # Verifica quantos dias tem o mês específico
            $dias = date("j", mktime(0, 0, 0, $parametroMes + 1, 0, $parametroAno));

            $contador = 0;
            while ($contador < $dias) {
                $contador++;

                # Define a data no formato americano (ano/mes/dia)
                $data = date("d/m/Y", mktime(0, 0, 0, $parametroMes, $contador, $parametroAno));

                # Determina o dia da semana numericamente
                $tstamp = mktime(0, 0, 0, $parametroMes, $contador, $parametroAno);
                $Tdate = getdate($tstamp);
                $wday = $Tdate["wday"];

                # Array dom os nomes do dia da semana
                $diaSemana = array("Domingo", "Segunda-feira", "Terça-feira", "Quarta-feira", "Quinta-feira", "Sexta-feira", "Sábado");

                # Verifica se nesta data existe um feriado
                $feriado = $pessoal->get_feriado($data);

                # inicia a linha do dia
                echo '<tr';

                if (($parametroAno == date('Y')) AND ($parametroMes == date('m')) AND ($contador == date('d'))) {
                    echo ' id="hoje"';
                } else {

                    if (!is_null($feriado)) {
                        echo ' id="feriado"';
                    } elseif (($wday == 0) OR ($wday == 6)) {
                        echo ' id="feriado"';
                    }
                }
                echo '>';

                # Exibe o número do dia
                echo '<td align="center">' . $contador . '</td>';

                # Exibe o nome da semana
                if (($parametroAno == date('Y')) AND ($parametroMes == date('m')) AND ($contador == date('d'))) {
                    echo '<td align="center"><b>Hoje</b></td>';
                } else {
                    echo '<td align="center">' . $diaSemana[$wday] . '</td>';
                }

                # Coluna do codigo
                if (!is_null($feriado)) {
                    echo '<td colspan="4" align="center">' . $feriado . '</td>';
                } elseif (($wday == 0) OR ($wday == 6)) {
                    echo '<td colspan="4" align="center"><b><span id="f14">----------</span></b></td>';
                } else {

                    # Define a regra de funcionamento para cada dia da semana seguindo o valor de $wday
                    # Sendo:
                    #   n -> não tem atendimento;
                    #   m -> atendimento no turno da manhã;
                    #   t -> atendimento no turno da tarde;
                    #   a -> ambos
                    #$regraFuncionamento = array('n', 't', 'm', 'a', 't', 'm', 'n');
                    $regraFuncionamento = array('n', 'a', 'a', 'a', 'a', 'a', 'n');

                    if ($editar == 1) {
                        # Monta os array de servidores para cada turno
                        # Manhã
                        $select1 = "SELECT idServidor
                                      FROM uenf_areaservidor.tbusuario JOIN uenf_grh.tbservidor USING (idServidor)
                                                                       JOIN uenf_grh.tbpessoa USING (idPessoa) 
                                                                       JOIN uenf_grh.tbhistlot USING (idServidor)
                                                                       JOIN uenf_grh.tblotacao ON (uenf_grh.tbhistlot.lotacao=uenf_grh.tblotacao.idLotacao)
                                    WHERE (balcao = 'Manhã' OR  balcao = 'Ambos')
                                      AND uenf_grh.tbhistlot.data = (select max(data) from uenf_grh.tbhistlot where uenf_grh.tbhistlot.idServidor = uenf_grh.tbservidor.idServidor)
                                      AND uenf_grh.tbhistlot.lotacao = 66
                                      AND situacao = 1
                                 ORDER BY uenf_grh.tbpessoa.nome";

                        $manha = $intra->select($select1);
                        array_unshift($manha, array(null, null)); # Adiciona o valor de nulo
                        # Tarde
                        $select2 = "SELECT idServidor
                                      FROM uenf_areaservidor.tbusuario JOIN uenf_grh.tbservidor USING (idServidor)
                                                                       JOIN uenf_grh.tbpessoa USING (idPessoa) 
                                                                       JOIN uenf_grh.tbhistlot USING (idServidor)
                                                                       JOIN uenf_grh.tblotacao ON (uenf_grh.tbhistlot.lotacao=uenf_grh.tblotacao.idLotacao)
                                    WHERE (balcao = 'Tarde' OR  balcao = 'Ambos')
                                      AND uenf_grh.tbhistlot.data = (select max(data) from uenf_grh.tbhistlot where uenf_grh.tbhistlot.idServidor = uenf_grh.tbservidor.idServidor)
                                      AND uenf_grh.tbhistlot.lotacao = 66
                                      AND situacao = 1
                                 ORDER BY uenf_grh.tbpessoa.nome";

                        $tarde = $intra->select($select2);
                        array_unshift($tarde, array(null, null)); # Adiciona o valor de nulo

                        /*
                         *  Turno da manhã
                         */
                        # Verifica se tem atendimento de manhã
                        if (($regraFuncionamento[$wday] == "m") OR ($regraFuncionamento[$wday] == "a")) {

                            echo '<td>';

                            # Pega o servidor
                            $coitadoPresencial = get_servidorBalcao($parametroAno, $parametroMes, $contador, "m");
                            $coitadoOnline = get_servidorBalcao($parametroAno, $parametroMes, $contador, "mo");

                            echo '<select name="m' . $contador . '">';

                            # Percorre o array de servidores Presencial
                            foreach ($manha as $servidores) {

                                # Simplifica o nome
                                #$servidores[0] = get_nomeSimples($servidores[0]);

                                echo ' <option value="' . $servidores[0] . '"';

                                # Varifica se é o cara
                                if ($servidores[0] == $coitadoPresencial) {
                                    echo ' selected="selected"';
                                }

                                echo '>' . $pessoal->get_nomeSimples($servidores[0]) . '</option>';
                            }

                            echo '</select>';
                            echo '</td><td>';
                            echo '<select name="mo' . $contador . '">';

                            # Percorre o array de servidores Online
                            foreach ($manha as $servidores) {

                                # Simplifica o nome
                                #$servidores[0] = get_nomeSimples($servidores[0]);

                                echo ' <option value="' . $servidores[0] . '"';

                                # Varifica se é o cara
                                if ($servidores[0] == $coitadoOnline) {
                                    echo ' selected="selected"';
                                }

                                echo '>' . $pessoal->get_nomeSimples($servidores[0]) . '</option>';
                            }

                            echo '</select>';
                            echo '</td>';
                        } else {
                            echo '<td align="center">-----</td>';
                        }

                        /*
                         * Turno da Tarde
                         */
                        # Verifica se tem atendimento
                        if (($regraFuncionamento[$wday] == "t") OR ($regraFuncionamento[$wday] == "a")) {
                            echo '<td>';

                            # Pega o servidor
                            $coitadoPresencial = get_servidorBalcao($parametroAno, $parametroMes, $contador, "t");
                            $coitadoOnline = get_servidorBalcao($parametroAno, $parametroMes, $contador, "to");

                            echo '<select name="t' . $contador . '">';

                            # Percorre o array de servidores Presencial
                            foreach ($manha as $servidores) {

                                # Simplifica o nome
                                #$servidores[0] = get_nomeSimples($servidores[0]);

                                echo ' <option value="' . $servidores[0] . '"';

                                # Varifica se é o cara
                                if ($servidores[0] == $coitadoPresencial) {
                                    echo ' selected="selected"';
                                }

                                echo '>' . $pessoal->get_nomeSimples($servidores[0]) . '</option>';
                            }

                            echo '</select>';
                            echo '</td><td>';
                            echo '<select name="to' . $contador . '">';

                            # Percorre o array de servidores Online
                            foreach ($manha as $servidores) {

                                # Simplifica o nome
                                #$servidores[0] = get_nomeSimples($servidores[0]);

                                echo ' <option value="' . $servidores[0] . '"';

                                # Varifica se é o cara
                                if ($servidores[0] == $coitadoOnline) {
                                    echo ' selected="selected"';
                                }

                                echo '>' . $pessoal->get_nomeSimples($servidores[0]) . '</option>';
                            }

                            echo '</select>';
                            echo '</td>';
                        } else {
                            echo '<td align="center">-----</td>';
                        }
                    } else {
                        /*
                         *  Turno da manhã
                         */
                        if (($regraFuncionamento[$wday] == "m") OR ($regraFuncionamento[$wday] == "a")) {

                            $idServidorBalcao = get_servidorBalcao($parametroAno, $parametroMes, $contador, "m");

                            if ($idservidorLogado == $idServidorBalcao) {
                                echo '<td id="eu" align="center">';
                                echo "<b>" . trataNulo($pessoal->get_nomeSimples($idServidorBalcao)) . "</b>";
                                echo '</td>';
                            } else {
                                echo '<td align="center">';
                                echo trataNulo($pessoal->get_nomeSimples($idServidorBalcao));
                                echo '</td>';
                            }

                            $idServidorBalcao = get_servidorBalcao($parametroAno, $parametroMes, $contador, "mo");

                            if ($idservidorLogado == $idServidorBalcao) {
                                echo '<td id="eu" align="center">';
                                echo "<b>" . trataNulo($pessoal->get_nomeSimples($idServidorBalcao)) . "</b>";
                                echo '</td>';
                            } else {
                                echo '<td align="center">';
                                echo trataNulo($pessoal->get_nomeSimples($idServidorBalcao));
                                echo '</td>';
                            }
                        } else {
                            echo '<td align="center">-----</td>';
                        }

                        /*
                         *  Turno da Tarde
                         */
                        if (($regraFuncionamento[$wday] == "t") OR ($regraFuncionamento[$wday] == "a")) {

                            $idServidorBalcao = get_servidorBalcao($parametroAno, $parametroMes, $contador, "t");

                            if ($idservidorLogado == $idServidorBalcao) {
                                echo '<td id="eu" align="center">';
                                echo "<b>" . trataNulo($pessoal->get_nomeSimples($idServidorBalcao)) . "</b>";
                                echo '</td>';
                            } else {
                                echo '<td align="center">';
                                echo trataNulo($pessoal->get_nomeSimples($idServidorBalcao));
                                echo '</td>';
                            }

                            $idServidorBalcao = get_servidorBalcao($parametroAno, $parametroMes, $contador, "to");

                            if ($idservidorLogado == $idServidorBalcao) {
                                echo '<td id="eu" align="center">';
                                echo "<b>" . trataNulo($pessoal->get_nomeSimples($idServidorBalcao)) . "</b>";
                                echo '</td>';
                            } else {
                                echo '<td align="center">';
                                echo trataNulo($pessoal->get_nomeSimples($idServidorBalcao));
                                echo '</td>';
                            }
                        } else {
                            echo '<td align="center">-----</td>';
                        }
                    }
                }

                echo '</tr>';
            }

            echo '</table>';

            # Fecha o form
            if ($editar == 1) {
                echo "</form>";
            }

            $grid1->fechaColuna();
            $grid1->fechaGrid();
            break;

#########################################################################################################################

        case "valida" :

            # Verifica quantos dias tem o mês específico
            $dias = date("j", mktime(0, 0, 0, $parametroMes + 1, 0, $parametroAno));

            # Log
            $atividade = null;
            $contador = 0;

            while ($contador < $dias) {
                $contador++;
                $vmanha = post("m$contador");
                $vmanhaOnline = post("mo$contador");
                $vtarde = post("t$contador");
                $vtardeOnline = post("to$contador");

                # Limpa os valores
                if (($vmanha == "---") OR (empty($vmanha))) {
                    $vmanha = null;
                }

                if (($vmanhaOnline == "---") OR (empty($vmanhaOnline))) {
                    $vmanhaOnline = null;
                }

                if (($vtarde == "---") OR (empty($vtarde))) {
                    $vtarde = null;
                }

                if (($vtardeOnline == "---") OR (empty($vtardeOnline))) {
                    $vtardeOnline = null;
                }

                # Verifica se já existe esse campo e pega o id para o update
                $idBalcao = get_idBalcao($parametroAno, $parametroMes, $contador);

                # Verifica se houve alteração e salva para o Log
                # Manha Presencial
                if ($vmanha <> get_servidorBalcao($parametroAno, $parametroMes, $contador, "m")) {
                    $atividade .= "{$contador}/{$parametroMes}/{$parametroAno} - Manhã Presencial:";
                    $atividade .= " De " . $pessoal->get_nomeSimples(get_servidorBalcao($parametroAno, $parametroMes, $contador, "m"));
                    $atividade .= " Para " . $pessoal->get_nomeSimples($vmanha) . "<br/>";
                }

                # Manha Online
                if ($vmanhaOnline <> get_servidorBalcao($parametroAno, $parametroMes, $contador, "mo")) {
                    $atividade .= "{$contador}/{$parametroMes}/{$parametroAno} - Manhã Online:";
                    $atividade .= " De " . $pessoal->get_nomeSimples(get_servidorBalcao($parametroAno, $parametroMes, $contador, "mo"));
                    $atividade .= " Para " . $pessoal->get_nomeSimples($vmanhaOnline) . "<br/>";
                }

                # Tarde Presencial
                if ($vtarde <> get_servidorBalcao($parametroAno, $parametroMes, $contador, "t")) {
                    $atividade .= "{$contador}/{$parametroMes}/{$parametroAno} - Tarde Presencial:";
                    $atividade .= " De " . $pessoal->get_nomeSimples(get_servidorBalcao($parametroAno, $parametroMes, $contador, "t"));
                    $atividade .= " Para " . $pessoal->get_nomeSimples($vtarde) . "<br/>";
                }

                # Tarde Omline
                if ($vtardeOnline <> get_servidorBalcao($parametroAno, $parametroMes, $contador, "to")) {
                    $atividade .= "{$contador}/{$parametroMes}/{$parametroAno} - Tarde Online:";
                    $atividade .= " De " . $pessoal->get_nomeSimples(get_servidorBalcao($parametroAno, $parametroMes, $contador, "to"));
                    $atividade .= " Para " . $pessoal->get_nomeSimples($vtardeOnline) . "<br/>";
                }

                # Grava na tabela
                $campos = array("ano", "mes", "dia", "idServidorManha", "idServidorManhaOnline", "idServidorTarde", "idServidorTardeOnline");
                $valor = array($parametroAno, $parametroMes, $contador, $vmanha, $vmanhaOnline, $vtarde, $vtardeOnline);
                $pessoal->gravar($campos, $valor, $idBalcao, "tbbalcao", "idBalcao", false);
            }

            # Grava no log a atividade
            if (!empty($atividade)) {
                $data = date("Y-m-d H:i:s");
                $intra->registraLog($idUsuario, $data, "Alterou o balcão:<br/>{$atividade}", "tbbalcao", null, 2);
            }

            loadPage("?");
            break;

#########################################################################################################################

        case "servidores" :

            # Botao Voltar
            botaoVoltar("?");

            # Monta o select
            $select = 'SELECT idServidor,
                              idServidor,
                              idServidor,
                              balcao,
                              idUsuario
                         FROM uenf_areaservidor.tbusuario JOIN uenf_grh.tbservidor USING (idServidor)
                                                          JOIN uenf_grh.tbpessoa USING (idPessoa)
                                                          JOIN tbhistlot USING (idServidor)
                                                          JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE senha IS NOT null
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND tbhistlot.lotacao = 66
                          AND situacao = 1
                     ORDER BY tbpessoa.nome asc';

            $lista = $pessoal->select($select);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($lista);
            $tabela->set_label(["Servidor", "Cargo", "Perfil", "Balcão"]);
            $tabela->set_align(["left", "left"]);
            $tabela->set_classe(["pessoal", "pessoal", "pessoal"]);
            $tabela->set_metodo(["get_nome", "get_cargo", "get_perfil"]);
            $tabela->set_titulo("Controle de Servidores da GRH que atendem ao Balcão");
            $tabela->set_editar('?fase=editaServidor');
            $tabela->set_idCampo('idUsuario');
            $tabela->show();
            break;

#########################################################################################################################

        case "editaServidor" :

            # Cria um menu
            $menu1 = new MenuBar();

            # Sair da Área do Servidor
            $linkVoltar = new Link("Não Salvar", "?fase=servidores");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title('Volta Sem Salvar');
            $menu1->add_link($linkVoltar, "left");

            # Editar
            $linkEditar = new Input("Editar", "submit");
            $linkEditar->set_valor('Salvar');
            #$menu1->add_link($linkEditar,"right");

            $menu1->show();

            # Titulo
            titulotable("Controle de Servidores da GRH que atendem ao Balcão");
            br();

            # Pega os valores
            $idServidor = $intra->get_idServidor($id);
            $nome = $pessoal->get_nomeSimples($idServidor);

            # Pega o valor atual
            $select = "SELECT balcao FROM tbusuario WHERE idUsuario = $id";
            $valorAtual = $intra->select($select, false);

            # Abre o form
            $form = new Form('?fase=validaServidor&id=' . $id);

            # Servidor
            $controle = new Input('nome', 'texto', 'Servidor:', 1);
            $controle->set_size(30);
            $controle->set_title('Atendimento no Balcão');
            $controle->set_valor($nome);
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);

            # Cria um array com os valores possiveis
            $array = array(null, "Manhã", "Tarde", "Ambos", "Não Atende");

            # Balcao
            $controle = new Input('balcao', 'combo', 'Atendimento:', 1);
            $controle->set_size(30);
            $controle->set_title('Atendimento no Balcão');
            $controle->set_array($array);
            $controle->set_valor($valorAtual[0]);
            $controle->set_autofocus(true);
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);

            # submit
            $controle = new Input('submit', 'submit');
            $controle->set_valor('Salvar');
            $controle->set_linha(1);
            $form->add_item($controle);

            $form->show();
            break;

#########################################################################################################################

        case "validaServidor" :
            $balcao = post("balcao");
            $id = get('id');
            $idServidor = $intra->get_idServidor($id);

            # Grava na tabela
            $campos = array("balcao");
            $valor = array($balcao);
            $intra->gravar($campos, $valor, $id, "tbusuario", "idUsuario", false);

            # Volta para o inicio
            loadpage("?fase=servidores");
            break;
    }

    $grid1->fechaColuna();
    $grid1->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
