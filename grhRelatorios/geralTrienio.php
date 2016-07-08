<?php
/**
 * Sistema GRH
 * 
 * Relatório de Triênio
 *   
 * By Alat
 */

# Servidor logado 
$idUsuario = null;

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
    
    $select ='SELECT tbfuncionario.idfuncional,
                     tbpessoa.nome,
                     tbfuncionario.matricula,
                     tbfuncionario.matricula,
                     tbfuncionario.matricula,
                     tbfuncionario.matricula,
                     tbfuncionario.matricula,
                     tbfuncionario.matricula,
                     tbfuncionario.matricula,
                     tbfuncionario.matricula					
                FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)								   	    
               WHERE tbfuncionario.Sit = 1
                 AND idperfil = 1
            ORDER BY nome';


    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral de Triênio');
    $relatorio->set_subtitulo('Ordenado por Nome do Servidor');

    $relatorio->set_label(array('Id','Nome','Salário','Triênio','%','a Partir de','Período Aquisitivo','Próximo Triênio','Processo','Publicação'));
    $relatorio->set_width(array(5,20,10,10,5,10,10,10,10,10));
    $relatorio->set_align(array("center","left"));
    $relatorio->set_funcao(array(null,null,'formataMoeda','formataMoeda'));
    
    $relatorio->set_classe(array(null,null,"pessoal","pessoal","pessoal","pessoal","pessoal","pessoal","pessoal","pessoal"));
    $relatorio->set_metodo(array(null,null,"get_salarioBase","get_trienioValor","get_trienioPercentual","get_trienioDataInicial","get_trienioPeriodoAquisitivo","get_trienioDataProximoTrienio","get_trienioNumProcesso","get_trienioPublicacao"));
        
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(3);
    $relatorio->set_botaoVoltar(null);
    $relatorio->show();

    $page->terminaPagina();
}