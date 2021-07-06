<?php

/**
 * Sistema do GRH
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

    # Verifica a fase do programa
    $fase = get('fase', 'menu');
    $alerta = get('alerta');
    $parametroMes = post('parametroMes', date("m"));
    $parametroLotacao = post('parametroLotacao', '*');

    # Define a senha padrão de acordo com o que está nas variáveis
    #define("SENHA_PADRAO",$config->get_variavel('senha_padrao'));    
    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Zera sessions
    set_session('origem');  
    set_session('origemId');
    set_session('sessionParametroPlano');
    set_session('sessionParametroNivel');
    set_session('parametroNomeMat');
    set_session('parametroNome');
    set_session('parametroCargo');
    set_session('parametroCargoComissao');
    set_session('parametroLotacao');
    set_session('parametroCurso');
    set_session('parametroInstituicao');
    set_session('parametroNivel');
    set_session('parametroTipo');
    set_session('parametroEscolaridade');
    set_session('parametroPerfil');
    set_session('parametroPasta');
    set_session('parametroSituacao');
    set_session('parametroPaginacao');
    set_session('parametroOrdenacao');
    set_session('parametroDescricao');
    set_session('parametroEntrega');
    set_session('sessionSelect');                      // Select para gerar relatório
    set_session('sessionTítulo');                      // Título do relatório
    set_session('sessionSubTítulo');                   // SubTítulo do relatório
    set_session('parametroAno');
    set_session('parametroMes');
    set_session('idCategoria');
    set_session('idProcedimento');
    set_session('parametroAfastamento');
    set_session('categoria');

    set_session('parametroCentro');
    set_session('parametroLab');
    set_session('parametroSituacao');

    set_session('sessionParametro'); # Zera a session do parâmetro de pesquisa da classe modelo
    set_session('sessionPaginacao'); # Zera a session de paginação da classe modelo
    set_session('sessionLicenca');      # Zera a session do tipo de licença
    set_session('matriculaGrh');        # Zera a session da pesquisa do sistema grh
    # RPA
    set_session('sessionidPrestador');
    set_session('sessionCpfPrestador');

    /*
     *  Menu
     */
    switch ($fase) {
        # Exibe o Menu Inicial
        case "menu" :

            p(SISTEMA, 'grhTitulo');
            p("Versão: " . VERSAO, "versao");

            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $linkVoltar = new Link("Sair", "../../areaServidor/sistema/login.php");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title('Sair do Sistema');
            $linkVoltar->set_confirma('Tem certeza que deseja sair do sistema?');
            $linkVoltar->set_accessKey('i');
            $menu->add_link($linkVoltar, "left");

            # Alterações
            $linkArea = new Link("Atualizações", '?fase=atualizacoes&grh=1');
            $linkArea->set_class('button');
            $linkArea->set_title('Exibe o histórico de atualizações do sistema');
            $menu->add_link($linkArea, "right");

            # Relatórios
            $imagem1 = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_url("grhRelatorios.php");
            $botaoRel->set_title("Relatórios dos Sistema");
            $botaoRel->set_imagem($imagem1);
            $menu->add_link($botaoRel, "right");

            # Alertas
            $linkArea = new Link("Alertas", "alertas.php?grh=1");
            $linkArea->set_class('button alert');
            $linkArea->set_title('Alertas do Sistema');
            $menu->add_link($linkArea, "right");

            # Administração do Sistema
            if (Verifica::acesso($idUsuario, 1)) {   // Somente Administradores
                $linkAdm = new Link("Administração", "../../areaServidor/sistema/administracao.php");
                $linkAdm->set_class('button success');
                $linkAdm->set_title('Administração dos Sistemas');
                $menu->add_link($linkAdm, "right");
            }

            $menu->show();

            $grid->fechaColuna();
            $grid->fechaGrid();


            /*
             *  Faz as alterações de férias
             */
            if ($intra->get_variavel('dataVerificaFeriasSolicitada') <> date("d/m/Y")) {
                # muda as férias solicitadas na data de hoje para fruídas
                $pessoal->mudaStatusFeriasSolicitadaFruida();

                # muda a variável para hoje
                $intra->set_variavel('dataVerificaFeriasSolicitada', date("d/m/Y"));

                # registra o log
                $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), 'Rotina de verificação de férias executada.', null, null, 6);
            }

            /*
             *  Faz as alterações de readaptação
             */
            if ($intra->get_variavel('dataVerificaStatusReadaptacao') <> date("d/m/Y")) {
                # muda os status de acordo com as regras
                $readaptacao = new Readaptacao();
                $readaptacao->mudaStatus();

                # muda a variável para hoje
                $intra->set_variavel('dataVerificaStatusReadaptacao', date("d/m/Y"));

                # registra o log
                $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), 'Rotina de alteração do status de readaptação executada.', null, null, 6);
            }

            /*
             *  Faz as alterações de redução de carga horária
             */
            if ($intra->get_variavel('dataVerificaStatusReducao') <> date("d/m/Y")) {
                # muda os status de acordo com as regras
                $reducao = new ReducaoCargaHoraria();
                $reducao->mudaStatus();

                # muda a variável para hoje
                $intra->set_variavel('dataVerificaStatusReducao', date("d/m/Y"));

                # registra o log
                $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), 'Rotina de alteração do status de redução de carga horária executada.', null, null, 6);
            }

            /*
             *  Sincroniza tbvagahistorico com idservidor
             */
            if ($intra->get_variavel('dataSincronizaIdConcurso') <> date("d/m/Y")) {
                # Faz a sincronização
                $concursoClasse = new Concurso();
                $qdade = $concursoClasse->sincronizaIdConcurso();

                # muda a variável para hoje
                $intra->set_variavel('dataSincronizaIdConcurso', date("d/m/Y"));

                # registra o log
                $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $qdade . ' registros sincronizados da tbvagahistorico com idservidor.', null, null, 6);
            }

            /*
             *  Faz o backup de hora em hora
             */
            # Verifica se o backup automático está habilitado
            if ($intra->get_variavel("backupAutomatico")) {

                # Verifica as horas
                $horaBackup = $intra->get_variavel("backupHora");
                $horaAtual = date("H");

                # Compara se são diferentes
                if ($horaAtual <> $horaBackup) {
                    # Realiza backup
                    $processo = new Processo();
                    $processo->run("php /var/www/html/areaServidor/sistema/backup.php 1 $idUsuario");
                }
            }

            ########
            # monta o menu principal
            $menu = new MenuPrincipal($idUsuario);

            # Perfil
            $idPessoa = $intra->get_idPessoa($idUsuario);
            $nickUser = $intra->get_nickUsuario($idUsuario);
            $title = "Usuário: $nickUser";

            $div = new Div("menuPerfil");
            $div->abre();

            echo '<button class="button" type="button" data-toggle="example-dropdown-1">' . $nickUser . '</button>';

            $div->fecha();

            echo '<div class="dropdown-pane" id="example-dropdown-1" data-dropdown data-hover="true" data-hover-pane="true">';

            $figura = new Imagem(PASTA_FOTOS . $idPessoa . '.jpg', $title, 70, 70);
            $figura->set_id('perfil');
            $figura->show();

            br();
            p("Usuário:", "puser");
            p($intra->get_nickUsuario($idUsuario), "f12", "left");

            p("Nome:", "puser");
            p($intra->get_nomeUsuario($idUsuario), "f12", "left");

            # Trocar Senha
            $botao = new Link("Trocar Senha", '../../areaServidor/sistema/trocarSenha.php');
            $botao->set_class('button small');
            $botao->set_title('Altera a senha do usuário logado');
            $botao->show();


            echo '</div>';

            # Zera a session de alerta
            set_session('alerta');

            # Exibe o rodapé da página
            Grh::rodape($idUsuario);
            break;

