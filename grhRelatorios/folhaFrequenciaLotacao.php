<?php

/**
 * Sistema GRH
 * 
 * Folha de Presença
 *   
 * By Alat
 */
# Servidor logado 
$idUsuario = null;

# Configuração
include ("../grhSistema/_config.php");

# Conecta ao Banco de Dados    
$pessoal = new Pessoal();

# Pega os parâmetros dos relatórios
$anoBase = get('anoBase', date('Y'));
$trimestre = get('trimestre', 1);
$lotacao = get('lotacao', $pessoal->get_idLotacao($intra->get_idServidor($idUsuario)));

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario);

if ($acesso) {

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Limita a página
    $grid = new Grid();
    $grid->abreColuna(12);

    ######
    # Corpo do relatorio        
    $select = 'SELECT tbservidor.idServidor
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                      JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                      JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)						   	    
               WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                 AND situacao = 1';

    # lotacao
    if (!is_null($lotacao)) {
        # Verifica se o que veio é numérico
        if (is_numeric($lotacao)) {
            $select .= ' AND (tblotacao.idlotacao =  "' . $lotacao . '")';
        } else { # senão é uma diretoria genérica
            $select .= ' AND (tblotacao.DIR = "' . $lotacao . '")';
        }
    }

    $select .= ' ORDER BY lotacao, tbpessoa.nome';
    $result = $pessoal->select($select);

    $folha = new FolhaFrequencia();
    $contador = 0;

    # Imprime cada servidor retornado
    foreach ($result as $item) {
        if ($contador == 0) {
            $folha->exibeFolha($item[0], $anoBase, $trimestre, $idUsuario, true);
        } else {
            $folha->exibeFolha($item[0], $anoBase, $trimestre, $idUsuario, false);
        }
        $contador++;
    }

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
}