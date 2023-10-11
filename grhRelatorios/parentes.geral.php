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
    $select = "SELECT tbdependente.nome,
                     TIMESTAMPDIFF (YEAR,tbdependente.dtNasc,CURDATE()) as idade,
                     tbparentesco.Parentesco,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbservidor.idServidor
                FROM tbdependente JOIN tbpessoa USING (idPessoa)
                                  JOIN tbservidor USING (idPessoa)
                                  JOIN tbperfil USING (idPerfil)
                                  JOIN tbparentesco USING (idParentesco)
              WHERE situacao = 1
                AND tbperfil.tipo <> 'Outros'  
           ORDER BY tbdependente.nome";

    $result = $pessoal->select($select);

    # Monta o Relatório
    $relatorio = new Relatorio();
    $relatorio->set_conteudo($result);

    $relatorio->set_titulo('Cadastro de Parentes de Servidores Ativos');
    $relatorio->set_subtitulo("Ordenados por Nome");

    $relatorio->set_label(["Parente", "Idade", "Parentesco", "Servidor", "Cargo", "Lotação"]);
    $relatorio->set_align(["left", "center", "center", "left", "left", "left"]);
    $relatorio->set_classe([null, null, null, null, "pessoal", "pessoal"]);
    $relatorio->set_metodo([null, null, null, null, "get_Cargo", "get_Lotacao"]);
    $relatorio->show();

    $page->terminaPagina();
}