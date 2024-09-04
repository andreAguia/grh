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
    $subTitulo = null;
    $titulo = "Servidores com Solicitação de Redução de Carga Horária";

    # Pega os dados
    $select = "SELECT CASE tbreducao.tipo
                    WHEN 1 THEN 'Inicial'
                    WHEN 2 THEN 'Renovação'
                    ELSE '--'
                  END,                              
                  idReducao,
                  tbservidor.idServidor,                              
                  idServidor,
                  idReducao,
                  idReducao,
                  idReducao,
                  idServidor,
                  ADDDATE(dtInicio,INTERVAL periodo MONTH) as dtTermino
             FROM tbservidor JOIN tbpessoa USING (idPessoa)
                             JOIN tbreducao USING (idServidor)
                             JOIN tbperfil USING (idPerfil)
            WHERE tbperfil.tipo <> 'Outros' ";

    # status
    if ($parametroStatus <> 0) {
        $select .= " AND status = " . $parametroStatus;
        $subTitulo .= "Status: " . $statusPossiveis[$parametroStatus][1];
    }

    # nome
    if (!is_null($parametroNomeMat)) {

        # Verifica se tem espaços
        if (strpos($parametroNomeMat, ' ') !== false) {
            # Separa as palavras
            $palavras = explode(' ', $parametroNomeMat);

            # Percorre as palavras
            foreach ($palavras as $item) {
                $select .= " AND (tbpessoa.nome LIKE '%{$item}%')";
            }
        } else {
            $select .= " AND (tbpessoa.nome LIKE '%{$parametroNomeMat}%')";
        }

        $subTitulo .= "Nome: " . $parametroNomeMat . " ";
    }

    $select .= " ORDER BY status, 
                    CASE WHEN status = 3 THEN dtTermino END DESC,
                    CASE WHEN status <> 3 THEN dtTermino END ASC,
                    dtInicio";

    $resumo = $pessoal->select($select);

    # Monta o Relatório
    $relatorio = new Relatorio();
    $relatorio->set_conteudo($resumo);

    $relatorio->set_label(["Tipo", "Status", "Servidor", "Processo", "Resultado", "Publicação", "Período"]);
    $relatorio->set_align(["center", "center", "left", "center", "center", "center", "left"]);
    $relatorio->set_classe([null, "ReducaoCargaHoraria", "Pessoal", "ReducaoCargaHoraria", "ReducaoCargaHoraria", "ReducaoCargaHoraria", "ReducaoCargaHoraria"]);
    $relatorio->set_metodo([null, "exibeStatus", "get_nomeEidFuncional", "get_numProcesso", "exibeResultado", "exibePublicacao", "exibePeriodo"]);

    $relatorio->set_titulo($titulo);
    $relatorio->set_subtitulo($subTitulo);
    $relatorio->show();

    $page->terminaPagina();
}