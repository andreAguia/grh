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
    $intra = new Intra();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    ######
    
    # Pega os valores da aposentadoria   
    $anosAposentadoriaMasculino = $intra->get_variavel("aposentadoria.integral.idade.masculino");
    $anosAposentadoriaFeminino = $intra->get_variavel("aposentadoria.integral.idade.feminino");
    
    $select ='(SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.dtAdmissao,
                     tbpessoa.dtNasc,
                     YEAR(CURRENT_DATE) - YEAR(tbpessoa.dtNasc) - (DATE_FORMAT(CURRENT_DATE, "%m%d") < DATE_FORMAT(tbpessoa.dtNasc, "%m%d")) as idade
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
               WHERE tbservidor.situacao = 1
                 AND idPerfil = 1
                 AND tbpessoa.sexo = "Masculino"
                 AND YEAR(CURRENT_DATE) - YEAR(tbpessoa.dtNasc) - (DATE_FORMAT(CURRENT_DATE, "%m%d") < DATE_FORMAT(tbpessoa.dtNasc, "%m%d")) >= '.$anosAposentadoriaMasculino.')
                     UNION 
            (SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.dtAdmissao,
                     tbpessoa.dtNasc,
                     YEAR(CURRENT_DATE) - YEAR(tbpessoa.dtNasc) - (DATE_FORMAT(CURRENT_DATE, "%m%d") < DATE_FORMAT(tbpessoa.dtNasc, "%m%d")) as idade                     
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
               WHERE tbservidor.situacao = 1
                 AND idPerfil = 1
                 AND tbpessoa.sexo = "Feminino"
                 AND YEAR(CURRENT_DATE) - YEAR(tbpessoa.dtNasc) - (DATE_FORMAT(CURRENT_DATE, "%m%d") < DATE_FORMAT(tbpessoa.dtNasc, "%m%d")) >= '.$anosAposentadoriaFeminino.')
ORDER BY 6';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Estatutários Que Atingiram Idade para Aposentadoria');
    $relatorio->set_label(array('IdFuncional','Nome','Cargo','Lotaçao','Admissão','Nascimento','Idade'));
    #$relatorio->set_width(array(10,30,30,0,10,10,10));
    $relatorio->set_align(array("center","left","left","left"));
    $relatorio->set_funcao(array(NULL,NULL,NULL,NULL,"date_to_php","date_to_php"));
    
    $relatorio->set_classe(array(NULL,NULL,"pessoal","pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,"get_CargoRel","get_LotacaoRel"));
    
    $relatorio->set_conteudo($result);        
    $relatorio->show();

    $page->terminaPagina();
}