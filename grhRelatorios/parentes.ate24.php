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

if ($acesso) {

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######   
    # Título & Subtitulo
    $subTitulo = NULL;
    $titulo = "Servidores com Acumulação de Cargo Público";

    # Pega os dados
    $select = 'SELECT tbdependente.nome,
                     TIMESTAMPDIFF (YEAR,tbdependente.dtNasc,CURDATE()) as idade,
                     tbparentesco.Parentesco,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbservidor.idServidor
                FROM tbdependente JOIN tbpessoa USING (idPessoa)
                                  JOIN tbservidor USING (idPessoa)
                                  JOIN tbparentesco ON (tbdependente.parentesco = tbparentesco.idParentesco)
              WHERE situacao = 1 AND TIMESTAMPDIFF (YEAR,tbdependente.dtNasc,CURDATE()) < 25
              ORDER BY tbdependente.nome';

    $result = $pessoal->select($select);

    # Monta o Relatório
    $relatorio = new Relatorio();
    $relatorio->set_conteudo($result);

    $relatorio->set_titulo('Cadastro de Parentes de Servidores Ativos');
    $relatorio->set_subtitulo("Com até 24 anos");

    $relatorio->set_label(array("Parente", "Idade", "Parentesco", "Servidor", "Cargo", "Lotação"));
    $relatorio->set_align(array("left", "center", "center", "left", "left", "left"));
    $relatorio->set_classe(array(NULL, NULL, NULL, NULL, "pessoal", "pessoal"));
    $relatorio->set_metodo(array(NULL, NULL, NULL, NULL, "get_Cargo", "get_Lotacao"));
    $relatorio->show();

    $page->terminaPagina();
}