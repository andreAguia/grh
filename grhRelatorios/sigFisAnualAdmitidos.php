<?php
/**
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
    $relatorioAno = post('ano',date('Y'));

    ######

    $select = 'SELECT tbfuncionario.matricula,
                      tbfuncionario.idfuncional,
                      tbpessoa.nome,
                      tbdocumentacao.cpf,
                      tbpessoa.dtNasc,
                      tbfuncionario.matricula,
                      tbperfil.nome,
                      tbfuncionario.dtAdmissao,
                      tbfuncionario.dtDemissao,
                      tbfuncionario.dtPublicAdm,
                      MONTH(tbfuncionario.dtAdmissao)
                 FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)                                
                                    LEFT JOIN tbperfil ON(tbfuncionario.idPerfil = tbperfil.idPerfil)
                                    LEFT JOIN tbdocumentacao ON (tbpessoa.idPessoa = tbdocumentacao.idPessoa)
                WHERE YEAR(tbfuncionario.dtAdmissao) = "'.$relatorioAno.'"
             ORDER BY MONTH(tbfuncionario.dtAdmissao), dtadmissao';		


    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Servidores Admitidos em '.$relatorioAno);
    $relatorio->set_subtitulo('Ordenado pela Data de Admissão');

    $relatorio->set_label(array('Matrícula','Id','Nome','CPF','Nascimento','Cargo','Perfil','Admissão','Demissão','Publicação','Mês'));
    $relatorio->set_width(array(7,5,18,10,10,20,8,10,10,10));
    $relatorio->set_align(array('center','center','left'));
    $relatorio->set_funcao(array('dv',null,null,null,"date_to_php",null,null,"date_to_php","date_to_php","date_to_php","get_NomeMes"));
    
    $relatorio->set_classe(array(null,null,null,null,null,"pessoal"));
    $relatorio->set_metodo(array(null,null,null,null,null,"get_cargo"));

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(10);
    $relatorio->set_botaoVoltar(false);
    #$relatorio->set_zebrado(false);
    #$relatorio->set_bordaInterna(true);
    #$relatorio->set_cabecalho(false);
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
