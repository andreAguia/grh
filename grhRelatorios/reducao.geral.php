<?php

/**
 * Relatório
 *    
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = NULL;              # Servidor logado
# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

# Pega os parâmetros
$parametroNomeMat = get_session('parametroNomeMat');
$parametroStatus = get_session('parametroStatus', 0);

# Variáveis
$statusPossiveis = array(array(0, "-- Todos --"), array(1, "Em Aberto"), array(2, "Vigente"), array(3, "Arquivado"));

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######   
    # Título & Subtitulo
    $subTitulo = NULL;
    $titulo = "Servidores com Solicitação de Redução de Carga Horária";

    # Pega os dados
    $select = "SELECT idServidor,
                      tbpessoa.nome,
                      CASE tipo
                        WHEN 1 THEN 'Inicial'
                        WHEN 2 THEN 'Renovação'
                        ELSE '--'
                      END,     
                      idReducao,                              
                      idServidor,
                      dtSolicitacao,
                      idReducao,
                      idReducao,
                      idReducao,
                      idReducao,
                      idServidor
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                 JOIN tbreducao USING (idServidor)
                WHERE tbservidor.idPerfil <> 10";

    # status
    if ($parametroStatus <> 0) {
        $select .= " AND status = " . $parametroStatus;
        $subTitulo .= "Status: " . $statusPossiveis[$parametroStatus][1];
    }

    # nome ou matrícula
    if (!is_null($parametroNomeMat)) {
        $select .= " AND tbpessoa.nome LIKE '%$parametroNomeMat%'";
        $subTitulo .= "Nome: " . $parametroNomeMat;
    }

    $select .= " ORDER BY status, dtInicio";

    $resumo = $pessoal->select($select);

    # Monta o Relatório
    $relatorio = new Relatorio();
    $relatorio->set_conteudo($resumo);
    $relatorio->set_label(array("Id/Matrícula", "Nome", "Tipo", "Status", "Processo", "Solicitado em:", "Pericia", "Resultado", "Publicação", "Período"));
    $relatorio->set_align(array("center", "left", "center", "center", "center", "center", "left", "center", "center", "left"));
    $relatorio->set_funcao(array("idMatricula", NULL, NULL, NULL, NULL, "date_to_php"));

    $relatorio->set_classe(array(NULL, NULL, NULL, "ReducaoCargaHoraria", "ReducaoCargaHoraria", NULL, "ReducaoCargaHoraria", "ReducaoCargaHoraria", "ReducaoCargaHoraria", "ReducaoCargaHoraria"));
    $relatorio->set_metodo(array(NULL, NULL, NULL, "exibeStatus", "get_numProcesso", NULL, "exibeDadosPericia", "exibeResultado", "exibePublicacao", "exibePeriodo"));

    $relatorio->set_titulo($titulo);
    $relatorio->set_subtitulo($subTitulo);
    $relatorio->show();

    $page->terminaPagina();
}