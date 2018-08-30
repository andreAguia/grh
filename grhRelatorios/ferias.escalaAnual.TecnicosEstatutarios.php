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
    
    $select ='SELECT tbservidor.idfuncional,
                     tbpessoa.nome,
                     concat(IFNULL(tblotacao.UADM,"")," - ",IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")) lotacao,
                     tbservidor.dtAdmissao,
                     concat(tbservidor.idServidor,"&",'.$parametroAno.'),
                     "___/___/___ (___)&nbsp;&nbsp;&nbsp;___/___/___ (___)&nbsp;&nbsp;&nbsp;___/___/___ (___)",
                     concat(tbservidor.idServidor,"&",'.$parametroAno.')
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     JOIN tbhistlot USING (idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                     JOIN tbcargo USING (idCargo)
                                     JOIN tbtipocargo USING (idTipoCargo)
               WHERE tbservidor.situacao = 1
                 AND idPerfil = 1
                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                 AND tbtipocargo.tipo = "Adm/Tec"
            ORDER BY 3,tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Escala Anual de Férias dos Técnicos Estatutários - Ano Exercício: '.$parametroAno);
    #$relatorio->set_tituloLinha2('Ano Exercicio:'.$anoBase);

    $relatorio->set_label(['Id','Nome','Lotação','Admissão','Prazo para<br/>o Gozo','Início Previsto (Dias)','Observação']);
    $relatorio->set_width([6,25,0,10,10,35,25]);
    $relatorio->set_align(["center","left","center","center","center","center","right"]);
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
