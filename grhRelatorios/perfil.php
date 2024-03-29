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
    $relatorio->set_label(["id", "Perfil", "Tipo", "Ativos", "Inativos", "Progressão", "Triênio", "Cargo em Comissão", "Gratificação", "Férias", "Licença"]);
    $relatorio->set_align(["center", "left", "left"]);
    
    $relatorio->set_classe([null, null, null, "Pessoal", "Pessoal"]);
    $relatorio->set_metodo([null, null, null, "get_servidoresAtivosPerfil", "get_servidoresInativosPerfil"]);
    
    $relatorio->set_colunaSomatorio([3,4]);
    $relatorio->set_conteudo($result);
    $relatorio->set_subTotal(false);
    $relatorio->show();

    $page->terminaPagina();
}