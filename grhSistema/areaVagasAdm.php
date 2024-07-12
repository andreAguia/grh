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

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    $grid = new Grid();
    $grid->abreColuna(12);

    switch ($fase) {
        case "":
            /*
             * Menu
             */
            $menu = new MenuBar();

            # Voltar
            $botao = new Link("Voltar", "areaConcursoAdm.php");
            $botao->set_class('button');
            $botao->set_title('Voltar a página anterior');
            $botao->set_accessKey('V');
            $menu->add_link($botao, "left");

            $botao = new Link("pelas Vagas Ocupadas", "?fase=vagasOcupadas");
            $botao->set_class('button');
            $botao->set_title('Voltar a página anterior');
            $botao->set_accessKey('p');
            $menu->add_link($botao, "right");
            $menu->show();

            tituloTable("Vacância para Cargos Administrativos e Técnicos", null, "Vagas Autorizadas nos Concursos");
            br();

            $texto = "Observações Importantes:<br/>"
                    . " - Para essa análise serão consideradas somente as vagas novas(vagas reais) e descartadas as vagas de reposição.<br/>"
                    . " - Antes de aceitar estes dados como corretos, deve-se verificar no menu de vagas de cada concurso se não há problemas detectados.";
            callout($texto);

            $grid->fechaColuna();
            $grid->abreColuna(5);

            # Exibe as vagas 
            $select = "SELECT tbtipocargo.sigla,
                              idTipoCargo,
                              idTipoCargo,
                              idTipoCargo
                             FROM tbtipocargo
                              WHERE tipo = 'Adm/Tec'
                         ORDER BY 1 DESC";

            $conteudo = $pessoal->select($select);
            $numConteudo = $pessoal->count($select);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($conteudo);
            $tabela->set_titulo("Geral");
            $tabela->set_label(["Cargo", "Vagas Novas", "Servidores Ativos", "Vagas Disponíveis"]);
            $tabela->set_width([25, 25, 25, 25]);

            $tabela->set_colunaSomatorio([1, 2, 3]);
            $tabela->set_textoSomatorio("Total:");
            $tabela->set_totalRegistro(false);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->set_classe([null, "VagaAdm", "VagaAdm", "VagaAdm"]);
            $tabela->set_metodo([null, "get_numReaisCargo", "get_numServidoresAtivosCargo", "get_vagasDisponiveisCargo"]);
            $tabela->show();

            $grid->fechaColuna();
            $grid->abreColuna(7);

            # Exibe as vagas 
            $select = "SELECT CONCAT(tbconcurso.anobase,'<br/>',tbconcurso.regime),
                              tbtipocargo.sigla,
                                  vagasNovas,
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
            $tabela->set_titulo("por Concurso");
            $tabela->set_label(["Concurso", "Cargo", "Vagas Novas", "Servidores Ativos", "Vagas Disponíveis"]);
            $tabela->set_width([20, 20, 20, 20, 20]);

            $tabela->set_colunaSomatorio([2, 3, 4]);
            $tabela->set_textoSomatorio("Total:");
            $tabela->set_totalRegistro(false);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->set_classe([null, null, null, "VagaAdm", "VagaAdm"]);
            $tabela->set_metodo([null, null, null, "get_numServidoresAtivosVaga", "get_vagasDisponiveis"]);
            $tabela->show();
            break;

        case "vagasOcupadas":

            /*
             * Menu
             */
            $menu = new MenuBar();

            # Voltar
            $botao = new Link("Voltar", "areaConcursoAdm.php");
            $botao->set_class('button');
            $botao->set_title('Voltar a página anterior');
            $botao->set_accessKey('V');
            $menu->add_link($botao, "left");

            $botao = new Link("pelas Vagas Autorizadas", "?");
            $botao->set_class('button');
            $botao->set_title('Voltar a página anterior');
            $botao->set_accessKey('p');
            $menu->add_link($botao, "right");
            $menu->show();

            # Reserva para as máximas
            $pne = 0;
            $pnf = 0;
            $pnm = 0;
            $pns = 0;

            $anoInicial = 1990;
            $anoFinal = date("Y");

            for ($i = $anoInicial; $i < $anoFinal + 1; $i++) {

                # Habilita os valores
                $vpne = $pessoal->get_numServidoresAtivosNaEpocaTipoCargo(6, $i);
                $vpnf = $pessoal->get_numServidoresAtivosNaEpocaTipoCargo(5, $i);
                $vpnm = $pessoal->get_numServidoresAtivosNaEpocaTipoCargo(4, $i);
                $vpns = $pessoal->get_numServidoresAtivosNaEpocaTipoCargo(3, $i);

                $dados[] = [$i, $vpne, $vpnf, $vpnm, $vpns, ($vpne + $vpnf + $vpnm + $vpns), $i];

                # PNE
                if ($pne < $vpne) {
                    $pne = $vpne;
                }

                # PNF
                if ($pnf < $vpnf) {
                    $pnf = $vpnf;
                }

                # PNM
                if ($pnm < $vpnm) {
                    $pnm = $vpnm;
                }

                # PNS
                if ($pns < $vpns) {
                    $pns = $vpns;
                }
            }

            tituloTable("Vacância para Cargos Administrativos e Técnicos", null, "Vagas Ocupadas (Maior Valor)");
            br();

            $grid->fechaColuna();
            $grid->abreColuna(8);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($dados);
            $tabela->set_titulo("Quantidade de Servidores");
            $tabela->set_subtitulo("{$anoInicial} a {$anoFinal}");
            $tabela->set_label(["Ano", "PNE", "PNF", "PNM", "PNS", "Total", "Ver"]);
            $tabela->set_classe([null, null, null, null, null, null, "Concurso"]);
            $tabela->set_metodo([null, null, null, null, null, null, "rel_ServidoresPorAno"]);
            $tabela->show();

            $grid->fechaColuna();
            $grid->abreColuna(4);

            # Habilita os valores
            $vapne = $pessoal->get_numServidoresAtivosTipoCargo(6, $i);
            $vapnf = $pessoal->get_numServidoresAtivosTipoCargo(5, $i);
            $vapnm = $pessoal->get_numServidoresAtivosTipoCargo(4, $i);
            $vapns = $pessoal->get_numServidoresAtivosTipoCargo(3, $i);

            $dados2 = [
                ["PNE", $pne, $vapne, ($pne - $vapne)],
                ["PNF", $pnf, $vapnf, ($pnf - $vapnf)],
                ["PNM", $pnm, $vapnm, ($pnm - $vapnm)],
                ["PNS", $pns, $vapns, ($pns - $vapns)],
            ];

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($dados2);
            $tabela->set_titulo("Maiores Valores");
            $tabela->set_subtitulo("{$anoInicial} a {$anoFinal}");
            $tabela->set_label(["Cargo", "Max", "Atuais", "Diferença"]);
            $tabela->set_width([25, 25, 25, 25]);
            $tabela->set_colunaSomatorio([1, 2, 3]);
            $tabela->set_totalRegistro(false);
            $tabela->show();

            $grid->fechaColuna();
            $grid->abreColuna(12);
            break;
    }

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}