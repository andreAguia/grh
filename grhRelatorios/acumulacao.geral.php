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

    ######   
    # Título & Subtitulo
    $subTitulo = null;
    $titulo = "Servidores com Acumulação de Cargo Público";

    # Começa uma nova página
    $page = new Page();
    $page->set_title($titulo);
    $page->iniciaPagina();

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

    # Nome
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

    $select .= " ORDER BY conclusao, tbpessoa.nome";

    $resumo = $pessoal->select($select);

    # Monta o Relatório
    $relatorio = new Relatorio();
    $relatorio->set_conteudo($resumo);

    $relatorio->set_label(["Conclusão", "Resultado", "Publicação", "Servidor", "Processo", "Vínculo da Uenf", "Outro Vínculo"]);
    $relatorio->set_align(["center", "center", "center", "left", "center", "left", "left"]);
    $relatorio->set_classe([null, "Acumulacao", "Acumulacao", "Pessoal", "Acumulacao", "Acumulacao", "Acumulacao"]);
    $relatorio->set_metodo([null, "get_resultado", "exibePublicacao", "get_nomeEidFuncional", "exibeProcesso", "exibeDadosUenf", "exibeDadosOutroVinculo"]);
    $relatorio->set_titulo($titulo);
    $relatorio->set_subtitulo($subTitulo);
    $relatorio->set_bordaInterna(true);
    $relatorio->show();

    $page->terminaPagina();
}