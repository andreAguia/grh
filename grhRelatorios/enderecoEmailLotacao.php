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

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    ######
    
    $select ='SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbperfil.nome
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa) 
                                LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
               WHERE tbservidor.situacao = 1                 
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores com Endereço, Emails, Telefones e Lotaçao');
    $relatorio->set_subtitulo('Ordenado pelo nome');
    $relatorio->set_label(array('IdFuncional','Nome','Lotação','Endereço','E-mail','Telefones','Perfil'));
    $relatorio->set_bordaInterna(TRUE);
    $relatorio->set_align(array("center","left","left","left","left","left"));
    $relatorio->set_funcao(array(NULL,NULL,NULL,"primeiraLetraMaiuscula"));
    
    $relatorio->set_classe(array(NULL,NULL,"pessoal","pessoal","pessoal","pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,"get_lotacaoRel","get_endereco","get_emails","get_telefones"));
    
    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}