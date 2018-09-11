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

if($acesso){    
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    ######
    
    $select = "SELECT idFuncional,
                    tbpessoa.nome,
                    idServidor,
                    idServidor,
                    (SELECT IFNULL(sum(dias),0) FROM tbtrabalhotre  WHERE tbtrabalhotre.idServidor = tbservidor.idServidor) as trabalhados,
                    (SELECT IFNULL(sum(folgas),0) FROM tbtrabalhotre WHERE tbtrabalhotre.idServidor = tbservidor.idServidor) as concedidas,
                    (SELECT IFNULL(sum(dias),0) FROM tbfolga WHERE tbfolga.idServidor = tbservidor.idServidor) as fruidas,
                    (SELECT IFNULL(sum(folgas),0) FROM tbtrabalhotre WHERE tbtrabalhotre.idServidor = tbservidor.idServidor) - (SELECT IFNULL(sum(dias),0) FROM tbfolga WHERE tbfolga.idServidor = tbservidor.idServidor)
               FROM tbservidor JOIN tbpessoa USING (idPessoa)
              WHERE situacao = 1
                AND (SELECT sum(dias) FROM tbtrabalhotre  WHERE tbtrabalhotre.idServidor = tbservidor.idServidor) > 0
           ORDER BY tbpessoa.nome";		


    $result = $pessoal->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral TRE');
    $relatorio->set_subtitulo('Ordenado pelo Nome');

    $relatorio->set_label(array("Id","Nome","Cargo","Lotação","Dias Trabalhados","Folgas Concedidas","Folgas Fruidas","Folgas Pendentes"));
    $relatorio->set_align(array("center","left","left","left"));
    $relatorio->set_classe(array(NULL,NULL,"pessoal","pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,"get_cargo","get_lotacao"));
    
    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}