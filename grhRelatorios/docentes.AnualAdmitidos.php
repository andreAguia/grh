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
    $relatorioAno = post('ano',date('Y'));

    ######

    $select = 'SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      tbservidor.idServidor,
                      tbperfil.nome,
                      tbservidor.dtAdmissao,
                      tbservidor.dtDemissao,
                      MONTH(tbservidor.dtAdmissao)
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)                                
                                    LEFT JOIN tbperfil ON(tbservidor.idPerfil = tbperfil.idPerfil)
                WHERE YEAR(tbservidor.dtAdmissao) = "'.$relatorioAno.'"
                  AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                  AND (tbservidor.idCargo = 128 OR tbservidor.idCargo = 129)
             ORDER BY MONTH(tbservidor.dtAdmissao), dtadmissao';		


    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Docentes Admitidos em '.$relatorioAno);
    $relatorio->set_subtitulo('Ordenado pela Data de Admissão');

    $relatorio->set_label(array('IdFuncional','Nome','Cargo','Lotação','Perfil','Admissão','Demissão','Mês'));
    #$relatorio->set_width(array(10,20,10,10,10,10,10,10,10));
    $relatorio->set_align(array('center','left','left','left'));
    $relatorio->set_funcao(array(NULL,NULL,NULL,NULL,NULL,"date_to_php","date_to_php","get_NomeMes"));
    
    $relatorio->set_classe(array(NULL,NULL,"pessoal","pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,"get_cargoSimples","get_lotacao"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(7);
    $relatorio->set_botaoVoltar(FALSE);
    $relatorio->set_formCampos(array(
                  array ('nome' => 'ano',
                         'label' => 'Ano:',
                         'tipo' => 'texto',
                         'size' => 4,
                         'title' => 'Ano',
                         'onChange' => 'formPadrao.submit();',
                         'padrao' => $relatorioAno,
                         'col' => 3,
                         'linha' => 1)));

    $relatorio->set_formFocus('ano');		
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}
?>
