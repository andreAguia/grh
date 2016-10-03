<?php
/**
 * Relatório
 *    
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null;	# Servidor Editado na pesquisa do sistema do GRH

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    ######
    
    # Dados do Servidor
    $select = 'SELECT tbservidor.idFuncional,
                      tbservidor.matricula,
                      tbpessoa.nome,
                      tbperfil.nome,
                      tbsituacao.situacao 
                 FROM tbservidor left join tbpessoa on (tbservidor.idPessoa = tbpessoa.idPessoa)
                                    left join tbperfil on (tbservidor.idPerfil = tbperfil.idPerfil)
                                    left join tbsituacao on (tbservidor.situacao = tbsituacao.idSituacao)
                WHERE tbservidor.idServidor = '.$idServidorPesquisado;

    $result = $pessoal->select($select);   

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Licenças');
    $relatorio->set_label(array('IdFuncional','Matrícula','Servidor','Perfil','Situação'));
    $relatorio->set_width(array(15,10,40,15,20));
    $relatorio->set_align(array('center'));
    $relatorio->set_conteudo($result);
    $relatorio->set_zebrado(false);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_linhaNomeColuna(false);
    $relatorio->set_brHr(0);
    $relatorio->show();
    
    ######
    br(2);

    $select = "SELECT tbtipolicenca.nome,
                    dtInicial,
                    numdias,
                    ADDDATE(dtInicial,numDias-1),
                    tblicenca.processo,
                    dtInicioPeriodo,
                    dtFimPeriodo,
                    dtPublicacao,
                    pgPublicacao,
                    idLicenca
               FROM tblicenca LEFT JOIN tbtipolicenca ON tblicenca.idTpLicenca = tbtipolicenca.idTpLicenca
              WHERE idServidor=$idServidorPesquisado
           ORDER BY tblicenca.dtInicial";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();   
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_subTotal(false);
    $relatorio->set_label(array("Licença","Inicio","Dias","Término","Processo","Período Aquisitivo Início","Período Aquisitivo Término","Publicação","Pag."));
    $relatorio->set_width(array(23,10,5,10,17,10,10,10,5));
    $relatorio->set_align(array('center'));
    $relatorio->set_funcao(array(null,'date_to_php',null,'date_to_php',null,'date_to_php','date_to_php','date_to_php'));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(false);
    $relatorio->show();

    $page->terminaPagina();
}