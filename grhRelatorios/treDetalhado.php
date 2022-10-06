<?php

/**
 * Relatório
 *    
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null; # Servidor Editado na pesquisa do sistema do GRH
# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Pega os parâmetros
    $selectRelatorio = get_session('selectRelatorio');
    $parametroNomeMat = get_session('parametroNomeMat');
    $parametroLotacao = get_session('parametroLotacao');
    $subtitulo = null;

    # Pega os dados
    $result = $pessoal->select($selectRelatorio);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    $menuRelatorio = new menuRelatorio();
    $menuRelatorio->show();

    # Título
    p("Relatório TRE Detalhado", "pRelatorioTitulo");

    # Lotação
    if (!is_null($parametroLotacao) AND ($parametroLotacao <> "*")) {
        $subtitulo .= $pessoal->get_nomeLotacao($parametroLotacao);
    }

    if (!is_null($parametroNomeMat)) {
        $subtitulo .= "Filtro: " . $parametroNomeMat;
    }

    # Subtítulo
    if (!is_null($subtitulo)) {
        p($subtitulo, "pRelatorioSubtitulo");
    }
    
    br();

    foreach ($result as $dados) {

        #####################################
        # Servidor
        $grid = new Grid();
        $grid->abreColuna(12);
        
        $identificação = [
            [$pessoal->get_nome($dados["idServidor"])],
        ];
        
        $relatorio = new Relatorio();
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(false);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_label([""]);
        $relatorio->set_align(['left', 'left']);
        $relatorio->set_conteudo($identificação);
        $relatorio->show();

        $grid->fechaColuna();
        $grid->abreColuna(6);

        $identificação = [
            ["IdFuncional:", $pessoal->get_idFuncional($dados["idServidor"])],
            ["Cargo:", $pessoal->get_cargo($dados["idServidor"])],
            ["Lotação:", $pessoal->get_lotacao($dados["idServidor"])],
            ["Perfil:", $pessoal->get_perfil($dados["idServidor"])],
        ];

        tituloRelatorio('Identificação do Servidor');
        $relatorio = new Relatorio();
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(false);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_label(["Descrição", "Dias"]);
        $relatorio->set_align(['left', 'left']);
        $relatorio->set_conteudo($identificação);
        $relatorio->show();

        $grid->fechaColuna();

        #####################################
        # Resumo

        $grid->abreColuna(6);

        # Pegas os valores
        $diasTrabalhados = $pessoal->get_treDiasTrabalhados($dados["idServidor"]);
        $folgasConcedidas = $pessoal->get_treFolgasConcedidas($dados["idServidor"]);
        $folgasFruidas = $pessoal->get_treFolgasFruidas($dados["idServidor"]);
        $folgasPendentes = $folgasConcedidas - $folgasFruidas;

        $resumo = Array(
            Array('Dias Trabalhados', $diasTrabalhados),
            Array('Folgas Concedidas', $folgasConcedidas),
            Array('Folgas Fruídas', $folgasFruidas),
            Array('Folgas Pendentes', $folgasPendentes)
        );

        tituloRelatorio('Resumo');
        $relatorio = new Relatorio();
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(false);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_label(["Descrição", "Dias"]);
        $relatorio->set_align(['left']);
        $relatorio->set_conteudo($resumo);
        $relatorio->show();

        $grid->fechaColuna();

        #####################################
        # Dias Trabalhados e Folgas Concedidas

        $grid->abreColuna(6);

        $select = 'SELECT data,
                      ADDDATE(data,dias-1),
                      dias,
                      folgas
                 FROM tbtrabalhotre
                WHERE idServidor=' . $dados["idServidor"] . '
             ORDER BY data desc';

        $dtrab = $pessoal->select($select);

        tituloRelatorio('Dias Trabalhados e Folgas Concedidas');
        $relatorio = new Relatorio();
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(false);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_label(["Início", "Término", "Dias Trabalhados", "Folgas Concedidas"]);
        $relatorio->set_funcao(["date_to_php", "date_to_php"]);
        $relatorio->set_colunaSomatorio([2, 3]);
        $relatorio->set_conteudo($dtrab);
        $relatorio->show();

        $grid->fechaColuna();
        #####################################
        #  Folgas Fruídas
        $grid->abreColuna(6);

        $select = 'SELECT data,
                    ADDDATE(data,dias-1),                                 
                    dias,
                    idFolga
               FROM tbfolga
              WHERE idServidor=' . $idServidorPesquisado . '
           ORDER BY data desc';

        $folgas = $pessoal->select($select);

        tituloRelatorio('Folgas Fruídas');
        $relatorio = new Relatorio();
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(false);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_label(["Início", "Término", "Folgas Fruídas"]);
        $relatorio->set_funcao(["date_to_php", "date_to_php"]);
        $relatorio->set_colunaSomatorio(2);
        $relatorio->set_conteudo($folgas);
        $relatorio->show();

        $grid->fechaColuna();
        $grid->abreColuna(12);

        hr();

        $grid->fechaColuna();
        $grid->fechaGrid();
    }

    $page->terminaPagina();
}