<?php
/**
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
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    ######
    
    $select = 'select tbservidor.idFuncional,
                      tbpessoa.nome,
                      processo,
                      dtInicioPeriodo,
		      dtFimPeriodo,		      
		      dtPublicacao,
                      tbpublicacaoPremio.idServidor,
                      tbpublicacaoPremio.idServidor
                 from tbpublicacaoPremio join tbservidor on (tbpublicacaoPremio.idServidor = tbservidor.idServidor) 
                                         join tbpessoa on (tbpessoa.idPessoa = tbservidor.idpessoa)
                 where situacao = 1                        
             order by 2';

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Licença Prêmio');
    #$relatorio->set_subtitulo('Ordem de Data Inicial da Licença');

    $relatorio->set_label(array('IdFuncional','Nome','Processo','P.Aquisitivo (Início)','P.Aquisitivo (Fim)','Data Publicação','Dias Fruídos','Dispon'));
    $relatorio->set_width(array(10,34,20,10,10,8,8));
    $relatorio->set_align(array('center','left'));
    $relatorio->set_funcao(array(NULL,NULL,NULL,"date_to_php","date_to_php","date_to_php"));
    $relatorio->set_classe(array(NULL,NULL,NULL,NULL,NULL,NULL,'Pessoal','Pessoal'));
    $relatorio->set_metodo(array(NULL,NULL,NULL,NULL,NULL,NULL,'get_licencaPremioNumDiasFruidasPorId','get_licencaPremioNumDiasDisponiveisPorId'));

    $relatorio->set_conteudo($result);
    $relatorio->set_botaoVoltar(FALSE);
    
    
    $relatorio->show();

    $page->terminaPagina();
}