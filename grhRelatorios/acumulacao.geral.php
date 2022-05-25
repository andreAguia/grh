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

if ($acesso) {

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######   
    # Título & Subtitulo
    $subTitulo = null;
    $titulo = "Servidores com Acumulação de Cargo Público";

    # Pega os dados
    $select = "SELECT CASE conclusao
                                WHEN 1 THEN 'Pendente'
                                WHEN 2 THEN 'Resolvido'
                                ELSE '--'
                              END,
                              idAcumulacao,
                              idAcumulacao,
                              tbservidor.idServidor,
                              idAcumulacao,
                              tbservidor.idServidor,
                              idAcumulacao,                         
                              tbservidor.idServidor
                         FROM tbacumulacao JOIN tbservidor USING (idServidor)
                                           JOIN tbpessoa USING (idPessoa)
                        WHERE true";

    # nome
    if (!is_null($parametroNomeMat)) {
        $select .= " AND tbpessoa.nome LIKE '%$parametroNomeMat%'";
    }

    $select .= " ORDER BY conclusao, tbpessoa.nome";

    $resumo = $pessoal->select($select);

    # Monta o Relatório
    $relatorio = new Relatorio();
    $relatorio->set_conteudo($resumo);

    $relatorio->set_label(array("Conclusão", "Resultado", "Publicação", "Servidor", "Processo", "Vínculo da Uenf", "Outro Vínculo"));
    $relatorio->set_align(array("center", "center", "center", "left", "center", "left", "left"));
    #$relatorio->set_funcao(array(null, null, "date_to_php"));
    $relatorio->set_classe(array(null, "Acumulacao", "Acumulacao", "Pessoal", "Acumulacao", "Acumulacao", "Acumulacao"));
    $relatorio->set_metodo(array(null, "get_resultado", "exibePublicacao", "get_nomeEidFuncional", "exibeProcesso", "exibeDadosUenf", "exibeDadosOutroVinculo"));
    $relatorio->set_titulo($titulo);
    $relatorio->set_subtitulo($subTitulo);
    $relatorio->show();

    $page->terminaPagina();
}