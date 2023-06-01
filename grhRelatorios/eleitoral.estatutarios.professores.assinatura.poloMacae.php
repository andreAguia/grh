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
                      tbdocumentacao.cpf,                    
                      "_________________________________________"
                 FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                      JOIN tbhistlot USING (idServidor)
                                      JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                 LEFT JOIN tbcargo USING (idCargo)
                                      JOIN tbtipocargo USING (idTipoCargo) 
                                      JOIN tbdocumentacao using (idPessoa)
               WHERE tbservidor.situacao = 1
                 AND idPerfil = 1
                 AND tbtipocargo.tipo = "Professor"
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                 AND tblotacao.idCampus = 2
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);
    
    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores Estatutários Ativos');
    $relatorio->set_tituloLinha2("Professores<br/>Polo Macaé");
    $relatorio->set_subtitulo('Ordenados pelo Nome');
    
    $relatorio->set_label(['IdFuncional', 'Nome', 'CPF', 'Assinatura']);
    $relatorio->set_align(["center", "left", "left", "left"]);
    $relatorio->set_conteudo($result);
    $relatorio->set_espacamento(3);
    $relatorio->show();

    $page->terminaPagina();
}