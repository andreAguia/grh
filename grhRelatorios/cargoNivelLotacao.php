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
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    ######
   
    # Pega as diretorias ativas
    $select1 ='SELECT DISTINCT dir
                FROM tblotacao
               WHERE ativo';

    $diretorias = $pessoal->select($select1);
    
    $select2 = 'SELECT idtipocargo, sigla
                FROM tbtipocargo
            ORDER BY idTipoCargo';

    $cargos = $pessoal->select($select2);
    
    $resultado = array();
    $label = array("Diretoria");
    foreach($cargos as $cc){
        $label[]=$cc[1];
    }
    $linha = 0;
    $label[] = "Total";
    
    
    foreach($diretorias as $dd){
        $resultado[$linha][0] = $dd[0];
        $coluna = 1;
        
        # Linha - cada diretoria
        $totalLinha = 0;
        foreach($cargos as $cc){
            $quantidade = $pessoal->get_numServidoresAtivosCargoLotacao($cc[0], $dd[0]);
            $resultado[$linha][$coluna] = $quantidade;
            $totalLinha = $totalLinha + $quantidade;
            $coluna++;
        }
        $resultado[$linha][$coluna] = $totalLinha;
        $linha++;
    }
    
    $resultado[$linha][0] = "Total";
    foreach($cargos as $cc){
        $quantidade = $pessoal->get_numServidoresAtivosCargoLotacao($cc[0], $dd[0]);
        $resultado[$linha][$coluna] = $quantidade;
        $totalLinha = $totalLinha + $quantidade;
        $coluna++;
    }
    

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Numero de Servidores por Diretoria / Cargo');

    $relatorio->set_label($label);
    #$relatorio->set_width(array(0,40,60));
    $relatorio->set_align(array("left"));

    $relatorio->set_conteudo($resultado);
    #$relatorio->set_numGrupo(0);
    $relatorio->show();

    $page->terminaPagina();
}