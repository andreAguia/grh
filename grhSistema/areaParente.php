<?php

/**
 * Área de Parente
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
        $atividade = "Visualizou a área de parentes";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros    
    $parametroNome = post('parametroNome', get_session('parametroNome'));
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', $pessoal->get_idLotacao($intra->get_idServidor($idUsuario))));
    $parametroParentesco = post('parametroParentesco', get_session('parametroParentesco'));

    # Joga os parâmetros par as sessions   
    set_session('parametroNome', $parametroNome);
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroParentesco', $parametroParentesco);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "relatorio") {
        AreaServidor::cabecalho();
    }

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

            loadPage('?fase=exibeLista');
            break;

################################################################

        case "exibeLista" :
            $grid = new Grid();
            $grid->abreColuna(12);
            br();

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "grh.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dessa pesquisa");
            $botaoRel->set_url('../grhRelatorios/parentes.geral.php');
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            $menu1->add_link($botaoRel, "right");

            $menu1->show();

            ##############
            # Formulário de Pesquisa
            $form = new Form('?');

            # Nome do Parente
            $controle = new Input('parametroNome', 'texto', 'Nome do Parente', 1);
            $controle->set_size(55);
            $controle->set_title('Nome, matrícula ou ID:');
            $controle->set_valor($parametroNome);
            $controle->set_autofocus(true);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
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
            $controle->set_col(5);
            $form->add_item($controle);

            # Parentesco
            $parente = $pessoal->select('SELECT idParentesco, Parentesco
                                           FROM tbparentesco
                                         ORDER BY Parentesco');
            array_unshift($parente, array('*', '-- Todos --'));

            # Parentesco
            $controle = new Input('parametroParentesco', 'combo', 'Parentesco:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Parentesco');
            $controle->set_array($parente);
            $controle->set_valor($parametroParentesco);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            $form->add_item($controle);

            $form->show();

            ##############
            # Pega os dados
            $select = "SELECT tbservidor.idServidor,
                              tbdependente.nome,
                              tbdependente.cpf,
                              tbparentesco.Parentesco,
                              tbdependente.dtNasc,
                              TIMESTAMPDIFF (YEAR,tbdependente.dtNasc,CURDATE())
                         FROM tbdependente JOIN tbpessoa USING (idPessoa)
                                           JOIN tbservidor USING (idPessoa)
                                           JOIN tbparentesco USING (idParentesco)
                                           JOIN tbhistlot USING (idServidor)
                                           JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                       WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)                  
                         AND situacao = 1";

            # nome do parente e do servidor
            if (!empty($parametroNome)) {

                # Verifica se tem espaços
                if (strpos($parametroNome, ' ') !== false) {
                    # Separa as palavras
                    $palavras = explode(' ', $parametroNome);

                    $contator = 1;
                    $select .= " AND ((";    

                    # Percorre as palavras para o servidor
                    foreach ($palavras as $item) {

                        if ($contator == 1) {
                            $select .= "tbpessoa.nome LIKE '%{$item}%' ";
                            $contator = 0;
                        } else {
                            $select .= "AND tbpessoa.nome LIKE '%{$item}%' ";
                        }
                    }
                    
                    $contator = 1;
                    
                    $select .= ") OR (";                    

                    # Percorre as palavras para o parente
                    foreach ($palavras as $item) {
                        if ($contator == 1) {
                            $select .= "tbdependente.nome LIKE '%{$item}%' ";
                            $contator = 0;
                        } else {
                            $select .= "AND tbdependente.nome LIKE '%{$item}%' ";
                        }
                    }
                    
                    $select .= ")) ";  
                } else {
                    $select .= " AND (tbdependente.nome LIKE '%{$parametroNome}%'";
                    $select .= " OR tbpessoa.nome LIKE '%{$parametroNome}%') ";
                }
            }
            
            if (($parametroParentesco <> "*") AND ($parametroParentesco <> "")) {
                $select .= " AND idParentesco = {$parametroParentesco}";
            }

            # Lotação
            if (($parametroLotacao <> "*") AND ($parametroLotacao <> "")) {
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            $select .= " ORDER BY tbpessoa.nome, tbdependente.nome";

            $result = $pessoal->select($select);

            $tabela = new Tabela();
            $tabela->set_titulo('Cadastro de Parentes de Servidores');
            #$tabela->set_subtitulo('Filtro: '.$relatorioParametro);
            $tabela->set_label(["Servidor", "Parente", "CPF", "Parentesco", "Nascimento", "Idade"]);
            #$tabela->set_width([30, 30, 10, 10, 10, 10]);
            $tabela->set_conteudo($result);
            $tabela->set_align(["left", "left"]);

            $tabela->set_classe(["Pessoal"]);
            $tabela->set_metodo(["get_nomeECargoELotacao"]);
            $tabela->set_funcao([null, null, null, null, "date_to_php"]);

            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editaServidor');

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            if (!vazio($parametroNome)) {
                $tabela->set_textoRessaltado($parametroNome);
            }

            $tabela->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "editaServidor" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaParente.php');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        ################################################################
        # Relatório
        case "relatorio" :

            $subTitulo = null;

            # Pega os dados
            $select = 'SELECT tbservidor.idfuncional,
                          tbpessoa.nome,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbescolaridade.escolaridade,
                          idFormacao
                     FROM tbformacao JOIN tbpessoa USING (idPessoa)
                                     JOIN tbservidor USING (idPessoa)
                                     JOIN tbescolaridade USING (idEscolaridade)
                                     LEFT JOIN tbcargo USING (idCargo)
                                     LEFT JOIN tbtipocargo USING (idTipoCargo)
                     WHERE situacao = 1
                       AND idPerfil = 1';

            if ($parametroNivel <> "Todos") {
                $select .= ' AND tbtipocargo.nivel = "' . $parametroNivel . '"';
                $subTitulo .= 'Cargo Efetivo de Nível ' . $parametroNivel . '<br/>';
            }

            if ($parametroEscolaridade <> "*") {
                $select .= ' AND tbformacao.idEscolaridade = ' . $parametroEscolaridade;
                $subTitulo .= 'Curso de Nível ' . $pessoal->get_escolaridade($parametroEscolaridade) . '<br/>';
            }

            if (!vazio($parametroCurso)) {
                $select .= ' AND tbformacao.habilitacao like "%' . $parametroCurso . '%"';
                $subTitulo .= 'Filtro : ' . $parametroCurso . '<br/>';
            }

            $select .= ' ORDER BY tbpessoa.nome, tbformacao.anoTerm';

            # Monta o Relatório
            $relatorio = new Relatorio();
            $relatorio->set_titulo('Relatório Geral de Formação Servidores');

            if (!is_null($subTitulo)) {
                $relatorio->set_subtitulo($subTitulo);
            }

            $result = $pessoal->select($select);

            $relatorio->set_label(array("IdFuncional", "Nome", "Cargo", "Lotação", "Escolaridade", "Curso"));
            $relatorio->set_conteudo($result);
            $relatorio->set_align(array("center", "left", "left", "left", "left", "left"));
            $relatorio->set_classe(array(null, null, "pessoal", "pessoal", null, "Formacao"));
            $relatorio->set_metodo(array(null, null, "get_Cargo", "get_Lotacao", null, "get_curso"));
            $relatorio->show();
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


