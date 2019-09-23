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
                     tbhistcessao.orgao,
                     tbhistcessao.dtInicio,
                     tbhistcessao.dtFim
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                               RIGHT JOIN tbhistcessao ON(tbservidor.idServidor = tbhistcessao.idServidor)
                                LEFT JOIN tbcargo USING (idCargo)
                                     JOIN tbtipocargo USING (idTipoCargo) 
               WHERE tbservidor.idPerfil = 1
               AND tbtipocargo.tipo = "Adm/Tec"
                 AND situacao = 1 
                 AND ((tbhistcessao.dtFim is NULL) OR (tbhistcessao.dtFim > CURDATE()))
             ORDER BY nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Estatutários Administrativos e Técnicos Cedidos');
    $relatorio->set_subtitulo('Ordenados por Nome');

    $relatorio->set_label(array('IdFuncional','Nome','Cargo','Órgão','Início','Término'));
    $relatorio->set_width(array(10,30,20,20,10,10));
    $relatorio->set_align(array("center","left","left","left"));
    $relatorio->set_funcao(array(NULL,NULL,NULL,NULL,"date_to_php","date_to_php"));
    $relatorio->set_classe(array(NULL,NULL,"Pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,"get_Cargo"));  

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(5);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}
?>
