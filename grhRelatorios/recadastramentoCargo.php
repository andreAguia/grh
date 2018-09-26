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
$acesso = Verifica::acesso($idUsuario,2);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    ######
    
    $select ='SELECT tbservidor.idFuncional,
                    tbpessoa.nome,
                    tbservidor.idServidor,
                    concat(IFNULL(tblotacao.UADM,"")," - ",IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) lotacao,
                    tbrecadastramento.dataAtualizacao,
                    tbtipocargo.tipo
               FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                               LEFT JOIN tbrecadastramento USING (idServidor)
                               LEFT JOIN tbperfil USING (idPerfil)
                               JOIN tbhistlot USING (idServidor)
                               JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                               JOIN tbcargo USING (idCargo)
                               JOIN tbtipocargo USING (idTipoCargo)
             WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
               AND tbservidor.situacao = 1
               AND tbrecadastramento.dataAtualizacao is NOT NULL
               ORDER BY tbtipocargo.nivel asc, tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();            
            
    $relatorio->set_titulo('Relatório De Servidores Ativos Recadastrados');
    $relatorio->set_subtitulo('Agrupada por Tipo de Cargo - Ordenados pelo Nome');
    $relatorio->set_label(array('IdFuncional','Nome','Cargo','Lotação','Atualizado em:','Tipo'));
    $relatorio->set_align(array("center","left","left","left"));
    $relatorio->set_funcao(array(NULL,NULL,NULL,NULL,"date_to_php"));
    
    $relatorio->set_classe(array(NULL,NULL,"pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,"get_CargoRel"));
    
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(5);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}