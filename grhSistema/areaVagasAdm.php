<?php

/**
 * Cadastro de Banco
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

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de vagas de Administrativo e Técnico";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # Define a função usada em dois momentos nesse codigo

    function botaoServidoresAtivosVagas($sigla = null) {
        # Ver servidores ativos
        $servAtivos = new Link(null, "../grhRelatorios/geral.concursados.ativos.admTec.php?sigla={$sigla}");
        $servAtivos->set_imagem(PASTA_FIGURAS_GERAIS . 'olho.png', 20, 20);
        $servAtivos->set_target("_blank");
        $servAtivos->set_title("Exibe os servidores ativos");
        $servAtivos->show();
    }

    function botaoServidoresAtivosVagas2($id = null) {
        # Ver servidores ativos
        $servAtivos = new Link(null, "?fase=relatorioAtivos&id={$id}");
        $servAtivos->set_imagem(PASTA_FIGURAS_GERAIS . 'olho.png', 20, 20);
        $servAtivos->set_target("_blank");
        $servAtivos->set_title("Exibe os servidores ativos");
        $servAtivos->show();
    }

    function botaoServidoresAtivosVagas3($sigla = null) {
        # Ver servidores inativos
        $servAtivos = new Link(null, "../grhRelatorios/geral.concursados.inativos.admTec.php?sigla={$sigla}");
        $servAtivos->set_imagem(PASTA_FIGURAS_GERAIS . 'olho.png', 20, 20);
        $servAtivos->set_target("_blank");
        $servAtivos->set_title("Exibe os servidores inativos");
        $servAtivos->show();
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "relatorioAtivos"
            AND $fase <> "relatorioInativos"
            AND $fase <> "relatorioClassificacao"
            AND $fase <> "relatorioTodos") {

        AreaServidor::cabecalho();
    }


    $grid = new Grid();
    $grid->abreColuna(12);

    $menu = new MenuBar();

    # Voltar
    $botao = new Link("Voltar", "areaConcursoAdm.php");
    $botao->set_class('button');
    $botao->set_title('Voltar a página anterior');
    $botao->set_accessKey('V');
    $menu->add_link($botao, "left");
    $menu->show();

    ################################################################

    switch ($fase) {
        case "":

            tituloTable("Vacância para Cargos Administrativos e Técnicos");
            br();

            # Menu de Abas
            $tab = new Tab([
                "Considerando os Servidores que Ocupara a Vaga Anteriormente",
                "Considerando o Máximo de Vagas Ocupadas",
                "Considerando as Vagas Autorizadas no Edital"
            ]);

            #################################################

            $tab->abreConteudo();

            # Pega os parâmetros
            $parametroCargo = post('parametroCargo', get_session('parametroCargo', 6));

            # Joga os parâmetros par as sessions
            set_session('parametroCargo', $parametroCargo);

            $grid1 = new Grid();
            $grid1->abreColuna(12);

            tituloTable("Considerando os Servidores que Ocupara a Vaga Anteriormente");
            $texto = "Observações Importantes:<br/>"
                    . " - Aqui temos todos os servidores concursados que ocuparam ou acupam as vagas.<br/>"
                    . " - Segundo a informação inserida no campo de servidor que ocupava a vaga anteriormente.";
            callout($texto);

            $grid1->fechaColuna();
            $grid1->abreColuna(8);

            # Formulário de Pesquisa
            $form = new Form('?');

            # Cargo
            $result = $pessoal->select('SELECT idTipoCargo,
                                       CONCAT(sigla," - ",cargo)
                                  FROM tbtipocargo
                                 WHERE tipo = "Adm/Tec" 
                              ORDER BY cargo');

            $controle = new Input('parametroCargo', 'combo', 'Cargo:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Cargol');
            $controle->set_array($result);
            $controle->set_optgroup(true);
            $controle->set_valor($parametroCargo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(12);
            $form->add_item($controle);
            $form->show();

            # Monta o select
            $select = "SELECT idServidor,
                              idServidor,
                              idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         LEFT JOIN tbperfil USING (idPerfil)
                                         LEFT JOIN tbcargo USING (idCargo)
                                         LEFT JOIN tbtipocargo ON (tbcargo.idTipoCargo = tbtipocargo.idTipoCargo)
                        WHERE (idPerfil = 1 OR idPerfil = 4)                       
                          AND (idServidorOcupanteAnterior is null OR idServidorOcupanteAnterior = 0)
                          AND tbtipocargo.tipo = 'Adm/Tec'
                          AND tbtipocargo.idTipoCargo = {$parametroCargo}
                     ORDER BY dtAdmissao, tbpessoa.nome";

            # Pega os dados
            $row = $pessoal->select($select);

            $tipocargo = new TipoCargo();

            # tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Vagas Discriminadas");
            $tabela->set_subtitulo($tipocargo->get_cargo($parametroCargo));
            $tabela->set_conteudo($row);
            $tabela->set_label(["Primeiro na Vaga", "Vaga Posterior", "Vaga Posterior"]);
            $tabela->set_width([33, 33, 33]);
            $tabela->set_align(["left", "left", "left"]);
            $tabela->set_numeroOrdem(true);

            $tabela->set_classe(["Concurso", "Concurso", "Concurso", "Concurso"]);
            $tabela->set_metodo(["exibeServidorEConcurso", "exibeOcupantePosteriorComBotao", "exibeOcupantePosteriorPosteriorComBotao"]);
            $tabela->show();

            $grid1->fechaColuna();
            $grid1->abreColuna(4);

            $concurso = new Concurso();
            $concurso->exibeQuadroResumoVagasDisponiveis();

            $grid1->fechaColuna();
            $grid1->fechaGrid();

            $tab->fechaConteudo();

            #################################################

            $tab->abreConteudo();

            $grid1 = new Grid();
            $grid1->abreColuna(12);

            # Reserva para as máximas
            $maxpne = 0;
            $maxpnf = 0;
            $maxpnm = 0;
            $maxpns = 0;

            $anoInicial = 1997;
            $anoFinal = date("Y");

            # Pega os valores máximos
            for ($i = $anoInicial; $i < $anoFinal + 1; $i++) {

                # Habilita os valores
                $vpne = $pessoal->get_numConcursadosAtivosNaEpocaTipoCargo(6, $i);
                $vpnf = $pessoal->get_numConcursadosAtivosNaEpocaTipoCargo(5, $i);
                $vpnm = $pessoal->get_numConcursadosAtivosNaEpocaTipoCargo(4, $i);
                $vpns = $pessoal->get_numConcursadosAtivosNaEpocaTipoCargo(3, $i);

                # PNE
                if ($maxpne < $vpne) {
                    $maxpne = $vpne;
                }

                # PNF
                if ($maxpnf < $vpnf) {
                    $maxpnf = $vpnf;
                }

                # PNM
                if ($maxpnm < $vpnm) {
                    $maxpnm = $vpnm;
                }

                # PNS
                if ($maxpns < $vpns) {
                    $maxpns = $vpns;
                }
            }

            # monta o array
            for ($i = $anoInicial; $i < $anoFinal + 1; $i++) {

                # Habilita os valores
                $vpne = $pessoal->get_numConcursadosAtivosNaEpocaTipoCargo(6, $i);
                $vpnf = $pessoal->get_numConcursadosAtivosNaEpocaTipoCargo(5, $i);
                $vpnm = $pessoal->get_numConcursadosAtivosNaEpocaTipoCargo(4, $i);
                $vpns = $pessoal->get_numConcursadosAtivosNaEpocaTipoCargo(3, $i);

                $total = $vpne + $vpnf + $vpnm + $vpns;

                # PNE
                if ($maxpne == $vpne) {
                    $vpne = "<span class='label warning'>{$vpne}</span>";
                }

                # PNF
                if ($maxpnf == $vpnf) {
                    $vpnf = "<span class='label warning'>{$vpnf}</span>";
                }

                # PNM
                if ($maxpnm == $vpnm) {
                    $vpnm = "<span class='label warning'>{$vpnm}</span>";
                }

                # PNS
                if ($maxpns == $vpns) {
                    $vpns = "<span class='label warning'>{$vpns}</span>";
                }

                $dados[] = [$i, $vpne, $vpnf, $vpnm, $vpns, $total, $i];
            }

            tituloTable("Considerando o Máximo de Vagas Ocupadas");
            $texto = "Observações Importantes:<br/>"
                    . " - Para essa análise é considerado o número máximo de servidores concursados ativos por ano / cargo desde {$anoInicial}.";
            callout($texto);

            $grid1->fechaColuna();
            $grid1->abreColuna(8);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($dados);
            $tabela->set_titulo("Quantidade de Servidores Ativos por Ano");
            $tabela->set_subtitulo("Estatutários e Celetistas Concursados<br/>{$anoInicial} a {$anoFinal}");
            $tabela->set_label(["Ano", "PNE", "PNF", "PNM", "PNS", "Total", "Ver"]);
            $tabela->set_colspanLabel([null, null, null, null, null, 2]);
            $tabela->set_classe([null, null, null, null, null, null, "Concurso"]);
            $tabela->set_metodo([null, null, null, null, null, null, "rel_ServidoresPorAno"]);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->show();

            $grid1->fechaColuna();
            $grid1->abreColuna(4);

            # Habilita os valores
            $vapne = $pessoal->get_numConcursadosAtivosNaEpocaTipoCargo(6);
            $vapnf = $pessoal->get_numConcursadosAtivosNaEpocaTipoCargo(5);
            $vapnm = $pessoal->get_numConcursadosAtivosNaEpocaTipoCargo(4);
            $vapns = $pessoal->get_numConcursadosAtivosNaEpocaTipoCargo(3);

            # Diferença
            $difpne = $maxpne - $vapne;
            $difpnf = $maxpnf - $vapnf;
            $difpnm = $maxpnm - $vapnm;
            $difpns = $maxpns - $vapns;

            # VagasTotais
            $vagasTotais = $difpne + $difpnf + $difpnm + $difpns;

            $dados2 = [
                ["PNS", $maxpns, $vapns, "PNS", $difpns, "PNS"],
                ["PNM", $maxpnm, $vapnm, "PNM", $difpnm, "PNM"],
                ["PNF", $maxpnf, $vapnf, "PNF", $difpnf, "PNF"],
                ["PNE", $maxpne, $vapne, "PNE", $difpne, "PNE"],
            ];

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($dados2);
            $tabela->set_titulo("Maiores Valores");
            $tabela->set_subtitulo("{$anoInicial} a {$anoFinal}");
            $tabela->set_label(["Cargo", "Max", "Atual", "Ver", "Diferença", "Ver"]);

            $tabela->set_funcao([null, null, null, "botaoServidoresAtivosVagas", null, "botaoServidoresAtivosVagas3"]);

            $tabela->set_colspanLabel([null, null, 2]);
            $tabela->set_width([16, 16, 16, 16, 16, 16]);
            $tabela->set_colunaSomatorio([1, 2, 4]);
            $tabela->set_totalRegistro(false);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $painel = new Callout("primary");
            $painel->abre();

            p("Vagas Totais:", "vagaCargo");
            p($vagasTotais, "vagaCentro");
            p("PNE: {$difpne} | PNF: {$difpnf} | PNM: {$difpnm} | PNS: {$difpns}", "vagaCargo");

            $painel->fecha();
            $tabela->show();

            $grid1->fechaColuna();
            $grid1->fechaGrid();

            $tab->fechaConteudo();

            #################################################

            $tab->abreConteudo();

            $grid1 = new Grid();
            $grid1->abreColuna(12);

            tituloTable("Considerando as Vagas Autorizadas no Edital");

            $texto = "Observações Importantes:<br/>"
                    . " - Para essa análise são consideradas as vagas informadas no cadastro de concurso, definidas no edital de cada concurso.<br/>"
                    . " - Sersomente as vagas novas (vagas reais) e descartadas as vagas de reposição.";
            callout($texto);

            $grid1->fechaColuna();
            $grid1->abreColuna(8);

            # Exibe as vagas 
            $select = "SELECT CONCAT(tbconcurso.anobase,'<br/>',tbconcurso.regime),
                              tbtipocargo.sigla,
                              vagasNovas,
                              vagasReposicao,
                              idConcursoVaga,
                              idConcursoVaga,
                              idConcursoVaga
                         FROM tbconcursovaga JOIN tbtipocargo USING (idTipoCargo)
                                             JOIN tbconcurso USING (idConcurso)
                        WHERE tbconcurso.tipo = 1
                     ORDER BY tbconcurso.anobase DESC, tbtipocargo.sigla DESC";

            $conteudo = $pessoal->select($select);
            $numConteudo = $pessoal->count($select);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($conteudo);
            $tabela->set_titulo("Quantidade de Vagas Novas por Concurso");
            $tabela->set_subtitulo("Desde o Concurso de 1997");
            $tabela->set_label(["Concurso", "Cargo", "Vagas Novas", "Vagas de Reposição", "Servidores Ativos", "Ver", "Vagas Disponíveis"]);
            $tabela->set_width([15, 10, 15, 15, 10, 10, 15]);
            $tabela->set_colspanLabel([null, null, null, null, 2]);

            $tabela->set_funcao([null, null, null, null, null, "botaoServidoresAtivosVagas2"]);

            $tabela->set_colunaSomatorio([2, 3, 4, 6]);
            $tabela->set_textoSomatorio("Total:");
            $tabela->set_totalRegistro(false);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->set_classe([null, null, null, null, "VagaAdm", null, "VagaAdm"]);
            $tabela->set_metodo([null, null, null, null, "get_numServidoresAtivosVaga", null, "get_vagasDisponiveis"]);
            $tabela->show();

            $grid1->fechaColuna();
            $grid1->abreColuna(4);

            # Exibe as vagas 
            $select = "SELECT tbtipocargo.sigla,
                      idTipoCargo,
                      idTipoCargo,
                      tbtipocargo.sigla,
                      idTipoCargo
                 FROM tbtipocargo
                WHERE tipo = 'Adm/Tec'
             ORDER BY 1 DESC";

            $conteudo = $pessoal->select($select);
            $numConteudo = $pessoal->count($select);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($conteudo);
            $tabela->set_titulo("Total Geral");
            $tabela->set_subtitulo("Desde o Concurso de 1997");
            $tabela->set_label(["Cargo", "Vagas Novas", "Vagas Ocupadas", "Ver", "Vagas Disponíveis"]);
            $tabela->set_colspanLabel([null, null, 2]);
            $tabela->set_width([20, 20, 20, 20, 20]);

            $tabela->set_funcao([null, null, null, "botaoServidoresAtivosVagas"]);

            $tabela->set_colunaSomatorio([1, 2, 4]);
            $tabela->set_textoSomatorio("Total:");
            $tabela->set_totalRegistro(false);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->set_classe([null, "VagaAdm", "VagaAdm", null, "VagaAdm"]);
            $tabela->set_metodo([null, "get_numReaisCargo", "get_numServidoresAtivosCargo", null, "get_vagasDisponiveisCargo"]);

            $painel = new Callout("primary");
            $painel->abre();

            $vagasAdm = new VagaAdm();

            p("Vagas Totais:", "vagaCargo");
            p($vagasAdm->get_vagasDisponiveisCargo(6) + $vagasAdm->get_vagasDisponiveisCargo(5) + $vagasAdm->get_vagasDisponiveisCargo(4) + $vagasAdm->get_vagasDisponiveisCargo(3), "vagaCentro");
            p("PNE: {$vagasAdm->get_vagasDisponiveisCargo(6)} | PNF: {$vagasAdm->get_vagasDisponiveisCargo(5)} | PNM: {$vagasAdm->get_vagasDisponiveisCargo(4)} | PNS: {$vagasAdm->get_vagasDisponiveisCargo(3)}", "vagaCargo");

            $painel->fecha();
            $tabela->show();

            $grid1->fechaColuna();
            $grid1->fechaGrid();

            $tab->fechaConteudo();
            $tab->show();
            break;

        ################################################################

        case "relatorioAtivos" :

            $id = get("id");
            $vaga = new VagaAdm();
            $dados = $vaga->get_dados($id);

            # Lista de Servidores Ativos
            $lista = new ListaServidores('Servidores Ativos');
            $lista->set_situacao(1);
            $lista->set_concurso($dados["idConcurso"]);
            $lista->set_tipoCargo($dados["idTipoCargo"]);
            $lista->showRelatorio();
            break;

        ################################################################

        case "editaServidor" :
            br(8);
            aguarde();

            set_session('idServidorPesquisado', $id);
            set_session('origem', "areaVagasAdm.php");
            loadPage('servidorConcurso.php');
            break;
        
        ################################################################
    }

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}