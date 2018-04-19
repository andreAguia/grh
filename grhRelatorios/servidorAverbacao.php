<?php
/**
 * Relatório
 *    
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = NULL;              # Servidor logado
$idServidorPesquisado = NULL;	# Servidor Editado na pesquisa do sistema do GRH

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso){
    
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    ######
    
    # Dados do Servidor
    Grh::listaDadosServidorRelatorio($idServidorPesquisado,'Histórico de Tempo de Serviço Averbado');
    
    br();
    $select = "SELECT dtInicial,
                        dtFinal,
                        dias,
                        empresa,
                        CASE empresaTipo
                           WHEN 1 THEN 'Pública'
                           WHEN 2 THEN 'Privada'
                        END,
                        CASE regime
                           WHEN 1 THEN 'Celetista'
                           WHEN 2 THEN 'Estatutário'
                        END,
                        CASE cargo
                           WHEN 1 THEN 'Professor'
                           WHEN 2 THEN 'Outros'
                        END,
                        CONCAT(date_format(dtPublicacao,'%d/%m/%Y'),' - Pag ',pgPublicacao),
                        processo,
                        idAverbacao
                   FROM tbaverbacao
                  WHERE idServidor = $idServidorPesquisado
               ORDER BY 1 desc";

    $result = $pessoal->select($select);

    $relatorio = new Relatorio();   
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    $relatorio->set_subTotal(TRUE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_label(array("Data Inicial","Data Final","Dias","Empresa","Tipo","Regime","Cargo","Publicação","Processo"));
    #$relatorio->set_width(array(10,10,10,5,8,10,15));
    $relatorio->set_align(array('center'));
    $relatorio->set_funcao(array("date_to_php","date_to_php"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(FALSE);
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->set_logDetalhe("Visualizou o Relatório de Histórico de Tempo de Serviço Averbado");
    $relatorio->show();

    $page->terminaPagina();
}