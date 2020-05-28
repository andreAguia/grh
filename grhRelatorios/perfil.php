<?php

/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */
# Servidor logado 
$idUsuario = NULL;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $lotacao = get('lotacao', post('lotacao'));

    ######

    $select = 'SELECT idPerfil,
                     nome,
                     tipo,
                     idPerfil,
                     idPerfil,
                     progressao,
                     trienio,
                     comissao,
                     gratificacao,
                     ferias,
                     licenca,
                     idPerfil,
                     idPerfil
                FROM tbperfil
            ORDER BY idPerfil';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Perfil');
    #$relatorio->set_subtitulo('Agrupados por Diretoria - Ordenados pelo Nome');
    $relatorio->set_label(array("id", "Perfil", "Tipo", "Ativos", "Inativos", "Progressão", "Triênio", "Cargo em Comissão", "Gratificação", "Férias", "Licença"));
    $relatorio->set_classe(array(NULL, NULL, NULL, "Pessoal", "Pessoal"));
    $relatorio->set_metodo(array(NULL, NULL, NULL, "get_servidoresAtivosPerfil", "get_servidoresInativosPerfil"));
    $relatorio->set_align(array("center", "left", "left"));
    $relatorio->set_conteudo($result);
    $relatorio->set_subTotal(FALSE);
    $relatorio->show();

    $page->terminaPagina();
}