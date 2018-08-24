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

if($acesso){    
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();
    
    # Pega o ano exercicio quando vem da área de férias
    $anoBase = get("parametroAnoExercicio",date('Y'));
    
    ######
    
     $select ='SELECT tbservidor.idfuncional,
                     tbpessoa.nome,
                     concat(IFNULL(tblotacao.UADM,"")," - ",IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")) lotacao,
                     tbservidor.dtAdmissao,
                     concat(tbservidor.idServidor,"&",'.$anoBase.'),
                     "___ /___ /____  (_____)",
                      tbservidor.idServidor
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     JOIN tbhistlot USING (idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                     JOIN tbcargo USING (idCargo)
                                     JOIN tbtipocargo USING (idTipoCargo)
                                     JOIN tbcomissao USING (idServidor)
               WHERE tbservidor.situacao = 1
                 AND idPerfil = 1
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                 AND tbtipocargo.tipo = "Professor"
                 AND ((CURRENT_DATE BETWEEN tbcomissao.dtNom AND tbcomissao.dtExo) OR (tbcomissao.dtExo is NULL)) 
            ORDER BY 3,tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Escala Anual de Férias de Docentes Estatutarios com Cargo de Comissao - Ano Exercicio: '.$anoBase);
    #$relatorio->set_tituloLinha2('Ano Exercicio:'.$anoBase);

    $relatorio->set_label(['IdFuncional','Nome','Lotação','Admissão','Prazo para o Gozo','Início Previsto (Dias)','Observação']);
    $relatorio->set_width([10,25,0,10,15,15,30]);
    $relatorio->set_align(["center","left"]);
    $relatorio->set_funcao([NULL,NULL,NULL,"date_to_php","exibePrazoParaGozoEscalaFerias",NULL,"exibeFeriasPendentes"]);
    #$relatorio->set_classe(array(NULL,NULL,NULL,NULL,NULL,NULL,"pessoal"));
    #$relatorio->set_metodo(array(NULL,NULL,NULL,NULL,NULL,NULL,"get_feriasPeriodo"));
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(2);
    $relatorio->set_saltoAposGrupo(TRUE);
    $relatorio->set_bordaInterna(TRUE);
    $relatorio->set_subTotal(FALSE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_dataImpressao(FALSE);
    $relatorio->set_funcaoFinalGrupo("textoEscalaFerias");
    $relatorio->set_funcaoFinalGrupoParametro(NULL);
    $relatorio->show();

    $page->terminaPagina();
}