##################################################################	

        case "aniversariantes" :
            br();

            # Grava no log a atividade
            $atividade = "Visualizou os anivesariantes de " . get_nomeMes($parametroMes);
            $data = date("Y-m-d H:i:s");
            $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);

            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Botão voltar
            botaoVoltar('?');

            # Mês 
            $form = new Form('?fase=aniversariantes');

            $controle = new Input('parametroMes', 'combo', "Mês", 1);
            $controle->set_size(30);
            $controle->set_title('O mês dos aniversários');
            $controle->set_array($mes);
            $controle->set_valor($parametroMes);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_col(3);
            $controle->set_linha(1);
            $form->add_item($controle);

            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                          FROM tblotacao
                                         WHERE ativo) UNION (SELECT distinct DIR, DIR
                                          FROM tblotacao
                                         WHERE ativo)
                                      ORDER BY 2');
            array_unshift($result, array('*', '-- Todos --'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_col(9);
            $controle->set_linha(1);
            $form->add_item($controle);
            $form->show();

            if ($parametroLotacao == "*") {
                $parametroLotacao = null;
            }
            $grid->fechaColuna();
            $grid->fechaGrid();

            # Tabela
            $grid = new Grid();
            $grid->abreColuna(3);

            # Resumo
            $pessoal = new Pessoal();
            $numAniversdariantes = $pessoal->get_numAniversariantes();
            $numHoje = $pessoal->get_numAniversariantesHoje();
            $numServidores = $pessoal->get_numServidoresAtivos();

            # Exibe os valores            
            $dados[] = array("Aniversariantes do Mês", $numAniversdariantes);
            $dados[] = array("Aniversariantes de Hoje", $numHoje);

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($dados);
            $tabela->set_titulo("Resumo");
            $tabela->set_label(array("", ""));
            $tabela->set_width(array(70, 30));
            $tabela->set_totalRegistro(false);
            $tabela->set_align(array("left", "center"));
            $tabela->show();

            # Calendário
            $cal = new Calendario($parametroMes);
            $cal->show();

            $grid->fechaColuna();
            $grid->abreColuna(9);

            # Exibe a tabela            
            $select = 'SELECT DAY(tbpessoa.dtNasc),
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor
                FROM tbpessoa LEFT JOIN tbservidor ON (tbpessoa.idPessoa = tbservidor.idPessoa)
                                   JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                   JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
               WHERE tbservidor.situacao = 1
                 AND MONTH(tbpessoa.dtNasc) = ' . $parametroMes . '
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';

            # lotacao
            if (!is_null($parametroLotacao)) {
                # Verifica se o que veio é numérico
                if (is_numeric($parametroLotacao)) {
                    $select .= ' AND (tblotacao.idlotacao = "' . $parametroLotacao . '")';
                } else { # senão é uma diretoria genérica
                    $select .= ' AND (tblotacao.DIR = "' . $parametroLotacao . '")';
                }
            }

            $select .= ' ORDER BY month(tbpessoa.dtNasc), day(tbpessoa.dtNasc)';

            $result = $pessoal->select($select);
            $count = $pessoal->count($select);
            $titulo = "Aniversariantes de " . get_nomeMes($parametroMes);

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(array("Dia", "Nome", "Lotação", "Cargo", "Perfil"));
            $tabela->set_align(array("center", "left", "left", "left"));
            $tabela->set_classe(array(null, null, 'Pessoal', 'Pessoal', 'Pessoal'));
            $tabela->set_metodo(array(null, null, 'get_lotacao', 'get_cargo', 'get_perfilSimples'));
            $tabela->set_titulo($titulo);
            if (date("m") == $parametroMes) {
                $tabela->set_formatacaoCondicional(array(array('coluna' => 0, 'valor' => date("d"), 'operador' => '=', 'id' => 'aniversariante')));
            }
            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);
            $tabela->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

##################################################################

        case "atualizacoes" :

            # Limita a tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Verifica se veio menu grh e registra o acesso no log
            $grh = get('grh', false);
            if ($grh) {
                # Grava no log a atividade
                $atividade = "Visualizou a area de atualizações do sistema";
                $data = date("Y-m-d H:i:s");
                $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
            }

            # botão voltar
            botaoVoltar("?", "Voltar", "Volta ao Menu principal");

            # Título
            titulo("Detalhes das Atualizações");
            #p("Detalhes das Atualizações","center","f16");
            br();

            # Limita ainda mais a tela para o painel
            $grid = new Grid("center");
            $grid->abreColuna(10);

            # Pega os dados 
            $atualizacoes = $intra->get_atualizacoes();

            # Percorre os dados
            foreach ($atualizacoes as $valor) {
                $painel = new Callout();
                $painel->abre();

                $grid2 = new Grid("center");
                $grid2->abreColuna(6);                
                p("Versão: " . $valor[0], "patualizacaoL");
                $grid2->fechaColuna();
                $grid2->abreColuna(6);
                p(date_to_php($valor[1]), "patualizacaoR");
                $grid2->fechaColuna();
                $grid2->fechaGrid();
                hr("rpa");

                p(str_replace('-', '<br/>-', $valor[2]), "f14");
                $painel->fecha();
            }

            $grid->fechaColuna();
            $grid->fechaGrid();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

##################################################################

        case "acumulacao" :

            # Limita a tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # botão voltar
            botaoVoltar("?", "Voltar", "Volta ao Menu principal");

            # Título
            titulo("Área de Acumulação de Cargos");
            br(2);

            # Limita a tela
            $grid = new Grid("center");
            $grid->abreColuna(8);

            $tamanhoImage = 60;
            $menu = new MenuGrafico(2);
            $menu->set_espacoEntreLink(true);

            $botao = new BotaoGrafico();
            $botao->set_label('Servidores que Acumulam Cargos Públicos');
            $botao->set_url('areaAcumulacao.php?grh=1');
            $botao->set_imagem(PASTA_FIGURAS . 'acumulacao.jpg', $tamanhoImage, $tamanhoImage);
            $botao->set_title('Controle de Acumulação de Cargo Público');
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            #$botao->set_novo(true);
            $botao->set_label('Controle da Entrega da Declaração Anual');
            $botao->set_url('areaAcumulacaoDeclaracao.php?grh=1');
            $botao->set_imagem(PASTA_FIGURAS . 'declaracao.png', $tamanhoImage, $tamanhoImage);
            $botao->set_title('Controle da entrega da declaração anual de acumulação de cargos públicos');
            $menu->add_item($botao);

            $menu->show();

            callout("É dever do servidor ou empregado público informar à GRH
                quanto a eventual acumulação de cargos, empregos ou funções
                públicas (Art. 282 e 283 do Decreto nº 2.479/79), inclusive
                quando da nomeação para o segundo vínculo (Art. 10 do Decreto
                nº 2.479/79) e nos casos de acumulações já analisadas e
                publicadas em Diário Oficial pela SEPLAG, quando ocorrer
                alguma alteração.<br/><br/>
                
                A omissão de tais informações ou a prestação de informação
                inverídica configura falta funcional, tanto pelo servidor
                ou empregado público que acumula os vínculos quanto por outro
                agente público que, tendo ciência da situação de acúmulo
                irregular, não o comunique à autoridade competente (Art. 37,
                Parágrafo Único, do Decreto-Lei nº 220/75 RS nº 109).<br/><br/>
                
                Para atender ao que determina a legislação, todos os servidores
                ativos da UENF, deverão, anualmente, preencher e encaminhar
                à GRH, via SEI, assinado eletrônicamente, a declaração
                Anual de Acumulação de Cargo.");

            $grid->fechaColuna();
            $grid->fechaGrid();
            br(2);

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

##################################################################
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");    
}
