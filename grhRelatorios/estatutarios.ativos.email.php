<?php

/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */
# Servidor logado 
$idUsuario = null;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######

    $relatorio = new Relatorio();

    $select = "SELECT tbpessoa.nome,                      
                      tbservidor.idservidor,
                      tbservidor.idservidor
                 FROM tbservidor JOIN tbpessoa USING (idpessoa)
                WHERE tbservidor.situacao = 1
                  AND idPerfil = 1
             ORDER BY tbpessoa.nome";

    $result = $servidor->select($select);

    $relatorio->set_titulo('Relatório de Servidores Estatutários Ativos');
    $relatorio->set_label(['Servidor', 'Cargo', 'Email Uenf']);
    $relatorio->set_align(["left", "left", "left"]);
    $relatorio->set_funcao([null, null, null, "date_to_php"]);
    $relatorio->set_classe([null, "pessoal", "pessoal"]);
    $relatorio->set_metodo([null, "get_cargoSimples", "get_emailUenf"]);
    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}