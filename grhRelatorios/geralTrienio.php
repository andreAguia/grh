<?php
/**
 * Sistema GRH
 * 
 * Relatório de Triênio
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
    
    $select ='SELECT tbservidor.idfuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor					
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)								   	    
               WHERE tbservidor.situacao = 1
                 AND idperfil = 1
            ORDER BY nome';


    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral de Triênio');
    $relatorio->set_subtitulo('Ordenado por Nome do Servidor');

    $relatorio->set_label(array('Id Funcional','Nome','Salário','Triênio','%','a Partir de','Período Aquisitivo','Próximo Triênio','Processo','Publicação'));
    $relatorio->set_width(array(5,20,10,10,5,10,10,10,10,10));
    $relatorio->set_align(array("center","left"));
    $relatorio->set_funcao(array(NULL,NULL,'formataMoeda','formataMoeda'));
    
    $relatorio->set_classe(array(NULL,NULL,"pessoal","pessoal","pessoal","pessoal","pessoal","pessoal","pessoal","pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,"get_salarioBase","get_trienioValor","get_trienioPercentual","get_trienioDataInicial","get_trienioPeriodoAquisitivo","get_trienioDataProximoTrienio","get_trienioNumProcesso","get_trienioPublicacao"));
        
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(3);
    $relatorio->set_botaoVoltar(NULL);
    $relatorio->show();

    $page->terminaPagina();
}