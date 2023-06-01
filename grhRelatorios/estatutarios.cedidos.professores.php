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

    $select = 'SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbhistcessao.orgao,
                     tbhistcessao.dtInicio,
                     tbhistcessao.dtFim
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                               RIGHT JOIN tbhistcessao ON(tbservidor.idServidor = tbhistcessao.idServidor)
                                LEFT JOIN tbcargo USING (idCargo)
                                     JOIN tbtipocargo USING (idTipoCargo) 
               WHERE tbservidor.idPerfil = 1
               AND tbtipocargo.tipo = "Professor"
                 AND situacao = 1 
                 AND (tbhistcessao.dtFim IS NULL OR (now() BETWEEN tbhistcessao.dtInicio AND tbhistcessao.dtFim)) 
             ORDER BY nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Professores Estatutários Atualmente Cedidos');
    $relatorio->set_subtitulo('Ordenados por Nome');

    $relatorio->set_label(['IdFuncional', 'Nome', 'Cargo', 'Órgão', 'Início', 'Término']);
    $relatorio->set_width([10, 30, 20, 20, 10, 10]);
    $relatorio->set_align(["center", "left", "left", "left"]);
    $relatorio->set_funcao([null, null, null, null, "date_to_php", "date_to_php"]);
    $relatorio->set_classe([null, null, "Pessoal"]);
    $relatorio->set_metodo([null, null, "get_Cargo"]);

    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}
?>
