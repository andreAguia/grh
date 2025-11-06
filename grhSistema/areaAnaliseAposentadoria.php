<?php

/**
 * Área de Controle da Entrega da Certidão de Tempo do INSS
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

    # Defne a data limite
    $dataLimite = "31/12/2001";

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();

    # Verifica a fase do programa
    $fase = get('fase', 'aguarde');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a área de análise de aposentadoria";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', $pessoal->get_idLotacao($intra->get_idServidor($idUsuario))));
    $parametroSexo = post('parametroSexo', get_session('parametroSexo'));

    # Joga os parâmetros para as sessions
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroSexo', $parametroSexo);

    # Limita a página
    $grid = new Grid();
    $grid->abreColuna(12);

    # Menu e Cabeçalho
    if ($fase <> "relatorio") {

        AreaServidor::cabecalho();

        # Cria o Menu
        $menu = new MenuBar();

        # Voltar
        $botaoVoltar = new Link("Voltar", "areaPrevisao.php");
        $botaoVoltar->set_class('button');
        $botaoVoltar->set_title('Voltar a página anterior');
        $botaoVoltar->set_accessKey('V');
        $menu->add_link($botaoVoltar, "left");

        # Relatórios
        $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
        $botaoRel = new Button();
        $botaoRel->set_title("Relatório dessa pesquisa");
        $botaoRel->set_url("?fase=relatorio");
        $botaoRel->set_target("_blank");
        $botaoRel->set_imagem($imagem);
        #$menu->add_link($botaoRel, "right");

        $menu->show();

        ################################################################
        # Formulário de Pesquisa
        $form = new Form('?');

        # Lotação    
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
        $controle->set_col(6);
        $form->add_item($controle);

        # Entregou ? 
        $controle = new Input('parametroSexo', 'combo', 'Sexo:', 1);
        $controle->set_size(30);
        $controle->set_title('Filtra por Sexo');
        $controle->set_array([
            ["Masculino", "Masculino"],
            ["Feminino", "Feminino"],
        ]);
        $controle->set_valor($parametroSexo);
        $controle->set_onChange('formPadrao.submit();');
        $controle->set_linha(1);
        $controle->set_col(3);
        $form->add_item($controle);
        $form->show();
    }

    #######################################

    switch ($fase) {
        case "":
        case "aguarde":

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=listar');
            break;

        #######################################

        case "listar" :

            if ($parametroLotacao == "*") {
                $parametroLotacao = null;
            }

            $subTitulo = null;

            if ($parametroSexo == "Feminino") {
                $texto = "Data com<br/>62 anos";
            } else {
                $texto = "Data com<br/>65 anos";
            }

            ######
            # -----------------------------------
            # IdFuncional
            $sql[] = "tbservidor.idFuncional";
            $label[] = 'idFuncional';
            $align[] = "center";
            $funcao[] = null;
            $classe[] = null;
            $metodo[] = null;
            # -----------------------------------
            # Nome
            $sql[] = "tbpessoa.nome";
            $label[] = 'Nome';
            $align[] = "left";
            $funcao[] = null;
            $classe[] = null;
            $metodo[] = null;
            # -----------------------------------
            # Data de Nascimento
            $sql[] = "DATE_FORMAT(tbpessoa.dtNasc, '%d/%m/%Y')";
            $label[] = 'Nascimento';
            $align[] = "center";
            $funcao[] = null;
            $classe[] = null;
            $metodo[] = null;
            # -----------------------------------
            # Idade
            $sql[] = "DATE_FORMAT(tbpessoa.dtNasc, '%d/%m/%Y')";
            $label[] = 'Idade';
            $align[] = "center";
            $funcao[] = "idade";
            $classe[] = null;
            $metodo[] = null;
            # -----------------------------------            
            # Data de Admissão
            $sql[] = "DATE_FORMAT(tbservidor.dtAdmissao, '%d/%m/%Y')";
            $label[] = 'Admissão';
            $align[] = "center";
            $funcao[] = null;
            $classe[] = null;
            $metodo[] = null;
            # -----------------------------------
            # Data com X idade
            $sql[] = "IF(tbpessoa.sexo = 'Feminino', DATE_ADD(tbpessoa.dtNasc, INTERVAL 62 YEAR), DATE_ADD(tbpessoa.dtNasc, INTERVAL 65 YEAR))";
            $label[] = $texto;
            $align[] = "center";
            $funcao[] = null;
            $classe[] = "Averbacao";
            $metodo[] = "get_tempoAverbadoPublico";
            # -----------------------------------
            # Tempo Averbado Publico
            $sql[] = "tbservidor.idServidor";
            $label[] = 'Tempo Averbado<br/>Público (em dias)';
            $align[] = "center";
            $funcao[] = null;
            $classe[] = "Averbacao";
            $metodo[] = "get_tempoAverbadoPublico";
            # -----------------------------------
            # Tempo Averbado Privado
            $sql[] = "tbservidor.idServidor";
            $label[] = 'Tempo Averbado<br/>Privado (em dias)';
            $align[] = "center";
            $funcao[] = null;
            $classe[] = "Averbacao";
            $metodo[] = "get_tempoAverbadoPrivado";
            # -----------------------------------
            # Tempo de Contribuição na Uenf
            $sql[] = "tbservidor.idServidor";
            $label[] = 'Tempo de Contribuição<br/>na Uenf (em dias)';
            $align[] = "center";
            $funcao[] = null;
            $classe[] = "TempoServico";
            $metodo[] = "get_tempoServicoUenfLiquido";
            
            # Monta o select
            $select = "SELECT ";
            
            foreach ($sql as $item){
                $select .= "{$item},";
            }
            
            # Retira a ultima vírgula
            $select = substr($select, 0, -1);
            
            # Continua o select
            $select .= " FROM tbservidor JOIN tbpessoa USING (idpessoa)
                                JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
               WHERE tbservidor.situacao = 1
                 AND idPerfil = 1                 
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

            if (!is_null($parametroSexo)) {
                $select .= " AND tbpessoa.sexo = '{$parametroSexo}'";
            }

            if (!empty($parametroLotacao)) {
                # Verifica se o que veio é numérico
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                    $subTitulo .= $pessoal->get_nomeCompletoLotacao($parametroLotacao);
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                    $subTitulo .= $parametroLotacao;
                }
            }

            $select .= " ORDER BY tbpessoa.sexo, tbpessoa.dtNasc, dtAdmissao";

            $result = $pessoal->select($select);
            

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_titulo('Relatório de Análise de Aposentadoria');
            $tabela->set_subtitulo("{$subTitulo}<br/>Em Ordem Decrescente de Idade");

            $tabela->set_label($label);
            $tabela->set_align($align);
            $tabela->set_funcao($funcao);
            $tabela->set_classe($classe);
            $tabela->set_metodo($metodo);

            $tabela->set_conteudo($result);
            $tabela->show();

//            $tabela->set_label(['Servidor', 'Idade', $texto, 'Data de Ingresso<br/>no Serv.Público', 'Tempo de Contribuição<br/>(em dias)', 'Tempo Averbado<br/>(em dias)', "Data com<br/>5 anos no Cargo Atual", "Data com<br/>10 anos Públicos", "Data com<br/>20 anos Públicos", "Data com<br/>25 anos Públicos", "Data com<br/>30 anos Públicos", "Data com<br/>35 anos Públicos"]);
//            $tabela->set_align(["left", "center", "center", "center", "left",  "left"]);
//            $tabela->set_funcao([null, "idade", "date_to_php"]);
//            $tabela->set_classe(["pessoal", null, null, "Aposentadoria", "Aposentadoria", "Averbacao", "Aposentadoria", "Aposentadoria", "Aposentadoria", "Aposentadoria", "Aposentadoria", "Aposentadoria"]);
//            $tabela->set_metodo(["get_nomeELotacaoEDtAdmissao", null, null, "get_dtIngressoParaTempoPublico", "exibe_tempoContribuicao", "exibe_tempoAverbadoTotal", "get_data5AnosCargo", "get_data10anosPublicos", "get_data20anosPublicos", "get_data25anosPublicos", "get_data30anosPublicos", "get_data35anosPublicos"]);
//            
            break;

        #######################################            

        case "editaServidor" :

            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaCtcInss.php?fase=aguarde');
            set_session('voltaCtc', 'areaCtcInss.php?fase=aguarde');

            # Carrega a página específica
            loadPage('servidorCtc.php');
            break;

        #######################################        
        # Relatório
        case "relatorio" :

            $subTitulo = null;

            $select = "SELECT tbservidor.idServidor,
                              tbpessoa.nome,
                              tbservidor.idServidor,
                              concat(IFnull(tblotacao.UADM,''),' - ',IFnull(tblotacao.DIR,''),' - ',IFnull(tblotacao.GER,'')) lotacao,
                              tbservidor.dtAdmissao,
                              tbservidor.idServidor,
                              tbservidor.idServidor
                        FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                             JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                             JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                             LEFT JOIN tbcargo ON (tbservidor.idCargo = tbcargo.idCargo)
                                             JOIN tbtipocargo ON (tbcargo.idTipoCargo = tbtipocargo.idTipoCargo)
                       WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                             AND dtAdmissao < '" . date_to_bd($dataLimite) . "'";

            # Situação
            if ($parametroSituacao == "Ativos") {
                $select .= ' AND situacao = 1';
                $titulo = "Servidores Estatutários Ativos Admitidos antes de {$dataLimite}";
            } else {
                $select .= ' AND situacao <> 1 AND (idPerfil = 4 OR idPerfil = 1)';
                $select .= ' AND tbpessoa.idPessoa IN (SELECT idPessoa FROM tbservidor WHERE situacao = 1)';
                $titulo = "Servidores Celetistas e/ou Estatutários Inativos Admitidos antes de {$dataLimite}";
            }

            # Lotação
            if (($parametroLotacao <> "*") AND ($parametroLotacao <> "")) {
                # Verifica se o que veio é numérico
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = '{$parametroLotacao}')";
                    $subTitulo = null;
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                    $subTitulo = $parametroLotacao;
                }
            }

            # Entregou  
            if ($parametroEntregou <> "Todos") {
                if ($parametroEntregou == "Sim") {
                    $select .= " AND tbservidor.entregouCtc = 's'";
                } elseif ($parametroEntregou == "Não") {
                    $select .= " AND tbservidor.entregouCtc = 'n'";
                } else {
                    $select .= " AND (tbservidor.entregouCtc is null)";
                }
            }

            $select .= " ORDER BY  tbservidor.entregouCtc desc, tbpessoa.nome";

            $result = $pessoal->select($select);

            $relatorio = new Relatorio();
            $relatorio->set_titulo($titulo);
            $relatorio->set_subtitulo($subTitulo);
            $relatorio->set_label(['Id Funcional', 'Nome', 'Cargo', 'Lotação', 'Admissão', 'Entregou CTC?']);
            $relatorio->set_align(["center", "left", "left", "left"]);
            $relatorio->set_funcao([null, null, null, null, "date_to_php"]);
            $relatorio->set_classe(["pessoal", null, "pessoal", null, null, "Aposentadoria"]);
            $relatorio->set_metodo(["get_idFuncional", null, "get_cargoSimples", null, null, "exibeEntregouCtcRelatorio"]);

            if (is_numeric($parametroLotacao)) {
                $relatorio->set_numGrupo(3);
            }
            $relatorio->set_conteudo($result);
            $relatorio->show();
            break;
    }

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}