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
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######

    $select = 'SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbdocumentacao.cpf,                    
                     "_________________________________________"
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     JOIN tbhistlot USING (idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                LEFT JOIN tbcargo USING (idCargo)
                                     JOIN tbtipocargo USING (idTipoCargo) 
                                     JOIN tbdocumentacao using (idPessoa)
               WHERE tbservidor.situacao = 1
                 AND (idPerfil = 1 OR idPerfil = 4)
                 AND tbtipocargo.tipo = "Professor"
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                 AND (tblotacao.idlotacao <> 94 AND tblotacao.idlotacao <> 90 AND tblotacao.idlotacao <> 120)
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Professores Ativos');
    $relatorio->set_tituloLinha2('Polo Campos dos Goytacazes');
    $relatorio->set_subtitulo('Ordenado pelo Nome');
    $relatorio->set_label(array('IdFuncional', 'Nome', 'CPF', 'Assinatura'));
    #$relatorio->set_width(array(10,30,30,0,10,10,10));
    $relatorio->set_align(array("center", "left", "left", "left"));
    #$relatorio->set_funcao(array(null,null,null,null,null,"date_to_php"));
    #$relatorio->set_classe(array(null,null,"pessoal"));
    #$relatorio->set_metodo(array(null,null,"get_CargoSimples"));

    $relatorio->set_conteudo($result);
    $relatorio->set_espacamento(3);
    $relatorio->show();

    $page->terminaPagina();
}