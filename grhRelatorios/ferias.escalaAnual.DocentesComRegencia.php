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
    
    # Pega o ano exercicio
    $parametroAno = post("parametroAno",date('Y'));
    
    ######
    
     $select ='SELECT distinct tbservidor.idfuncional,
                     tbpessoa.nome,
                     concat(IFNULL(tblotacao.UADM,"")," - ",IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")) lotacao,
                     tbservidor.dtAdmissao,
                     concat(tbservidor.idServidor,"&",'.$parametroAno.'),
                     "___ /___ /____  (_____)",
                     concat(tbservidor.idServidor,"&",'.$parametroAno.')
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     JOIN tbhistlot USING (idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                     JOIN tbcargo USING (idCargo)
                                     JOIN tbtipocargo USING (idTipoCargo)
                                LEFT JOIN tbcomissao USING (idServidor)
               WHERE tbservidor.situacao = 1
                 AND idPerfil = 1
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                 AND tbtipocargo.tipo = "Professor"
                 AND CURRENT_DATE > tbcomissao.dtExo
                 AND tbservidor.idServidor NOT IN(SELECT tbservidor.idServidor
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
                                                     AND ((CURRENT_DATE BETWEEN tbcomissao.dtNom AND tbcomissao.dtExo) OR (tbcomissao.dtExo is NULL)))
            ORDER BY 3,tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Escala Anual de Férias de Docentes com Regencia de Turma - Ano Exercicio: '.$parametroAno);
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
    $relatorio->set_formCampos(array(
                               array ('nome' => 'parametroAno',
                                      'label' => 'Ano:',
                                      'tipo' => 'texto',
                                      'size' => 10,
                                      'padrao' => $parametroAno,
                                      'title' => 'Ano',
                                      'onChange' => 'formPadrao.submit();',
                                      'col' => 3,
                                      'linha' => 1)));

    $relatorio->set_formFocus('mesBase');
    $relatorio->set_formLink('?');    
    $relatorio->show();

    $page->terminaPagina();
}
