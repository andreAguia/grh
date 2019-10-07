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
                     tbperfil.nome,
                     tbservidor.dtAdmissao,
                     tbservidor.idServidor
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                        JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                        JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                                   LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
                                   LEFT JOIN tbcargo USING (idCargo)
                                        JOIN tbtipocargo USING (idTipoCargo) 
               WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                 AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                 AND situacao = 1
                 AND (idLotacao = 56 OR idLotacao = 57 OR idLotacao = 58)
                 AND tbtipocargo.tipo = "Adm/Tec"
            ORDER BY lotacao, tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores Ativos das Pro-Reitorias');
    $relatorio->set_subtitulo('Agrupados por Lotação - Ordenados pelo Nome');
    $relatorio->set_label(array('IdFuncional','Nome','Cargo','Lotação','Perfil','Admissão','Situação'));
    $relatorio->set_width(array(10,30,30,0,10,10,10));
    $relatorio->set_align(array("center","left","left"));
    $relatorio->set_funcao(array(NULL,NULL,NULL,NULL,NULL,"date_to_php"));
    
    $relatorio->set_classe(array(NULL,NULL,"pessoal",NULL,NULL,NULL,"pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,"get_CargoSimples",NULL,NULL,NULL,"get_Situacao"));
    
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(3);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}