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
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();
    
    # Pega os parâmetros dos relatórios
    $parametroSexo = get_session('parametroSexo',"Feminino");

    ######

    # Monta o select
    $select ='SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
               WHERE tbservidor.situacao = 1
                 AND idPerfil = 1
                 AND tbpessoa.sexo = "'.$parametroSexo.'"
            ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Estatutários Ativos com Previsão para Aposentadoria - Sexo: '.$parametroSexo);
    $relatorio->set_subtitulo('Servidores do Sexo '.$parametroSexo);
    $relatorio->set_label(array('IdFuncional','Nome','Cargo','Integral','Proporcional','Compulsória'));
    #$tabela->set_width(array(30,15,15,15,15));
    $relatorio->set_align(array("center","left","left"));
    $relatorio->set_funcaoDepoisClasse(array(NULL,NULL,NULL,"marcaSePassou","marcaSePassou","marcaSePassou"));

    $relatorio->set_classe(array(NULL,NULL,"pessoal","Aposentadoria","Aposentadoria","Aposentadoria"));
    $relatorio->set_metodo(array(NULL,NULL,"get_CargoSimples","get_dataAposentadoriaIntegral","get_dataAposentadoriaProporcional","get_dataAposentadoriaCompulsoria"));

    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}
?>
