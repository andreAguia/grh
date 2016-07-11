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
    $anoBase = post('anoBase',date('Y'));

    ######

    $select = 'SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbperfil.nome,
                      tbfaltas.data,
                      tbfaltas.numDias,
                      ADDDATE(data,numDias-1)
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                    LEFT JOIN tbfaltas ON (tbservidor.idServidor = tbfaltas.idServidor)                                
                                    LEFT JOIN tbperfil ON(tbservidor.idPerfil=tbperfil.idPerfil)
                WHERE tbservidor.situacao = 1
                  AND ((YEAR(tbfaltas.data)='.$anoBase.') OR (YEAR(ADDDATE(tbfaltas.data,tbfaltas.numDias-1))='.$anoBase.'))
             ORDER BY data desc';		

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Faltas de Servidores');
    $relatorio->set_tituloLinha2($anoBase);
    $relatorio->set_subtitulo('Ordem Decrescente de Data Inicial da Falta');

    $relatorio->set_label(array('IdFuncional','Nome','Perfil','Data Inicial','Dias','Data Final'));
    $relatorio->set_width(array(10,40,20,10,10,10));
    $relatorio->set_align(array("center","left"));
    $relatorio->set_funcao(array(null,null,null,"date_to_php",null,"date_to_php"));

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(2);
    $relatorio->set_botaoVoltar(false);
    #$relatorio->set_zebrado(false);
    #$relatorio->set_bordaInterna(true);
    #$relatorio->set_cabecalho(false);
    $relatorio->set_formCampos(array(
                  array ('nome' => 'anoBase',
                         'label' => 'Ano:',
                         'tipo' => 'texto',
                         'size' => 4,
                         'title' => 'Ano',
                         'padrao' => $anoBase,
                         'onChange' => 'formPadrao.submit();',
                         'col' => 3,
                         'linha' => 1)));

    $relatorio->set_formFocus('anoBase');		
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}
