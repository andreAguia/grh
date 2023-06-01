<?php

/**
 * Relatório
 *    
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

# Pega os parâmetros
$parametroNomeMat = get_session('parametroNomeMat');
$parametroStatus = get_session('parametroStatus', 0);
$parametroOrigem = get_session('parametroOrigem', 0);

# Variáveis
$statusPossiveis = array(array(0, "-- Todos --"), array(1, "Em Aberto"), array(2, "Vigente"), array(3, "Arquivado"));
$origemsPossiveis = array(array(0, "-- Todos --"), array(1, "Ex-Ofício"), array(2, "Solicitada"));

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ###### 
    # Título & Subtitulo
    $subTitulo = null;
    $titulo = "Servidores com Solicitação de Readaptação";

    # Pega os dados
    $select = "SELECT idServidor,
                      tbpessoa.nome,
                      CASE origem
                                WHEN 1 THEN 'Ex-Ofício'
                                WHEN 2 THEN 'Solicitada'
                                ELSE '--'
                              END,
                              CASE tipo
                                WHEN 1 THEN 'Inicial'
                                WHEN 2 THEN 'Renovação'
                                ELSE '--'
                              END,
                      idReadaptacao,
                      processo,
                      idReadaptacao,
                      idReadaptacao,
                      idReadaptacao,
                      idReadaptacao,
                      idReadaptacao,                                   
                      idReadaptacao
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                 JOIN tbreadaptacao USING (idServidor)
                                 JOIN tbperfil USING (idPerfil)
                WHERE tbperfil.tipo <> 'Outros' ";

    # status
    if ($parametroStatus <> 0) {
        $select .= " AND status = " . $parametroStatus;
        $subTitulo .= "Status: " . $statusPossiveis[$parametroStatus][1] . " ";
    }

    # origem
    if ($parametroOrigem <> 0) {
        $select .= " AND origem = " . $parametroOrigem;
        $subTitulo .= "Origem: " . $origemsPossiveis[$parametroOrigem][1] . " ";
    }

    # nome ou matrícula
    if (!is_null($parametroNomeMat)) {
        $select .= " AND tbpessoa.nome LIKE '%$parametroNomeMat%'";
        $subTitulo .= "Nome: " . $parametroNomeMat . " ";
    }


    $select .= " ORDER BY status, dtInicio";

    $resumo = $pessoal->select($select);

    # Monta o Relatório
    $relatorio = new Relatorio();
    $relatorio->set_conteudo($resumo);
    $relatorio->set_label(["idServidor", "Nome", "Origem", "Tipo", "Status", "Processo", "Resultado", "Publicação", "Período"]);
    $relatorio->set_align(["center", "left", "center", "center", "center", "center", "center", "center", "left"]);
    $relatorio->set_funcao(["idMatricula"]);
    $relatorio->set_classe([null, null, null, null, "Readaptacao", null, "Readaptacao", "Readaptacao", "Readaptacao"]);
    $relatorio->set_metodo([null, null, null, null, "exibeStatus", null, "exibeResultado", "exibePublicacao", "exibePeriodo"]);
    $relatorio->set_titulo($titulo);
    $relatorio->set_subtitulo($subTitulo);
    $relatorio->show();

    $page->terminaPagina();
}