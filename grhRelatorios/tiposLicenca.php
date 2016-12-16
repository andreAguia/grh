<?php
/**
 * Sistema GRH
 * 
 * Relatório
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
    
    # Pega os parâmetros dos relatórios
    $lotacao = get('lotacao',post('lotacao'));

    ######
    
    $select ='SELECT idTpLicenca,
                    nome,
                    periodo,
                    pericia,
                    publicacao,
                    processo,                                  
                    dtPeriodo,
                    limite_sexo,
                    idTpLicenca
               FROM tbtipolicenca
           ORDER BY 1';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Tipos de Afastamentos e Licenças');
    $relatorio->set_bordaInterna(TRUE);
    $relatorio->set_label(array("id","Licença","Período</br>(em dias)","Perícia","Publicação","Processo","Período Aquisitivo","Gênero"));
    $relatorio->set_width(array(5,35,10,10,10,10,10,10));
    $relatorio->set_align(array("center","left"));
    $relatorio->set_conteudo($result);
    $relatorio->set_subTotal(FALSE);
    $relatorio->show();

    $page->terminaPagina();
}