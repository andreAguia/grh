<?php

/**
 * Área de Licença Prêmio
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

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a área de licença médica";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros
    $parametroNomeMat = post('parametroNomeMat', get_session('parametroNomeMat'));
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao'));
    $parametroAlta = post('parametroAlta', get_session('parametroAlta', "Sem Alta"));

    # Joga os parâmetros par as sessions    
    set_session('parametroNomeMat', $parametroNomeMat);
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroAlta', $parametroAlta);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    $grid = new Grid();
    $grid->abreColuna(12);

    # Cria um menu
    $menu1 = new MenuBar();

    # Voltar
    $botaoVoltar = new Link("Voltar", "grh.php");
    $botaoVoltar->set_class('button');
    $botaoVoltar->set_title('Voltar a página anterior');
    $botaoVoltar->set_accessKey('V');
    $menu1->add_link($botaoVoltar, "left");

    $menu1->show();

    ################################################################
    # Formulário de Pesquisa
    $form = new Form('?');

    $controle = new Input('parametroNomeMat', 'texto', 'Nome, Matrícula ou id:', 1);
    $controle->set_size(100);
    $controle->set_title('Nome do servidor');
    $controle->set_valor($parametroNomeMat);
    $controle->set_autofocus(true);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_linha(1);
    $controle->set_col(3);
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
    $controle->set_linha(1);
    $controle->set_col(6);
    $form->add_item($controle);

    $controle = new Input('parametroAlta', 'combo', 'Alta:', 1);
    $controle->set_size(30);
    $controle->set_title('Filtra por Alta');
    $controle->set_array(["Com Alta", "Sem Alta"]);
    $controle->set_valor($parametroAlta);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_linha(1);
    $controle->set_col(3);
    $form->add_item($controle);

    $form->show();

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

            loadPage('?fase=lista');
            break;

################################################################

        case "lista" :

            # Pega os dados
            $select = "SELECT tbservidor.idServidor,
                              tblicenca.idLicenca,
                              tblicenca.idLicenca,
                              tblicenca.idTpLicenca,
                              tbservidor.idServidor
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         JOIN tblicenca USING (idServidor)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND tblicenca.dtInicial = (select max(dtInicial) from tblicenca where tblicenca.idServidor = tbservidor.idServidor AND (tblicenca.idTpLicenca = 1 OR tblicenca.idTpLicenca = 2 OR tblicenca.idTpLicenca = 30))
                          AND situacao = 1
                          AND (tblicenca.idTpLicenca = 1 OR tblicenca.idTpLicenca = 2 OR tblicenca.idTpLicenca = 30)                          
                          AND idPerfil = 1";

            # Alta
            if ($parametroAlta == "Sem Alta") {
                $select .= " AND alta <> 1";
                $titulo = "Servidores Com a Última Licença Médica SEM ALTA";
                $mensagem = "Servidores cuja data de término já passou estão com a licença em aberto. Deverão solicitar a prorrogação ou a alta.";
            } else {
                $select .= " AND alta = 1";
                $titulo = "Servidores Com a Última Licença Médica COM ALTA";
                $mensagem = "Servidores devem retornar ao seus setores no dia imediatamente após ao término da licença.";
            }

            # Matrícula, nome ou id
            if (!is_null($parametroNomeMat)) {
                if (is_numeric($parametroNomeMat)) {
                    $select .= ' AND ((';
                } else {
                    $select .= ' AND (';
                }

                $select .= 'tbpessoa.nome LIKE "%' . $parametroNomeMat . '%")';

                if (is_numeric($parametroNomeMat)) {
                    $select .= ' OR (tbservidor.matricula LIKE "%' . $parametroNomeMat . '%")
                                 OR (tbservidor.idfuncional LIKE "%' . $parametroNomeMat . '%"))';
                }
            }

            # Lotação
            if (($parametroLotacao <> "*") AND ($parametroLotacao <> "")) {
                if (is_numeric($parametroLotacao)) {
                    $select .= ' AND (tblotacao.idlotacao = "' . $parametroLotacao . '")';
                } else { # senão é uma diretoria genérica
                    $select .= ' AND (tblotacao.DIR = "' . $parametroLotacao . '")';
                }
            }

            $select .= "  ORDER BY ADDDATE(dtInicial,numDias-1)";

            if ($parametroAlta <> "Sem Alta") {
                $select .= " DESC";
            }

            # Guarde o select para o relatório
            set_session('selectRelatorio', $select);

            $resumo = $pessoal->select($select);

            callout($mensagem);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_label(["Servidor", "Período", "Análise", "Tipo"]);
            $tabela->set_align(["left", "left", "center", "left"]);
            #$tabela->set_width([15, 15, 15, 7, 5, 8, 15, 15]);
            #$tabela->set_funcao([ null, "date_to_php", null, "date_to_php"]);
            $tabela->set_classe(["pessoal", "Licenca", "Licenca", "Licenca"]);
            $tabela->set_metodo(["get_nomeECargoELotacao", "exibePeriodo", "analisaTermino", "exibeNomeSimples"]);
            $tabela->set_titulo($titulo);
            #$tabela->set_mensagemPreTabela($mensagem);

            $tabela->set_editar('?fase=editaServidor&id=');
            $tabela->set_nomeColunaEditar("Acessar");
            $tabela->set_editarBotao("bullet_edit.png");
            $tabela->set_idCampo('idServidor');
            $tabela->show();
            break;

        ################################################################
        # Chama o menu do Servidor que se quer editar
        case "editaServidor" :
            
            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaLicencaMedica.php');

            # Carrega a página específica
            loadPage('servidorLicenca.php');            
            break;

        ################################################################
    }
    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


